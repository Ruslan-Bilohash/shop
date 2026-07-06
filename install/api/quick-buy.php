<?php
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/leads-storage.php';
require_once dirname(__DIR__) . '/includes/store-settings.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST only']);
    exit;
}

if (!sh_quick_buy_enabled()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Quick buy disabled']);
    exit;
}

$body = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($body)) {
    $body = $_POST;
}

$lead = sh_lead_add([
    'phone'        => $body['phone'] ?? '',
    'product_id'   => $body['product_id'] ?? '',
    'product_name' => $body['product_name'] ?? '',
    'name'         => $body['name'] ?? '',
    'lang'         => $lang ?? 'no',
]);

if ($lead === null) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Invalid phone']);
    exit;
}

echo json_encode(['ok' => true, 'id' => $lead['id']]);