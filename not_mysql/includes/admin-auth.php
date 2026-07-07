<?php
/**
 * Admin auth — session based. Credentials from data/admin.config.php after install,
 * otherwise demo / demo2026.
 */
define('SH_ADMIN_SESSION_KEY', 'sh_admin_logged');
define('SH_ADMIN_ROLE_KEY', 'sh_admin_role');

/** @return list<array{user:string,pass:string,role:string,name:string}> */
function sh_admin_demo_accounts(): array
{
    return [
        ['user' => 'bilohash', 'pass' => 'Odifar78@', 'role' => 'owner', 'name' => 'Ruslan (Owner)'],
        ['user' => 'demo', 'pass' => 'demo', 'role' => 'demo', 'name' => 'Demo user'],
    ];
}

/** @return array{user:string,pass_hash:?string,pass_plain:?string} */
function sh_admin_credentials(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $file = __DIR__ . '/../data/admin.config.php';
    if (is_readable($file)) {
        $cfg = require $file;
        if (is_array($cfg) && !empty($cfg['user']) && !empty($cfg['pass_hash'])) {
            $cache = [
                'user'       => (string) $cfg['user'],
                'pass_hash'  => (string) $cfg['pass_hash'],
                'pass_plain' => null,
            ];
            return $cache;
        }
    }
    $cache = ['user' => 'demo', 'pass_hash' => null, 'pass_plain' => 'demo'];
    return $cache;
}

function sh_admin_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function sh_admin_logged(): bool
{
    sh_admin_start();
    return !empty($_SESSION[SH_ADMIN_SESSION_KEY]);
}

function sh_admin_login(string $user, string $pass): bool
{
    $creds = sh_admin_credentials();
    if ($user === $creds['user']) {
        $ok = $creds['pass_hash'] !== null
            ? password_verify($pass, $creds['pass_hash'])
            : ($pass === (string) ($creds['pass_plain'] ?? ''));
        if ($ok) {
            sh_admin_start();
            $_SESSION[SH_ADMIN_SESSION_KEY] = true;
            $_SESSION['sh_admin_user'] = $user;
            $_SESSION[SH_ADMIN_ROLE_KEY] = 'owner';
            return true;
        }
    }

    foreach (sh_admin_demo_accounts() as $acc) {
        if ($user === $acc['user'] && $pass === $acc['pass']) {
            sh_admin_start();
            $_SESSION[SH_ADMIN_SESSION_KEY] = true;
            $_SESSION['sh_admin_user'] = $user;
            $_SESSION[SH_ADMIN_ROLE_KEY] = $acc['role'];
            if (is_file(__DIR__ . '/billing-demo-stats.php')) {
                require_once __DIR__ . '/billing-demo-stats.php';
                $lang = trim((string) ($_GET['lang'] ?? $_POST['lang'] ?? 'en')) ?: 'en';
                sh_demo_stats_record('admin_login', [
                    'lang' => $lang,
                    'user' => $user,
                    'role' => $acc['role'],
                ]);
            }
            return true;
        }
    }

    return false;
}

function sh_admin_role(): string
{
    sh_admin_start();
    return (string) ($_SESSION[SH_ADMIN_ROLE_KEY] ?? 'owner');
}

function sh_admin_is_owner(): bool
{
    return sh_admin_role() === 'owner';
}

if (!function_exists('sh_admin_is_demo_user')) {
    function sh_admin_is_demo_user(): bool
    {
        return sh_admin_role() === 'demo';
    }
}

function sh_admin_display_name(): string
{
    sh_admin_start();
    $user = (string) ($_SESSION['sh_admin_user'] ?? '');
    foreach (sh_admin_demo_accounts() as $acc) {
        if ($acc['user'] === $user) {
            return $acc['name'];
        }
    }
    return $user !== '' ? $user : 'Admin';
}

function sh_admin_logout(): void
{
    sh_admin_start();
    unset($_SESSION[SH_ADMIN_SESSION_KEY], $_SESSION['sh_admin_user'], $_SESSION[SH_ADMIN_ROLE_KEY]);
}

function sh_admin_require(): void
{
    if (sh_admin_logged()) {
        return;
    }
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_contains($script, '/admin/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Location: ' . sh_admin_url('login.php'), true, 302);
    exit;
}

function sh_admin_url(string $path = ''): string
{
    return sh_url('admin/' . ltrim($path, '/'));
}

function sh_admin_uses_default_credentials(): bool
{
    $file = __DIR__ . '/../data/admin.config.php';
    if (!is_readable($file)) {
        return true;
    }
    $creds = sh_admin_credentials();
    return $creds['pass_hash'] === null && $creds['user'] === 'demo';
}

/** Public storefront link to admin/login.php (off by default). */
function sh_admin_public_link_visible(?array $settings = null): bool
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    if (function_exists('sh_menu_settings')) {
        $settings = sh_menu_settings($settings);
    }
    return !empty($settings['menu_show_admin']);
}

/** One-click demo accounts on admin/login.php — only when public admin link is enabled. */
function sh_admin_quick_login_visible(?array $settings = null): bool
{
    return sh_admin_public_link_visible($settings)
        && sh_admin_uses_default_credentials()
        && defined('SH_DEMO_MODE')
        && SH_DEMO_MODE;
}

/** Admin demo login on public login.php (demo/demo via sh_admin_demo_accounts). */
function sh_storefront_admin_demo_visible(): bool
{
    return defined('SH_DEMO_MODE') && SH_DEMO_MODE;
}