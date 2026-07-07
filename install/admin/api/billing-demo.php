<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/billing-demo.php';
require_once dirname(__DIR__, 2) . '/includes/billing-demo-stats.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$action = trim((string) ($payload['action'] ?? ''));
$lang = trim((string) ($payload['lang'] ?? $lang ?? 'en')) ?: 'en';

if ($action === 'subscribe') {
    $plan = trim((string) ($payload['plan'] ?? ''));
    $result = sh_billing_demo_subscribe($plan, $lang);
    sh_json_response([
        'ok'    => $result['ok'],
        'state' => $result['state'],
        'ref'   => $result['ref'],
        'error' => $result['error'],
    ], $result['ok'] ? 200 : 400);
}

if ($action === 'cancel') {
    $result = sh_billing_demo_cancel();
    sh_json_response([
        'ok'    => $result['ok'],
        'state' => $result['state'],
        'error' => $result['error'],
    ], $result['ok'] ? 200 : 400);
}

if ($action === 'api_request') {
    $result = sh_billing_demo_use_request();
    sh_json_response([
        'ok'        => $result['ok'],
        'state'     => $result['state'],
        'remaining' => $result['remaining'],
        'error'     => $result['error'],
    ], $result['ok'] ? 200 : 400);
}

if ($action === 'stats') {
    sh_json_response([
        'ok'    => true,
        'stats' => sh_demo_stats_summary(),
    ]);
}

sh_json_response(['ok' => false, 'error' => 'Unknown action'], 400);