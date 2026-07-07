<?php
/**
 * Storefront design theme runtime — body class, preview, CSS, settings apply.
 */
declare(strict_types=1);

require_once __DIR__ . '/design-themes.php';

/** @return list<string> */
function sh_storefront_theme_ids(): array
{
    return ['nordic', 'dark', 'fresh', 'bold', 'ocean', 'coral'];
}

function sh_normalize_storefront_theme_id(?string $id): string
{
    $id = strtolower(trim((string) $id));
    return in_array($id, sh_storefront_theme_ids(), true) ? $id : 'nordic';
}

function sh_theme_preview_id(): ?string
{
    if (!empty($_GET['theme_preview'])) {
        $id = sh_normalize_storefront_theme_id((string) $_GET['theme_preview']);
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['sh_theme_preview'] = $id;
        return $id;
    }
    if (!empty($_GET['clear_theme_preview'])) {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        unset($_SESSION['sh_theme_preview']);
        return null;
    }
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $sess = (string) ($_SESSION['sh_theme_preview'] ?? '');
    return $sess !== '' ? sh_normalize_storefront_theme_id($sess) : null;
}

function sh_active_design_theme_id(?array $settings = null): string
{
    $preview = sh_theme_preview_id();
    if ($preview !== null) {
        return $preview;
    }
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $settings = is_array($settings) ? $settings : [];
    return sh_normalize_storefront_theme_id((string) ($settings['design_theme_id'] ?? 'nordic'));
}

function sh_theme_body_classes(?array $settings = null, string $extra = ''): string
{
    $id = sh_active_design_theme_id($settings);
    $classes = ['sh-theme', 'sh-theme--' . $id];
    if ($extra !== '') {
        $classes[] = trim($extra);
    }
    if (sh_theme_preview_id() !== null) {
        $classes[] = 'sh-theme-preview';
    }
    return implode(' ', $classes);
}

function sh_render_storefront_theme_stylesheet(): void
{
    if (!function_exists('sh_asset')) {
        return;
    }
    $href = sh_asset('css/storefront-themes.css') . '?v=1';
    echo '<link rel="stylesheet" href="' . htmlspecialchars($href) . '">' . "\n";
}

function sh_render_theme_preview_banner(array $t): void
{
    if (sh_theme_preview_id() === null) {
        return;
    }
    $labels = is_array($t['theme_preview'] ?? null) ? $t['theme_preview'] : [];
    $text = (string) ($labels['banner'] ?? 'Design preview — not saved yet.');
    $clear = sh_url('index.php?clear_theme_preview=1');
    ?>
    <div class="sh-theme-preview-banner" role="status">
        <i class="fas fa-palette" aria-hidden="true"></i>
        <span><?= htmlspecialchars($text) ?></span>
        <a href="<?= htmlspecialchars($clear) ?>"><?= htmlspecialchars((string) ($labels['exit'] ?? 'Exit preview')) ?></a>
    </div>
    <?php
}

