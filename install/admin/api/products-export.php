<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/product-io.php';
require_once dirname(__DIR__, 2) . '/includes/storage.php';

sh_admin_require();

$format = trim($_GET['format'] ?? 'shop_json');
$formats = sh_product_io_formats();
if (!in_array($format, sh_product_io_export_formats(), true) || !isset($formats[$format])) {
    http_response_code(400);
    exit('Unknown format');
}

$lang = trim($_GET['lang'] ?? $lang ?? 'en');
if (!array_key_exists($lang, sh_langs())) {
    $lang = sh_site_default_lang();
}

$filterOpts = [
    'active_only'   => !empty($_GET['active_only']),
    'featured_only' => !empty($_GET['featured_only']),
    'in_stock_only' => !empty($_GET['in_stock_only']),
    'category'      => trim($_GET['category'] ?? ''),
];

$exportOpts = [
    'include_seo' => !empty($_GET['include_seo']),
];

$products = sh_product_io_filter_products(sh_load_products_raw(), $filterOpts);

$content = sh_product_io_export_content($format, $products, $lang, $exportOpts);
$ext = $formats[$format]['ext'];
$filename = 'shop-products-' . $format . '-' . date('Y-m-d') . '.' . $ext;

header('Content-Type: ' . $formats[$format]['mime']);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');
echo $content;
exit;