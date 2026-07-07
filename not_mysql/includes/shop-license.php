<?php
/**
 * BILOHASH Shop CMS license — 30-day trial, signed keys (BHSHOP.…).
 * Used by bilohash.com/license.php and Shop CMS runtime.
 */
declare(strict_types=1);

const SHOP_LICENSE_PRODUCT = 'shop';
const SHOP_LICENSE_TRIAL_DAYS = 30;
const SHOP_LICENSE_PREFIX = 'BHSHOP';

function shop_license_secret(): string
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $file = __DIR__ . '/../data/shop-license-secret.php';
    if (is_readable($file)) {
        $v = require $file;
        if (is_string($v) && strlen($v) >= 32) {
            $secret = $v;
            return $secret;
        }
    }
    $env = getenv('BH_SHOP_LICENSE_SECRET');
    $secret = is_string($env) && strlen($env) >= 32 ? $env : 'bilohash-shop-cms-license-v1-change-in-production';
    return $secret;
}

/** @return array{ok:bool,valid:bool,expired:bool,domain_ok:bool,payload:array,error:string} */
function shop_license_parse_key(string $key, string $domain = ''): array
{
    $key = strtoupper(trim($key));
    if ($key === '' || !str_starts_with($key, SHOP_LICENSE_PREFIX)) {
        return ['ok' => false, 'valid' => false, 'expired' => false, 'domain_ok' => false, 'payload' => [], 'error' => 'Invalid key format'];
    }
    $parts = explode('.', $key);
    if (count($parts) !== 3 || $parts[0] !== SHOP_LICENSE_PREFIX) {
        return ['ok' => false, 'valid' => false, 'expired' => false, 'domain_ok' => false, 'payload' => [], 'error' => 'Invalid key structure'];
    }
    $body = $parts[1];
    $sig = $parts[2];
    $expected = strtoupper(substr(hash_hmac('sha256', $body, shop_license_secret()), 0, 16));
    if (!hash_equals($expected, $sig)) {
        return ['ok' => false, 'valid' => false, 'expired' => false, 'domain_ok' => false, 'payload' => [], 'error' => 'Invalid signature'];
    }
    $decoded = base64_decode(strtr($body, '-_', '+/') . str_repeat('=', (4 - strlen($body) % 4) % 4), true);
    if ($decoded === false) {
        return ['ok' => false, 'valid' => false, 'expired' => false, 'domain_ok' => false, 'payload' => [], 'error' => 'Invalid payload'];
    }
    $payload = json_decode($decoded, true);
    if (!is_array($payload) || ($payload['p'] ?? '') !== SHOP_LICENSE_PRODUCT) {
        return ['ok' => false, 'valid' => false, 'expired' => false, 'domain_ok' => false, 'payload' => [], 'error' => 'Wrong product'];
    }
    $exp = (int) ($payload['e'] ?? 0);
    $expired = $exp > 0 && $exp < time();
    $licDomain = strtolower(trim((string) ($payload['d'] ?? '*')));
    $host = strtolower(trim($domain));
    $domainOk = $licDomain === '*' || $licDomain === '' || $host === '' || $host === $licDomain
        || ($licDomain !== '' && str_ends_with($host, '.' . $licDomain));
    return [
        'ok'        => true,
        'valid'     => !$expired && $domainOk,
        'expired'   => $expired,
        'domain_ok' => $domainOk,
        'payload'   => $payload,
        'error'     => $expired ? 'License expired' : ($domainOk ? '' : 'Domain mismatch'),
    ];
}

/** Generate a license key. $domain = '*' for any host. $years = validity length. */
function shop_license_generate_key(string $domain = '*', int $years = 1, string $email = ''): string
{
    $payload = [
        'p' => SHOP_LICENSE_PRODUCT,
        'd' => $domain === '' ? '*' : strtolower($domain),
        'e' => strtotime('+' . max(1, $years) . ' year'),
        'v' => defined('SH_VERSION') ? SH_VERSION : '1.7.1',
        'm' => substr(trim($email), 0, 120),
        'i' => gmdate('Y-m-d'),
    ];
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $body = rtrim(strtr(base64_encode((string) $json), '+/', '-_'), '=');
    $sig = strtoupper(substr(hash_hmac('sha256', $body, shop_license_secret()), 0, 16));
    return SHOP_LICENSE_PREFIX . '.' . $body . '.' . $sig;
}