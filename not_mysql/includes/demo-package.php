<?php
/**
 * Shop CMS — 30-day demo package download + optional licensed / FTP install request.
 */
declare(strict_types=1);

define('SH_DEMO_TRIAL_DAYS', 30);

function sh_demo_package_root(): string
{
    return dirname(__DIR__);
}

function sh_demo_package_downloads_dir(): string
{
    return sh_demo_package_root() . '/downloads';
}

function sh_demo_package_requests_path(): string
{
    return sh_demo_package_root() . '/data/demo-install-requests.json';
}

/** @return ?string Absolute path to newest shop-demo-30d zip */
function sh_demo_package_latest_zip(): ?string
{
    $dir = sh_demo_package_downloads_dir();
    if (!is_dir($dir)) {
        return null;
    }
    $files = glob($dir . '/shop-demo-30d-*.zip') ?: [];
    if ($files === []) {
        $root = sh_demo_package_root();
        $files = glob($root . '/shop-demo-30d-*.zip') ?: [];
    }
    if ($files === []) {
        return null;
    }
    usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
    return $files[0];
}

function sh_demo_package_basename(): string
{
    $path = sh_demo_package_latest_zip();
    return $path !== null ? basename($path) : 'shop-demo-30d.zip';
}

/** @return array{ok:bool,error:string,plan:string} */
function sh_demo_package_verify_license_key(string $key, string $domain = ''): array
{
    $key = trim($key);
    if ($key === '') {
        return ['ok' => false, 'error' => 'empty', 'plan' => ''];
    }
    require_once __DIR__ . '/shop-license.php';
    $domain = $domain !== '' ? $domain : (function_exists('sh_license_host') ? sh_license_host() : '');
    $parsed = shop_license_parse_key($key, $domain);
    if (empty($parsed['ok'])) {
        return ['ok' => false, 'error' => (string) ($parsed['error'] ?? 'invalid'), 'plan' => ''];
    }
    $plan = (string) ($parsed['plan'] ?? 'monthly');
    return ['ok' => true, 'error' => '', 'plan' => $plan];
}

/** @param array<string, mixed> $row */
function sh_demo_package_log_request(array $row): void
{
    $path = sh_demo_package_requests_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $list = [];
    if (is_file($path)) {
        $decoded = json_decode((string) file_get_contents($path), true);
        if (is_array($decoded)) {
            $list = $decoded;
        }
    }
    $row['ts'] = gmdate('c');
    $row['ip'] = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $list[] = $row;
    if (count($list) > 500) {
        $list = array_slice($list, -500);
    }
    file_put_contents($path, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/** @return array{ok:bool,error:string} */
function sh_demo_package_validate_ftp_form(array $post): array
{
    $host = trim((string) ($post['ftp_host'] ?? ''));
    $user = trim((string) ($post['ftp_user'] ?? ''));
    $pass = (string) ($post['ftp_pass'] ?? '');
    $path = trim((string) ($post['ftp_path'] ?? '/public_html/shop'));
    if ($host === '' || $user === '' || $pass === '') {
        return ['ok' => false, 'error' => 'ftp_incomplete'];
    }
    if (!preg_match('/^[a-zA-Z0-9.\-:]+$/', $host)) {
        return ['ok' => false, 'error' => 'ftp_host'];
    }
    if (strlen($pass) > 256) {
        return ['ok' => false, 'error' => 'ftp_pass'];
    }
    if ($path !== '' && !preg_match('#^/[a-zA-Z0-9_./\-]*$#', $path)) {
        return ['ok' => false, 'error' => 'ftp_path'];
    }
    return ['ok' => true, 'error' => ''];
}