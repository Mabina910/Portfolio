<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/esewa_helper.php';

function syncOrderPaymentStatusByEsewaUuid(mysqli $conn, string $transactionUuid, string $paymentStatus, string $orderStatus): void
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

    $stmt = $conn->prepare('UPDATE payments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $paymentStatus, $paymentId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE payment_id = ?');
    $stmt->bind_param('si', $orderStatus, $paymentId);
    $stmt->execute();
    $stmt->close();
}

$transactionUuid = '';
$status = 'UNKNOWN';
$transactionCode = null;
$message = 'We are verifying your payment response.';
$isSuccess = false;
$httpCode = 200;
$autoRedirectUrl = rtrim((string)(defined('APP_BASE_URL') ? APP_BASE_URL : 'http://localhost/E-commerce'), '/') . '/e-commerce.php';

ensureEsewaTransactionsTable($conn);

$encodedData = $_GET['data'] ?? '';
if ($encodedData === '') {
    $transactionUuid = sanitizeTransactionUuid((string)($_GET['transaction_uuid'] ?? ''));

    if ($transactionUuid === '' && isset($_SESSION['esewa_pending_transaction_uuid'])) {
        $transactionUuid = sanitizeTransactionUuid((string)$_SESSION['esewa_pending_transaction_uuid']);
    }

    if ($transactionUuid === '') {
        $message = 'No payment payload was provided. Please check the latest status.';
    } else {
        $row = fetchEsewaTransaction($conn, $transactionUuid);
        $amountForCheck = 0.0;
        if (is_array($row)) {
            $status = strtoupper((string)($row['status'] ?? 'UNKNOWN'));
            $transactionCode = isset($row['transaction_code']) ? (string)$row['transaction_code'] : null;
            $amountForCheck = (float)($row['amount'] ?? 0.0);
        }

        if (isset($_GET['total_amount'])) {
            $amountForCheck = (float)$_GET['total_amount'];
        }

        if ($amountForCheck > 0) {
            $statusResult = checkEsewaStatusApi(
                ESEWA_STATUS_URL,
                ESEWA_PRODUCT_CODE,
                $transactionUuid,
                $amountForCheck
            );

            if (($statusResult['ok'] ?? false) && isset($statusResult['status'])) {
                $status = strtoupper((string)$statusResult['status']);
                $transactionCode = isset($statusResult['ref_id']) ? (string)$statusResult['ref_id'] : $transactionCode;
                updateEsewaTransactionStatus($conn, $transactionUuid, $status, $transactionCode);
            }
        }

        if ($status === 'COMPLETE') {
            $isSuccess = true;
            unset($_SESSION['esewa_pending_transaction_uuid']);
            $message = 'Payment completed successfully.';
            syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'completed', 'completed');
        } elseif ($status === 'FAILED' || $status === 'CANCELED') {
            $message = 'Payment was not completed.';
            syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'failed', 'failed');
        } else {
            $message = 'Payment is still being processed. Please check latest status.';
        }
    }
} else {
    $decoded = base64_decode((string)$encodedData, true);
    if ($decoded === false) {
        $httpCode = 400;
        $message = 'Invalid payment response format from eSewa.';
    } else {
        $response = json_decode($decoded, true);
        if (!is_array($response)) {
            $httpCode = 400;
            $message = 'Invalid payment response payload from eSewa.';
        } else {
            $signatureOk = verifyEsewaResponse($response, ESEWA_SECRET_KEY);

            logEsewaDebug('success.callback_received', [
                'payload' => $response,
                'signature_verified' => $signatureOk,
            ]);

            if (!$signatureOk) {
                $httpCode = 400;
                $message = 'Signature verification failed. Please contact support if money was deducted.';
            } else {
                $transactionUuid = sanitizeTransactionUuid((string)($response['transaction_uuid'] ?? ''));
                $status = strtoupper((string)($response['status'] ?? 'UNKNOWN'));
                $transactionCode = isset($response['transaction_code']) ? (string)$response['transaction_code'] : null;
                $totalAmount = isset($response['total_amount']) ? (float)$response['total_amount'] : 0.0;

                if ($transactionUuid === '') {
                    $httpCode = 400;
                    $message = 'Missing transaction ID in eSewa response.';
                } else {
                    if ($status === 'COMPLETE') {
                        updateEsewaTransactionStatus($conn, $transactionUuid, 'COMPLETE', $transactionCode);
                        unset($_SESSION['esewa_pending_transaction_uuid']);
                        $isSuccess = true;
                        $message = 'Payment completed successfully.';
                        syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'completed', 'completed');
                    } elseif ($totalAmount > 0) {
                        $statusResult = checkEsewaStatusApi(
                            ESEWA_STATUS_URL,
                            ESEWA_PRODUCT_CODE,
                            $transactionUuid,
                            $totalAmount
                        );

                        if (($statusResult['ok'] ?? false) && isset($statusResult['status'])) {
                            $resolvedStatus = strtoupper((string)$statusResult['status']);
                            $resolvedCode = isset($statusResult['ref_id']) ? (string)$statusResult['ref_id'] : $transactionCode;
                            updateEsewaTransactionStatus($conn, $transactionUuid, $resolvedStatus, $resolvedCode);
                            $status = $resolvedStatus;
                            $transactionCode = $resolvedCode;

                            if ($resolvedStatus === 'COMPLETE') {
                                unset($_SESSION['esewa_pending_transaction_uuid']);
                                $isSuccess = true;
                                $message = 'Payment completed successfully after verification.';
                                syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'completed', 'completed');
                            } else {
                                $message = 'Payment status: ' . $resolvedStatus;
                                if ($resolvedStatus === 'FAILED' || $resolvedStatus === 'CANCELED') {
                                    syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'failed', 'failed');
                                }
                            }
                        } else {
                            updateEsewaTransactionStatus($conn, $transactionUuid, $status, $transactionCode);
                            $message = 'Payment status: ' . $status;
                            if ($status === 'FAILED' || $status === 'CANCELED') {
                                syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'failed', 'failed');
                            }
                        }
                    } else {
                        updateEsewaTransactionStatus($conn, $transactionUuid, $status, $transactionCode);
                        $message = 'Payment status: ' . $status;
                        if ($status === 'FAILED' || $status === 'CANCELED') {
                            syncOrderPaymentStatusByEsewaUuid($conn, $transactionUuid, 'failed', 'failed');
                        }
                    }
                }
            }
        }
    }
}

