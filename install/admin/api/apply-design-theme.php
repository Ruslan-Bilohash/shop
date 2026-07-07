<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/theme-runtime.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$themeId = trim((string) ($payload['theme_id'] ?? $payload['id'] ?? ''));
if ($themeId === '') {
    sh_json_response(['ok' => false, 'error' => 'theme_id required'], 400);
}

$result = sh_design_theme_apply($themeId);
sh_json_response([
    'ok'       => $result['ok'],
    'theme_id' => $result['theme_id'],
    'error'    => $result['error'],
], $result['ok'] ? 200 : 400);