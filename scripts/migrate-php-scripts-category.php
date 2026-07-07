<?php
/**
 * One-time: add php-scripts category and move Shop CMS license demo products into it.
 * CLI: php scripts/migrate-php-scripts-category.php
 */
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/category-storage.php';
require_once dirname(__DIR__) . '/includes/storage.php';

$category = [
    'slug'   => 'php-scripts',
    'code'   => '70717071',
    'icon'   => 'bolt',
    'active' => true,
    'sort'   => 22,
    'name'   => [
        'uk' => 'PHP-скрипти',
        'no' => 'PHP-skript',
        'en' => 'PHP scripts',
        'ru' => 'PHP-скрипты',
        'sv' => 'PHP-skript',
        'lt' => 'PHP skriptai',
    ],
];

if (!sh_category_upsert($category)) {
    fwrite(STDERR, "Failed to upsert category php-scripts\n");
    exit(1);
}

$productIds = ['shop-cms-api-monthly', 'shop-cms-updates-yearly'];
$products = sh_load_products_raw();
$updated = 0;
foreach ($products as &$product) {
    $id = (string) ($product['id'] ?? '');
    if (!in_array($id, $productIds, true)) {
        continue;
    }
    if (($product['category'] ?? '') === 'php-scripts') {
        continue;
    }
    $product['category'] = 'php-scripts';
    $updated++;
}
unset($product);

if ($updated > 0 && !sh_save_products($products)) {
    fwrite(STDERR, "Failed to save products\n");
    exit(1);
}

echo "OK category=php-scripts products_updated={$updated}\n";