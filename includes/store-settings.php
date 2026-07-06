<?php

/** Built-in language registry (used when site_languages not customized). */
function sh_builtin_langs(): array
{
    return [
        'no' => ['label' => 'NO', 'name' => 'Norsk', 'flag' => '🇳🇴', 'locale' => 'nb-NO', 'html' => 'no'],
        'en' => ['label' => 'EN', 'name' => 'English', 'flag' => '🇬🇧', 'locale' => 'en-GB', 'html' => 'en'],
        'uk' => ['label' => 'UA', 'name' => 'Українська', 'flag' => '🇺🇦', 'locale' => 'uk-UA', 'html' => 'uk'],
        'ru' => ['label' => 'RU', 'name' => 'Русский', 'flag' => '🇷🇺', 'locale' => 'ru-RU', 'html' => 'ru'],
        'sv' => ['label' => 'SV', 'name' => 'Svenska', 'flag' => '🇸🇪', 'locale' => 'sv-SE', 'html' => 'sv'],
    ];
}

/** @return array<string, array{symbol:string,decimals:int,name:string}> */
function sh_currency_presets_primary(): array
{
    return [
        'NOK' => ['symbol' => 'kr', 'decimals' => 0, 'name' => 'Norwegian krone'],
        'UAH' => ['symbol' => '₴', 'decimals' => 0, 'name' => 'Ukrainian hryvnia'],
        'EUR' => ['symbol' => '€', 'decimals' => 2, 'name' => 'Euro'],
        'SEK' => ['symbol' => 'kr', 'decimals' => 0, 'name' => 'Swedish krona'],
        'USD' => ['symbol' => '$', 'decimals' => 2, 'name' => 'US dollar'],
        'GBP' => ['symbol' => '£', 'decimals' => 2, 'name' => 'British pound'],
        'PLN' => ['symbol' => 'zł', 'decimals' => 2, 'name' => 'Polish złoty'],
    ];
}

/** @return array<string, array{symbol:string,decimals:int,name:string}> */
function sh_currency_presets_other(): array
{
    return [
        'CHF' => ['symbol' => 'CHF', 'decimals' => 2, 'name' => 'Swiss franc'],
        'DKK' => ['symbol' => 'kr', 'decimals' => 2, 'name' => 'Danish krone'],
        'CZK' => ['symbol' => 'Kč', 'decimals' => 2, 'name' => 'Czech koruna'],
        'HUF' => ['symbol' => 'Ft', 'decimals' => 0, 'name' => 'Hungarian forint'],
        'RON' => ['symbol' => 'lei', 'decimals' => 2, 'name' => 'Romanian leu'],
        'BGN' => ['symbol' => 'лв', 'decimals' => 2, 'name' => 'Bulgarian lev'],
        'ISK' => ['symbol' => 'kr', 'decimals' => 0, 'name' => 'Icelandic króna'],
        'CAD' => ['symbol' => '$', 'decimals' => 2, 'name' => 'Canadian dollar'],
        'AUD' => ['symbol' => '$', 'decimals' => 2, 'name' => 'Australian dollar'],
        'JPY' => ['symbol' => '¥', 'decimals' => 0, 'name' => 'Japanese yen'],
        'CNY' => ['symbol' => '¥', 'decimals' => 2, 'name' => 'Chinese yuan'],
        'TRY' => ['symbol' => '₺', 'decimals' => 2, 'name' => 'Turkish lira'],
        'AED' => ['symbol' => 'د.إ', 'decimals' => 2, 'name' => 'UAE dirham'],
        'INR' => ['symbol' => '₹', 'decimals' => 2, 'name' => 'Indian rupee'],
        'BRL' => ['symbol' => 'R$', 'decimals' => 2, 'name' => 'Brazilian real'],
        'MXN' => ['symbol' => '$', 'decimals' => 2, 'name' => 'Mexican peso'],
        'KRW' => ['symbol' => '₩', 'decimals' => 0, 'name' => 'South Korean won'],
        'ZAR' => ['symbol' => 'R', 'decimals' => 2, 'name' => 'South African rand'],
    ];
}

/** @return array<string, array{symbol:string,decimals:int,name:string}> */
function sh_currency_presets(): array
{
    return array_merge(sh_currency_presets_primary(), sh_currency_presets_other());
}

