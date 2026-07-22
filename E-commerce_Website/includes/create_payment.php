<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/esewa_helper.php';

$useSample = isset($_GET['sample']) && $_GET['sample'] === '1';
$useExactSampleUuid = isset($_GET['sample_exact']) && $_GET['sample_exact'] === '1';
$useIntegerAmountFormat = $useSample;

if ($useSample) {
    $amount = 100.00;
    $taxAmount = 10.0;
    $serviceCharge = 0.0;
    $deliveryCharge = 0.0;
} else {
    $amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 100.00;
    $amount = max(0, $amount);

    $taxAmount = isset($_GET['tax_amount']) ? (float)$_GET['tax_amount'] : 0.0;
    $serviceCharge = isset($_GET['product_service_charge']) ? (float)$_GET['product_service_charge'] : 0.0;
    $deliveryCharge = isset($_GET['product_delivery_charge']) ? (float)$_GET['product_delivery_charge'] : 0.0;
}


$totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;
$totalAmount = round($totalAmount, 2);

$formatAmount = static function (float $value) use ($useIntegerAmountFormat): string {
    if ($useIntegerAmountFormat) {
        return (string)(int)round($value, 0);
    }

    return number_format($value, 2, '.', '');
};

$amountStr = $formatAmount($amount);
$taxAmountStr = $formatAmount($taxAmount);
$serviceChargeStr = $formatAmount($serviceCharge);
$deliveryChargeStr = $formatAmount($deliveryCharge);
$totalAmountStr = $formatAmount($totalAmount);

if ($totalAmount <= 0) {
    http_response_code(400);
    echo 'Invalid amount for payment.';
    exit;
}

$transactionUuid = createTransactionUuid();

if (isset($_GET['transaction_uuid']) && $_GET['transaction_uuid'] !== '') {
    $candidate = sanitizeTransactionUuid((string)$_GET['transaction_uuid']);
    if ($candidate !== '') {
        $transactionUuid = $candidate;
    }
}

if ($useSample && isset($_GET['sample_uuid']) && $_GET['sample_uuid'] !== '') {
    $transactionUuid = sanitizeTransactionUuid((string)$_GET['sample_uuid']);
} elseif ($useSample && $useExactSampleUuid) {
    $transactionUuid = '241028';
}

$_SESSION['esewa_pending_transaction_uuid'] = $transactionUuid;

ensureEsewaTransactionsTable($conn);
if (!createEsewaTransaction($conn, $transactionUuid, $totalAmount)) {
    http_response_code(500);
    logEsewaDebug('create_payment.transaction_create_failed', [
        'transaction_uuid' => $transactionUuid,
        'total_amount' => number_format($totalAmount, 2, '.', ''),
    ]);
    echo 'Unable to initialize eSewa transaction. Please try again.';
    exit;
}

if (defined('ESEWA_DEMO_MODE') && ESEWA_DEMO_MODE === true) {
    $demoTransactionCode = 'DEMO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $signedFieldNames = 'total_amount,transaction_uuid,product_code,status,transaction_code';
    $message =
        'total_amount=' . number_format($totalAmount, 2, '.', '')
        . ',transaction_uuid=' . $transactionUuid
        . ',product_code=' . ESEWA_PRODUCT_CODE
        . ',status=COMPLETE'
        . ',transaction_code=' . $demoTransactionCode;

    $payload = [
        'transaction_code' => $demoTransactionCode,
        'status' => 'COMPLETE',
        'total_amount' => number_format($totalAmount, 2, '.', ''),
        'transaction_uuid' => $transactionUuid,
        'product_code' => ESEWA_PRODUCT_CODE,
        'signed_field_names' => $signedFieldNames,
    ];

    $payload['signature'] = generateEsewaSignature($message, ESEWA_SECRET_KEY);
    $encodedData = base64_encode((string)json_encode($payload, JSON_UNESCAPED_SLASHES));

    header('Location: success.php?data=' . urlencode($encodedData));
    exit;
}

$signatureMessage = 'total_amount=' . $totalAmountStr
    . ',transaction_uuid=' . $transactionUuid
    . ',product_code=' . ESEWA_PRODUCT_CODE;

$signature = generateEsewaSignature($signatureMessage, ESEWA_SECRET_KEY);
$returnUrl = SUCCESS_URL;

logEsewaDebug('create_payment.form_built', [
    'mode' => $useSample ? 'sample' : 'normal',
    'form_action' => ESEWA_FORM_URL,
    'amount' => $amountStr,
    'tax_amount' => $taxAmountStr,
    'product_service_charge' => $serviceChargeStr,
    'product_delivery_charge' => $deliveryChargeStr,
    'total_amount' => $totalAmountStr,
    'transaction_uuid' => $transactionUuid,
    'product_code' => ESEWA_PRODUCT_CODE,
    'signed_field_names' => 'total_amount,transaction_uuid,product_code',
    'signature_message' => $signatureMessage,
    'signature' => $signature,
    'success_url' => $returnUrl,
    'failure_url' => $returnUrl,
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to eSewa...</title>
</head>
<body>
<form id="esewaForm" action="<?= htmlspecialchars(ESEWA_FORM_URL, ENT_QUOTES, 'UTF-8') ?>" method="POST">
    <input type="hidden" name="amount" value="<?= htmlspecialchars($amountStr, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="tax_amount" value="<?= htmlspecialchars($taxAmountStr, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="total_amount" value="<?= htmlspecialchars($totalAmountStr, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($transactionUuid, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="product_code" value="<?= htmlspecialchars(ESEWA_PRODUCT_CODE, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="product_service_charge" value="<?= htmlspecialchars($serviceChargeStr, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="product_delivery_charge" value="<?= htmlspecialchars($deliveryChargeStr, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="success_url" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="failure_url" value="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
    <input type="hidden" name="signature" value="<?= htmlspecialchars($signature, ENT_QUOTES, 'UTF-8') ?>">
</form>

<p>Redirecting you to eSewa payment gateway...</p>
<script>
    document.getElementById('esewaForm').submit();
</script>
</body>
</html>
