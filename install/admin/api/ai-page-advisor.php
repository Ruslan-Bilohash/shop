<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
require_once dirname(__DIR__, 2) . '/includes/service-pages.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$slug = strtolower(trim((string) ($payload['page_slug'] ?? '')));
$lang = trim((string) ($payload['lang'] ?? 'en')) ?: 'en';
$pageData = is_array($payload['page'] ?? null) ? $payload['page'] : [];

if ($slug === '' || !sh_service_page_slug_valid($slug)) {
    sh_json_response(['ok' => false, 'error' => 'Invalid page slug'], 400);
}

if ($pageData === []) {
    $settings = sh_load_settings();
    $settings = sh_merge_service_settings($settings);
    $pageData = $settings['service_pages'][$slug] ?? [];
}

$settings = sh_load_settings();
$result = sh_ai_page_advisor($settings, $slug, $pageData, $lang);

sh_json_response([
    'ok'            => $result['ok'],
    'demo'          => $result['demo'],
    'summary'       => $result['summary'],
    'suggestions'   => $result['suggestions'],
    'error'         => $result['error'],
], $result['ok'] ? 200 : 400);