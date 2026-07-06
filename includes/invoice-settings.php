<?php

require_once __DIR__ . '/invoice-print-designs.php';

function sh_invoice_demo_company_defaults(): array
{
    return [
        'invoice_company_name'     => 'BILOHASH Demo AS',
        'invoice_company_org_nr'   => '999 888 777 MVA',
        'invoice_company_vat_nr'   => 'NO999888777MVA',
        'invoice_company_address'  => 'Storgata 12',
        'invoice_company_city'     => 'Drammen',
        'invoice_company_postal'   => '3044',
        'invoice_company_country'  => 'Norway',
        'invoice_company_email'    => 'invoice@bilohash.com',
        'invoice_company_phone'    => '+47 40 00 00 00',
        'invoice_company_bank'     => 'DNB',
        'invoice_company_iban'     => 'NO93 8601 1117 947',
        'invoice_company_bic'      => 'DNBANOKK',
        'invoice_company_logo_url' => '',
        'invoice_notes'            => 'Demo invoice — payment within 14 days. Thank you for your order!',
    ];
}

function sh_invoice_settings_defaults(): array
{
    return array_merge([
        'invoice_enabled'        => true,
        'invoice_prefix'           => 'INV',
        'invoice_next_number'      => 1001,
        'invoice_due_days'         => 14,
        'invoice_auto_send'        => false,
        'invoice_print_design'     => 'classic-blue',
        'invoice_print_format'     => 'a4',
        'invoice_print_margin'     => '8mm',
    ], sh_invoice_demo_company_defaults());
}

function sh_invoice_merge_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $base = is_array($settings) ? $settings : [];
    $merged = array_merge(sh_invoice_settings_defaults(), $base);
    $merged['invoice_print_design'] = sh_inv_normalize_print_design($merged['invoice_print_design'] ?? '');
    $merged['invoice_print_format'] = sh_inv_normalize_print_format($merged['invoice_print_format'] ?? '');
    $merged['invoice_next_number'] = max(1, (int) ($merged['invoice_next_number'] ?? 1001));
    $merged['invoice_due_days'] = max(0, min(90, (int) ($merged['invoice_due_days'] ?? 14)));
    foreach (sh_invoice_demo_company_defaults() as $key => $demoVal) {
        if (trim((string) ($merged[$key] ?? '')) === '' && trim((string) $demoVal) !== '') {
            $merged[$key] = $demoVal;
        }
    }
    return $merged;
}

function sh_invoice_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_invoice_merge_settings($settings);
    $settings['invoice_enabled'] = !empty($post['invoice_enabled']);
    $settings['invoice_auto_send'] = !empty($post['invoice_auto_send']);
    $settings['invoice_prefix'] = preg_replace('/[^A-Za-z0-9\-]/', '', trim($post['invoice_prefix'] ?? 'INV')) ?: 'INV';
    $settings['invoice_next_number'] = max(1, (int) ($post['invoice_next_number'] ?? $settings['invoice_next_number']));
    $settings['invoice_due_days'] = max(0, min(90, (int) ($post['invoice_due_days'] ?? 14)));
    $settings['invoice_notes'] = trim($post['invoice_notes'] ?? '');
    $settings['invoice_print_design'] = sh_inv_normalize_print_design($post['invoice_print_design'] ?? '');
    $settings['invoice_print_format'] = sh_inv_normalize_print_format($post['invoice_print_format'] ?? '');
    $margin = trim($post['invoice_print_margin'] ?? '8mm');
    $settings['invoice_print_margin'] = in_array($margin, ['5mm', '8mm', '12mm', '15mm'], true) ? $margin : '8mm';

    foreach ([
        'invoice_company_name', 'invoice_company_org_nr', 'invoice_company_vat_nr',
        'invoice_company_address', 'invoice_company_city', 'invoice_company_postal',
        'invoice_company_country', 'invoice_company_email', 'invoice_company_phone',
        'invoice_company_bank', 'invoice_company_iban', 'invoice_company_bic',
        'invoice_company_logo_url',
    ] as $key) {
        $settings[$key] = trim($post[$key] ?? ($settings[$key] ?? ''));
    }

    if ($settings['invoice_company_org_nr'] === '' && trim($settings['tax_business_id'] ?? '') !== '') {
        $settings['invoice_company_org_nr'] = trim($settings['tax_business_id']);
    }
    if ($settings['invoice_company_name'] === '' && function_exists('sh_seo_org_name')) {
        $settings['invoice_company_name'] = sh_seo_org_name($settings);
    }

    return $settings;
}

