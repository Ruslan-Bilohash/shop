<?php
/**
 * Shared site settings: AI chat, reCAPTCHA, appearance colours.
 * Used across BILOHASH PHP CMS demos (booking, auction, shop, …).
 */

function bh_cms_site_settings_defaults(string $accent = '#2563eb'): array
{
    return [
        'chat_enabled'        => false,
        'chat_provider'       => 'none',
        'chat_api_key'        => '',
        'chat_instructions'   => '',
        'chat_widget_color'   => '',
        'chat_widget_icon'    => 'comments',
        'recaptcha_enabled'   => true,
        'recaptcha_site_key'  => '',
        'recaptcha_secret_key'=> '',
        'color_primary'       => $accent,
        'color_button'        => $accent,
        'color_button_hover'  => $accent,
        'bg_color'            => '',
        'bg_image'            => '',
    ];
}

function bh_cms_product_accent(string $product): string
{
    return match (strtolower($product)) {
        'booking'  => '#003580',
        'auction'  => '#b45309',
        'shop'     => '#2563eb',
        'pizza'    => '#c2410c',
        'gamehub'  => '#06b6d4',
        'faktura'  => '#0284c7',
        'today'    => '#1e3a8a',
        'freelance'=> '#10b981',
        default    => '#2563eb',
    };
}

function bh_cms_merge_site_settings(array $settings, string $product = ''): array
{
    $accent = bh_cms_product_accent($product);
    return array_merge(bh_cms_site_settings_defaults($accent), $settings);
}

function bh_cms_settings_apply_post(string $section, array $post, array $settings): array
{
    switch ($section) {
        case 'appearance':
            $settings['color_primary'] = trim($post['color_primary'] ?? ($settings['color_primary'] ?? '#2563eb'));
            $settings['color_button'] = trim($post['color_button'] ?? $settings['color_primary']);
            $settings['color_button_hover'] = trim($post['color_button_hover'] ?? $settings['color_button']);
            $settings['bg_color'] = trim($post['bg_color'] ?? '');
            $settings['bg_image'] = trim($post['bg_image'] ?? '');
            break;
        case 'recaptcha':
            $settings['recaptcha_enabled'] = !empty($post['recaptcha_enabled']);
            $settings['recaptcha_site_key'] = trim($post['recaptcha_site_key'] ?? '');
            $settings['recaptcha_secret_key'] = trim($post['recaptcha_secret_key'] ?? '');
            break;
        case 'chat':
            $settings['chat_enabled'] = !empty($post['chat_enabled']);
            $provider = $post['chat_provider'] ?? 'none';
            $settings['chat_provider'] = in_array($provider, ['none', 'grok', 'gpt'], true) ? $provider : 'none';
            $settings['chat_api_key'] = trim($post['chat_api_key'] ?? '');
            $settings['chat_instructions'] = trim($post['chat_instructions'] ?? '');
            $color = trim($post['chat_widget_color'] ?? '');
            $settings['chat_widget_color'] = $color !== '' ? bh_cms_hex_color($color, $color) : '';
            $icon = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($post['chat_widget_icon'] ?? 'comments')));
            $settings['chat_widget_icon'] = $icon !== '' ? $icon : 'comments';
            break;
    }
    return $settings;
}

function bh_cms_settings_tabs(): array
{
    return [
        'appearance' => ['file' => 'settings-appearance.php', 'icon' => 'palette'],
        'recaptcha'  => ['file' => 'settings-recaptcha.php',  'icon' => 'shield-alt'],
        'chat'       => ['file' => 'settings-chat.php',       'icon' => 'robot'],
    ];
}

function bh_cms_settings_tab_active(string $tab): bool
{
    global $settings_tab;
    return ($settings_tab ?? '') === $tab;
}

