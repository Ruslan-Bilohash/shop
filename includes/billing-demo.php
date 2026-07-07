<?php
/**
 * Demo subscription & BILOHASH AI API usage (no real payment).
 */
declare(strict_types=1);

require_once __DIR__ . '/billing-pricing.php';

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
        'api_requests_limit' => SH_BILLING_DEMO_REQUESTS,
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
        'plan'          => $plan,
        'subscribed_at' => gmdate('c'),
        'payment_ref'   => $ref,
        'currency'      => (string) ($pricing['currency'] ?? 'EUR'),
    ];
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'ref' => '', 'error' => 'Could not save'];
    }
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'ref' => $ref, 'error' => ''];
}

/** @return array{ok:bool,state:array,error:string} */
function sh_billing_demo_cancel(): array
{
    $state = sh_billing_demo_defaults();
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'error' => 'Could not save'];
    }
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'error' => ''];
}

/** @return array{ok:bool,state:array,remaining:int,error:string} */
function sh_billing_demo_use_request(): array
{
    $state = sh_billing_demo_load();
    $used = (int) ($state['api_requests_used'] ?? 0);
    $limit = (int) ($state['api_requests_limit'] ?? SH_BILLING_DEMO_REQUESTS);
    if ($used >= $limit) {
        return ['ok' => false, 'state' => $state, 'remaining' => 0, 'error' => 'Demo API limit reached'];
    }
    $state['api_requests_used'] = $used + 1;
    if (!sh_billing_demo_save($state)) {
        return ['ok' => false, 'state' => sh_billing_demo_load(), 'remaining' => max(0, $limit - $used), 'error' => 'Could not save'];
    }
    return ['ok' => true, 'state' => sh_billing_demo_load(), 'remaining' => max(0, $limit - $used - 1), 'error' => ''];
}

function sh_billing_demo_is_active(): bool
{
    $state = sh_billing_demo_load();
    return !empty($state['plan']);
}

function sh_billing_demo_requests_remaining(): int
{
    $state = sh_billing_demo_load();
    return max(0, (int) $state['api_requests_limit'] - (int) $state['api_requests_used']);
}