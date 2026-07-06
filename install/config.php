<?php
/**
 * Bilohash Shop — universal demo e-commerce CMS
 * /shop
 */
define('SH_BASE_PATH', '/shop');
define('SH_DOMAIN', 'bilohash.com');
define('SH_SITE_NAME', 'Shop CMS');
define('SH_CURRENCY', 'NOK');
define('SH_DEMO_MODE', true);

if (SH_DEMO_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

function sh_resolve_base_path(): string
{
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host !== '' && str_contains($host, SH_DOMAIN)) {
        return SH_BASE_PATH;
    }
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = rtrim(dirname($script), '/');
    foreach (['/admin/api', '/admin', '/site', '/api'] as $suffix) {
        if ($suffix !== '' && str_ends_with($dir, $suffix)) {
            $dir = substr($dir, 0, -strlen($suffix));
            break;
        }
    }
    return ($dir === '' || $dir === '.') ? '' : $dir;
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_path = sh_resolve_base_path();

$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
) ? 'https' : 'http';

$site_url   = rtrim($protocol . '://' . $host . $base_path, '/');
$assets_url = $base_path . '/assets';

function sh_url(string $path = ''): string
{
    global $base_path;
    return rtrim($base_path, '/') . '/' . ltrim($path, '/');
}

function sh_product_url(string $id, ?string $lang = null): string
{
    $id = trim($id);
    if ($id === '') {
        return sh_url('search.php');
    }
    $path = 'product.php?id=' . rawurlencode($id);
    if ($lang !== null && $lang !== '' && $lang !== 'no') {
        $path .= '&lang=' . rawurlencode($lang);
    }
    return sh_url($path);
}

function sh_asset(string $file): string
{
    global $assets_url;
    return $assets_url . '/' . ltrim($file, '/');
}

function sh_price(int $amount): string
{
    if (function_exists('sh_format_price')) {
        return sh_format_price($amount);
    }
    return number_format($amount, 0, ',', ' ') . ' kr';
}

function sh_placeholder_image(): string
{
    return sh_asset('images/placeholder.svg');
}

if (!function_exists('bh_str_lower')) {
    function bh_str_lower(string $str): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($str) : strtolower($str);
    }
}

if (!function_exists('bh_str_sub')) {
    function bh_str_sub(string $str, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return $length === null ? mb_substr($str, $start) : mb_substr($str, $start, $length);
        }
        return $length === null ? substr($str, $start) : substr($str, $start, $length);
    }
}