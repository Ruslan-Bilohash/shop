<?php
/**
 * Paysera server-to-server callback (production).
 */
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/paysera-gateway.php';
require_once dirname(__DIR__) . '/includes/orders-storage.php';

$settings = sh_load_settings();
$password = (string) ($settings['paysera']['sign_password'] ?? '');
$data = (string) ($_REQUEST['data'] ?? '');
$ss1 = (string) ($_REQUEST['ss1'] ?? '');

if ($data === '' || $password === '' || $ss1 === '' || !hash_equals(md5($data . $password), $ss1)) {
    http_response_code(400);
    echo 'ERROR';
    exit;
}

$decoded = base64_decode(strtr($data, '-_', '+/'), true);
if ($decoded === false) {
    http_response_code(400);
    echo 'ERROR';
    exit;
}
parse_str($decoded, $fields);

$orderId = (string) ($fields['orderid'] ?? '');
$status = (int) ($fields['status'] ?? 0);
if ($orderId !== '' && $status === 1) {
    sh_order_update_payment_status($orderId, 'paid', 'paysera');
}

echo 'OK';