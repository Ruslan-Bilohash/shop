<?php

require_once __DIR__ . '/database.php';

function sh_default_payment_settings(): array
{
    return [
        'paypal' => [
            'enabled'       => false,
            'mode'          => 'sandbox',
            'client_id'     => '',
            'client_secret' => '',
            'currency'      => 'NOK',
        ],
        'stripe' => [
            'enabled'         => false,
            'mode'            => 'test',
            'publishable_key' => '',
            'secret_key'      => '',
            'webhook_secret'  => '',
        ],
        'vipps' => [
            'enabled'           => false,
            'environment'       => 'test',
            'client_id'         => '',
            'client_secret'     => '',
            'subscription_key'  => '',
            'merchant_serial'   => '',
            'callback_token'    => '',
        ],
        'google_pay' => [
            'enabled'     => false,
            'merchant_id' => '',
            'gateway'     => 'stripe',
        ],
        'apple_pay' => [
            'enabled'         => false,
            'merchant_id'     => '',
            'domain'          => '',
            'verify_domain'   => true,
        ],
        'cod' => [
            'enabled'       => true,
            'title'         => 'Cash on delivery',
            'instructions'  => 'Pay the courier when your order arrives. Demo only — no real shipment.',
            'fee'           => 0,
        ],
    ];
}

function sh_load_settings(): array
{
    require_once __DIR__ . '/site-settings.php';
    if (!sh_is_installed()) {
        return sh_merge_site_settings([]);
    }
    try {
        $data = sh_db_load_settings();
    } catch (Throwable $e) {
        $data = [];
    }
    if (!is_array($data)) {
        return sh_merge_site_settings([]);
    }
    return sh_merge_site_settings($data);
}

function sh_settings_secret_keys(): array
{
    return [
        'ai_api_key',
        'nova_poshta_api_key',
        'posten_api_key',
        'customer_google_client_secret',
        'customer_apple_private_key',
        'smtp_password',
    ];
}

