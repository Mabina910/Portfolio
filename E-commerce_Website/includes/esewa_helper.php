<?php
declare(strict_types=1);

function generateEsewaSignature(string $message, string $secretKey): string
{
    $hash = hash_hmac('sha256', $message, $secretKey, true);
    return base64_encode($hash);
}

// Used when creating the payment
function buildSignedFields(float $totalAmount, string $transactionUuid, string $productCode, string $secretKey): string
{
    $amount = number_format($totalAmount, 2, '.', '');
    $message = "total_amount={$amount},transaction_uuid={$transactionUuid},product_code={$productCode}";
    return generateEsewaSignature($message, $secretKey);
}

// Used when verifying eSewa's callback response
function verifyEsewaResponse(array $data, string $secretKey): bool
{
    if (!isset($data['signed_field_names'], $data['signature'])) {
        return false;
    }

    $fields = explode(',', (string)$data['signed_field_names']);
    $parts = [];

    foreach ($fields as $field) {
        $field = trim($field);
        if ($field === '' || !array_key_exists($field, $data)) {
            return false;
        }
        $parts[] = "{$field}=" . (string)$data[$field];
    }

    $message = implode(',', $parts);
    $expectedSignature = generateEsewaSignature($message, $secretKey);

    return hash_equals($expectedSignature, (string)$data['signature']);
}

function sanitizeTransactionUuid(string $value): string
{
    $clean = preg_replace('/[^A-Za-z0-9-]/', '', $value) ?: '';
    return substr($clean, 0, 64);
}

function createTransactionUuid(): string
{
    $datePart = date('ymd');
    $randomPart = bin2hex(random_bytes(6));
    return sanitizeTransactionUuid($datePart . '-' . $randomPart);
}

function ensureEsewaTransactionsTable(mysqli $conn): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS esewa_transactions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            transaction_uuid VARCHAR(64) NOT NULL UNIQUE,
            amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(32) NOT NULL,
            transaction_code VARCHAR(64) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    $conn->query($sql);
}

function createEsewaTransaction(mysqli $conn, string $transactionUuid, float $amount): bool
{
    $stmt = $conn->prepare(
        'INSERT INTO esewa_transactions (transaction_uuid, amount, status) VALUES (?, ?, ?) '
        . 'ON DUPLICATE KEY UPDATE amount = VALUES(amount), status = VALUES(status), transaction_code = NULL'
    );
    $status = 'PENDING';
    $stmt->bind_param('sds', $transactionUuid, $amount, $status);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function updateEsewaTransactionStatus(
    mysqli $conn,
    string $transactionUuid,
    string $status,
    ?string $transactionCode = null
): bool {
    $stmt = $conn->prepare(
        'UPDATE esewa_transactions SET status = ?, transaction_code = ? WHERE transaction_uuid = ?'
    );
    $stmt->bind_param('sss', $status, $transactionCode, $transactionUuid);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetchEsewaTransaction(mysqli $conn, string $transactionUuid): ?array
{
    $stmt = $conn->prepare(
        'SELECT transaction_uuid, amount, status, transaction_code, created_at, updated_at
         FROM esewa_transactions
         WHERE transaction_uuid = ?
         LIMIT 1'
    );
    $stmt->bind_param('s', $transactionUuid);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function pendingEsewaTransactionsOlderThan(mysqli $conn, int $minutes): array
{
    $stmt = $conn->prepare(
        'SELECT transaction_uuid, amount, status, created_at
         FROM esewa_transactions
         WHERE status = ? AND created_at <= (NOW() - INTERVAL ? MINUTE)
         ORDER BY created_at ASC'
    );
    $pending = 'PENDING';
    $stmt->bind_param('si', $pending, $minutes);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];

    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return $rows;
}

function checkEsewaStatusApi(
    string $statusUrl,
    string $productCode,
    string $transactionUuid,
    float $totalAmount,
    int $timeoutSec = 10
): array {
    $query = http_build_query([
        'product_code' => $productCode,
        'total_amount' => number_format($totalAmount, 2, '.', ''),
        'transaction_uuid' => $transactionUuid,
    ]);

    $url = rtrim($statusUrl, '/') . '/?' . $query;

    $raw = false;
    $code = 0;
    $err = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);

        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'error' => $err ?: 'Status check failed'];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSec,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            return [
                'ok' => false,
                'error' => 'Status check failed: cURL not available and file_get_contents request failed',
            ];
        }

        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\/[0-9.]+\s+(\d+)/i', $headerLine, $m)) {
                    $code = (int)$m[1];
                    break;
                }
            }
        }
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'error' => 'Invalid JSON from eSewa status API',
            'http_code' => $code,
            'raw' => $raw,
        ];
    }

    $decoded['ok'] = true;
    $decoded['http_code'] = $code;
    return $decoded;
}

function logEsewaDebug(string $event, array $context = []): void
{
    $logPath = (string)(getenv('ESEWA_DEBUG_LOG_PATH') ?: 'C:/xampp/secure/esewa-debug.log');
    $dir = dirname($logPath);

    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $entry = [
        'time' => date('c'),
        'event' => $event,
        'context' => $context,
    ];

    @file_put_contents($logPath, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
}
