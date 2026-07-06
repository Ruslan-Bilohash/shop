<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/category-storage.php';

sh_admin_require();

header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'ok'    => true,
    'icons' => sh_category_icon_options(),
], JSON_UNESCAPED_UNICODE);