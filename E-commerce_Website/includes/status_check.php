<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/esewa_helper.php';

ensureEsewaTransactionsTable($conn);

$results = [];
$error = null;

function reconcileOne(mysqli $conn, string $transactionUuid, float $amount): array
{
    $api = checkEsewaStatusApi(
        ESEWA_STATUS_URL,
        ESEWA_PRODUCT_CODE,
        $transactionUuid,
        $amount
    );

    if (!($api['ok'] ?? false)) {
        return [
            'transaction_uuid' => $transactionUuid,
            'amount' => $amount,
            'ok' => false,
            'error' => $api['error'] ?? 'Unknown error',
        ];
    }

    $status = strtoupper((string)($api['status'] ?? 'UNKNOWN'));
    $refId = isset($api['ref_id']) ? (string)$api['ref_id'] : null;

    updateEsewaTransactionStatus($conn, $transactionUuid, $status, $refId);

    return [
        'transaction_uuid' => $transactionUuid,
        'amount' => $amount,
        'ok' => true,
        'status' => $status,
        'transaction_code' => $refId,
        'raw' => $api,
    ];
}

$requestedUuid = isset($_GET['transaction_uuid']) ? sanitizeTransactionUuid((string)$_GET['transaction_uuid']) : '';
$requestedAmount = isset($_GET['total_amount']) ? (float)$_GET['total_amount'] : 0.0;
$runPendingReconcile = isset($_GET['reconcile_pending']);

if ($requestedUuid !== '' && $requestedAmount > 0) {
    $results[] = reconcileOne($conn, $requestedUuid, $requestedAmount);
} elseif ($requestedUuid !== '') {
    $row = fetchEsewaTransaction($conn, $requestedUuid);
    if ($row) {
        $results[] = reconcileOne($conn, $requestedUuid, (float)$row['amount']);
    } else {
        $error = 'Transaction not found.';
    }
} elseif ($runPendingReconcile) {
    $pendingRows = pendingEsewaTransactionsOlderThan($conn, 5);
    foreach ($pendingRows as $row) {
        $results[] = reconcileOne($conn, (string)$row['transaction_uuid'], (float)$row['amount']);
    }
}

$pendingList = pendingEsewaTransactionsOlderThan($conn, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f6f6f6; }
        .ok { color: #1b7a1b; }
        .err { color: #a10f0f; }
    </style>
</head>
<body>
<h1>eSewa Transaction Reconciliation</h1>

<form method="get" action="status_check.php">
    <button type="submit" name="reconcile_pending" value="1">Reconcile Pending &gt; 5 Minutes</button>
</form>

<h2>Check Single Transaction</h2>
<form method="get" action="status_check.php">
    <label>
        Transaction UUID:
        <input type="text" name="transaction_uuid" required>
    </label>
    <label>
        Total Amount (optional):
        <input type="number" step="0.01" name="total_amount">
    </label>
    <button type="submit">Check Now</button>
</form>

<?php if ($error): ?>
    <p class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if (!empty($results)): ?>
    <h2>Reconciliation Results</h2>
    <table>
        <tr>
            <th>Transaction UUID</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Reference</th>
            <th>Outcome</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars((string)$row['transaction_uuid'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(number_format((float)$row['amount'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($row['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($row['transaction_code'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="<?= !empty($row['ok']) ? 'ok' : 'err' ?>">
                    <?= !empty($row['ok']) ? 'Updated' : htmlspecialchars((string)($row['error'] ?? 'Failed'), ENT_QUOTES, 'UTF-8') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h2>Pending Transactions Older Than 5 Minutes</h2>
<table>
    <tr>
        <th>Transaction UUID</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Action</th>
    </tr>
    <?php if (empty($pendingList)): ?>
        <tr><td colspan="5">No pending transactions found.</td></tr>
    <?php else: ?>
        <?php foreach ($pendingList as $row): ?>
            <tr>
                <td><?= htmlspecialchars((string)$row['transaction_uuid'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(number_format((float)$row['amount'], 2, '.', ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="status_check.php?transaction_uuid=<?= urlencode((string)$row['transaction_uuid']) ?>">Check</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>
</body>
</html>
