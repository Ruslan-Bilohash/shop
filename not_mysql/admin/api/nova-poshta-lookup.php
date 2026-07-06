<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'GET or POST required'], 405);
}

require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
require_once dirname(__DIR__, 2) . '/includes/nova-poshta.php';

$payload = $_GET;
if ($payload === [] || !isset($payload['action'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$action = trim((string) ($payload['action'] ?? ''));
$settings = sh_load_settings();

if ($action === 'cities') {
    $q = trim((string) ($payload['q'] ?? ''));
    $cities = sh_nova_poshta_search_cities($q, $settings);
    sh_json_response(['ok' => true, 'cities' => $cities]);
}

if ($action === 'warehouses') {
    $cityRef = trim((string) ($payload['city_ref'] ?? ''));
    $warehouses = sh_nova_poshta_warehouses($cityRef, $settings);
    sh_json_response(['ok' => true, 'warehouses' => $warehouses]);
}

sh_json_response(['ok' => false, 'error' => 'Unknown action'], 400);