/** @return array<string, array{label_key:string,presets:array<string,array{symbol:string,decimals:int,name:string}>}> */
function sh_currency_preset_groups(): array
{
    return [
        'primary' => ['label_key' => 'currency_group_primary', 'presets' => sh_currency_presets_primary()],
        'other'   => ['label_key' => 'currency_group_other', 'presets' => sh_currency_presets_other()],
    ];
}

function sh_store_settings_defaults(): array
{
    return [
        'site_currency'           => 'NOK',
        'site_default_lang'       => 'no',
        'currency_symbol'         => 'kr',
        'currency_decimals'       => 0,
        'currency_thousands_sep'  => ' ',
        'currency_decimal_sep'    => ',',

        'card_show_category'      => true,
        'card_show_stock'         => true,
        'card_show_excerpt'       => true,
        'card_excerpt_len'        => 85,
        'card_show_sale_badge'    => true,
        'card_show_featured'      => true,
        'card_show_add_cart'      => true,
        'card_show_view_btn'      => true,

        'quick_buy_enabled'       => true,
        'quick_buy_show_after_phone' => true,

        'tracking_gtag_id'        => '',
        'tracking_meta_pixel'     => '',

        'custom_head_html'        => '',
        'custom_footer_js'        => '',

        'design_text_color'       => '',
        'design_text_muted'       => '',
        'design_card_bg'          => '',
        'design_header_bg'        => '',
        'design_footer_bg'        => '',
        'design_border_color'     => '',
        'design_sale_color'       => '',
        'design_border_radius'    => 10,
        'design_font_family'      => '',

        'posten_enabled'          => false,
        'posten_api_key'          => '',
        'posten_client_id'        => '',
        'posten_demo_mode'        => true,

        'site_languages'          => [],

        'customer_auth_enabled'         => true,
        'customer_phone_login'          => true,
        'customer_google_login'         => true,
        'customer_apple_login'          => true,
        'customer_google_client_id'     => '',
        'customer_google_client_secret' => '',
        'customer_apple_client_id'      => '',
        'customer_apple_team_id'        => '',
        'customer_apple_key_id'         => '',
        'customer_apple_private_key'    => '',
        'customer_email_login'          => true,

        'shop_maintenance_enabled'      => true,
        'shop_maintenance_allow_admin'  => true,
        'shop_dev_errors'               => true,
        'cookie_consent_enabled'        => true,
    ];
}

function sh_store_settings_keys(): array
{
    return array_keys(sh_store_settings_defaults());
}

function sh_merge_store_settings(array $settings): array
{
    $defaults = sh_store_settings_defaults();
    foreach ($defaults as $key => $val) {
        if (!array_key_exists($key, $settings)) {
            $settings[$key] = $val;
        }
    }
    if (!is_array($settings['site_languages'] ?? null)) {
        $settings['site_languages'] = [];
    }
    return $settings;
}

function sh_store_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_store_settings($settings);
    $settings['site_currency'] = strtoupper(substr(trim($post['site_currency'] ?? 'NOK'), 0, 3)) ?: 'NOK';
    $settings['currency_symbol'] = trim($post['currency_symbol'] ?? 'kr') ?: 'kr';
    $settings['currency_decimals'] = max(0, min(2, (int) ($post['currency_decimals'] ?? 0)));

    if (is_file(__DIR__ . '/shop-mode.php')) {
        require_once __DIR__ . '/shop-mode.php';
        if (array_key_exists('shop_open', $post)) {
            $settings = sh_shop_mode_apply_post($post, $settings);
        }
        if (array_key_exists('shop_dev_errors', $post)) {
            $settings['shop_dev_errors'] = !empty($post['shop_dev_errors']);
        }
    }

    $defaultLang = strtolower(trim((string) ($post['site_default_lang'] ?? 'no')));
    $active = sh_active_langs($settings);
    $settings['site_default_lang'] = isset($active[$defaultLang]) ? $defaultLang : (array_key_first($active) ?: 'no');

    return $settings;
}

function sh_site_default_lang(?array $settings = null): string
{
    $settings = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $code = strtolower(trim((string) ($settings['site_default_lang'] ?? 'no')));
    $active = sh_active_langs($settings);
    return isset($active[$code]) ? $code : (array_key_first($active) ?: 'no');
}

function sh_analytics_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_store_settings($settings);
    $settings['tracking_gtag_id'] = trim($post['tracking_gtag_id'] ?? '');
    $settings['tracking_meta_pixel'] = trim($post['tracking_meta_pixel'] ?? '');
    return $settings;
}

