<?php

function sh_seo_settings_defaults(): array
{
    return [
        'seo_site_name'             => '',
        'seo_default_og_image'      => '',
        'seo_org_name'              => '',
        'seo_geo_region'            => 'NO',
        'seo_geo_placename'         => 'Norway',
        'seo_twitter_site'          => '',
        'seo_default_country_code'  => 'NO',
        'seo_schema_product'        => true,
        'seo_schema_breadcrumbs'    => true,
        'seo_schema_itemlist'       => true,
        'seo_schema_website'        => true,
        'seo_schema_organization'   => true,
        'sitemap_enabled'           => true,
        'sitemap_include_products'  => true,
        'sitemap_include_categories'=> true,
        'sitemap_include_verticals' => true,
        'sitemap_priority_home'     => '1.0',
        'sitemap_priority_product'  => '0.8',
        'sitemap_priority_category' => '0.85',
        'sitemap_last_generated'    => '',
    ];
}

function sh_ai_settings_keys(): array
{
    return array_keys(sh_ai_defaults());
}

function sh_merge_site_settings(array $settings): array
{
    if (!function_exists('sh_langs')) {
        require_once __DIR__ . '/store-settings.php';
        global $SH_LANGS;
        if (!isset($SH_LANGS) || !is_array($SH_LANGS)) {
            $SH_LANGS = sh_builtin_langs();
        }
        function sh_langs(): array
        {
            global $SH_LANGS;
            return $SH_LANGS;
        }
    }

    require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';
    require_once __DIR__ . '/payment-settings.php';

    require_once __DIR__ . '/ai.php';
    require_once __DIR__ . '/service-pages.php';
    require_once __DIR__ . '/store-settings.php';
    require_once __DIR__ . '/menu-settings.php';
    $merged = array_merge(
        sh_default_payment_settings(),
        bh_cms_site_settings_defaults(bh_cms_product_accent('shop')),
        sh_seo_settings_defaults(),
        sh_ai_defaults(),
        sh_service_pages_defaults(),
        sh_store_settings_defaults(),
        sh_menu_settings_defaults(),
        $settings
    );
    $merged = sh_merge_store_settings($merged);
    $merged = sh_merge_service_settings($merged);

    foreach (sh_default_payment_settings() as $provider => $fields) {
        if (!is_array($fields)) {
            continue;
        }
        $merged[$provider] = array_merge(
            $fields,
            is_array($merged[$provider] ?? null) ? $merged[$provider] : []
        );
    }

    return $merged;
}

