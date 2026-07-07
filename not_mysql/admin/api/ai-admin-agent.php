<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$message = trim((string) ($payload['message'] ?? ''));
$lang = trim((string) ($payload['lang'] ?? 'en')) ?: 'en';
$history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

$settings = sh_load_settings();
$result = sh_ai_admin_agent_reply($settings, $message, $history, $lang);

sh_json_response([
    'ok'    => $result['ok'],
    'demo'  => $result['demo'],
    'reply' => $result['reply'],
    'tips'  => $result['tips'],
    'error' => $result['error'],
], $result['ok'] ? 200 : 400);