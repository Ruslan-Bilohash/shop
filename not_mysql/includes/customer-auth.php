<?php

function sh_customer_auth_defaults(): array
{
    return [
        'customer_auth_enabled'       => true,
        'customer_phone_login'        => true,
        'customer_email_login'        => true,
        'customer_google_login'       => true,
        'customer_apple_login'        => true,
        'customer_google_client_id'   => '',
        'customer_google_client_secret' => '',
        'customer_apple_client_id'    => '',
        'customer_apple_team_id'      => '',
        'customer_apple_key_id'       => '',
        'customer_apple_private_key'  => '',
    ];
}

function sh_customer_auth_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $defaults = sh_customer_auth_defaults();
    $settings = is_array($settings) ? $settings : [];
    $out = [];
    foreach ($defaults as $key => $val) {
        $out[$key] = $settings[$key] ?? $val;
    }
    return $out;
}

function sh_customer_auth_enabled(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return !empty($s['customer_auth_enabled']);
}

function sh_customer_phone_login_enabled(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return sh_customer_auth_enabled($s) && !empty($s['customer_phone_login']);
}

function sh_customer_email_login_enabled(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return sh_customer_auth_enabled($s) && !empty($s['customer_email_login']);
}

function sh_customer_google_login_enabled(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return sh_customer_auth_enabled($s) && !empty($s['customer_google_login']);
}

function sh_customer_apple_login_enabled(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return sh_customer_auth_enabled($s) && !empty($s['customer_apple_login']);
}

function sh_demo_mode(): bool
{
    return defined('SH_DEMO_MODE') && SH_DEMO_MODE;
}

function sh_customer_google_login_available(?array $settings = null): bool
{
    if (sh_customer_google_login_enabled($settings)) {
        return true;
    }
    return sh_demo_mode() && sh_customer_auth_enabled($settings);
}

function sh_customer_apple_login_available(?array $settings = null): bool
{
    if (sh_customer_apple_login_enabled($settings)) {
        return true;
    }
    return sh_demo_mode() && sh_customer_auth_enabled($settings);
}

function sh_customer_google_demo_only(?array $settings = null): bool
{
    return sh_demo_mode() && sh_customer_auth_enabled($settings) && !sh_customer_google_login_enabled($settings);
}

function sh_customer_apple_demo_only(?array $settings = null): bool
{
    return sh_demo_mode() && sh_customer_auth_enabled($settings) && !sh_customer_apple_login_enabled($settings);
}

function sh_customer_google_configured(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return trim((string) ($s['customer_google_client_id'] ?? '')) !== '';
}

function sh_customer_apple_configured(?array $settings = null): bool
{
    $s = sh_customer_auth_settings($settings);
    return trim((string) ($s['customer_apple_client_id'] ?? '')) !== ''
        && trim((string) ($s['customer_apple_team_id'] ?? '')) !== ''
        && trim((string) ($s['customer_apple_key_id'] ?? '')) !== ''
        && trim((string) ($s['customer_apple_private_key'] ?? '')) !== '';
}

function sh_customer_google_redirect_uri(): string
{
    if (!function_exists('sh_absolute_url')) {
        require_once __DIR__ . '/seo.php';
    }
    return sh_absolute_url(sh_url('auth/google-callback.php'));
}

function sh_customer_apple_redirect_uri(): string
{
    if (!function_exists('sh_absolute_url')) {
        require_once __DIR__ . '/seo.php';
    }
    return sh_absolute_url(sh_url('auth/apple-callback.php'));
}

function sh_customer_logged_in(): bool
{
    return !empty($_SESSION['sh_customer']['id']);
}

/** @return array{id:string,phone?:string,provider?:string,name?:string}|null */
function sh_customer_user(): ?array
{
    $user = $_SESSION['sh_customer'] ?? null;
    return is_array($user) && !empty($user['id']) ? $user : null;
}

function sh_customer_normalize_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    return strlen($digits) >= 8 ? $digits : '';
}

function sh_customer_login_phone(string $phone): bool
{
    $normalized = sh_customer_normalize_phone($phone);
    if ($normalized === '') {
        return false;
    }
    $_SESSION['sh_customer'] = [
        'id'       => 'phone:' . $normalized,
        'phone'    => $normalized,
        'provider' => 'phone',
        'name'     => $normalized,
    ];
    sh_customer_after_login();
    return true;
}

function sh_customer_login_email(string $email): bool
{
    require_once __DIR__ . '/customer-profile.php';
    $normalized = sh_customer_normalize_email($email);
    if ($normalized === '') {
        return false;
    }
    $_SESSION['sh_customer'] = [
        'id'       => 'email:' . $normalized,
        'email'    => $normalized,
        'provider' => 'email',
        'name'     => $normalized,
    ];
    $profile = sh_customer_profile_get('email:' . $normalized);
    if ($profile === []) {
        sh_customer_profile_save('email:' . $normalized, ['email' => $normalized]);
    }
    sh_customer_after_login();
    return true;
}