function sh_advanced_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_store_settings($settings);
    $settings['custom_head_html'] = trim($post['custom_head_html'] ?? '');
    $settings['custom_footer_js'] = trim($post['custom_footer_js'] ?? '');

    if (is_file(__DIR__ . '/shop-mode.php')) {
        require_once __DIR__ . '/shop-mode.php';
        $settings = sh_shop_mode_apply_post($post, $settings);
    }

    return $settings;
}

function sh_appearance_settings_apply_post(array $post, array $settings): array
{
    require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';
    $settings = bh_cms_settings_apply_post('appearance', $post, $settings);
    $settings = sh_merge_store_settings($settings);

    foreach (['design_text_color', 'design_text_muted', 'design_card_bg', 'design_header_bg', 'design_footer_bg', 'design_border_color', 'design_sale_color'] as $key) {
        $val = trim($post[$key] ?? '');
        $settings[$key] = $val !== '' ? bh_cms_hex_color($val, '') : '';
    }
    $radius = (int) ($post['design_border_radius'] ?? 10);
    $settings['design_border_radius'] = max(0, min(24, $radius));
    $settings['design_font_family'] = trim($post['design_font_family'] ?? '');

    $settings['card_show_category'] = !empty($post['card_show_category']);
    $settings['card_show_stock'] = !empty($post['card_show_stock']);
    $settings['card_show_excerpt'] = !empty($post['card_show_excerpt']);
    $settings['card_excerpt_len'] = max(20, min(300, (int) ($post['card_excerpt_len'] ?? 85)));
    $settings['card_show_sale_badge'] = !empty($post['card_show_sale_badge']);
    $settings['card_show_featured'] = !empty($post['card_show_featured']);
    $settings['card_show_add_cart'] = !empty($post['card_show_add_cart']);
    $settings['card_show_view_btn'] = !empty($post['card_show_view_btn']);
    $settings['quick_buy_enabled'] = !empty($post['quick_buy_enabled']);
    $settings['quick_buy_show_after_phone'] = !empty($post['quick_buy_show_after_phone']);

    return $settings;
}

function sh_posten_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_store_settings($settings);
    $settings['posten_enabled'] = !empty($post['posten_enabled']);
    $settings['posten_demo_mode'] = !empty($post['posten_demo_mode']);
    $settings['posten_client_id'] = trim($post['posten_client_id'] ?? '');
    if (trim($post['posten_api_key'] ?? '') !== '') {
        $settings['posten_api_key'] = trim($post['posten_api_key']);
    }
    return $settings;
}

function sh_languages_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_store_settings($settings);
    $rows = [];
    $indices = $post['lang_idx'] ?? [];
    if (!is_array($indices)) {
        return $settings;
    }
    foreach ($indices as $i) {
        $i = (int) $i;
        $code = strtolower(trim($post['lang_code_' . $i] ?? ''));
        if ($code === '' || !preg_match('/^[a-z]{2,5}$/', $code)) {
            continue;
        }
        $rows[] = [
            'code'   => $code,
            'label'  => strtoupper(substr(trim($post['lang_label_' . $i] ?? $code), 0, 5)),
            'name'   => trim($post['lang_name_' . $i] ?? '') ?: ucfirst($code),
            'flag'   => trim($post['lang_flag_' . $i] ?? '') ?: '🌐',
            'locale' => trim($post['lang_locale_' . $i] ?? '') ?: ($code . '-' . strtoupper($code)),
            'html'   => trim($post['lang_html_' . $i] ?? '') ?: $code,
            'active' => !empty($post['lang_active_' . $i]),
        ];
    }
    if ($rows !== []) {
        $settings['site_languages'] = $rows;
    }
    return $settings;
}

/** @return array<string, array{label:string,name:string,flag:string,locale:string,html:string}> */
function sh_active_langs(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $settings = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $builtin = sh_builtin_langs();
    $custom = $settings['site_languages'] ?? [];
    if ($custom === []) {
        return $builtin;
    }
    $out = [];
    foreach ($custom as $row) {
        if (empty($row['active']) || empty($row['code'])) {
            continue;
        }
        $code = $row['code'];
        $out[$code] = [
            'label'  => $row['label'] ?? strtoupper($code),
            'name'   => $row['name'] ?? ($builtin[$code]['name'] ?? ucfirst($code)),
            'flag'   => $row['flag'] ?? ($builtin[$code]['flag'] ?? '🌐'),
            'locale' => $row['locale'] ?? ($builtin[$code]['locale'] ?? $code),
            'html'   => $row['html'] ?? ($builtin[$code]['html'] ?? $code),
        ];
    }
    return $out !== [] ? $out : $builtin;
}

