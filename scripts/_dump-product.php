<?php
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/storage.php';
$id = $argv[1] ?? 'shop-cms-api-monthly';
$p = sh_product_by_id($id, true);
echo json_encode($p, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);