<?php
/**
 * Shop CMS license runtime — 30-day trial, activation via bilohash.com/license.php keys.
 */
declare(strict_types=1);

const SH_LICENSE_TRIAL_DAYS = 30;
const SH_LICENSE_VERIFY_URL = 'https://bilohash.com/api/shop-license-verify.php';
const SH_LICENSE_REGISTER_URL = 'https://bilohash.com/api/shop-license-register.php';

function sh_license_state_path(): string
{
    return dirname(__DIR__) . '/data/license.state.json';
}

/** @return array<string, mixed> */
function sh_license_state(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $path = sh_license_state_path();
    $defaults = [
        'installed_at' => gmdate('c'),
        'trial_days'   => SH_LICENSE_TRIAL_DAYS,
        'license_key'  => '',
        'activated_at' => '',
        'license_exp'  => 0,
        'license_domain' => '',
        'status'       => 'trial',
    ];
    if (!is_file($path)) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($path, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n", LOCK_EX);
        $cache = $defaults;
        return $cache;
    }
    $raw = json_decode((string) file_get_contents($path), true);
    $cache = is_array($raw) ? array_merge($defaults, $raw) : $defaults;
    return $cache;
}

function sh_license_save_state(array $state): bool
{
    $path = sh_license_state_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents($path, $json . "\n", LOCK_EX) !== false;
}

function sh_license_host(): string
{
    $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
    return preg_replace('/:\d+$/', '', $host) ?: '';
}

/** @return array{status:string,trial_days_left:int,licensed:bool,expired:bool,message:string} */
function sh_license_status(): array
{
    $state = sh_license_state();
    if (($state['status'] ?? '') === 'licensed' && trim((string) ($state['license_key'] ?? '')) !== '') {
        $exp = (int) ($state['license_exp'] ?? 0);
        if ($exp > 0 && $exp < time()) {
            return [
                'status'           => 'expired',
                'trial_days_left'  => 0,
                'licensed'         => false,
                'expired'          => true,
                'message'          => 'License key expired',
            ];
        }
        return [
            'status'           => 'licensed',
            'trial_days_left'  => 0,
            'licensed'         => true,
            'expired'          => false,
            'message'          => '',
        ];
    }

    $installed = strtotime((string) ($state['installed_at'] ?? '')) ?: time();
    $trialDays = max(1, (int) ($state['trial_days'] ?? SH_LICENSE_TRIAL_DAYS));
    $ends = $installed + ($trialDays * 86400);
    $left = (int) max(0, ceil(($ends - time()) / 86400));

    if ($left > 0) {
        return [
            'status'           => 'trial',
            'trial_days_left'  => $left,
            'licensed'         => false,
            'expired'          => false,
            'message'          => '',
        ];
    }

    return [
        'status'           => 'expired',
        'trial_days_left'  => 0,
        'licensed'         => false,
        'expired'          => true,
        'message'          => 'Trial period ended',
    ];
}

function sh_license_is_active(): bool
{
    $s = sh_license_status();
    return $s['status'] === 'trial' || $s['status'] === 'licensed';
}

/** @return array{ok:bool,error:string}> */
function sh_license_activate(string $key): array
{
    $key = trim($key);
    if ($key === '') {
        return ['ok' => false, 'error' => 'Enter license key'];
    }

    $local = sh_license_verify_local($key);
    if (!$local['ok']) {
        $remote = sh_license_verify_remote($key);
        if (!$remote['ok']) {
            return ['ok' => false, 'error' => $remote['error'] ?: $local['error']];
        }
        $local = $remote;
    }

    $state = sh_license_state();
    $state['status'] = 'licensed';
    $state['license_key'] = $key;
    $state['activated_at'] = gmdate('c');
    $state['license_exp'] = (int) ($local['exp'] ?? 0);
    $state['license_domain'] = (string) ($local['domain'] ?? '');
    if (!sh_license_save_state($state)) {
        return ['ok' => false, 'error' => 'Could not save license state'];
    }
    sh_license_register_site($key);
    return ['ok' => true, 'error' => ''];
}

function sh_license_key_fingerprint(string $key): string
{
    $key = strtoupper(trim($key));
    $parts = explode('.', $key);
    $body = $parts[1] ?? $key;
    return substr(hash('sha256', $body), 0, 16);
}

