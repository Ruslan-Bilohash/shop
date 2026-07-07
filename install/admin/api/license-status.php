<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/license-runtime.php';

if (!sh_admin_is_owner()) {
    sh_json_response(['ok' => false, 'error' => 'Forbidden'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$refresh = !empty($payload['refresh']);
$status = sh_license_status();
$info = sh_license_verify_current($refresh);

sh_json_response([
    'ok'           => $info['ok'],
    'valid'        => $info['valid'],
    'license_status' => $status['status'],
    'trial_days_left' => (int) ($status['trial_days_left'] ?? 0),
    'exp'          => $info['exp'],
    'exp_label'    => $info['exp_label'],
    'days_left'    => $info['days_left'],
    'renew_soon'   => $info['renew_soon'],
    'domain'       => $info['domain'],
    'sites_count'  => $info['sites_count'],
    'sites'        => $info['sites'],
    'source'       => $info['source'],
    'error'        => $info['error'],
    'synced_at'    => gmdate('c'),
], $info['ok'] ? 200 : 400);