function sh_customer_after_login(): void
{
    if (is_file(__DIR__ . '/customer-profile.php')) {
        require_once __DIR__ . '/customer-profile.php';
        sh_customer_sync_session_profile();
    }
}

function sh_customer_post_login_url(): string
{
    if (is_file(__DIR__ . '/customer-profile.php')) {
        require_once __DIR__ . '/customer-profile.php';
        if (!sh_customer_profile_complete()) {
            return sh_url('account.php?setup=1');
        }
    }
    return sh_url('index.php');
}

function sh_customer_login_demo(): void
{
    $_SESSION['sh_customer'] = [
        'id'       => 'demo:customer',
        'provider' => 'demo',
        'name'     => 'Demo Customer',
        'email'    => 'demo@bilohash.com',
        'role'     => 'customer',
    ];
    if (is_file(__DIR__ . '/billing-demo-stats.php')) {
        require_once __DIR__ . '/billing-demo-stats.php';
        $lang = trim((string) ($_GET['lang'] ?? $_POST['lang'] ?? 'en')) ?: 'en';
        sh_demo_stats_record('customer_demo_login', ['lang' => $lang, 'role' => 'customer']);
    }
    sh_customer_after_login();
}

function sh_customer_login_oauth(string $provider, string $externalId, string $name = '', string $email = ''): void
{
    $provider = in_array($provider, ['google', 'apple'], true) ? $provider : 'oauth';
    $id = $provider . ':' . $externalId;
    $_SESSION['sh_customer'] = [
        'id'       => $id,
        'provider' => $provider,
        'name'     => $name !== '' ? $name : ucfirst($provider) . ' user',
    ];
    if ($email !== '') {
        $_SESSION['sh_customer']['email'] = $email;
    }
    sh_customer_after_login();
}

function sh_customer_logout(): void
{
    unset($_SESSION['sh_customer']);
}

function sh_customer_sms_login_enabled(?array $settings = null): bool
{
    require_once __DIR__ . '/sms.php';
    $s = sh_sms_settings($settings);
    return sh_customer_phone_login_enabled($settings) && (!empty($s['sms_enabled']) || sh_sms_demo_forced());
}

function sh_customer_auth_apply_post(array $post, array $settings): array
{
    $existing = sh_customer_auth_settings($settings);
    $settings['customer_auth_enabled'] = !empty($post['customer_auth_enabled']);
    $settings['customer_phone_login'] = !empty($post['customer_phone_login']);
    $settings['customer_email_login'] = !empty($post['customer_email_login']);
    $settings['customer_google_login'] = !empty($post['customer_google_login']);
    $settings['customer_apple_login'] = !empty($post['customer_apple_login']);
    $settings['customer_google_client_id'] = trim($post['customer_google_client_id'] ?? '');
    $googleSecret = trim($post['customer_google_client_secret'] ?? '');
    $settings['customer_google_client_secret'] = $googleSecret !== ''
        ? $googleSecret
        : (string) ($existing['customer_google_client_secret'] ?? '');
    $settings['customer_apple_client_id'] = trim($post['customer_apple_client_id'] ?? '');
    $settings['customer_apple_team_id'] = trim($post['customer_apple_team_id'] ?? '');
    $settings['customer_apple_key_id'] = trim($post['customer_apple_key_id'] ?? '');
    $appleKey = trim($post['customer_apple_private_key'] ?? '');
    $settings['customer_apple_private_key'] = $appleKey !== ''
        ? $appleKey
        : (string) ($existing['customer_apple_private_key'] ?? '');
    require_once __DIR__ . '/sms.php';
    return sh_sms_settings_apply_post($post, $settings);
}

function sh_customer_display_name(): string
{
    $user = sh_customer_user();
    if ($user === null) {
        return '';
    }
    if (is_file(__DIR__ . '/customer-profile.php')) {
        require_once __DIR__ . '/customer-profile.php';
        $profile = sh_customer_profile_get();
        $full = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
        if ($full !== '') {
            return $full;
        }
    }
    if (!empty($user['email'])) {
        $e = (string) $user['email'];
        $at = strpos($e, '@');
        if ($at > 2) {
            return substr($e, 0, 2) . '***' . substr($e, $at);
        }
        return $e;
    }
    if (!empty($user['phone'])) {
        $p = (string) $user['phone'];
        if (strlen($p) > 6) {
            return substr($p, 0, 3) . '***' . substr($p, -2);
        }
        return $p;
    }
    return (string) ($user['name'] ?? '');
}