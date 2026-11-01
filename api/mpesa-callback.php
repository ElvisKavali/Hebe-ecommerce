<?php
/**
 * Hebe Store — M-Pesa Callback Endpoint
 * URL: /api/mpesa-callback.php
 *
 * Daraja POSTs a JSON body to this URL when the STK Push resolves.
 * This file must be publicly reachable (no auth middleware).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Order.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Read and decode Daraja's JSON body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// Log raw callback for debugging (production: use a real log library)
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logDir . '/mpesa_callbacks.log', date('c') . ' ' . $raw . "\n", FILE_APPEND);

// Always respond 200 immediately — Daraja retries if it gets anything else
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);

// ── Parse callback ────────────────────────────────────────────────────────────
$body = $data['Body']['stkCallback'] ?? null;
if (!$body) {
    exit;
}

$resultCode       = (int) ($body['ResultCode'] ?? -1);
$checkoutRequestId = $body['CheckoutRequestID'] ?? '';

$pdo = db();

if ($resultCode !== 0) {
    $pdo->prepare("UPDATE payments SET status = 'failed', payload = ? WHERE reference = ?")
        ->execute([$raw, $checkoutRequestId]);
    exit;
}

$meta    = $body['CallbackMetadata']['Item'] ?? [];
$extract = [];
foreach ($meta as $item) {
    $extract[$item['Name']] = $item['Value'] ?? null;
}

$receiptNumber = $extract['MpesaReceiptNumber'] ?? null;
$amount        = $extract['Amount'] ?? 0;
$phone         = $extract['PhoneNumber'] ?? null;

$stmt = $pdo->prepare("UPDATE payments SET status = 'paid', reference = ?, payload = ? WHERE reference = ? OR reference IS NULL")
    ->execute([$receiptNumber ?: $checkoutRequestId, $raw, $checkoutRequestId]);

if ($stmt->rowCount() === 0) {
    error_log('[mpesa-callback] No payment found for CheckoutRequestID: ' . $checkoutRequestId);
    exit;
}

$pdo->prepare("UPDATE orders o INNER JOIN payments p ON p.order_id = o.id SET o.payment_status = 'paid', o.status = 'processing' WHERE p.reference = ?")
    ->execute([$receiptNumber ?: $checkoutRequestId]);
