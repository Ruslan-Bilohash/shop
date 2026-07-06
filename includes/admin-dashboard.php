<?php

function sh_admin_dashboard_stats(): array
{
    require_once __DIR__ . '/leads-storage.php';
    require_once __DIR__ . '/shop-mode.php';
    require_once __DIR__ . '/store-settings.php';

    $productsAll = sh_products(true);
    $productsActive = sh_products(false);
    $onSale = array_filter($productsActive, fn($p) => sh_product_on_sale($p));
    $outOfStock = array_filter($productsActive, fn($p) => (int) ($p['stock'] ?? 0) <= 0);
    $inactive = array_filter($productsAll, fn($p) => ($p['active'] ?? true) === false);
    $featured = array_filter($productsActive, fn($p) => !empty($p['featured']));
    $platform = sh_platform_stats();
    $settings = function_exists('sh_load_settings') ? sh_load_settings() : [];
    $mode = sh_merge_shop_mode_settings(sh_merge_store_settings(is_array($settings) ? $settings : []));

    return array_merge($platform, [
        'active_products'  => count($productsActive),
        'inactive_products'=> count($inactive),
        'on_sale'          => count($onSale),
        'out_of_stock'     => count($outOfStock),
        'new_leads'        => sh_leads_count_by_status('new'),
        'total_leads'      => count(sh_leads_load()),
        'languages'        => count(sh_active_langs($settings)),
        'currency'         => sh_site_currency($settings),
        'shop_open'        => empty($mode['shop_maintenance_enabled']),
        'cookie_consent'   => !empty($mode['cookie_consent_enabled']),
        'quick_buy'        => sh_quick_buy_enabled($settings),
        'avg_price'        => count($productsActive) > 0
            ? (int) round($platform['volume'] / count($productsActive))
            : 0,
    ]);
}