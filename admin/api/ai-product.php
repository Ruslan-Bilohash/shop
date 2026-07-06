<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';

sh_admin_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$productName = trim((string) ($payload['product_name'] ?? ''));
$category = trim((string) ($payload['category'] ?? ''));
$sourceLang = trim((string) ($payload['source_lang'] ?? ''));

if ($sourceLang === '' || !array_key_exists($sourceLang, sh_langs())) {
    $settings = sh_load_settings();
    $sourceLang = (string) (sh_ai_settings($settings)['ai_source_lang'] ?? 'en');
}

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
$settings = sh_load_settings();
$result = sh_ai_generate_product($settings, $productName, $category, $sourceLang);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'data'  => $result['data'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);