function sh_save_settings(array $settings): bool
{
    require_once __DIR__ . '/site-settings.php';
    $merged = sh_merge_site_settings($settings);

    $existingRaw = [];
    try {
        $existingRaw = sh_db_load_settings();
    } catch (Throwable $e) {
        $existingRaw = [];
    }
    foreach (sh_settings_secret_keys() as $secretKey) {
        $incoming = trim((string) ($merged[$secretKey] ?? ''));
        $stored = trim((string) ($existingRaw[$secretKey] ?? ''));
        if ($incoming === '' && $stored !== '') {
            $merged[$secretKey] = $existingRaw[$secretKey];
        }
    }

    $out = [];

    foreach (sh_default_payment_settings() as $provider => $fields) {
        $out[$provider] = $merged[$provider] ?? $fields;
    }

    foreach (array_keys(sh_seo_settings_defaults()) as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    require_once __DIR__ . '/ai.php';
    foreach (array_keys(sh_ai_defaults()) as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    require_once __DIR__ . '/service-pages.php';
    foreach (['service_pages', 'footer_links'] as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    require_once __DIR__ . '/store-settings.php';
    foreach (sh_store_settings_keys() as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    require_once __DIR__ . '/tax-settings.php';
    foreach (sh_tax_settings_keys() as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    if (array_key_exists('home_blocks', $merged) && is_array($merged['home_blocks'])) {
        $out['home_blocks'] = $merged['home_blocks'];
    }

    if (array_key_exists('block_templates', $merged) && is_array($merged['block_templates'])) {
        $out['block_templates'] = $merged['block_templates'];
    }

    require_once __DIR__ . '/menu-settings.php';
    foreach (array_keys(sh_menu_settings_defaults()) as $key) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }
    if (array_key_exists('header_nav_links', $merged) && is_array($merged['header_nav_links'])) {
        $out['header_nav_links'] = $merged['header_nav_links'];
    }

    require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';
    foreach (bh_cms_site_settings_defaults(bh_cms_product_accent('shop')) as $key => $val) {
        if (array_key_exists($key, $merged)) {
            $out[$key] = $merged[$key];
        }
    }

    if (!sh_is_installed()) {
        return false;
    }
    return sh_db_save_settings($out);
}

function sh_payment_tabs(): array
{
    return [
        'paypal'     => ['icon' => 'fab fa-paypal', 'brand' => true],
        'stripe'     => ['icon' => 'fab fa-stripe-s', 'brand' => true],
        'vipps'      => ['icon' => 'fas fa-mobile-alt', 'brand' => false],
        'cod'        => ['icon' => 'fas fa-truck', 'brand' => false],
        'google_pay' => ['icon' => 'fab fa-google-pay', 'brand' => true],
        'apple_pay'  => ['icon' => 'fab fa-apple-pay', 'brand' => true],
    ];
}

function sh_payment_tab_valid(string $tab): bool
{
    return isset(sh_payment_tabs()[$tab]);
}

function sh_payment_apply_post(string $tab, array $post, array $settings): array
{
    if (!sh_payment_tab_valid($tab)) {
        return $settings;
    }

    $bool = static fn(string $key): bool => !empty($post[$key]);
    $str  = static fn(string $key): string => trim($post[$key] ?? '');

    switch ($tab) {
        case 'paypal':
            $settings['paypal']['enabled'] = $bool('enabled');
            $settings['paypal']['mode'] = in_array($post['mode'] ?? '', ['sandbox', 'live'], true)
                ? $post['mode'] : 'sandbox';
            $settings['paypal']['client_id'] = $str('client_id');
            if ($str('client_secret') !== '') {
                $settings['paypal']['client_secret'] = $str('client_secret');
            }
            $settings['paypal']['currency'] = $str('currency') ?: 'NOK';
            break;

        case 'stripe':
            $settings['stripe']['enabled'] = $bool('enabled');
            $settings['stripe']['mode'] = in_array($post['mode'] ?? '', ['test', 'live'], true)
                ? $post['mode'] : 'test';
            $settings['stripe']['publishable_key'] = $str('publishable_key');
            if ($str('secret_key') !== '') {
                $settings['stripe']['secret_key'] = $str('secret_key');
            }
            if ($str('webhook_secret') !== '') {
                $settings['stripe']['webhook_secret'] = $str('webhook_secret');
            }
            break;

        case 'vipps':
            $settings['vipps']['enabled'] = $bool('enabled');
            $settings['vipps']['environment'] = in_array($post['environment'] ?? '', ['test', 'production'], true)
                ? $post['environment'] : 'test';
            $settings['vipps']['client_id'] = $str('client_id');
            if ($str('client_secret') !== '') {
                $settings['vipps']['client_secret'] = $str('client_secret');
            }
            $settings['vipps']['subscription_key'] = $str('subscription_key');
            $settings['vipps']['merchant_serial'] = $str('merchant_serial');
            if ($str('callback_token') !== '') {
                $settings['vipps']['callback_token'] = $str('callback_token');
            }
            break;

        case 'google_pay':
            $settings['google_pay']['enabled'] = $bool('enabled');
            $settings['google_pay']['merchant_id'] = $str('merchant_id');
            $settings['google_pay']['gateway'] = in_array($post['gateway'] ?? '', ['stripe', 'paypal'], true)
                ? $post['gateway'] : 'stripe';
            break;

        case 'apple_pay':
            $settings['apple_pay']['enabled'] = $bool('enabled');
            $settings['apple_pay']['merchant_id'] = $str('merchant_id');
            $settings['apple_pay']['domain'] = $str('domain');
            $settings['apple_pay']['verify_domain'] = $bool('verify_domain');
            break;

        case 'cod':
            $settings['cod']['enabled'] = $bool('enabled');
            $settings['cod']['title'] = $str('title') ?: 'Cash on delivery';
            $settings['cod']['instructions'] = $str('instructions');
            $settings['cod']['fee'] = max(0, (int)($post['fee'] ?? 0));
            break;
    }

    return $settings;
}

function sh_payment_is_configured(string $provider, ?array $settings = null): bool
{
    $settings ??= sh_load_settings();
    $cfg = $settings[$provider] ?? [];

    return match ($provider) {
        'paypal' => !empty($cfg['client_id']) && !empty($cfg['client_secret']),
        'stripe' => !empty($cfg['publishable_key']) && !empty($cfg['secret_key']),
        'vipps'  => !empty($cfg['client_id']) && !empty($cfg['client_secret'])
            && !empty($cfg['subscription_key']) && !empty($cfg['merchant_serial']),
        'google_pay' => !empty($cfg['merchant_id']),
        'apple_pay'  => !empty($cfg['merchant_id']) && !empty($cfg['domain']),
        'cod'        => !empty($cfg['enabled']),
        default => false,
    };
}

/** @return list<array{id:string,icon:string,label:string}> */
function sh_enabled_checkout_methods(?array $settings = null): array
{
    $settings ??= sh_load_settings();
    $out = [];
    foreach (sh_payment_tabs() as $key => $meta) {
        if (!in_array($key, ['stripe', 'paypal', 'vipps', 'cod'], true)) {
            continue;
        }
        if (empty($settings[$key]['enabled'])) {
            continue;
        }
        if ($key !== 'cod' && !sh_payment_is_configured($key, $settings)) {
            continue;
        }
        $out[] = ['id' => $key, 'icon' => $meta['icon']];
    }
    return $out;
}

function sh_secret_preview(string $value): string
{
    if ($value === '') {
        return '';
    }
    $len = strlen($value);
    if ($len <= 4) {
        return str_repeat('•', $len);
    }
    return str_repeat('•', min(12, $len - 4)) . substr($value, -4);
}