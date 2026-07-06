<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once dirname(__DIR__, 2) . '/includes/category-storage.php';

sh_admin_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$raw = file_get_contents('php://input') ?: '';
$payload = json_decode($raw, true);
$order = is_array($payload['order'] ?? null) ? $payload['order'] : [];

if ($order === []) {
    sh_json_response(['ok' => false, 'error' => 'Empty order'], 400);
}

if (!sh_category_reorder($order)) {
    sh_json_response(['ok' => false, 'error' => 'Could not save categories to database'], 500);
}

sh_json_response(['ok' => true]);