function sh_invoice_company_block(?array $settings = null): array
{
    $s = sh_invoice_merge_settings($settings);
    return [
        'name'    => $s['invoice_company_name'] ?: (function_exists('sh_seo_org_name') ? sh_seo_org_name($s) : 'Shop'),
        'org_nr'  => $s['invoice_company_org_nr'] ?: trim($s['tax_business_id'] ?? ''),
        'vat_nr'  => $s['invoice_company_vat_nr'],
        'address' => $s['invoice_company_address'],
        'city'    => $s['invoice_company_city'],
        'postal'  => $s['invoice_company_postal'],
        'country' => $s['invoice_company_country'],
        'email'   => $s['invoice_company_email'],
        'phone'   => $s['invoice_company_phone'],
        'bank'    => $s['invoice_company_bank'],
        'iban'    => $s['invoice_company_iban'],
        'bic'     => $s['invoice_company_bic'],
        'logo'    => $s['invoice_company_logo_url'],
        'logo_url'=> $s['invoice_company_logo_url'],
    ];
}

function sh_invoice_allocate_number(array &$settings): string
{
    $s = sh_invoice_merge_settings($settings);
    $num = (int) $s['invoice_next_number'];
    $prefix = $s['invoice_prefix'] ?: 'INV';
    $year = gmdate('Y');
    $invoiceNo = $prefix . '-' . $year . '-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    $settings['invoice_next_number'] = $num + 1;
    return $invoiceNo;
}

function sh_invoice_label(string $key, ?string $lang = null, array $fallback = []): string
{
    $lang = strtolower((string) ($lang ?? 'en'));
    if (isset($fallback[$key]) && $fallback[$key] !== '') {
        return (string) $fallback[$key];
    }
    static $cache = [];
    if (!isset($cache[$lang])) {
        $file = dirname(__DIR__) . '/lang/' . $lang . '.php';
        if (!is_readable($file)) {
            $file = dirname(__DIR__) . '/lang/en.php';
        }
        $all = is_readable($file) ? require $file : [];
        $cache[$lang] = is_array($all['invoice'] ?? null) ? $all['invoice'] : [];
    }
    return (string) ($cache[$lang][$key] ?? $cache['en'][$key] ?? $key);
}

/** @return array<string, mixed> */
function sh_invoice_demo_preview_doc(?array $settings = null): array
{
    require_once __DIR__ . '/store-settings.php';
    $s = sh_invoice_merge_settings($settings);
    $seller = sh_invoice_company_block($s);
    $today = gmdate('Y-m-d');
    $due = gmdate('Y-m-d', strtotime('+' . (int) ($s['invoice_due_days'] ?? 14) . ' days'));
    $year = gmdate('Y');
    $num = (int) ($s['invoice_next_number'] ?? 1001);
    $prefix = $s['invoice_prefix'] ?: 'INV';
    $invoiceNo = $prefix . '-' . $year . '-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);

    return [
        'invoice_no'   => $invoiceNo,
        'invoice_date' => $today,
        'due_date'     => $due,
        'seller'       => $seller,
        'buyer'        => [
            'name'    => 'Ola Nordmann',
            'email'   => 'ola@example.no',
            'phone'   => '+47 900 00 000',
            'address' => 'Karl Johans gate 1',
            'city'    => 'Oslo',
            'postal'  => '0154',
            'country' => 'Norway',
        ],
        'lines' => [
            ['name' => 'Scandinavian table lamp', 'qty' => 1, 'unit_price' => 1290, 'subtotal' => 1290],
            ['name' => 'Nordic wool blanket', 'qty' => 2, 'unit_price' => 890, 'subtotal' => 1780],
        ],
        'totals' => [
            'net'       => 2456,
            'tax'       => 614,
            'total'     => 3070,
            'tax_rate'  => 25.0,
            'tax_label' => 'MVA',
            'currency'  => sh_site_currency($s),
        ],
        'notes'            => trim($s['invoice_notes'] ?? ''),
        'print_design'     => $s['invoice_print_design'],
        'print_format'     => $s['invoice_print_format'],
        'payment_purpose'  => $invoiceNo,
    ];
}