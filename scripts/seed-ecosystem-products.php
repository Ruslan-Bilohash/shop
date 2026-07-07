<?php
/**
 * Merge BILOHASH ecosystem CMS products into the live catalog (MySQL or JSON).
 * CLI: php scripts/seed-ecosystem-products.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/products-ecosystem-seed.php';

if (!sh_is_installed()) {
    fwrite(STDERR, "Shop not installed — cannot seed products.\n");
    exit(1);
}

$definitions = sh_ecosystem_cms_products_seed();
if ($definitions === []) {
    fwrite(STDERR, "No ecosystem product definitions.\n");
    exit(1);
}

$added = 0;
$updated = 0;
foreach ($definitions as $product) {
    $id = (string) ($product['id'] ?? '');
    if ($id === '') {
        continue;
    }
    $before = sh_product_by_id($id, true);
    if (!sh_product_upsert($product)) {
        fwrite(STDERR, "Failed to upsert: {$id}\n");
        exit(1);
    }
    if ($before === null) {
        $added++;
    } else {
        $updated++;
    }
}

echo 'OK ecosystem_products=' . count($definitions) . " added={$added} updated={$updated}\n";