function sh_settings_apply_post(string $section, array $post, array $settings): array
{
    require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';

    if ($section === 'appearance') {
        require_once __DIR__ . '/store-settings.php';
        return sh_appearance_settings_apply_post($post, $settings);
    }

    if ($section === 'chat') {
        $settings = bh_cms_settings_apply_post($section, $post, $settings);
        $chatModel = trim($post['chat_model'] ?? '');
        if ($chatModel === '' && !empty($post['chat_model_select'])) {
            $chatModel = trim($post['chat_model_select']);
        }
        $settings['chat_model'] = $chatModel;
        return $settings;
    }

    if ($section === 'recaptcha') {
        return bh_cms_settings_apply_post($section, $post, $settings);
    }

    if ($section === 'ai') {
        require_once __DIR__ . '/ai.php';
        $existingAi = sh_ai_settings($settings);
        $providers = sh_ai_providers();
        $provider = trim($post['ai_provider'] ?? 'grok');
        if (!isset($providers[$provider])) {
            $provider = 'grok';
        }
        $model = trim($post['ai_model'] ?? '');
        if ($model === '' && !empty($post['ai_model_select'])) {
            $model = trim($post['ai_model_select']);
        }
        $settings['ai_enabled'] = !empty($post['ai_enabled']);
        $settings['ai_provider'] = $provider;
        $settings['ai_api_base'] = rtrim(trim($post['ai_api_base'] ?? ''), '/');
        $settings['ai_model'] = $model !== '' ? $model : ($providers[$provider]['models'][0] ?? 'grok-3-mini');
        $incomingKey = trim($post['ai_api_key'] ?? '');
        if ($incomingKey !== '') {
            $settings['ai_api_key'] = $incomingKey;
            $settings['ai_enabled'] = true;
        } else {
            $settings['ai_api_key'] = (string) ($existingAi['ai_api_key'] ?? '');
        }
        $settings['ai_prompt_product'] = trim($post['ai_prompt_product'] ?? '');
        $settings['ai_prompt_news'] = trim($post['ai_prompt_news'] ?? '');
        $settings['ai_prompt_seo'] = trim($post['ai_prompt_seo'] ?? '');
        foreach (['product', 'chat', 'news', 'seo'] as $ctx) {
            $key = 'ai_model_' . $ctx;
            $model = trim($post[$key] ?? '');
            if ($model === '' && !empty($post[$key . '_select'])) {
                $model = trim($post[$key . '_select']);
            }
            $settings[$key] = $model;
        }
        $src = trim($post['ai_source_lang'] ?? 'en');
        $settings['ai_source_lang'] = array_key_exists($src, sh_langs()) ? $src : 'en';
        return $settings;
    }

    if ($section === 'seo') {
        $settings['seo_site_name'] = trim($post['seo_site_name'] ?? '');
        $settings['seo_default_og_image'] = trim($post['seo_default_og_image'] ?? '');
        $settings['seo_org_name'] = trim($post['seo_org_name'] ?? '');
        $settings['seo_geo_region'] = strtoupper(substr(trim($post['seo_geo_region'] ?? 'NO'), 0, 8));
        $settings['seo_geo_placename'] = trim($post['seo_geo_placename'] ?? '');
        $settings['seo_twitter_site'] = trim($post['seo_twitter_site'] ?? '');
        $settings['seo_schema_product'] = !empty($post['seo_schema_product']);
        $settings['seo_schema_breadcrumbs'] = !empty($post['seo_schema_breadcrumbs']);
        $settings['seo_schema_itemlist'] = !empty($post['seo_schema_itemlist']);
        $settings['seo_schema_website'] = !empty($post['seo_schema_website']);
        $settings['seo_schema_organization'] = !empty($post['seo_schema_organization']);
        $settings['sitemap_enabled'] = !empty($post['sitemap_enabled']);
        $settings['sitemap_include_products'] = !empty($post['sitemap_include_products']);
        $settings['sitemap_include_categories'] = !empty($post['sitemap_include_categories']);
        $settings['sitemap_include_verticals'] = !empty($post['sitemap_include_verticals']);
        $settings['sitemap_priority_home'] = sh_sitemap_priority_value($post['sitemap_priority_home'] ?? '1.0', '1.0');
        $settings['sitemap_priority_product'] = sh_sitemap_priority_value($post['sitemap_priority_product'] ?? '0.8', '0.8');
        $settings['sitemap_priority_category'] = sh_sitemap_priority_value($post['sitemap_priority_category'] ?? '0.85', '0.85');
        $cc = strtoupper(trim($post['seo_default_country_code'] ?? 'NO'));
        $settings['seo_default_country_code'] = preg_match('/^[A-Z]{2}$/', $cc) ? $cc : 'NO';
    }

    if ($section === 'pages') {
        require_once __DIR__ . '/service-pages.php';
        $settings = sh_service_pages_apply_post($post, $settings);
    }

    if ($section === 'footer') {
        require_once __DIR__ . '/service-pages.php';
        $settings = sh_footer_links_apply_post($post, $settings);
    }

    if ($section === 'store') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_store_settings_apply_post($post, $settings);
    }

    if ($section === 'analytics') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_analytics_settings_apply_post($post, $settings);
    }

    if ($section === 'advanced') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_advanced_settings_apply_post($post, $settings);
    }

    if ($section === 'customer_auth') {
        require_once __DIR__ . '/customer-auth.php';
        $settings = sh_customer_auth_apply_post($post, $settings);
    }

    if ($section === 'languages') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_languages_apply_post($post, $settings);
    }

    if ($section === 'homepage') {
        require_once __DIR__ . '/homepage-blocks.php';
        $settings = sh_home_blocks_apply_post($post, $settings);
    }

    if ($section === 'block_builder') {
        require_once __DIR__ . '/block-templates.php';
        $settings = sh_block_templates_apply_post($post, $settings);
    }

    if ($section === 'header') {
        require_once __DIR__ . '/menu-settings.php';
        $settings = sh_menu_settings_apply_post($post, $settings);
    }

    if ($section === 'posten') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_posten_settings_apply_post($post, $settings);
    }

    if ($section === 'nova_poshta') {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_nova_poshta_settings_apply_post($post, $settings);
    }

    return $settings;
}