function bh_cms_admin_label(string $key, array $ta = []): string
{
    $fallbacks = [
        'settings'              => 'Settings',
        'settings_saved'        => 'Settings saved.',
        'error'                 => 'Could not save settings.',
        'save'                  => 'Save',
        'settings_tab_appearance'=> 'Appearance',
        'settings_tab_recaptcha' => 'reCAPTCHA',
        'settings_tab_chat'      => 'AI Chat',
        'settings_appearance'   => 'Site colours',
        'appearance_help'       => 'Choose the main accent colour for buttons, links and highlights on the public site.',
        'color_primary'         => 'Main site colour',
        'color_button'          => 'Button colour',
        'color_button_hover'    => 'Button hover',
        'bg_color'              => 'Background colour (optional)',
        'bg_image'              => 'Background image URL (optional)',
        'recaptcha_section'     => 'reCAPTCHA',
        'recaptcha_help'      => 'Google reCAPTCHA v2 keys for contact and booking forms. Get keys at google.com/recaptcha/admin.',
        'recaptcha_enabled'     => 'Enable reCAPTCHA on public forms',
        'recaptcha_site_key'    => 'Site key',
        'recaptcha_secret_key'  => 'Secret key',
        'chat_section'          => 'AI chat widget',
        'chat_help'             => 'Floating AI assistant on the public site (Grok xAI or OpenAI GPT). API key is stored in settings JSON.',
        'chat_enabled'          => 'Enable chat widget',
        'chat_provider'         => 'Provider',
        'chat_provider_none'    => 'Disabled',
        'chat_provider_grok'    => 'Grok xAI',
        'chat_provider_gpt'     => 'OpenAI GPT',
        'chat_api_key'          => 'API key',
        'chat_api_key_help'     => 'xAI key for Grok, OpenAI key for GPT.',
        'chat_instructions'     => 'System instructions',
        'chat_instructions_help'=> 'Context for the assistant: product name, languages, support policy.',
    ];
    return $ta[$key] ?? $fallbacks[$key] ?? ucfirst(str_replace('_', ' ', $key));
}

function bh_cms_hex_color(string $hex, string $fallback = '#2563eb'): string
{
    $hex = trim($hex);
    if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hex)) {
        return $hex;
    }
    return $fallback;
}

function bh_cms_bind_recaptcha_settings(?array $settings): void
{
    $GLOBALS['bh_cms_recaptcha_settings'] = is_array($settings) ? $settings : null;
}

function bh_cms_recaptcha_site_key(?array $settings = null): string
{
    $settings ??= $GLOBALS['bh_cms_recaptcha_settings'] ?? null;
    if (is_array($settings) && !empty($settings['recaptcha_site_key'])) {
        return (string) $settings['recaptcha_site_key'];
    }
    // Caller (cms_recaptcha_site_key) applies demo fallback — never recurse back.
    return '';
}

function bh_cms_recaptcha_secret_key(?array $settings = null): string
{
    $settings ??= $GLOBALS['bh_cms_recaptcha_settings'] ?? null;
    if (is_array($settings) && !empty($settings['recaptcha_secret_key'])) {
        return (string) $settings['recaptcha_secret_key'];
    }
    // Caller (cms_recaptcha_secret_key) applies demo fallback — never recurse back.
    return '';
}

function bh_cms_recaptcha_enabled(?array $settings = null): bool
{
    $settings ??= $GLOBALS['bh_cms_recaptcha_settings'] ?? null;
    if (is_array($settings) && array_key_exists('recaptcha_enabled', $settings)) {
        return (bool) $settings['recaptcha_enabled'];
    }
    return true;
}

function bh_cms_verify_recaptcha(?string $response, ?array $settings = null): bool
{
    if (!bh_cms_recaptcha_enabled($settings)) {
        return true;
    }
    $response = trim((string) $response);
    if ($response === '') {
        return false;
    }
    $secret = bh_cms_recaptcha_secret_key($settings);
    if ($secret === '') {
        return false;
    }
    $payload = http_build_query([
        'secret'   => $secret,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 12,
        ],
    ]);
    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) {
        return false;
    }
    $data = json_decode($raw, true);
    return !empty($data['success']);
}

function bh_cms_chat_enabled(?array $settings = null): bool
{
    $settings ??= $GLOBALS['bh_cms_site_settings'] ?? null;
    if (!is_array($settings)) {
        return false;
    }
    return !empty($settings['chat_enabled']) && ($settings['chat_provider'] ?? 'none') !== 'none';
}

