<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$type = trim((string) ($payload['type'] ?? 'custom'));
$hint = trim((string) ($payload['hint'] ?? ''));

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
$settings = sh_load_settings();
$result = sh_ai_generate_homepage_block($settings, $type, $hint);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'data'  => $result['data'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);