<?php

/**
 * Shop CMS subscription pricing — EUR base with FX conversion per language.
 */

define('SH_BILLING_EUR_MONTHLY', 49);
define('SH_BILLING_EUR_YEARLY', 249);
define('SH_BILLING_DEMO_REQUESTS', 100);

/** @return array<string, string> */
function sh_billing_lang_currencies(): array
{
    return [
        'no' => 'NOK',
        'en' => 'EUR',
        'uk' => 'UAH',
        'ru' => 'EUR',
        'sv' => 'SEK',
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
        'base'    => 'EUR',
        'updated' => gmdate('c'),
        'rates'   => [
            'EUR' => 1.0,
            'NOK' => 11.52,
            'UAH' => 43.2,
            'SEK' => 11.18,
            'USD' => 1.08,
            'GBP' => 0.86,
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
    $data['base'] = (string) ($data['base'] ?? 'EUR');
    $data['updated'] = (string) ($data['updated'] ?? gmdate('c'));
    $data['source'] = (string) ($data['source'] ?? 'cache');
    return $data;
}

/** @return array{ok:bool,fx:array,error:string} */
function sh_billing_fx_refresh(): array
{
    require_once __DIR__ . '/store-settings.php';
    $targets = array_values(array_unique(array_merge(
        array_values(sh_billing_lang_currencies()),
        ['USD', 'GBP']
    )));
    $targets = array_values(array_filter($targets, static fn(string $c): bool => $c !== 'EUR'));
    $url = 'https://api.frankfurter.app/latest?from=EUR&to=' . implode(',', $targets);

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 12,
            'header'  => "Accept: application/json\r\nUser-Agent: ShopCMS-Billing/1.0\r\n",
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

    $rates = ['EUR' => 1.0];
    foreach ($parsed['rates'] as $code => $rate) {
        $rates[strtoupper((string) $code)] = (float) $rate;
    }

    $fx = [
        'base'    => 'EUR',
        'updated' => gmdate('c'),
        'rates'   => $rates,
        'source'  => 'frankfurter.app',
    ];

    $dir = dirname(sh_billing_fx_path());
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        sh_billing_fx_path(),
        json_encode($fx, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    return ['ok' => true, 'fx' => $fx, 'error' => ''];
}

function sh_billing_convert_eur(float $eur, string $currency): float
{
    $fx = sh_billing_fx_load();
    $code = strtoupper(trim($currency)) ?: 'EUR';
    $rate = (float) ($fx['rates'][$code] ?? 1.0);
    return round($eur * $rate, $code === 'EUR' ? 2 : 0);
}

function sh_billing_format_amount(float $amount, string $currency): string
{
    require_once __DIR__ . '/store-settings.php';
    $presets = sh_currency_presets();
    $code = strtoupper(trim($currency)) ?: 'EUR';
    $preset = $presets[$code] ?? ['symbol' => $code, 'decimals' => 2];
    $decimals = (int) ($preset['decimals'] ?? 2);
    $formatted = number_format($amount, $decimals, ',', ' ');

    if (in_array($code, ['NOK', 'SEK', 'UAH'], true)) {
        return $formatted . ' ' . ($preset['symbol'] ?? $code);
    }
    return ($preset['symbol'] ?? '€') . $formatted;
}

/** @return array<string, mixed> */
function sh_billing_pricing_for_lang(string $lang): array
{
    $currencies = sh_billing_lang_currencies();
    $currency = $currencies[$lang] ?? 'EUR';
    $monthly = sh_billing_convert_eur((float) SH_BILLING_EUR_MONTHLY, $currency);
    $yearly = sh_billing_convert_eur((float) SH_BILLING_EUR_YEARLY, $currency);
    $fx = sh_billing_fx_load();

    return [
        'lang'           => $lang,
        'currency'       => $currency,
        'monthly_eur'    => SH_BILLING_EUR_MONTHLY,
        'yearly_eur'     => SH_BILLING_EUR_YEARLY,
        'monthly'        => $monthly,
        'yearly'         => $yearly,
        'monthly_fmt'    => sh_billing_format_amount($monthly, $currency),
        'yearly_fmt'     => sh_billing_format_amount($yearly, $currency),
        'demo_requests'  => SH_BILLING_DEMO_REQUESTS,
        'fx_updated'     => (string) ($fx['updated'] ?? ''),
        'fx_source'      => (string) ($fx['source'] ?? ''),
    ];
}

/**
 * @param array<string, mixed> $labels from $t['billing'] or $ta['billing_banner']
 */
function sh_billing_banner_text(array $labels, string $lang): string
{
    $p = sh_billing_pricing_for_lang($lang);
    $tpl = (string) ($labels['text'] ?? '{monthly}/mo + BILOHASH AI API or {demo} demo requests · or {yearly}/yr');
    return strtr($tpl, [
        '{monthly}' => $p['monthly_fmt'],
        '{yearly}'  => $p['yearly_fmt'],
        '{demo}'    => (string) $p['demo_requests'],
        '{eur_m}'   => '€' . SH_BILLING_EUR_MONTHLY,
        '{eur_y}'   => '€' . SH_BILLING_EUR_YEARLY,
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
    $link = (string) ($labels['link'] ?? '');
    if ($link === '' || $link === 'billing-demo') {
        $link = function_exists('sh_admin_url') ? sh_admin_url('billing-demo.php') : '';
    }
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

function sh_billing_render_shop_banner(array $t, string $lang): void
{
    $labels = is_array($t['billing'] ?? null) ? $t['billing'] : [];
    if (($labels['enabled'] ?? true) === false) {
        return;
    }
    $text = sh_billing_banner_text($labels, $lang);
    ?>
    <div class="sh-billing-strip" role="status">
        <i class="fas fa-sparkles" aria-hidden="true"></i>
        <span><?= htmlspecialchars($text) ?></span>
    </div>
    <?php
}