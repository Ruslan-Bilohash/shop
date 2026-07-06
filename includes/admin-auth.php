<?php
/**
 * Demo admin auth — session based
 * Login: demo / demo2026
 */
define('SH_ADMIN_USER', 'demo');
define('SH_ADMIN_PASS', 'demo2026');
define('SH_ADMIN_SESSION_KEY', 'sh_admin_logged');

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
    if ($user === SH_ADMIN_USER && $pass === SH_ADMIN_PASS) {
        sh_admin_start();
        $_SESSION[SH_ADMIN_SESSION_KEY] = true;
        $_SESSION['sh_admin_user'] = $user;
        return true;
    }
    return false;
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