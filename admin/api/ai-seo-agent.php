<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
require_once dirname(__DIR__, 2) . '/includes/seo-checklist.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$page = is_array($payload['page'] ?? null) ? $payload['page'] : [];
$lang = trim((string) ($payload['lang'] ?? 'en')) ?: 'en';

if (($page['key'] ?? '') === '' && ($page['label'] ?? '') === '') {
    sh_json_response(['ok' => false, 'error' => 'Page data required'], 400);
}

$settings = sh_load_settings();
$result = sh_ai_seo_page_suggestions($settings, $page, $lang);

sh_json_response([
    'ok'          => $result['ok'],
    'demo'        => $result['demo'],
    'summary'     => $result['summary'],
    'suggestions' => $result['suggestions'],
    'error'       => $result['error'],
], $result['ok'] ? 200 : 400);