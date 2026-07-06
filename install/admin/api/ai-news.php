<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';

sh_admin_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$title = trim((string) ($payload['title'] ?? ''));
$slug = trim((string) ($payload['slug'] ?? ''));
$brief = trim((string) ($payload['brief'] ?? ''));
$sourceLang = strtolower(trim((string) ($payload['source_lang'] ?? 'en'))) ?: 'en';

if ($title === '') {
    sh_json_response(['ok' => false, 'error' => 'Article title required'], 400);
}

$settings = sh_load_settings();
$result = sh_ai_generate_news($settings, $title, $slug, $sourceLang, $brief);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'data'  => $result['data'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);