<?php

/**
 * Shop CMS subscription pricing — NOK 49/mo base with FX conversion per language.
 */

$__shEcoPricing = dirname(__DIR__, 2) . '/includes/ecosystem-pricing.php';
if (is_file($__shEcoPricing)) {
    require_once $__shEcoPricing;
}
require_once __DIR__ . '/subscription-links.php';

define('SH_BILLING_NOK_MONTHLY', defined('ECOSYSTEM_SCRIPT_PRICE_NOK') ? (int) ECOSYSTEM_SCRIPT_PRICE_NOK : 49);
define('SH_BILLING_NOK_YEARLY', defined('ECOSYSTEM_FULL_PRICE_NOK') ? (int) ECOSYSTEM_FULL_PRICE_NOK : 249);
/** Full CMS library plan — monthly subscription (alias for legacy "yearly" plan id). */
define('SH_BILLING_NOK_FULL_MONTHLY', SH_BILLING_NOK_YEARLY);
define('SH_BILLING_API_REQUESTS_MONTHLY', 30);
define('SH_BILLING_API_REQUESTS_YEARLY', 100);
/** @deprecated use SH_BILLING_API_REQUESTS_YEARLY */
define('SH_BILLING_DEMO_REQUESTS', SH_BILLING_API_REQUESTS_YEARLY);

function sh_billing_api_limit_for_plan(string $plan): int
{
    return $plan === 'yearly' ? SH_BILLING_API_REQUESTS_YEARLY : SH_BILLING_API_REQUESTS_MONTHLY;
}

/** @deprecated use SH_BILLING_NOK_MONTHLY — kept for banner placeholders */
define('SH_BILLING_EUR_MONTHLY', SH_BILLING_NOK_MONTHLY);
/** @deprecated use SH_BILLING_NOK_YEARLY */
define('SH_BILLING_EUR_YEARLY', SH_BILLING_NOK_YEARLY);

/** @return array<string, array{monthly:float,yearly:float}> */
function sh_billing_fixed_prices(): array
{
    return [];
}

function sh_billing_ecosystem_inc(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $path = dirname(__DIR__, 2) . '/includes/ecosystem-pricing.php';
    if (is_file($path)) {
        require_once $path;
    }
    $done = true;
}

/** @return array<string, string> */
function sh_billing_lang_currencies(): array
{
    return [
        'no' => 'NOK',
        'uk' => 'UAH',
        'en' => 'USD',
        'ru' => 'EUR',
        'sv' => 'EUR',
        'lt' => 'EUR',
    ];
}

function sh_billing_fx_path(): string
{
    return __DIR__ . '/../data/pricing-fx.json';
}

/** @return array{base:string,updated:string,rates:array<string,float>,source:string} */
function sh_billing_fx_fallback(): array
{
    return [
        'base'    => 'NOK',
        'updated' => gmdate('c'),
        'rates'   => [
            'NOK' => 1.0,
            'EUR' => 0.087,
            'UAH' => 3.75,
            'USD' => 0.094,
            'SEK' => 0.97,
            'GBP' => 0.075,
        ],
        'source' => 'fallback',
    ];
}

/** @return array{base:string,updated:string,rates:array<string,float>,source:string} */
function sh_billing_fx_load(): array
{
    $path = sh_billing_fx_path();
    if (!is_file($path)) {
        return sh_billing_fx_fallback();
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '', true);
    if (!is_array($data) || !is_array($data['rates'] ?? null)) {
        return sh_billing_fx_fallback();
    }
    $data['base'] = (string) ($data['base'] ?? 'NOK');
    $data['updated'] = (string) ($data['updated'] ?? gmdate('c'));
    $data['source'] = (string) ($data['source'] ?? 'cache');
    if (!isset($data['rates']['NOK'])) {
        $data['rates']['NOK'] = 1.0;
    }
    return $data;
}

