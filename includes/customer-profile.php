<?php

require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/json-store.php';

function sh_customer_profiles_file(): string
{
    return sh_data_path('customer-profiles.json');
}

/** @return array<string, array<string, mixed>> */
function sh_customer_profiles_load(): array
{
    $file = sh_customer_profiles_file();
    $data = sh_json_store_decode($file, true);
    return is_array($data) ? $data : [];
}

function sh_customer_profiles_save(array $profiles): bool
{
    $json = json_encode($profiles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return sh_json_store_write(sh_customer_profiles_file(), $json);
}

function sh_customer_profile_id(): ?string
{
    $user = sh_customer_user();
    return $user['id'] ?? null;
}

/** @return array<string, mixed> */
function sh_customer_profile_get(?string $customerId = null): array
{
    $id = $customerId ?? sh_customer_profile_id();
    if ($id === null || $id === '') {
        return [];
    }
    $all = sh_customer_profiles_load();
    return is_array($all[$id] ?? null) ? $all[$id] : [];
}

function sh_customer_profile_save(string $customerId, array $data): bool
{
    $all = sh_customer_profiles_load();
    $existing = is_array($all[$customerId] ?? null) ? $all[$customerId] : [];
    $merged = array_merge($existing, $data, [
        'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ]);
    $all[$customerId] = $merged;
    return sh_customer_profiles_save($all);
}

/** @return list<string> */
function sh_customer_profile_required_fields(): array
{
    return ['first_name', 'last_name', 'email', 'country', 'city', 'postal_code', 'address'];
}

function sh_customer_profile_complete(?string $customerId = null): bool
{
    $profile = sh_customer_profile_get($customerId);
    foreach (sh_customer_profile_required_fields() as $field) {
        if (trim((string) ($profile[$field] ?? '')) === '') {
            return false;
        }
    }
    return true;
}

function sh_customer_normalize_email(string $email): string
{
    $email = strtolower(trim($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function sh_customer_initials(array $profile): string
{
    $f = function_exists('mb_substr') ? mb_substr(trim((string) ($profile['first_name'] ?? '')), 0, 1) : substr(trim((string) ($profile['first_name'] ?? '')), 0, 1);
    $l = function_exists('mb_substr') ? mb_substr(trim((string) ($profile['last_name'] ?? '')), 0, 1) : substr(trim((string) ($profile['last_name'] ?? '')), 0, 1);
    $ini = strtoupper($f . $l);
    return $ini !== '' ? $ini : '?';
}

function sh_customer_avatar_hue(string $seed): int
{
    return abs(crc32($seed)) % 360;
}

function sh_customer_profile_redirect(): void
{
    if (!function_exists('sh_customer_logged_in') || !sh_customer_logged_in()) {
        return;
    }
    if (sh_customer_profile_complete()) {
        return;
    }
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $exempt = ['login.php', 'logout.php', 'account.php', '_health.php', 'maintenance.php'];
    foreach ($exempt as $name) {
        if ($script === $name) {
            return;
        }
    }
    if (str_starts_with($script, 'api') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        return;
    }
    header('Location: ' . sh_url('account.php?setup=1'));
    exit;
}

function sh_customer_sync_session_profile(): void
{
    if (!sh_customer_logged_in()) {
        return;
    }
    $id = sh_customer_profile_id();
    if ($id === null) {
        return;
    }
    $profile = sh_customer_profile_get($id);
    if ($profile === []) {
        return;
    }
    $name = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
    if ($name !== '') {
        $_SESSION['sh_customer']['name'] = $name;
    }
    if (!empty($profile['email'])) {
        $_SESSION['sh_customer']['email'] = $profile['email'];
    }
    if (!empty($profile['avatar'])) {
        $_SESSION['sh_customer']['avatar'] = $profile['avatar'];
    }
}

function sh_customer_process_avatar_upload(string $customerId, array $file): array
{
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => 'No file'];
    }
    require_once __DIR__ . '/image-upload.php';
    $result = sh_process_uploaded_image($file['tmp_name'], 'avatars', 400, 80);
    if (!$result['ok']) {
        return $result;
    }
    $profile = sh_customer_profile_get($customerId);
    if (!empty($profile['avatar'])) {
        sh_delete_uploaded_file($profile['avatar']);
    }
    sh_customer_profile_save($customerId, ['avatar' => $result['url']]);
    $_SESSION['sh_customer']['avatar'] = $result['url'];
    return ['ok' => true, 'url' => $result['url']];
}