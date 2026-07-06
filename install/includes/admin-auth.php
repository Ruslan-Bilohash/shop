<?php
/**
 * Admin auth — session based. Credentials from data/admin.config.php after install,
 * otherwise demo / demo2026.
 */
define('SH_ADMIN_SESSION_KEY', 'sh_admin_logged');

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
    $cache = ['user' => 'demo', 'pass_hash' => null, 'pass_plain' => 'demo2026'];
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
    if ($user !== $creds['user']) {
        return false;
    }
    $ok = $creds['pass_hash'] !== null
        ? password_verify($pass, $creds['pass_hash'])
        : ($pass === (string) ($creds['pass_plain'] ?? ''));
    if (!$ok) {
        return false;
    }
    sh_admin_start();
    $_SESSION[SH_ADMIN_SESSION_KEY] = true;
    $_SESSION['sh_admin_user'] = $user;
    return true;
}

function sh_admin_logout(): void
{
    sh_admin_start();
    unset($_SESSION[SH_ADMIN_SESSION_KEY], $_SESSION['sh_admin_user']);
}

function sh_admin_require(): void
{
    if (!sh_admin_logged()) {
        header('Location: ' . sh_admin_url('login.php'), true, 302);
        exit;
    }
}

function sh_admin_url(string $path = ''): string
{
    return sh_url('admin/' . ltrim($path, '/'));
}