function sh_sitemap_priority_value(string $value, string $fallback): string
{
    $value = trim($value);
    if ($value === '' || !is_numeric($value)) {
        return $fallback;
    }
    $num = (float) $value;
    if ($num < 0.0) {
        return '0.0';
    }
    if ($num > 1.0) {
        return '1.0';
    }
    return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.') ?: '0';
}

function sh_settings_tabs(): array
{
    return [
        'store'      => ['file' => 'settings-store.php',      'icon' => 'store', 'group' => 'shop'],
        'languages'  => ['file' => 'settings-languages.php',  'icon' => 'language', 'group' => 'advanced'],
        'payments'   => ['file' => 'payments.php',            'icon' => 'credit-card', 'group' => 'integrations'],
        'homepage'      => ['file' => 'settings-homepage.php',      'icon' => 'house', 'group' => 'content'],
        'block_builder' => ['file' => 'settings-block-builder.php', 'icon' => 'wand-magic-sparkles', 'group' => 'design'],
        'pages'      => ['file' => 'settings-pages.php',      'icon' => 'file-lines', 'group' => 'content'],
        'footer'     => ['file' => 'settings-footer.php',     'icon' => 'link', 'group' => 'content'],
        'header'     => ['file' => 'settings-header.php',     'icon' => 'bars', 'group' => 'content'],
        'posten'      => ['file' => 'settings-posten.php',      'icon' => 'truck', 'group' => 'integrations'],
        'nova_poshta' => ['file' => 'settings-nova-poshta.php', 'icon' => 'warehouse', 'group' => 'integrations'],
        'appearance' => ['file' => 'settings-appearance.php', 'icon' => 'palette', 'group' => 'design'],
        'seo'           => ['file' => 'settings-seo.php',           'icon' => 'chart-line', 'group' => 'marketing'],
        'seo_analysis'  => ['file' => 'settings-seo-analysis.php',  'icon' => 'magnifying-glass-chart', 'group' => 'marketing'],
        'analytics'     => ['file' => 'settings-analytics.php',       'icon' => 'chart-pie', 'group' => 'marketing'],
        'chat'          => ['file' => 'settings-chat.php',          'icon' => 'comments', 'group' => 'integrations'],
        'ai'            => ['file' => 'settings-ai.php',            'icon' => 'wand-magic-sparkles', 'group' => 'integrations'],
        'recaptcha'     => ['file' => 'settings-recaptcha.php',     'icon' => 'shield-alt', 'group' => 'integrations'],
        'customer_auth' => ['file' => 'settings-customer-auth.php', 'icon' => 'user-lock', 'group' => 'integrations'],
        'advanced'   => ['file' => 'settings-advanced.php',   'icon' => 'gear', 'group' => 'advanced'],
    ];
}

/** @return array<string, array{label_key:string,icon:string,tabs:list<string>}> */
function sh_settings_tab_groups(): array
{
    return [
        'shop' => [
            'label_key' => 'settings_group_shop',
            'icon'      => 'store',
            'tabs'      => ['store'],
        ],
        'content' => [
            'label_key' => 'settings_group_content',
            'icon'      => 'file-lines',
            'tabs'      => ['homepage', 'pages', 'footer', 'header'],
        ],
        'design' => [
            'label_key' => 'settings_group_design',
            'icon'      => 'palette',
            'tabs'      => ['appearance', 'block_builder'],
        ],
        'marketing' => [
            'label_key' => 'settings_group_marketing',
            'icon'      => 'bullhorn',
            'tabs'      => ['seo', 'seo_analysis', 'analytics'],
        ],
        'integrations' => [
            'label_key' => 'settings_group_integrations',
            'icon'      => 'plug',
            'tabs'      => ['chat', 'ai', 'recaptcha', 'customer_auth', 'payments', 'posten', 'nova_poshta'],
        ],
        'advanced' => [
            'label_key' => 'settings_group_advanced',
            'icon'      => 'gear',
            'tabs'      => ['advanced', 'languages'],
        ],
    ];
}

