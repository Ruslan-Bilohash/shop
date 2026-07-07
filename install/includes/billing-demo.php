<?php
/**
 * Demo subscription & BILOHASH AI API usage (no real payment).
 */
declare(strict_types=1);

require_once __DIR__ . '/billing-pricing.php';
require_once __DIR__ . '/billing-demo-stats.php';
require_once __DIR__ . '/admin-api-usage.php';

function sh_billing_demo_path(): string
{
    return dirname(__DIR__) . '/data/billing-demo.json';
}

/** @return array{plan:?string,api_requests_used:int,api_requests_limit:int,subscribed_at:?string,payment_ref:?string,currency:string} */
function sh_billing_demo_defaults(): array
{
    return [
        'plan'               => null,
        'api_requests_used'  => 0,
        'api_requests_limit' => 0,
        'subscribed_at'      => null,
        'payment_ref'        => null,
        'currency'           => 'EUR',
    ];
}

/** @return array{plan:?string,api_requests_used:int,api_requests_limit:int,subscribed_at:?string,payment_ref:?string,currency:string} */
function sh_billing_demo_load(): array
{
    $path = sh_billing_demo_path();
    if (!is_file($path)) {
        return sh_billing_demo_defaults();
    }
    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        return sh_billing_demo_defaults();
    }
    return array_merge(sh_billing_demo_defaults(), $data);
}

/** @param array<string, mixed> $state */
function sh_billing_demo_save(array $state): bool
{
    $dir = dirname(sh_billing_demo_path());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $merged = array_merge(sh_billing_demo_load(), $state);
    $json = json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(sh_billing_demo_path(), $json . "\n", LOCK_EX) !== false;
}

/** @return array{ok:bool,state:array,ref:string,error:string} */
function sh_billing_demo_subscribe(string $plan, string $lang = 'en'): array
{
    $plan = in_array($plan, ['monthly', 'yearly'], true) ? $plan : '';
    if ($plan === '') {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'ref' => '', 'error' => 'Invalid plan'];
    }

    $pricing = sh_billing_pricing_for_lang($lang);
    $ref = 'DEMO-' . strtoupper($plan) . '-' . gmdate('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    $state = [
        'plan'               => $plan,
        'api_requests_used'  => 0,
        'api_requests_limit' => sh_billing_api_limit_for_plan($plan),
        'subscribed_at'      => gmdate('c'),
        'payment_ref'        => $ref,
        'currency'           => (string) ($pricing['currency'] ?? 'EUR'),
    ];
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'ref' => '', 'error' => 'Could not save'];
    }
    sh_demo_stats_record('billing_subscribe', [
        'lang' => $lang,
        'plan' => $plan,
        'user' => (string) ($_SESSION['sh_admin_user'] ?? ''),
        'role' => function_exists('sh_admin_role') ? sh_admin_role() : '',
    ]);
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'ref' => $ref, 'error' => ''];
}

/** @return array{ok:bool,state:array,error:string} */
function sh_billing_demo_cancel(): array
{
    $state = sh_billing_demo_defaults();
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'error' => 'Could not save'];
    }
    sh_demo_stats_record('billing_cancel', [
        'user' => (string) ($_SESSION['sh_admin_user'] ?? ''),
        'role' => function_exists('sh_admin_role') ? sh_admin_role() : '',
    ]);
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'error' => ''];
}

/** @return array{ok:bool,state:array,remaining:int,error:string} */
function sh_billing_demo_use_request(): array
{
    if (function_exists('sh_admin_is_demo_user') && sh_admin_is_demo_user()) {
        $q = sh_admin_api_try_consume();
        $state = sh_billing_demo_load();
        $limit = SH_ADMIN_DEMO_API_LIMIT;
        $remaining = (int) ($q['remaining'] ?? 0);
        $used = $limit - max(0, $remaining);
        if (!$q['ok']) {
            $state['api_requests_used'] = $used;
            $state['api_requests_limit'] = $limit;
            return ['ok' => false, 'state' => $state, 'remaining' => 0, 'error' => $q['error']];
        }
        $state['api_requests_used'] = $used;
        $state['api_requests_limit'] = $limit;
        sh_demo_stats_record('billing_api', [
            'user' => (string) ($_SESSION['sh_admin_user'] ?? ''),
            'role' => function_exists('sh_admin_role') ? sh_admin_role() : 'demo',
        ]);
        return ['ok' => true, 'state' => $state, 'remaining' => $remaining, 'error' => ''];
    }

    $state = sh_billing_demo_load();
    $plan = (string) ($state['plan'] ?? '');
    if ($plan === '') {
        return ['ok' => false, 'state' => $state, 'remaining' => 0, 'error' => 'No active subscription'];
    }
    $used = (int) ($state['api_requests_used'] ?? 0);
    $limit = (int) ($state['api_requests_limit'] ?? sh_billing_api_limit_for_plan($plan));
    if ($limit <= 0 || $used >= $limit) {
        return ['ok' => false, 'state' => $state, 'remaining' => 0, 'error' => 'API limit reached'];
    }
    $state['api_requests_used'] = $used + 1;
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'remaining' => max(0, $limit - $used), 'error' => 'Could not save'];
    }
    sh_demo_stats_record('billing_api', [
        'user' => (string) ($_SESSION['sh_admin_user'] ?? ''),
        'role' => function_exists('sh_admin_role') ? sh_admin_role() : '',
    ]);
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'remaining' => max(0, $limit - $used - 1), 'error' => ''];
}

function sh_billing_demo_is_active(): bool
{
    $state = sh_billing_demo_load();
    return !empty($state['plan']);
}

function sh_billing_demo_requests_remaining(): int
{
    if (function_exists('sh_admin_is_demo_user') && sh_admin_is_demo_user()) {
        $remaining = sh_admin_api_remaining();
        return $remaining >= 0 ? $remaining : 0;
    }
    $state = sh_billing_demo_load();
    return max(0, (int) $state['api_requests_limit'] - (int) $state['api_requests_used']);
}