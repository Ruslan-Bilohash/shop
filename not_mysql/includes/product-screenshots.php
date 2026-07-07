<?php

/**
 * Shop CMS admin / storefront screenshot manifest for product site gallery.
 * Images live in /shop/screenshot/ (WebP preferred, JPG fallback).
 */

/** @return list<array{id:string,file:string,group:string}> */
function sh_product_screenshot_manifest(): array
{
    return [
        ['id' => 'dashboard', 'file' => 'dashboard', 'group' => 'catalog'],
        ['id' => 'catalog_product', 'file' => 'catalog_product', 'group' => 'catalog'],
        ['id' => 'catalog_categories', 'file' => 'catalog_categories', 'group' => 'catalog'],
        ['id' => 'store_setting', 'file' => 'store_setting', 'group' => 'store'],
        ['id' => 'setting_shop', 'file' => 'setting_shop', 'group' => 'store'],
        ['id' => 'seting_color', 'file' => 'seting_color', 'group' => 'design'],
        ['id' => 'header_setting', 'file' => 'header_setting', 'group' => 'design'],
        ['id' => 'main_block', 'file' => 'main_block', 'group' => 'content'],
        ['id' => 'servise_page_editor', 'file' => 'servise_page_editor', 'group' => 'content'],
        ['id' => 'servise_page_editor_2', 'file' => 'servise_page_editor_2', 'group' => 'content'],
        ['id' => 'footer_link_editor', 'file' => 'footer_link_editor', 'group' => 'content'],
        ['id' => 'integrations_stripe', 'file' => 'integrations_stripe', 'group' => 'payments'],
        ['id' => 'integrations_paypal', 'file' => 'integrations_paypal', 'group' => 'payments'],
        ['id' => 'integrations_vipps', 'file' => 'integrations_vipps', 'group' => 'payments'],
        ['id' => 'integrations_cash_on_delivery', 'file' => 'integrations_cash_on_delivery', 'group' => 'payments'],
        ['id' => 'integrations_google_pay', 'file' => 'integrations_google_pay', 'group' => 'payments'],
        ['id' => 'integrations_apple_pay', 'file' => 'integrations_apple_pay', 'group' => 'payments'],
        ['id' => 'integrations_ai_assistant', 'file' => 'integrations_ai_assistant', 'group' => 'ai'],
        ['id' => 'integrations_ai_assistant2', 'file' => 'integrations_ai_assistant2', 'group' => 'ai'],
        ['id' => 'integrations_chat', 'file' => 'integrations_chat', 'group' => 'ai'],
        ['id' => 'integrations_chat_design', 'file' => 'integrations_chat_design', 'group' => 'ai'],
        ['id' => 'integrations_recapcha', 'file' => 'integrations_recapcha', 'group' => 'integrations'],
        ['id' => 'integrations_bring_posten_api', 'file' => 'integrations_bring_posten_api', 'group' => 'integrations'],
        ['id' => 'seo_schema', 'file' => 'seo_schema', 'group' => 'seo'],
        ['id' => 'generate_schema_sitemap', 'file' => 'generate_schema_sitemap', 'group' => 'seo'],
        ['id' => 'advanced_settings', 'file' => 'advanced_settings', 'group' => 'advanced'],
    ];
}

function sh_product_screenshot_cdn_base(): string
{
    return 'https://bilohash.com/shop/screenshot/';
}

/** @return list<string> */
function sh_product_screenshot_dirs(): array
{
    static $dirs = null;
    if ($dirs !== null) {
        return $dirs;
    }

    $pkgRoot = dirname(__DIR__);
    $candidates = [
        $pkgRoot . '/screenshot',
        dirname($pkgRoot) . '/screenshot',
        dirname($pkgRoot) . '/shop/screenshot',
    ];

    $dirs = array_values(array_unique(array_filter($candidates, 'is_dir')));

    return $dirs;
}

/** @return array{ext:string}|null */
function sh_product_screenshot_resolve(string $basename): ?array
{
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        foreach (sh_product_screenshot_dirs() as $dir) {
            $path = $dir . '/' . $basename . '.' . $ext;
            if (is_file($path)) {
                return ['ext' => $ext];
            }
        }
    }

    return null;
}

function sh_product_screenshot_exists(string $basename): bool
{
    if (sh_product_screenshot_resolve($basename) !== null) {
        return true;
    }

    foreach (sh_product_screenshot_manifest() as $row) {
        if (($row['file'] ?? '') === $basename) {
            return true;
        }
    }

    return false;
}

function sh_product_screenshot_path(string $basename): string
{
    $resolved = sh_product_screenshot_resolve(pathinfo($basename, PATHINFO_FILENAME));
    $stem = pathinfo($basename, PATHINFO_FILENAME);
    $ext = $resolved['ext'] ?? (str_contains($basename, '.') ? pathinfo($basename, PATHINFO_EXTENSION) : 'webp');
    $dirs = sh_product_screenshot_dirs();

    return ($dirs[0] ?? (dirname(__DIR__) . '/screenshot')) . '/' . $stem . '.' . $ext;
}

function sh_product_screenshot_public_file(string $basename): string
{
    $resolved = sh_product_screenshot_resolve($basename);

    return $basename . '.' . ($resolved['ext'] ?? 'webp');
}

function sh_product_screenshot_url(string $basename): string
{
    return sh_url('screenshot/' . sh_product_screenshot_public_file($basename));
}

/** Product site — screenshots under parent /shop/ or CDN fallback for JSON package */
function shs_product_screenshot_url(string $basename): string
{
    if (sh_product_screenshot_resolve($basename) !== null) {
        return shs_demo_url('screenshot/' . sh_product_screenshot_public_file($basename));
    }

    return sh_product_screenshot_cdn_base() . sh_product_screenshot_public_file($basename);
}