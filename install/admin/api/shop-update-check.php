<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/license-runtime.php';
require_once dirname(__DIR__, 2) . '/includes/shop-updates.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$refresh = !empty($payload['refresh']);
$result = sh_update_check($refresh);

if ($result['blocked']) {
    sh_json_response([
        'ok'              => false,
        'blocked'         => true,
        'license_reason'  => $result['license_reason'],
        'license_message' => $result['license_message'],
        'current_version' => $result['current_version'],
        'error'           => $result['license_message'],
    ], 403);
}

sh_json_response([
    'ok'               => $result['ok'],
    'blocked'          => false,
    'current_version'  => $result['current_version'],
    'latest_version'   => $result['latest_version'],
    'update_available' => $result['update_available'],
    'release_url'      => $result['release_url'],
    'release_name'     => $result['release_name'],
    'release_date'     => $result['release_date'],
    'checked_at'       => $result['checked_at'],
    'cached'           => $result['cached'],
    'error'            => $result['error'],
], $result['ok'] ? 200 : 400);