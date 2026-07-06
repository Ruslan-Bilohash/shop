<?php
/**
 * Shop CMS — product marketing site
 * /shop/site
 */
require_once dirname(__DIR__) . '/config.php';

define('SHS_BASE_PATH', '/shop/site');
define('SHS_PRODUCT_NAME', 'Shop CMS');
define('SHS_PARENT_PATH', '/shop');

$detected = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_path = (strpos($host, SH_DOMAIN) !== false) ? SHS_BASE_PATH : ($detected ?: SHS_BASE_PATH);

$protocol = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
) ? 'https' : 'http';

$site_url   = rtrim($protocol . '://' . $host . $base_path, '/');
$assets_url = $base_path . '/assets';

function shs_url(string $path = ''): string
{
    global $base_path;
    return rtrim($base_path, '/') . '/' . ltrim($path, '/');
}

function shs_asset(string $file): string
{
    global $assets_url;
    return $assets_url . '/' . ltrim($file, '/');
}

function shs_demo_url(string $path = ''): string
{
    global $host, $protocol;
    return rtrim($protocol . '://' . $host . SHS_PARENT_PATH, '/') . '/' . ltrim($path, '/');
}