/** @return array<string, mixed> */
function sh_design_theme_settings_patch(string $id): array
{
    $id = sh_normalize_storefront_theme_id($id);
    $patches = [
        'nordic' => [
            'design_theme_id'       => 'nordic',
            'color_primary'         => '#2563eb',
            'color_button'          => '#2563eb',
            'color_button_hover'    => '#1d4ed8',
            'bg_color'              => '#faf9f7',
            'design_card_bg'        => '#ffffff',
            'design_header_bg'      => '#ffffff',
            'design_footer_bg'      => '#f8fafc',
            'design_text_color'     => '#1e293b',
            'design_text_muted'     => '#64748b',
            'design_border_color'   => '#e2e8f0',
            'design_sale_color'     => '#ea580c',
            'design_border_radius'  => 10,
            'design_font_family'    => '',
        ],
        'dark' => [
            'design_theme_id'       => 'dark',
            'color_primary'         => '#fbbf24',
            'color_button'          => '#fbbf24',
            'color_button_hover'    => '#f59e0b',
            'bg_color'              => '#0f172a',
            'design_card_bg'        => '#1e293b',
            'design_header_bg'      => '#1e293b',
            'design_footer_bg'      => '#0f172a',
            'design_text_color'     => '#f1f5f9',
            'design_text_muted'     => '#94a3b8',
            'design_border_color'   => '#334155',
            'design_sale_color'     => '#fbbf24',
            'design_border_radius'  => 10,
            'design_font_family'    => '',
        ],
        'fresh' => [
            'design_theme_id'       => 'fresh',
            'color_primary'         => '#059669',
            'color_button'          => '#059669',
            'color_button_hover'    => '#047857',
            'bg_color'              => '#ecfdf5',
            'design_card_bg'        => '#ffffff',
            'design_header_bg'      => '#ffffff',
            'design_footer_bg'      => '#d1fae5',
            'design_text_color'     => '#064e3b',
            'design_text_muted'     => '#047857',
            'design_border_color'   => '#a7f3d0',
            'design_sale_color'     => '#16a34a',
            'design_border_radius'  => 16,
            'design_font_family'    => '',
        ],
        'bold' => [
            'design_theme_id'       => 'bold',
            'color_primary'         => '#dc2626',
            'color_button'          => '#dc2626',
            'color_button_hover'    => '#b91c1c',
            'bg_color'              => '#fef2f2',
            'design_card_bg'        => '#ffffff',
            'design_header_bg'      => '#ffffff',
            'design_footer_bg'      => '#fee2e2',
            'design_text_color'     => '#1e293b',
            'design_text_muted'     => '#64748b',
            'design_border_color'   => '#fecaca',
            'design_sale_color'     => '#dc2626',
            'design_border_radius'  => 8,
            'design_font_family'    => '',
        ],
        'ocean' => [
            'design_theme_id'       => 'ocean',
            'color_primary'         => '#0891b2',
            'color_button'          => '#0891b2',
            'color_button_hover'    => '#0e7490',
            'bg_color'              => '#ecfeff',
            'design_card_bg'        => '#ffffff',
            'design_header_bg'      => '#ffffff',
            'design_footer_bg'      => '#cffafe',
            'design_text_color'     => '#164e63',
            'design_text_muted'     => '#0e7490',
            'design_border_color'   => '#a5f3fc',
            'design_sale_color'     => '#06b6d4',
            'design_border_radius'  => 12,
            'design_font_family'    => '',
        ],
        'coral' => [
            'design_theme_id'       => 'coral',
            'color_primary'         => '#ea580c',
            'color_button'          => '#ea580c',
            'color_button_hover'    => '#c2410c',
            'bg_color'              => '#fff7ed',
            'design_card_bg'        => '#ffffff',
            'design_header_bg'      => '#ffffff',
            'design_footer_bg'      => '#ffedd5',
            'design_text_color'     => '#431407',
            'design_text_muted'     => '#9a3412',
            'design_border_color'   => '#fed7aa',
            'design_sale_color'     => '#ea580c',
            'design_border_radius'  => 16,
            'design_font_family'    => '',
        ],
    ];
    return $patches[$id] ?? $patches['nordic'];
}

/** @return array{ok:bool,theme_id:string,error:string} */
function sh_design_theme_apply(string $id): array
{
    $id = sh_normalize_storefront_theme_id($id);
    if (!function_exists('sh_site_settings') || !function_exists('sh_save_settings')) {
        return ['ok' => false, 'theme_id' => $id, 'error' => 'Settings unavailable'];
    }
    $settings = sh_site_settings();
    $patch = sh_design_theme_settings_patch($id);
    $merged = array_merge($settings, $patch);
    if (!sh_save_settings($merged)) {
        return ['ok' => false, 'theme_id' => $id, 'error' => 'Could not save settings'];
    }
    return ['ok' => true, 'theme_id' => $id, 'error' => ''];
}