function sh_card_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    return [
        'show_category'   => !empty($s['card_show_category']),
        'show_stock'      => !empty($s['card_show_stock']),
        'show_excerpt'    => !empty($s['card_show_excerpt']),
        'excerpt_len'     => (int) ($s['card_excerpt_len'] ?? 85),
        'show_sale_badge' => !empty($s['card_show_sale_badge']),
        'show_featured'   => !empty($s['card_show_featured']),
        'show_add_cart'   => !empty($s['card_show_add_cart']),
        'show_view_btn'   => !empty($s['card_show_view_btn']),
    ];
}

function sh_quick_buy_enabled(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    return !empty($s['quick_buy_enabled']);
}

function sh_format_price(int $amount, ?array $settings = null): string
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $decimals = (int) ($s['currency_decimals'] ?? 0);
    $thousands = (string) ($s['currency_thousands_sep'] ?? ' ');
    $decimal = (string) ($s['currency_decimal_sep'] ?? ',');
    $symbol = trim((string) ($s['currency_symbol'] ?? 'kr'));
    $formatted = number_format($amount, $decimals, $decimal, $thousands);
    return $formatted . ($symbol !== '' ? ' ' . $symbol : '');
}

function sh_site_currency(?array $settings = null): string
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    return strtoupper((string) ($s['site_currency'] ?? 'NOK'));
}

function sh_render_custom_head_html(?array $settings = null): void
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $html = trim((string) ($s['custom_head_html'] ?? ''));
    if ($html !== '') {
        echo $html;
    }
}

function sh_render_custom_footer_js(?array $settings = null): void
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $js = trim((string) ($s['custom_footer_js'] ?? ''));
    if ($js !== '') {
        echo '<script>' . $js . '</script>';
    }
}

function sh_render_shop_theme_styles(?array $settings = null): void
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    bh_cms_render_theme_styles('shop', $s);

    $vars = [];
    $map = [
        'design_text_color'   => '--sh-text',
        'design_text_muted'   => '--sh-text-muted',
        'design_card_bg'      => '--sh-bg-card',
        'design_header_bg'    => '--sh-header-bg',
        'design_footer_bg'    => '--sh-footer-bg',
        'design_border_color' => '--sh-border',
        'design_sale_color'   => '--sh-sale',
    ];
    foreach ($map as $key => $cssVar) {
        $val = trim((string) ($s[$key] ?? ''));
        if ($val !== '') {
            $vars[] = $cssVar . ':' . bh_cms_hex_color($val, $val) . ';';
        }
    }
    $radius = (int) ($s['design_border_radius'] ?? 0);
    if ($radius > 0) {
        $vars[] = '--sh-radius:' . $radius . 'px;';
    }
    $font = trim((string) ($s['design_font_family'] ?? ''));
    if ($font !== '') {
        $vars[] = '--sh-font:' . $font . ';';
    }
    if ($vars === []) {
        return;
    }
    echo '<style id="sh-shop-theme-extra">:root{' . implode('', $vars) . '}</style>';
    $headerBg = trim((string) ($s['design_header_bg'] ?? ''));
    if ($headerBg !== '') {
        echo '<style id="sh-shop-header-bg">.sh-header{background:' . htmlspecialchars(bh_cms_hex_color($headerBg, $headerBg)) . ';}</style>';
    }
    $footerBg = trim((string) ($s['design_footer_bg'] ?? ''));
    if ($footerBg !== '') {
        echo '<style id="sh-shop-footer-bg">.sh-footer-inner{background:' . htmlspecialchars(bh_cms_hex_color($footerBg, $footerBg)) . ';}</style>';
    }
}

function sh_render_tracking_snippets(?array $settings = null): void
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $gtag = trim((string) ($s['tracking_gtag_id'] ?? ''));
    $pixel = trim((string) ($s['tracking_meta_pixel'] ?? ''));
    if ($gtag !== ''): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gtag) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= htmlspecialchars($gtag) ?>');</script>
    <?php endif;
    if ($pixel !== ''): ?>
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= htmlspecialchars($pixel) ?>');fbq('track','PageView');</script>
    <?php endif;
}