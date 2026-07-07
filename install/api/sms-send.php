<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../includes/sms.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$payload = $_POST;
if ($payload === [] || !isset($payload['phone'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$phone = trim((string) ($payload['phone'] ?? ''));
$settings = sh_load_settings();
$result = sh_sms_send_otp($phone, $settings);

$out = [
    'ok'    => $result['ok'],
    'demo'  => $result['demo'] ?? false,
    'error' => $result['error'] ?? '',
];
if (!empty($result['demo']) && !empty($result['code'])) {
    $out['demo_code'] = $result['code'];
}

http_response_code($result['ok'] ? 200 : 400);
echo json_encode($out, JSON_UNESCAPED_UNICODE);