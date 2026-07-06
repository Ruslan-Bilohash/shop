<?php
/**
 * Shop CMS — VAT / sales tax presets and checkout integration.
 */
declare(strict_types=1);

/** @return array<string, array{country:string,rate:float,labels:array<string,string>,currency:string,business_label:string}> */
function sh_tax_country_catalog(): array
{
    return [
        'NO' => [
            'country'        => 'Norway',
            'rate'           => 25.0,
            'currency'       => 'NOK',
            'business_label' => 'Org.nr.',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
        'LT' => [
            'country'        => 'Lithuania',
            'rate'           => 21.0,
            'currency'       => 'EUR',
            'business_label' => 'PVM kodas',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
        'UA' => [
            'country'        => 'Ukraine',
            'rate'           => 20.0,
            'currency'       => 'UAH',
            'business_label' => 'ЄДРПОУ / ІПН',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
        'SE' => [
            'country'        => 'Sweden',
            'rate'           => 25.0,
            'currency'       => 'SEK',
            'business_label' => 'Org.nr.',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
        'PL' => [
            'country'        => 'Poland',
            'rate'           => 23.0,
            'currency'       => 'PLN',
            'business_label' => 'NIP',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
        'GB' => [
            'country'        => 'United Kingdom',
            'rate'           => 20.0,
            'currency'       => 'GBP',
            'business_label' => 'VAT number',
            'labels'         => [
                'no' => 'MVA', 'en' => 'VAT', 'uk' => 'ПДВ', 'ru' => 'НДС', 'sv' => 'Moms',
            ],
        ],
    ];
}

function sh_tax_settings_defaults(): array
{
    return [
        'tax_enabled'          => false,
        'tax_country'          => 'NO',
        'tax_mode'             => 'inclusive',
        'tax_rate'             => 25.0,
        'tax_custom_label'     => '',
        'tax_business_id'      => '',
        'tax_show_in_catalog'  => true,
        'tax_show_breakdown'   => true,
    ];
}

function sh_tax_settings_keys(): array
{
    return array_keys(sh_tax_settings_defaults());
}

function sh_tax_merge_settings(array $settings): array
{
    $defaults = sh_tax_settings_defaults();
    foreach ($defaults as $key => $val) {
        if (!array_key_exists($key, $settings)) {
            $settings[$key] = $val;
        }
    }
    $catalog = sh_tax_country_catalog();
    $country = strtoupper((string) ($settings['tax_country'] ?? 'NO'));
    if (!isset($catalog[$country])) {
        $settings['tax_country'] = 'NO';
    } else {
        $settings['tax_country'] = $country;
    }
    $mode = (string) ($settings['tax_mode'] ?? 'inclusive');
    if (!in_array($mode, ['inclusive', 'exclusive'], true)) {
        $settings['tax_mode'] = 'inclusive';
    }
    $rate = (float) ($settings['tax_rate'] ?? 0);
    $settings['tax_rate'] = max(0.0, min(100.0, round($rate, 2)));
    $settings['tax_enabled'] = !empty($settings['tax_enabled']);
    $settings['tax_show_in_catalog'] = !empty($settings['tax_show_in_catalog']);
    $settings['tax_show_breakdown'] = !empty($settings['tax_show_breakdown']);
    return $settings;
}

function sh_tax_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_tax_merge_settings($settings);
    $catalog = sh_tax_country_catalog();

    $settings['tax_enabled'] = !empty($post['tax_enabled']);
    $country = strtoupper(substr(trim((string) ($post['tax_country'] ?? 'NO')), 0, 2));
    $settings['tax_country'] = isset($catalog[$country]) ? $country : 'NO';

    $mode = trim((string) ($post['tax_mode'] ?? 'inclusive'));
    $settings['tax_mode'] = in_array($mode, ['inclusive', 'exclusive'], true) ? $mode : 'inclusive';

    $postedRate = trim((string) ($post['tax_rate'] ?? ''));
    if ($postedRate === '' || !is_numeric($postedRate)) {
        $settings['tax_rate'] = (float) $catalog[$settings['tax_country']]['rate'];
    } else {
        $settings['tax_rate'] = max(0.0, min(100.0, round((float) $postedRate, 2)));
    }

    $settings['tax_custom_label'] = trim((string) ($post['tax_custom_label'] ?? ''));
    $settings['tax_business_id'] = trim((string) ($post['tax_business_id'] ?? ''));
    $settings['tax_show_in_catalog'] = !empty($post['tax_show_in_catalog']);
    $settings['tax_show_breakdown'] = !empty($post['tax_show_breakdown']);

    return $settings;
}

function sh_tax_is_active(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    $s = sh_tax_merge_settings(is_array($settings) ? $settings : []);
    return !empty($s['tax_enabled']) && (float) ($s['tax_rate'] ?? 0) > 0;
}

function sh_tax_label(?array $settings = null, ?string $lang = null): string
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    $s = sh_tax_merge_settings(is_array($settings) ? $settings : []);
    $custom = trim((string) ($s['tax_custom_label'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }
    $code = strtoupper((string) ($s['tax_country'] ?? 'NO'));
    $catalog = sh_tax_country_catalog();
    $labels = $catalog[$code]['labels'] ?? ['en' => 'VAT'];
    if ($lang === null && function_exists('sh_current_lang')) {
        $lang = sh_current_lang();
    }
    $lang = strtolower((string) ($lang ?? 'en'));
    return $labels[$lang] ?? $labels['en'] ?? 'VAT';
}

/**
 * @return array{enabled:bool,mode:string,rate:float,label:string,country:string,subtotal:int,net:int,tax:int,total:int}
 */
function sh_tax_breakdown(int $subtotal, ?array $settings = null, ?string $lang = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    $s = sh_tax_merge_settings(is_array($settings) ? $settings : []);
    $rate = (float) ($s['tax_rate'] ?? 0);
    $mode = (string) ($s['tax_mode'] ?? 'inclusive');
    $enabled = sh_tax_is_active($s);
    $label = sh_tax_label($s, $lang);
    $country = strtoupper((string) ($s['tax_country'] ?? 'NO'));

    if (!$enabled || $subtotal <= 0 || $rate <= 0) {
        return [
            'enabled'  => false,
            'mode'     => $mode,
            'rate'     => $rate,
            'label'    => $label,
            'country'  => $country,
            'subtotal' => $subtotal,
            'net'      => $subtotal,
            'tax'      => 0,
            'total'    => $subtotal,
        ];
    }

    if ($mode === 'exclusive') {
        $tax = (int) round($subtotal * $rate / 100);
        return [
            'enabled'  => true,
            'mode'     => $mode,
            'rate'     => $rate,
            'label'    => $label,
            'country'  => $country,
            'subtotal' => $subtotal,
            'net'      => $subtotal,
            'tax'      => $tax,
            'total'    => $subtotal + $tax,
        ];
    }

    $tax = (int) round($subtotal * $rate / (100 + $rate));
    return [
        'enabled'  => true,
        'mode'     => $mode,
        'rate'     => $rate,
        'label'    => $label,
        'country'  => $country,
        'subtotal' => $subtotal,
        'net'      => $subtotal - $tax,
        'tax'      => $tax,
        'total'    => $subtotal,
    ];
}

function sh_cart_total_gross(?array $settings = null): int
{
    return sh_tax_breakdown(sh_cart_total(), $settings)['total'];
}

function sh_tax_price_suffix(?array $settings = null, ?string $lang = null): string
{
    if (!sh_tax_is_active($settings)) {
        return '';
    }
    $s = sh_tax_merge_settings(is_array($settings) ? $settings : []);
    if (empty($s['tax_show_in_catalog'])) {
        return '';
    }
    global $t;
    $tpl = is_array($t ?? null) ? ($t['tax']['price_incl'] ?? '') : '';
    if ($tpl === '') {
        return '';
    }
    $bd = sh_tax_breakdown(100, $s, $lang);
    $rate = rtrim(rtrim(number_format($bd['rate'], 2, '.', ''), '0'), '.');
    return str_replace(['{label}', '{rate}'], [$bd['label'], $rate], $tpl);
}