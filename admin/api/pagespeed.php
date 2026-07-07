<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/pagespeed-insights.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$url = trim((string) ($payload['url'] ?? ''));
if ($url === '') {
    $url = sh_psi_default_url();
}
$strategy = trim((string) ($payload['strategy'] ?? 'mobile'));
$refresh = !empty($payload['refresh']);

$settings = sh_load_settings();
$result = sh_psi_get($url, $strategy, $refresh, [], $settings);

sh_json_response([
    'ok'         => $result['ok'],
    'demo'       => $result['demo'],
    'url'        => $result['url'],
    'strategy'   => $result['strategy'],
    'fetched_at' => $result['fetched_at'],
    'scores'     => $result['scores'],
    'vitals'     => $result['vitals'],
    'error'      => $result['error'],
], $result['ok'] ? 200 : 400);