http_response_code($httpCode);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <style>
        :root {
            --bg: #eef4ec;
            --card: #ffffff;
            --text: #10231a;
            --muted: #4a6257;
            --accent: #1d6f46;
            --danger: #9e2a2b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at top, #f8fbf7 0%, var(--bg) 58%);
            color: var(--text);
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .card {
            width: min(620px, 100%);
            background: var(--card);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 20px 45px rgba(16, 35, 26, 0.12);
            border: 1px solid #d9e5dd;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            background: <?= $isSuccess ? "'#d5f7e6'" : "'#ffe3de'" ?>;
            color: <?= $isSuccess ? "'#0f6a3f'" : "'#922b28'" ?>;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: 30px;
            line-height: 1.2;
        }

        p {
            margin: 8px 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .meta {
            margin-top: 18px;
            padding: 14px;
            border-radius: 10px;
            background: #f5faf7;
            border: 1px solid #d6e5dd;
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
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: #fff;
            color: var(--text);
            border-color: #c7d8cf;
        }

        .hint {
            margin-top: 14px;
            font-size: 13px;
            color: #60786b;
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
        <span class="badge"><?= $isSuccess ? 'PAYMENT SUCCESS' : 'PAYMENT UPDATE' ?></span>
        <h1><?= $isSuccess ? 'Payment completed' : 'Payment could not be confirmed' ?></h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

        <section class="meta">
            <p><strong>Status:</strong> <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($transactionUuid !== ''): ?>
                <p><strong>Transaction UUID:</strong> <?= htmlspecialchars($transactionUuid, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($transactionCode)): ?>
                <p><strong>Reference:</strong> <?= htmlspecialchars((string)$transactionCode, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </section>

        <div class="actions">
            <a id="continueBtn" class="btn btn-primary" href="<?= htmlspecialchars($autoRedirectUrl, ENT_QUOTES, 'UTF-8') ?>">Continue</a>
            <?php if ($transactionUuid !== ''): ?>
                <a class="btn btn-secondary" href="status_check.php?transaction_uuid=<?= urlencode($transactionUuid) ?>">Check latest status</a>
            <?php endif; ?>
        </div>

        <p class="hint">This page will redirect in 7 seconds.</p>
    </main>

    <script>
        const clearCartStorage = function () {
            try {
                sessionStorage.removeItem('cartItems');
            } catch (e) {
                // Ignore browser storage errors.
            }
        };

        <?php if ($isSuccess): ?>
        clearCartStorage();
        <?php endif; ?>

        const continueBtn = document.getElementById('continueBtn');
        if (continueBtn) {
            continueBtn.addEventListener('click', function () {
                <?php if ($isSuccess): ?>
                clearCartStorage();
                <?php endif; ?>
            });
        }

        setTimeout(function () {
            <?php if ($isSuccess): ?>
            clearCartStorage();
            <?php endif; ?>
            window.location.href = <?= json_encode($autoRedirectUrl, JSON_UNESCAPED_SLASHES) ?>;
        }, 7000);
    </script>
</body>
</html>
