<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';

sh_admin_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$brandName = trim((string) ($payload['brand_name'] ?? ''));
$countryCode = trim((string) ($payload['country_code'] ?? 'NO'));

if ($brandName === '') {
    sh_json_response(['ok' => false, 'error' => 'Brand name required'], 400);
}

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
$settings = sh_load_settings();
$result = sh_ai_generate_site_seo($settings, $brandName, $countryCode);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'data'  => $result['data'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);