<?php

function sh_shop_mode_defaults(): array
{
    return [
        'shop_maintenance_enabled' => true,
        'shop_maintenance_allow_admin' => true,
        'shop_dev_errors' => true,
        'cookie_consent_enabled' => true,
    ];
}

function sh_merge_shop_mode_settings(array $settings): array
{
    foreach (sh_shop_mode_defaults() as $key => $val) {
        if (!array_key_exists($key, $settings)) {
            $settings[$key] = $val;
        }
    }
    return $settings;
}

function sh_shop_mode_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_shop_mode_settings($settings);
    if (array_key_exists('shop_open', $post)) {
        $settings['shop_maintenance_enabled'] = empty($post['shop_open']);
    } elseif (array_key_exists('shop_maintenance_enabled', $post)) {
        $settings['shop_maintenance_enabled'] = !empty($post['shop_maintenance_enabled']);
    }
    if (array_key_exists('shop_maintenance_allow_admin', $post)) {
        $settings['shop_maintenance_allow_admin'] = !empty($post['shop_maintenance_allow_admin']);
    }
    if (array_key_exists('shop_dev_errors', $post)) {
        $settings['shop_dev_errors'] = !empty($post['shop_dev_errors']);
    }
    if (array_key_exists('cookie_consent_enabled', $post)) {
        $settings['cookie_consent_enabled'] = !empty($post['cookie_consent_enabled']);
    }
    return $settings;
}

function sh_read_store_settings_light(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $file = dirname(__DIR__) . '/data/settings.json';
    $data = [];
    if (!function_exists('sh_json_store_decode')) {
        require_once __DIR__ . '/json-store.php';
    }
    $decoded = sh_json_store_decode($file, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
    $cache = $data;
    return $cache;
}

function sh_shop_maintenance_enabled(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    if ($settings === null) {
        $settings = sh_read_store_settings_light();
    }
    $settings = sh_merge_shop_mode_settings(is_array($settings) ? $settings : []);
    return !empty($settings['shop_maintenance_enabled']);
}

function sh_shop_dev_errors_enabled(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $settings = sh_merge_shop_mode_settings(is_array($settings) ? $settings : []);
    return !empty($settings['shop_dev_errors']);
}

function sh_cookie_consent_enabled(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    if ($settings === null) {
        $settings = sh_read_store_settings_light();
    }
    $settings = sh_merge_shop_mode_settings(is_array($settings) ? $settings : []);
    return !empty($settings['cookie_consent_enabled']);
}

function sh_is_admin_request(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_contains($script, '/admin/');
}

function sh_is_maintenance_exempt_script(): bool
{
    $base = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return in_array($base, ['_health.php', 'maintenance.php'], true);
}

function sh_boot_dev_errors(): void
{
    $enabled = defined('SH_DEMO_MODE') && SH_DEMO_MODE;
    if (function_exists('sh_shop_dev_errors_enabled')) {
        $enabled = $enabled || sh_shop_dev_errors_enabled();
    }
    if (!$enabled) {
        return;
    }
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

/** @param array<string, mixed> $t */
function sh_render_maintenance_page(array $t, string $lang): void
{
    $m = $t['maintenance'] ?? [];
    $title = $m['title'] ?? 'Store under development';
    $text = $m['text'] ?? '';
    $cta = $m['cta_product'] ?? 'Product page';
    $ctaDemo = $m['cta_demo'] ?? 'Live demo';
    $productUrl = 'https://bilohash.com/shop/site/';
    $contactUrl = function_exists('sh_url') ? sh_url('contact.php') : '/shop/contact.php';
    header('HTTP/1.1 503 Service Unavailable');
    header('Retry-After: 3600');
    ?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= htmlspecialchars($title) ?> | Shop CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        :root { --p:#2563eb; --bg:#f8fafc; --card:#fff; --text:#0f172a; --muted:#64748b; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;
            font-family:'Segoe UI',system-ui,sans-serif; background:linear-gradient(160deg,#eff6ff,#f8fafc); color:var(--text); }
        .sh-maint { max-width:520px; width:100%; background:var(--card); border:1px solid #e2e8f0; border-radius:16px;
            padding:32px 28px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.08); }
        .sh-maint-icon { width:64px; height:64px; margin:0 auto 16px; border-radius:50%; background:#dbeafe; color:var(--p);
            display:flex; align-items:center; justify-content:center; font-size:28px; }
        h1 { margin:0 0 12px; font-size:1.5rem; }
        p { margin:0 0 20px; color:var(--muted); line-height:1.6; font-size:15px; }
        .sh-maint-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
        a { display:inline-flex; align-items:center; gap:8px; padding:12px 18px; border-radius:10px; font-weight:600;
            font-size:14px; text-decoration:none; }
        .primary { background:var(--p); color:#fff; }
        .outline { border:1px solid #cbd5e1; color:var(--text); background:#fff; }
    </style>
</head>
<body>
    <div class="sh-maint">
        <div class="sh-maint-icon"><i class="fas fa-hard-hat" aria-hidden="true"></i></div>
        <h1><?= htmlspecialchars($title) ?></h1>
        <p><?= htmlspecialchars($text) ?></p>
        <div class="sh-maint-actions">
            <a href="<?= htmlspecialchars($productUrl) ?>" class="primary"><i class="fas fa-laptop-code"></i> <?= htmlspecialchars($cta) ?></a>
            <a href="<?= htmlspecialchars($contactUrl) ?>" class="outline"><i class="fas fa-comments"></i> <?= htmlspecialchars($ctaDemo) ?></a>
        </div>
    </div>
</body>
</html><?php
    exit;
}

function sh_shop_maybe_maintenance(): void
{
    if (sh_is_maintenance_exempt_script()) {
        return;
    }
    if (!sh_shop_maintenance_enabled()) {
        return;
    }
    if (sh_is_admin_request() && sh_merge_shop_mode_settings(function_exists('sh_site_settings') ? sh_site_settings() : [])['shop_maintenance_allow_admin']) {
        return;
    }
    global $t, $lang;
    if (!is_array($t ?? null)) {
        $t = [];
    }
    sh_render_maintenance_page($t, (string) ($lang ?? 'en'));
}