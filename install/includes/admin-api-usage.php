<?php
/**
 * Per-admin BILOHASH AI API quota — demo staff limited, owner unlimited + no demo fallbacks.
 */
declare(strict_types=1);

define('SH_ADMIN_DEMO_API_LIMIT', 30);

function sh_admin_api_usage_path(): string
{
    return dirname(__DIR__) . '/data/admin-api-usage.json';
}

/** @return array<string, array{used:int}> */
function sh_admin_api_usage_load(): array
{
    $path = sh_admin_api_usage_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/** @param array<string, array{used:int}> $data */
function sh_admin_api_usage_save(array $data): bool
{
    $dir = dirname(sh_admin_api_usage_path());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(sh_admin_api_usage_path(), $json . "\n", LOCK_EX) !== false;
}

function sh_admin_api_user_key(): string
{
    if (!function_exists('sh_admin_start')) {
        return '';
    }
    sh_admin_start();
    return trim((string) ($_SESSION['sh_admin_user'] ?? ''));
}

function sh_admin_api_limit(): int
{
    if (function_exists('sh_admin_is_owner') && sh_admin_is_owner()) {
        return 0;
    }
    if (function_exists('sh_admin_role') && sh_admin_role() === 'demo') {
        return SH_ADMIN_DEMO_API_LIMIT;
    }
    return 0;
}

function sh_admin_api_remaining(): int
{
    $limit = sh_admin_api_limit();
    if ($limit <= 0) {
        return -1;
    }
    $user = sh_admin_api_user_key();
    if ($user === '') {
        return $limit;
    }
    $all = sh_admin_api_usage_load();
    $used = (int) ($all[$user]['used'] ?? 0);
    return max(0, $limit - $used);
}

/** @return array{ok:bool,remaining:int,error:string} */
function sh_admin_api_try_consume(): array
{
    $limit = sh_admin_api_limit();
    if ($limit <= 0) {
        return ['ok' => true, 'remaining' => -1, 'error' => ''];
    }
    $user = sh_admin_api_user_key();
    if ($user === '') {
        return ['ok' => false, 'remaining' => 0, 'error' => 'Not logged in'];
    }
    $all = sh_admin_api_usage_load();
    $used = (int) ($all[$user]['used'] ?? 0);
    if ($used >= $limit) {
        return [
            'ok'        => false,
            'remaining' => 0,
            'error'     => 'Demo API limit reached (' . $limit . ' requests). Log in as administrator for unlimited access.',
        ];
    }
    $all[$user] = ['used' => $used + 1];
    sh_admin_api_usage_save($all);
    return ['ok' => true, 'remaining' => max(0, $limit - $used - 1), 'error' => ''];
}

/** Owner/administrator: real API only. Demo staff: demo fallbacks allowed within quota. */
function sh_admin_allows_ai_demo_data(): bool
{
    if (!function_exists('sh_admin_is_owner')) {
        return true;
    }
    return !sh_admin_is_owner();
}