function bh_cms_render_chat_widget(string $productLabel, ?array $settings = null, ?string $lang = null): void
{
    $settings ??= $GLOBALS['bh_cms_site_settings'] ?? null;
    if (!bh_cms_chat_enabled($settings)) {
        return;
    }
    $provider = $settings['chat_provider'] ?? 'grok';
    echo '<script>window.FL_CHAT_CONFIG = {provider:' . json_encode($provider)
        . ',instructions:' . json_encode($settings['chat_instructions'] ?? '')
        . ',product:' . json_encode($productLabel) . '};</script>';
    $bh_chat_lang = $lang ?? ($GLOBALS['lang'] ?? 'en');
    $bh_chat_variant = 'root';
    $bh_chat_require_consent = false;
    $bh_chat_crm_url = 'https://bilohash.com/ai/crm/';
    $accent = trim((string) ($settings['chat_widget_color'] ?? ''));
    $bh_chat_accent_color = $accent !== ''
        ? bh_cms_hex_color($accent, $accent)
        : bh_cms_hex_color($settings['color_primary'] ?? '', bh_cms_product_accent('shop'));
    $icon = preg_replace('/[^a-z0-9-]/', '', strtolower(trim((string) ($settings['chat_widget_icon'] ?? 'comments'))));
    $bh_chat_toggle_icon = $icon !== '' ? $icon : 'comments';
    include __DIR__ . '/bh-chat-widget.php';
}

function bh_cms_render_theme_styles(string $product, ?array $settings = null): void
{
    $settings ??= $GLOBALS['bh_cms_site_settings'] ?? null;
    if (!is_array($settings)) {
        return;
    }
    $accent = bh_cms_hex_color($settings['color_primary'] ?? '', bh_cms_product_accent($product));
    $btn = bh_cms_hex_color($settings['color_button'] ?? '', $accent);
    $btnHover = bh_cms_hex_color($settings['color_button_hover'] ?? '', $btn);
    $bg = trim($settings['bg_color'] ?? '');
    $bgImage = trim($settings['bg_image'] ?? '');

    $css = match (strtolower($product)) {
        'booking' => ":root{--bk-blue:{$accent};--bk-blue-light:{$btn};--bk-yellow-hover:{$btnHover};}"
            . ($bg !== '' ? "body{background-color:{$bg};}" : ''),
        'auction' => ":root{--au-gold:{$accent};--au-gold-hover:{$btnHover};--au-accent:{$btn};}",
        'shop' => ":root{--sh-primary:{$accent};--sh-primary-hover:{$btnHover};}",
        'pizza' => ":root{--pz-terracotta:{$accent};--pz-terracotta-dim:{$btnHover};}",
        'gamehub' => ":root{--gh-accent:{$accent};--gh-accent-hover:{$btnHover};}",
        'faktura' => ":root{--fk-primary:{$accent};--fk-accent:{$btn};--fk-primary-light:{$btnHover};}",
        'today' => ":root{--td-red:{$accent};--td-red-dark:{$btnHover};}",
        default => ":root{--cms-accent:{$accent};--cms-accent-hover:{$btnHover};}",
    };

    if ($bgImage !== '') {
        $css .= 'body{background-image:url(' . json_encode($bgImage) . ');background-size:cover;background-attachment:fixed;background-position:center;}';
    }

    echo '<style id="bh-cms-theme-' . htmlspecialchars($product, ENT_QUOTES) . '">' . $css . '</style>';
}

function bh_cms_admin_settings_css_href(): string
{
    return '/includes/bh-cms-admin/admin-settings.css?v=1';
}

function bh_cms_render_settings_tabs(callable $adminUrlFn, array $ta = []): void
{
    require __DIR__ . '/bh-cms-admin/settings-tabs.php';
}

function bh_cms_render_settings_form(string $section, array $settings, array $ta = []): void
{
    $path = __DIR__ . '/bh-cms-admin/form-' . $section . '.php';
    if (is_file($path)) {
        include $path;
    }
}