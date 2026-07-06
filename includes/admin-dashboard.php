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

/** @return list<array{key:string,ok:bool,label:string,url:string}> */
function sh_admin_health_checks(array $ta): array
{
    require_once __DIR__ . '/payment-settings.php';
    require_once __DIR__ . '/site-settings.php';
    $settings = sh_load_settings();
    $dash = $ta['dashboard_page'] ?? [];
    $checks = $dash['health'] ?? [];

    $paymentsOk = false;
    foreach (['stripe', 'paypal', 'vipps'] as $provider) {
        if (sh_payment_is_configured($provider, $settings) && !empty($settings[$provider]['enabled'])) {
            $paymentsOk = true;
            break;
        }
    }

    $items = [
        ['key' => 'shop_open', 'ok' => sh_admin_dashboard_stats()['shop_open'], 'url' => 'settings-store.php'],
        ['key' => 'payments', 'ok' => $paymentsOk, 'url' => 'payments.php'],
        ['key' => 'seo', 'ok' => trim($settings['seo_site_name'] ?? '') !== '', 'url' => 'settings-seo.php'],
        ['key' => 'recaptcha', 'ok' => !empty($settings['recaptcha_enabled']) && trim($settings['recaptcha_site_key'] ?? '') !== '', 'url' => 'settings-recaptcha.php'],
        ['key' => 'tracking', 'ok' => trim($settings['tracking_gtag_id'] ?? '') !== '' || trim($settings['tracking_meta_pixel'] ?? '') !== '', 'url' => 'settings-analytics.php'],
    ];

    foreach ($items as &$item) {
        $item['label'] = $checks[$item['key']] ?? $item['key'];
        $item['url'] = sh_admin_url($item['url']);
    }
    unset($item);

    return $items;
}