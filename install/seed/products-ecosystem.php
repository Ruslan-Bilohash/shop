<?php
/**
 * BILOHASH ecosystem CMS products (install wizard seed).
 */
$seed = dirname(__DIR__) . '/includes/products-ecosystem-seed.php';
if (!is_readable($seed)) {
    $seed = dirname(__DIR__, 2) . '/includes/products-ecosystem-seed.php';
}
require_once $seed;

return sh_ecosystem_cms_products_seed();