function sh_sitemap_regenerate(array $settings): array
{
    $settings['sitemap_last_generated'] = gmdate('Y-m-d\TH:i:s\Z');
    return $settings;
}

function sh_settings_tab_active(string $tab): bool
{
    global $settings_tab;
    return ($settings_tab ?? '') === $tab;
}

function sh_settings_admin_label(string $key, array $ta = []): string
{
    if (isset($ta[$key]) && $ta[$key] !== '') {
        return (string) $ta[$key];
    }
    static $enAdmin = null;
    if ($enAdmin === null) {
        $enFile = dirname(__DIR__) . '/lang/en.php';
        if (is_readable($enFile)) {
            $enAll = require $enFile;
            $enAdmin = is_array($enAll['admin'] ?? null) ? $enAll['admin'] : [];
        } else {
            $enAdmin = [];
        }
    }
    if (isset($enAdmin[$key]) && $enAdmin[$key] !== '' && is_string($enAdmin[$key])) {
        return $enAdmin[$key];
    }
    return bh_cms_admin_label($key, $ta);
}

function sh_render_settings_tabs(callable $adminUrlFn, array $ta = []): void
{
    global $settings_tab;
    $tabs = sh_settings_tabs();
    $groups = sh_settings_tab_groups();
    $currentKey = $settings_tab ?? '';
    $currentGroup = null;
    foreach ($groups as $group) {
        if (in_array($currentKey, $group['tabs'], true)) {
            $currentGroup = $group;
            break;
        }
    }
    if ($currentGroup === null) {
        return;
    }
    $groupTabs = array_values(array_filter(
        $currentGroup['tabs'],
        static fn(string $key): bool => isset($tabs[$key])
    ));
    if (count($groupTabs) <= 1) {
        return;
    }
    $aria = sh_settings_admin_label('settings_nav_aria', $ta);
    ?>
    <nav class="adm-settings-subnav" aria-label="<?= htmlspecialchars($aria) ?>">
        <span class="adm-settings-subnav-group">
            <i class="fas fa-<?= htmlspecialchars($currentGroup['icon']) ?>" aria-hidden="true"></i>
            <?= htmlspecialchars(sh_settings_admin_label($currentGroup['label_key'], $ta)) ?>
        </span>
        <div class="adm-settings-subnav-tabs">
            <?php foreach ($groupTabs as $key):
                $tab = $tabs[$key];
                ?>
            <a href="<?= htmlspecialchars($adminUrlFn($tab['file'])) ?>"
               class="adm-settings-tab <?= sh_settings_tab_active($key) ? 'active' : '' ?>">
                <i class="fas fa-<?= htmlspecialchars($tab['icon']) ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars(sh_settings_admin_label('settings_tab_' . $key, $ta)) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>
    <?php
}

function sh_render_settings_form(string $section, array $settings, array $ta = []): void
{
    $base = __DIR__ . '/../admin/includes/form-';
    $candidates = [
        $base . $section . '.php',
        $base . str_replace('_', '-', $section) . '.php',
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            global $lang;
            if (!is_string($lang ?? null) || $lang === '') {
                $lang = function_exists('sh_site_default_lang') ? sh_site_default_lang() : 'en';
            }
            include $path;
            return;
        }
    }
    bh_cms_render_settings_form($section, $settings, $ta);
}

function sh_seo_site_name(?array $settings = null): string
{
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $name = trim($settings['seo_site_name'] ?? '');
    return $name !== '' ? $name : (defined('SH_SITE_NAME') ? SH_SITE_NAME : 'Shop CMS');
}

function sh_seo_org_name(?array $settings = null): string
{
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $name = trim($settings['seo_org_name'] ?? '');
    return $name !== '' ? $name : sh_seo_site_name($settings);
}