/**
 * @return array{ok:bool,sites_count:int,sites:list,exp:int,error:string}
 */
function sh_license_register_site(?string $key = null): array
{
    require_once __DIR__ . '/version.php';
    $state = sh_license_state();
    $key = trim($key ?? (string) ($state['license_key'] ?? ''));
    if ($key === '') {
        return ['ok' => false, 'sites_count' => 0, 'sites' => [], 'exp' => 0, 'error' => 'No license key'];
    }
    $payload = json_encode([
        'key'     => $key,
        'domain'  => sh_license_host(),
        'product' => 'shop',
        'version' => sh_version(),
    ], JSON_UNESCAPED_UNICODE);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 12,
        ],
    ]);
    $raw = @file_get_contents(SH_LICENSE_REGISTER_URL, false, $ctx);
    if ($raw === false || $raw === '') {
        return ['ok' => false, 'sites_count' => 1, 'sites' => [['domain' => sh_license_host(), 'current' => true]], 'exp' => 0, 'error' => 'Registry unreachable'];
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['ok'])) {
        return ['ok' => false, 'sites_count' => 0, 'sites' => [], 'exp' => 0, 'error' => (string) ($data['error'] ?? 'Register failed')];
    }
    $sites = is_array($data['sites'] ?? null) ? $data['sites'] : [];
    $state['connected_sites'] = $sites;
    $state['sites_count'] = (int) ($data['sites_count'] ?? count($sites));
    $state['sites_synced_at'] = gmdate('c');
    if ((int) ($data['exp'] ?? 0) > 0) {
        $state['license_exp'] = (int) $data['exp'];
    }
    sh_license_save_state($state);
    return [
        'ok'          => true,
        'sites_count' => (int) ($data['sites_count'] ?? count($sites)),
        'sites'       => $sites,
        'exp'         => (int) ($data['exp'] ?? 0),
        'error'       => '',
    ];
}

/**
 * @return array{ok:bool,valid:bool,exp:int,exp_label:string,days_left:int,renew_soon:bool,domain:string,sites_count:int,sites:list,error:string,source:string}
 */
function sh_license_verify_current(bool $refreshRegistry = false): array
{
    $state = sh_license_state();
    $status = sh_license_status();
    $key = trim((string) ($state['license_key'] ?? ''));
    $host = sh_license_host();
    $base = [
        'ok'          => false,
        'valid'       => false,
        'exp'         => 0,
        'exp_label'   => '',
        'days_left'   => 0,
        'renew_soon'  => false,
        'domain'      => $host,
        'sites_count' => max(1, (int) ($state['sites_count'] ?? 0)),
        'sites'       => is_array($state['connected_sites'] ?? null) ? $state['connected_sites'] : [],
        'error'       => '',
        'source'      => '',
    ];

    if ($status['status'] === 'trial') {
        $base['ok'] = true;
        $base['valid'] = true;
        $base['days_left'] = (int) ($status['trial_days_left'] ?? 0);
        $base['sites_count'] = 1;
        $base['sites'] = [['domain' => $host, 'current' => true, 'last_seen' => gmdate('c')]];
        $base['source'] = 'trial';
        return $base;
    }

    if ($key === '') {
        $base['error'] = $status['message'] ?: 'No license key';
        return $base;
    }

    $local = sh_license_verify_local($key);
    $source = 'local';
    if (!$local['ok']) {
        $remote = sh_license_verify_remote($key);
        if (!$remote['ok']) {
            $base['error'] = $remote['error'] ?: $local['error'];
            return $base;
        }
        $local = $remote;
        $source = 'remote';
    }

    $exp = (int) ($local['exp'] ?? (int) ($state['license_exp'] ?? 0));
    $daysLeft = $exp > 0 ? (int) max(0, ceil(($exp - time()) / 86400)) : 0;
    $base['ok'] = true;
    $base['valid'] = true;
    $base['exp'] = $exp;
    $base['exp_label'] = $exp > 0 ? gmdate('Y-m-d', $exp) : '';
    $base['days_left'] = $daysLeft;
    $base['renew_soon'] = $daysLeft > 0 && $daysLeft <= 30;
    $base['domain'] = (string) ($local['domain'] ?? $host);
    $base['source'] = $source;

    if ($refreshRegistry || empty($state['connected_sites'])) {
        $reg = sh_license_register_site($key);
        if ($reg['ok']) {
            $base['sites_count'] = $reg['sites_count'];
            $base['sites'] = $reg['sites'];
            if ($reg['exp'] > 0) {
                $base['exp'] = $reg['exp'];
                $base['days_left'] = (int) max(0, ceil(($reg['exp'] - time()) / 86400));
                $base['renew_soon'] = $base['days_left'] > 0 && $base['days_left'] <= 30;
                $base['exp_label'] = gmdate('Y-m-d', $reg['exp']);
            }
        }
    } else {
        $base['sites_count'] = max(1, (int) ($state['sites_count'] ?? count($base['sites'])));
    }

    return $base;
}