/** @return array{ok:bool,fx:array,error:string} */
function sh_billing_fx_refresh(): array
{
    sh_billing_ecosystem_inc();
    if (function_exists('ecosystem_fx_fetch_remote')) {
        $fresh = ecosystem_fx_fetch_remote();
        if (is_array($fresh) && is_array($fresh['rates'] ?? null)) {
            $rates = ['NOK' => 1.0];
            foreach (['USD', 'EUR', 'UAH', 'SEK', 'GBP'] as $code) {
                $r = (float) ($fresh['rates'][$code] ?? 0);
                if ($r > 0) {
                    $rates[$code] = $r;
                }
            }
            $fx = [
                'base'    => 'NOK',
                'updated' => gmdate('c'),
                'rates'   => $rates,
                'source'  => (string) ($fresh['source'] ?? 'exchangerate-api.com'),
            ];
            $dir = dirname(sh_billing_fx_path());
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(sh_billing_fx_path(), json_encode($fx, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return ['ok' => true, 'fx' => $fx, 'error' => ''];
        }
    }

    $targets = array_values(array_unique(array_merge(
        array_values(sh_billing_lang_currencies()),
        ['USD', 'GBP', 'SEK']
    )));
    $targets = array_values(array_filter($targets, static fn(string $c): bool => $c !== 'NOK'));
    $url = 'https://api.frankfurter.app/latest?from=NOK&to=' . implode(',', $targets);
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 12,
            'header'  => "Accept: application/json\r\nUser-Agent: ShopCMS-Billing/1.2\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'fx' => sh_billing_fx_load(), 'error' => 'FX API unreachable'];
    }
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || !is_array($parsed['rates'] ?? null)) {
        return ['ok' => false, 'fx' => sh_billing_fx_load(), 'error' => 'Invalid FX response'];
    }
    $rates = ['NOK' => 1.0];
    foreach ($parsed['rates'] as $code => $rate) {
        $rates[strtoupper((string) $code)] = (float) $rate;
    }
    $fx = [
        'base'    => 'NOK',
        'updated' => gmdate('c'),
        'rates'   => $rates,
        'source'  => 'frankfurter.app',
    ];
    $dir = dirname(sh_billing_fx_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(sh_billing_fx_path(), json_encode($fx, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return ['ok' => true, 'fx' => $fx, 'error' => ''];
}

function sh_billing_convert_nok(float $nok, string $currency): float
{
    sh_billing_ecosystem_inc();
    $code = strtoupper(trim($currency)) ?: 'NOK';
    if ($code === 'NOK') {
        return round($nok, 0);
    }
    if (function_exists('ecosystem_convert_nok')) {
        return (float) ecosystem_convert_nok((int) round($nok), $code);
    }
    $fx = sh_billing_fx_load();
    $rate = (float) ($fx['rates'][$code] ?? 0.0);
    if ($rate <= 0) {
        return round($nok, 0);
    }
    $amount = $nok * $rate;
    return round($amount, in_array($code, ['EUR', 'USD', 'GBP'], true) ? 2 : 0);
}

function sh_billing_format_amount(float $amount, string $currency): string
{
    require_once __DIR__ . '/store-settings.php';
    $presets = sh_currency_presets();
    $code = strtoupper(trim($currency)) ?: 'NOK';
    $preset = $presets[$code] ?? ['symbol' => $code, 'decimals' => 2];
    $decimals = (int) ($preset['decimals'] ?? 2);
    if (in_array($code, ['NOK', 'UAH', 'SEK'], true)) {
        $decimals = 0;
    }
    $formatted = number_format($amount, $decimals, ',', ' ');

    if (in_array($code, ['NOK', 'SEK', 'UAH'], true)) {
        return $formatted . ' ' . ($preset['symbol'] ?? $code);
    }
    if ($code === 'USD') {
        return '$' . $formatted;
    }
    return ($preset['symbol'] ?? '€') . $formatted;
}

/** @return array<string, mixed> */
function sh_billing_pricing_for_lang(string $lang): array
{
    $currencies = sh_billing_lang_currencies();
    $currency = $currencies[$lang] ?? 'EUR';
    $fixed = sh_billing_fixed_prices()[$currency] ?? null;
    if (is_array($fixed)) {
        $monthly = (float) ($fixed['monthly'] ?? 0);
        $yearly = (float) ($fixed['yearly'] ?? 0);
    } else {
        $monthly = sh_billing_convert_nok((float) SH_BILLING_NOK_MONTHLY, $currency);
        $yearly = sh_billing_convert_nok((float) SH_BILLING_NOK_YEARLY, $currency);
    }
    $fx = sh_billing_fx_load();

    sh_billing_ecosystem_inc();
    $fullMonthlyNok = defined('ECOSYSTEM_FULL_PRICE_NOK') ? (float) ECOSYSTEM_FULL_PRICE_NOK : 249.0;

    return [
        'lang'           => $lang,
        'currency'       => $currency,
        'base_currency'  => 'NOK',
        'monthly_nok'    => SH_BILLING_NOK_MONTHLY,
        'yearly_nok'     => SH_BILLING_NOK_YEARLY,
        'full_monthly_nok' => $fullMonthlyNok,
        'monthly'        => $monthly,
        'yearly'         => $yearly,
        'full_monthly'   => sh_billing_convert_nok($fullMonthlyNok, $currency),
        'monthly_fmt'    => sh_billing_format_amount($monthly, $currency),
        'yearly_fmt'     => sh_billing_format_amount($yearly, $currency),
        'full_monthly_fmt' => sh_billing_format_amount(sh_billing_convert_nok($fullMonthlyNok, $currency), $currency),
        'monthly_nok_fmt'=> sh_billing_format_amount((float) SH_BILLING_NOK_MONTHLY, 'NOK'),
        'yearly_nok_fmt' => sh_billing_format_amount((float) SH_BILLING_NOK_YEARLY, 'NOK'),
        'api_requests_monthly' => SH_BILLING_API_REQUESTS_MONTHLY,
        'api_requests_yearly'  => SH_BILLING_API_REQUESTS_YEARLY,
        'demo_requests'        => SH_BILLING_API_REQUESTS_YEARLY,
        'fx_updated'     => function_exists('ecosystem_fx_date') ? ecosystem_fx_date() : (string) ($fx['updated'] ?? ''),
        'fx_source'      => function_exists('ecosystem_fx_data') ? (string) (ecosystem_fx_data()['source'] ?? '') : (string) ($fx['source'] ?? ''),
        'fx_rate'        => (float) ($fx['rates'][$currency] ?? 1.0),
        'plan_monthly_label' => '1 CMS script · 1 domain',
        'plan_yearly_label'  => 'All CMS scripts · releases & updates · 1 domain',
    ];
}

/**
 * @param array<string, mixed> $labels from $t['billing'] or $ta['billing_banner']
 */
function sh_billing_ecosystem_lang(string $lang): string
{
    return match ($lang) {
        'no' => 'no',
        'uk', 'ua' => 'ua',
        default => 'en',
    };
}

function sh_billing_subscription_script_label(string $lang): string
{
    sh_billing_ecosystem_inc();
    $eco = sh_billing_ecosystem_lang($lang);
    if (function_exists('ecosystem_script_pricing_plan')) {
        return ecosystem_script_pricing_plan($eco);
    }
    $p = sh_billing_pricing_for_lang($lang);

    return (string) ($p['monthly_nok_fmt'] ?? '49 NOK') . '/mo';
}

function sh_billing_subscription_full_label(string $lang): string
{
    sh_billing_ecosystem_inc();
    $eco = sh_billing_ecosystem_lang($lang);
    if (function_exists('ecosystem_full_pricing_plan')) {
        return ecosystem_full_pricing_plan($eco);
    }
    $p = sh_billing_pricing_for_lang($lang);

    return (string) ($p['full_monthly_fmt'] ?? $p['yearly_fmt'] ?? '249 NOK') . '/mo';
}

function sh_billing_subscription_tagline(string $lang): string
{
    sh_billing_ecosystem_inc();
    $eco = sh_billing_ecosystem_lang($lang);
    if (function_exists('ecosystem_pricing_tagline')) {
        return ecosystem_pricing_tagline($eco);
    }

    return '49 kr/mo or 249 kr/mo · 1 domain · one-click install';
}

function sh_billing_banner_text(array $labels, string $lang): string
{
    $p = sh_billing_pricing_for_lang($lang);
    $fullFmt = (string) ($p['full_monthly_fmt'] ?? $p['yearly_fmt']);
    $tagline = sh_billing_subscription_tagline($lang);
    $tpl = (string) ($labels['text'] ?? '{tagline}');
    return strtr($tpl, [
        '{tagline}'      => $tagline,
        '{monthly}'      => $p['monthly_fmt'],
        '{yearly}'       => $fullFmt,
        '{full_monthly}' => $fullFmt,
        '{demo}'         => (string) $p['demo_requests'],
        '{demo_m}'       => (string) ($p['api_requests_monthly'] ?? SH_BILLING_API_REQUESTS_MONTHLY),
        '{demo_y}'       => (string) ($p['api_requests_yearly'] ?? SH_BILLING_API_REQUESTS_YEARLY),
        '{nok_m}'        => (string) $p['monthly_nok_fmt'],
        '{nok_y}'        => (string) ($p['yearly_nok_fmt'] ?? $p['full_monthly_fmt'] ?? ''),
        '{nok_full}'     => (string) ($p['yearly_nok_fmt'] ?? sh_billing_format_amount((float) SH_BILLING_NOK_FULL_MONTHLY, 'NOK')),
        '{eur_m}'        => (string) $p['monthly_fmt'],
        '{eur_y}'        => $fullFmt,
    ]);
}

function sh_billing_render_admin_banner(array $ta, string $lang): void
{
    $labels = is_array($ta['billing_banner'] ?? null) ? $ta['billing_banner'] : [];
    if (($labels['enabled'] ?? true) === false) {
        return;
    }
    $text = sh_billing_banner_text($labels, $lang);
    $badge = (string) ($labels['badge'] ?? 'Shop CMS');
    $link = sh_billing_banner_resolve_link((string) ($labels['link'] ?? ''));
    ?>
    <div class="adm-billing-banner" role="status">
        <div class="adm-billing-banner-inner">
            <span class="adm-billing-badge"><i class="fas fa-crown" aria-hidden="true"></i> <?= htmlspecialchars($badge) ?></span>
            <p class="adm-billing-text"><?= htmlspecialchars($text) ?></p>
            <?php if ($link !== ''): ?>
            <a href="<?= htmlspecialchars($link) ?>" class="adm-btn adm-btn-sm adm-btn-outline adm-billing-cta">
                <?= htmlspecialchars((string) ($labels['cta'] ?? 'Details')) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function sh_billing_render_site_banner(array $t, string $lang): void
{
    $labels = is_array($t['billing'] ?? null) ? $t['billing'] : [];
    if (($labels['enabled'] ?? true) === false) {
        return;
    }
    $text = sh_billing_banner_text($labels, $lang);
    ?>
    <div class="shs-billing-strip" role="status">
        <div class="shs-container shs-billing-strip-inner">
            <i class="fas fa-sparkles" aria-hidden="true"></i>
            <span><?= htmlspecialchars($text) ?></span>
        </div>
    </div>
    <?php
}

function sh_billing_banner_resolve_link(string $link): string
{
    if ($link === '' || $link === 'subscription' || $link === 'wordpress') {
        return sh_subscription_url();
    }
    if ($link === 'billing-demo') {
        return function_exists('sh_admin_url') ? sh_admin_url('billing-demo.php') : sh_subscription_url();
    }
    if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
        return $link;
    }
    if ($link !== '' && function_exists('sh_admin_url')) {
        return sh_admin_url($link);
    }
    if ($link !== '' && function_exists('sh_url')) {
        return sh_url($link);
    }

    return sh_subscription_url();
}

function sh_billing_render_shop_banner(array $t, string $lang): void
{
    $labels = is_array($t['billing'] ?? null) ? $t['billing'] : [];
    if (($labels['enabled'] ?? true) === false) {
        return;
    }
    $text = sh_billing_banner_text($labels, $lang);
    $href = sh_billing_banner_resolve_link((string) ($labels['link'] ?? 'subscription'));
    $cta = trim((string) ($labels['cta'] ?? ''));
    ?>
    <a href="<?= htmlspecialchars($href) ?>" class="sh-billing-strip" role="status" <?= sh_subscription_external_attrs() ?>>
        <i class="fas fa-sparkles" aria-hidden="true"></i>
        <span><?= htmlspecialchars($text) ?></span>
        <?php if ($cta !== ''): ?>
        <span class="sh-billing-strip-cta"><?= htmlspecialchars($cta) ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
        <?php endif; ?>
    </a>
    <?php
}