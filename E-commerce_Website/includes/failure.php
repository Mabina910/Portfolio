<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/esewa_helper.php';

function syncFailedByEsewaUuid(mysqli $conn, string $transactionUuid): void
{
    $stmt = $conn->prepare('SELECT id FROM payments WHERE transaction_id = ? LIMIT 1');
    $stmt->bind_param('s', $transactionUuid);
    $stmt->execute();
    $res = $stmt->get_result();
    $payment = $res->fetch_assoc() ?: null;
    $stmt->close();

    if (!$payment) {
        return;
    }

    $paymentId = (int)$payment['id'];

    $failedPayment = 'failed';
    $stmt = $conn->prepare('UPDATE payments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $failedPayment, $paymentId);
    $stmt->execute();
    $stmt->close();

    $failedOrder = 'failed';
    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE payment_id = ?');
    $stmt->bind_param('si', $failedOrder, $paymentId);
    $stmt->execute();
    $stmt->close();
}

$transactionUuid = '';
$currentStatus = 'FAILED';
$transactionCode = null;
$gatewayMessage = null;
$autoRedirectUrl = rtrim((string)(defined('APP_BASE_URL') ? APP_BASE_URL : 'http://localhost/E-commerce'), '/') . '/e-commerce.php';

$encodedData = $_GET['data'] ?? '';
if ($encodedData !== '') {
    $decoded = base64_decode((string)$encodedData, true);
    if ($decoded !== false) {
        $payload = json_decode($decoded, true);
        if (is_array($payload)) {
            logEsewaDebug('failure.callback_received', [
                'payload' => $payload,
            ]);

            $payloadUuid = sanitizeTransactionUuid((string)($payload['transaction_uuid'] ?? ''));
            if ($payloadUuid !== '') {
                $transactionUuid = $payloadUuid;
            }

            if (!empty($payload['status'])) {
                $currentStatus = strtoupper((string)$payload['status']);
            }

            if (!empty($payload['transaction_code'])) {
                $transactionCode = (string)$payload['transaction_code'];
            }

            if (!empty($payload['message'])) {
                $gatewayMessage = (string)$payload['message'];
            }
        }
    }
}

if (isset($_SESSION['esewa_pending_transaction_uuid'])) {
    if ($transactionUuid === '') {
        $transactionUuid = sanitizeTransactionUuid((string)$_SESSION['esewa_pending_transaction_uuid']);
    }
}

if ($transactionUuid !== '') {
    ensureEsewaTransactionsTable($conn);
    updateEsewaTransactionStatus($conn, $transactionUuid, $currentStatus, $transactionCode);
    syncFailedByEsewaUuid($conn, $transactionUuid);

    $row = fetchEsewaTransaction($conn, $transactionUuid);
    if (is_array($row)) {
        $currentStatus = (string)($row['status'] ?? 'FAILED');
        $transactionCode = $row['transaction_code'] ?? null;
    }

    unset($_SESSION['esewa_pending_transaction_uuid']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        :root {
            --bg: #fbf1ef;
            --card: #ffffff;
            --text: #261110;
            --muted: #6f4948;
            --danger: #b23a2f;
            --safe: #2f7b52;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at top, #fff7f4 0%, var(--bg) 60%);
            display: grid;
            place-items: center;
            color: var(--text);
            padding: 20px;
        }

        .card {
            width: min(620px, 100%);
            background: var(--card);
            border-radius: 16px;
            padding: 28px;
            border: 1px solid #f0d2cc;
            box-shadow: 0 18px 44px rgba(38, 17, 16, 0.1);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            background: #ffe3de;
            color: #8d2f2d;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: 30px;
            line-height: 1.2;
        }

        p {
            margin: 8px 0;
            line-height: 1.6;
            color: var(--muted);
        }

        .meta {
            margin-top: 18px;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #f1d9d4;
            background: #fff7f5;
        }

        .meta strong { color: var(--text); }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--danger);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            color: var(--text);
            border-color: #e5c6bf;
        }

        .btn-safe {
            background: #eaf7ef;
            color: var(--safe);
            border-color: #cfe6d9;
        }

        .hint {
            margin-top: 14px;
            font-size: 13px;
            color: #876260;
        }

        @media (max-width: 560px) {
            h1 { font-size: 24px; }
            .card { padding: 20px; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">PAYMENT NOT COMPLETED</span>
        <h1>Payment did not complete</h1>
        <p>Please retry the payment or choose another payment method.</p>

        <section class="meta">
            <?php if ($transactionUuid !== ''): ?>
                <p><strong>Transaction UUID:</strong> <?= htmlspecialchars($transactionUuid, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <p><strong>Current status:</strong> <?= htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($gatewayMessage)): ?>
                <p><strong>Gateway message:</strong> <?= htmlspecialchars($gatewayMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($transactionCode)): ?>
                <p><strong>Reference:</strong> <?= htmlspecialchars((string)$transactionCode, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </section>

        <div class="actions">
            <a class="btn btn-primary" href="<?= htmlspecialchars($autoRedirectUrl, ENT_QUOTES, 'UTF-8') ?>">Try payment again</a>
            <?php if ($transactionUuid !== ''): ?>
                <a class="btn btn-safe" href="status_check.php?transaction_uuid=<?= urlencode($transactionUuid) ?>">Check latest status</a>
            <?php endif; ?>
            <a class="btn btn-secondary" href="e-commerce.php">Continue shopping</a>
        </div>

        <p class="hint">This page will redirect to checkout in 8 seconds.</p>
    </main>

    <script>
        setTimeout(function () {
            window.location.href = <?= json_encode($autoRedirectUrl, JSON_UNESCAPED_SLASHES) ?>;
        }, 8000);
    </script>
</body>
</html>
