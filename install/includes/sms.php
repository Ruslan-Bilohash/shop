<?php

/**
 * SMS OTP for phone registration — per-country provider routing (demo + live hooks).
 */

function sh_sms_defaults(): array
{
    return [
        'sms_enabled'           => false,
        'sms_demo_mode'         => true,
        'sms_code_ttl'          => 300,
        'sms_provider_no'       => 'gatewayapi',
        'sms_api_key_no'        => '',
        'sms_sender_no'         => 'ShopCMS',
        'sms_provider_ua'       => 'turbosms',
        'sms_api_key_ua'        => '',
        'sms_sender_ua'         => 'ShopCMS',
        'sms_provider_se'       => 'gatewayapi',
        'sms_api_key_se'        => '',
        'sms_sender_se'         => 'ShopCMS',
        'sms_provider_ru'       => 'smsru',
        'sms_api_key_ru'        => '',
        'sms_sender_ru'         => 'ShopCMS',
        'sms_provider_lt'       => 'gatewayapi',
        'sms_api_key_lt'        => '',
        'sms_sender_lt'         => 'ShopCMS',
        'sms_provider_default'  => 'gatewayapi',
        'sms_api_key_default'   => '',
        'sms_sender_default'    => 'ShopCMS',
    ];
}

function sh_sms_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    require_once __DIR__ . '/store-settings.php';
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $out = sh_sms_defaults();
    foreach (array_keys($out) as $key) {
        if (array_key_exists($key, $s)) {
            $out[$key] = $s[$key];
        }
    }
    $out['sms_enabled'] = !empty($s['sms_enabled']);
    $out['sms_demo_mode'] = !empty($s['sms_demo_mode']) || sh_sms_demo_forced();
    return $out;
}

function sh_sms_demo_forced(): bool
{
    return (defined('SH_DEMO_MODE') && SH_DEMO_MODE) || !function_exists('sh_payment_is_configured');
}

function sh_sms_detect_country(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '380')) {
        return 'ua';
    }
    if (str_starts_with($digits, '47')) {
        return 'no';
    }
    if (str_starts_with($digits, '46')) {
        return 'se';
    }
    if (str_starts_with($digits, '7') || str_starts_with($digits, '375')) {
        return 'ru';
    }
    if (str_starts_with($digits, '370')) {
        return 'lt';
    }
    return 'default';
}

function sh_sms_provider_for_country(string $country, ?array $settings = null): array
{
    $cfg = sh_sms_settings($settings);
    $country = strtolower($country);
    $providerKey = 'sms_provider_' . $country;
    $apiKeyKey = 'sms_api_key_' . $country;
    $senderKey = 'sms_sender_' . $country;
    if (!array_key_exists($providerKey, $cfg)) {
        $providerKey = 'sms_provider_default';
        $apiKeyKey = 'sms_api_key_default';
        $senderKey = 'sms_sender_default';
    }
    return [
        'country'  => $country,
        'provider' => (string) ($cfg[$providerKey] ?? 'gatewayapi'),
        'api_key'  => trim((string) ($cfg[$apiKeyKey] ?? '')),
        'sender'   => trim((string) ($cfg[$senderKey] ?? 'ShopCMS')),
        'demo'     => !empty($cfg['sms_demo_mode']),
    ];
}

function sh_sms_generate_code(): string
{
    return (string) random_int(100000, 999999);
}

function sh_sms_store_pending(string $phone, string $code, int $ttl = 300): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['sh_sms_otp'] = [
        'phone'   => $phone,
        'code'    => $code,
        'expires' => time() + max(60, $ttl),
    ];
}

function sh_sms_verify_code(string $phone, string $code): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $pending = $_SESSION['sh_sms_otp'] ?? null;
    if (!is_array($pending)) {
        return false;
    }
    require_once __DIR__ . '/customer-auth.php';
    $normalized = sh_customer_normalize_phone($phone);
    if ($normalized === '' || ($pending['phone'] ?? '') !== $normalized) {
        return false;
    }
    if (time() > (int) ($pending['expires'] ?? 0)) {
        unset($_SESSION['sh_sms_otp']);
        return false;
    }
    $ok = hash_equals((string) ($pending['code'] ?? ''), trim($code));
    if ($ok) {
        unset($_SESSION['sh_sms_otp']);
    }
    return $ok;
}

/**
 * @return array{ok:bool,demo:bool,error:string,code?:string}
 */
