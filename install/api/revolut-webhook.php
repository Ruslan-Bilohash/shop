<?php
/**
 * Revolut Merchant webhook (production).
 */
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/orders-storage.php';

$settings = sh_load_settings();
$secret = trim((string) ($settings['revolut']['webhook_secret'] ?? ''));
$payload = file_get_contents('php://input');
$signature = (string) ($_SERVER['HTTP_REVOLUT_SIGNATURE'] ?? $_SERVER['HTTP_X_REVOLUT_SIGNATURE'] ?? '');

if ($secret !== '' && $payload !== '') {
    $expected = hash_hmac('sha256', $payload, $secret);
    if ($signature === '' || !hash_equals($expected, $signature)) {
        http_response_code(401);
        exit;
    }
}

$data = json_decode((string) $payload, true);
if (!is_array($data)) {
    http_response_code(400);
    exit;
}

$event = (string) ($data['event'] ?? '');
$orderRef = (string) ($data['merchant_order_ext_ref'] ?? $data['order_id'] ?? '');
if ($orderRef !== '' && in_array($event, ['ORDER_COMPLETED', 'ORDER_AUTHORISED'], true)) {
    sh_order_update_payment_status($orderRef, 'paid', 'revolut');
}

http_response_code(200);
echo json_encode(['ok' => true]);