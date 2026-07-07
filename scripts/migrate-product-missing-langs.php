<?php
/**
 * Fill missing product name/desc/SEO translations for all active languages.
 * CLI: php scripts/migrate-product-missing-langs.php [product-id]
 */
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/storage.php';

$settings = sh_load_settings();
$preferLang = (string) (sh_ai_settings($settings)['ai_source_lang'] ?? 'en');
$onlyId = trim($argv[1] ?? '');

$products = sh_load_products_raw();
$updated = 0;

foreach ($products as &$product) {
    $id = (string) ($product['id'] ?? '');
    if ($onlyId !== '' && $id !== $onlyId) {
        continue;
    }
    $before = json_encode($product, JSON_UNESCAPED_UNICODE);
    $product = sh_product_normalize_record($product, $preferLang);
    if (json_encode($product, JSON_UNESCAPED_UNICODE) !== $before) {
        $updated++;
        echo "updated: {$id}\n";
    }
}
unset($product);

if ($updated > 0 && !sh_save_products($products)) {
    fwrite(STDERR, "Failed to save products\n");
    exit(1);
}

echo "products_updated={$updated}\n";