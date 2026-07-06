<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$payload = $_POST;
if ($payload === [] || !isset($payload['prompt'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$prompt = trim((string) ($payload['prompt'] ?? ''));
if ($prompt === '') {
    sh_json_response(['ok' => false, 'error' => 'Prompt required'], 400);
}

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
$settings = sh_load_settings();
$result = sh_ai_generate_block_template($settings, $prompt);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'data'  => $result['data'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);