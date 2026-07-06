<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
require_once dirname(__DIR__, 2) . '/includes/nova-poshta.php';

$settings = sh_load_settings();
$result = sh_nova_poshta_test_connection($settings);

sh_json_response([
    'ok'      => $result['ok'],
    'message' => $result['message'],
], $result['ok'] ? 200 : 400);