function sh_sms_send_otp(string $phone, ?array $settings = null): array
{
    require_once __DIR__ . '/customer-auth.php';
    $normalized = sh_customer_normalize_phone($phone);
    if ($normalized === '') {
        return ['ok' => false, 'demo' => true, 'error' => 'invalid_phone'];
    }

    $cfg = sh_sms_settings($settings);
    if (!$cfg['sms_enabled'] && !sh_sms_demo_forced()) {
        return ['ok' => false, 'demo' => true, 'error' => 'sms_disabled'];
    }

    $country = sh_sms_detect_country($normalized);
    $provider = sh_sms_provider_for_country($country, $settings);
    $code = sh_sms_generate_code();
    $ttl = max(60, (int) ($cfg['sms_code_ttl'] ?? 300));
    sh_sms_store_pending($normalized, $code, $ttl);

    $message = 'Shop CMS code: ' . $code;

    if ($provider['demo'] || $provider['api_key'] === '') {
        return ['ok' => true, 'demo' => true, 'error' => '', 'code' => $code];
    }

    $sent = sh_sms_dispatch($normalized, $message, $provider);
    if (!$sent['ok']) {
        return ['ok' => false, 'demo' => false, 'error' => $sent['error'] ?? 'send_failed'];
    }
    return ['ok' => true, 'demo' => false, 'error' => ''];
}

/**
 * @return array{ok:bool,error:string}
 */
function sh_sms_dispatch(string $phone, string $message, array $provider): array
{
    $name = strtolower((string) ($provider['provider'] ?? ''));
    return match ($name) {
        'gatewayapi' => sh_sms_send_gatewayapi($phone, $message, $provider),
        'turbosms'   => sh_sms_send_turbosms($phone, $message, $provider),
        'smsru'      => sh_sms_send_smsru($phone, $message, $provider),
        default      => ['ok' => false, 'error' => 'unknown_provider'],
    };
}

function sh_sms_send_gatewayapi(string $phone, string $message, array $provider): array
{
    $key = trim((string) ($provider['api_key'] ?? ''));
    if ($key === '') {
        return ['ok' => false, 'error' => 'missing_api_key'];
    }
    $payload = json_encode([
        'sender'  => $provider['sender'] ?? 'ShopCMS',
        'message' => $message,
        'recipients' => [['msisdn' => $phone]],
    ], JSON_UNESCAPED_UNICODE);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAuthorization: Token " . $key . "\r\n",
            'content' => $payload,
            'timeout' => 15,
        ],
    ]);
    $raw = @file_get_contents('https://gatewayapi.com/rest/mtsms', false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'gatewayapi_http'];
    }
    return ['ok' => true, 'error' => ''];
}

function sh_sms_send_turbosms(string $phone, string $message, array $provider): array
{
    $key = trim((string) ($provider['api_key'] ?? ''));
    if ($key === '') {
        return ['ok' => false, 'error' => 'missing_api_key'];
    }
    $body = http_build_query([
        'recipients' => $phone,
        'sms'        => $message,
        'sender'     => $provider['sender'] ?? 'ShopCMS',
        'token'      => $key,
    ]);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $body,
            'timeout' => 15,
        ],
    ]);
    $raw = @file_get_contents('https://api.turbosms.ua/message/send.json', false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'turbosms_http'];
    }
    return ['ok' => true, 'error' => ''];
}

function sh_sms_send_smsru(string $phone, string $message, array $provider): array
{
    $key = trim((string) ($provider['api_key'] ?? ''));
    if ($key === '') {
        return ['ok' => false, 'error' => 'missing_api_key'];
    }
    $url = 'https://sms.ru/sms/send?api_id=' . rawurlencode($key)
        . '&to=' . rawurlencode($phone)
        . '&msg=' . rawurlencode($message)
        . '&json=1';
    $raw = @file_get_contents($url);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'smsru_http'];
    }
    return ['ok' => true, 'error' => ''];
}

function sh_sms_settings_apply_post(array $post, array $settings): array
{
    require_once __DIR__ . '/store-settings.php';
    $settings = sh_merge_store_settings($settings);
    $settings['sms_enabled'] = !empty($post['sms_enabled']);
    $settings['sms_demo_mode'] = !empty($post['sms_demo_mode']);
    $settings['sms_code_ttl'] = max(60, min(900, (int) ($post['sms_code_ttl'] ?? 300)));
    foreach (['no', 'ua', 'se', 'ru', 'lt', 'default'] as $cc) {
        $settings['sms_provider_' . $cc] = trim($post['sms_provider_' . $cc] ?? $settings['sms_provider_' . $cc] ?? '');
        $settings['sms_sender_' . $cc] = trim($post['sms_sender_' . $cc] ?? $settings['sms_sender_' . $cc] ?? '');
        if (trim($post['sms_api_key_' . $cc] ?? '') !== '') {
            $settings['sms_api_key_' . $cc] = trim($post['sms_api_key_' . $cc]);
        }
    }
    return $settings;
}