/** @return array{ok:bool,exp:int,domain:string,error:string} */
function sh_license_verify_local(string $key): array
{
    $lib = dirname(__DIR__, 2) . '/includes/shop-license.php';
    if (!is_file($lib)) {
        $lib = __DIR__ . '/shop-license.php';
    }
    if (!is_file($lib)) {
        return ['ok' => false, 'exp' => 0, 'domain' => '', 'error' => 'License library missing'];
    }
    require_once $lib;
    if (!function_exists('shop_license_parse_key')) {
        return ['ok' => false, 'exp' => 0, 'domain' => '', 'error' => 'License parser missing'];
    }
    $parsed = shop_license_parse_key($key, sh_license_host());
    if (!$parsed['ok'] || !$parsed['valid']) {
        return ['ok' => false, 'exp' => 0, 'domain' => '', 'error' => (string) ($parsed['error'] ?? 'Invalid key')];
    }
    return [
        'ok'     => true,
        'exp'    => (int) ($parsed['payload']['e'] ?? 0),
        'domain' => (string) ($parsed['payload']['d'] ?? '*'),
        'error'  => '',
    ];
}

/** @return array{ok:bool,exp:int,domain:string,error:string} */
function sh_license_verify_remote(string $key): array
{
    $payload = json_encode(['key' => $key, 'domain' => sh_license_host(), 'product' => 'shop'], JSON_UNESCAPED_UNICODE);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => $payload,
            'timeout' => 12,
        ],
    ]);
    $raw = @file_get_contents(SH_LICENSE_VERIFY_URL, false, $ctx);
    if ($raw === false || $raw === '') {
        return ['ok' => false, 'exp' => 0, 'domain' => '', 'error' => 'License server unreachable'];
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['ok'])) {
        return ['ok' => false, 'exp' => 0, 'domain' => '', 'error' => (string) ($data['error'] ?? 'Verification failed')];
    }
    return [
        'ok'     => true,
        'exp'    => (int) ($data['exp'] ?? 0),
        'domain' => (string) ($data['domain'] ?? '*'),
        'error'  => '',
    ];
}

/**
 * Gate CMS update checks — trial or valid licensed key required.
 *
 * @return array{allowed:bool,reason:string,message:string,status:array}
 */
function sh_license_can_check_updates(): array
{
    $status = sh_license_status();
    if ($status['status'] === 'trial') {
        return ['allowed' => true, 'reason' => '', 'message' => '', 'status' => $status];
    }
    if ($status['status'] === 'licensed') {
        $state = sh_license_state();
        $key = trim((string) ($state['license_key'] ?? ''));
        if ($key !== '') {
            $verified = sh_license_verify_local($key);
            if (!$verified['ok']) {
                $verified = sh_license_verify_remote($key);
            }
            if (!$verified['ok']) {
                return [
                    'allowed' => false,
                    'reason'  => 'license_invalid',
                    'message' => $verified['error'] ?: 'License key is no longer valid',
                    'status'  => $status,
                ];
            }
        }
        return ['allowed' => true, 'reason' => '', 'message' => '', 'status' => $status];
    }
    $message = $status['status'] === 'expired'
        ? 'Trial expired — activate a license key to check for updates'
        : ($status['message'] ?: 'License required');
    return [
        'allowed' => false,
        'reason'  => 'license_expired',
        'message' => $message,
        'status'  => $status,
    ];
}

function sh_license_require_admin(): void
{
    if (sh_license_is_active()) {
        return;
    }
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (str_contains($script, 'license.php') || str_contains($script, 'login.php') || str_contains($script, 'logout.php')) {
        return;
    }
    header('Location: ' . sh_admin_url('license.php'), true, 302);
    exit;
}