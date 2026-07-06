<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'appearance';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);

$hexOr = static function (string $key, string $fallback) use ($settings): string {
    $val = trim((string) ($settings[$key] ?? ''));
    return $val !== '' ? bh_cms_hex_color($val, $fallback) : $fallback;
};
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'appearance-colors', $sections['appearance-colors'] ?? sh_settings_admin_label('appearance_colors_section', $ta), 'palette', $ta); ?>
            <div class="adm-form-grid adm-color-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_primary', $ta)) ?></label>
                    <input type="color" name="color_primary" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_primary'] ?? '#2563eb')) ?>">
                    <?php sh_admin_render_field_hint($tab, 'color_primary', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_button', $ta)) ?></label>
                    <input type="color" name="color_button" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_button'] ?? ($settings['color_primary'] ?? '#2563eb'))) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_button_hover', $ta)) ?></label>
                    <input type="color" name="color_button_hover" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_button_hover'] ?? '#1d4ed8')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('bg_color', $ta)) ?></label>
                    <input type="color" name="bg_color" value="<?= htmlspecialchars($settings['bg_color'] !== '' ? bh_cms_hex_color($settings['bg_color'], '#f5f5f5') : '#f5f5f5') ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('bg_image', $ta)) ?></label>
                    <input type="url" name="bg_image" value="<?= htmlspecialchars($settings['bg_image'] ?? '') ?>" placeholder="https://..." inputmode="url">
                    <?php sh_admin_render_field_hint($tab, 'bg_image', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'appearance-buttons', $sections['appearance-buttons'] ?? sh_settings_admin_label('appearance_buttons_section', $ta), 'hand-pointer', $ta); ?>
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('appearance_buttons_help', $ta)) ?></p>
            <div class="adm-form-grid adm-color-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_search', $ta)) ?></label>
                    <input type="color" name="color_btn_search" value="<?= htmlspecialchars($hexOr('color_btn_search', bh_cms_hex_color($settings['color_button'] ?? ($settings['color_primary'] ?? '#2563eb')))) ?>">
                    <?php sh_admin_render_field_hint($tab, 'color_btn_search', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_search_hover', $ta)) ?></label>
                    <input type="color" name="color_btn_search_hover" value="<?= htmlspecialchars($hexOr('color_btn_search_hover', bh_cms_hex_color($settings['color_button_hover'] ?? '#1d4ed8'))) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_cart', $ta)) ?></label>
                    <input type="color" name="color_btn_cart" value="<?= htmlspecialchars($hexOr('color_btn_cart', bh_cms_hex_color($settings['color_primary'] ?? '#2563eb'))) ?>">
                    <?php sh_admin_render_field_hint($tab, 'color_btn_cart', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_cart_hover', $ta)) ?></label>
                    <input type="color" name="color_btn_cart_hover" value="<?= htmlspecialchars($hexOr('color_btn_cart_hover', bh_cms_hex_color($settings['color_button_hover'] ?? '#1d4ed8'))) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_outline', $ta)) ?></label>
                    <input type="color" name="color_btn_outline" value="<?= htmlspecialchars($hexOr('color_btn_outline', bh_cms_hex_color($settings['color_primary'] ?? '#2563eb'))) ?>">
                    <?php sh_admin_render_field_hint($tab, 'color_btn_outline', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('color_btn_outline_hover', $ta)) ?></label>
                    <input type="color" name="color_btn_outline_hover" value="<?= htmlspecialchars($hexOr('color_btn_outline_hover', bh_cms_hex_color($settings['color_button_hover'] ?? '#1d4ed8'))) ?>">
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'appearance-typography', $sections['appearance-typography'] ?? sh_settings_admin_label('appearance_typography_section', $ta), 'font', $ta); ?>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_font_family', $ta)) ?></label>
                    <input type="text" name="design_font_family" value="<?= htmlspecialchars($settings['design_font_family'] ?? '') ?>" placeholder="'Segoe UI', system-ui, sans-serif">
                    <?php sh_admin_render_field_hint($tab, 'design_font_family', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_border_radius', $ta)) ?></label>
                    <input type="number" name="design_border_radius" min="0" max="24" value="<?= (int) ($settings['design_border_radius'] ?? 10) ?>">
                    <?php sh_admin_render_field_hint($tab, 'design_border_radius', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'appearance-surfaces', $sections['appearance-surfaces'] ?? sh_settings_admin_label('appearance_surfaces_section', $ta), 'swatchbook', $ta); ?>
            <div class="adm-form-grid adm-color-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_text_color', $ta)) ?></label>
                    <input type="color" name="design_text_color" value="<?= htmlspecialchars($hexOr('design_text_color', '#1e293b')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_text_muted', $ta)) ?></label>
                    <input type="color" name="design_text_muted" value="<?= htmlspecialchars($hexOr('design_text_muted', '#64748b')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_card_bg', $ta)) ?></label>
                    <input type="color" name="design_card_bg" value="<?= htmlspecialchars($hexOr('design_card_bg', '#ffffff')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_header_bg', $ta)) ?></label>
                    <input type="color" name="design_header_bg" value="<?= htmlspecialchars($hexOr('design_header_bg', '#ffffff')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_footer_bg', $ta)) ?></label>
                    <input type="color" name="design_footer_bg" value="<?= htmlspecialchars($hexOr('design_footer_bg', '#1e293b')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_border_color', $ta)) ?></label>
                    <input type="color" name="design_border_color" value="<?= htmlspecialchars($hexOr('design_border_color', '#e2e8f0')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('design_sale_color', $ta)) ?></label>
                    <input type="color" name="design_sale_color" value="<?= htmlspecialchars($hexOr('design_sale_color', '#ea580c')) ?>">
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <div class="adm-card adm-card--dense adm-settings-section" id="appearance-product-card">
        <div class="adm-card-head adm-card-head--compact">
            <h2><i class="fas fa-id-card"></i> <?= htmlspecialchars($sections['appearance-product-card'] ?? sh_settings_admin_label('store_card_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded-compact">
            <?php
            $cardToggles = [];
            foreach ([
                'card_show_category', 'card_show_stock', 'card_show_excerpt', 'card_show_sale_badge',
                'card_show_featured', 'card_show_add_cart', 'card_show_view_btn',
            ] as $key) {
                $cardToggles[] = [
                    'name' => $key,
                    'label' => sh_settings_admin_label($key, $ta),
                    'checked' => !empty($settings[$key]),
                ];
            }
            sh_admin_toggle_section('', $cardToggles, 'id-card');
            ?>
            <div class="adm-field adm-field--inline adm-field--inline-tight" style="margin-top:12px">
                <label for="card_excerpt_len"><?= htmlspecialchars(sh_settings_admin_label('card_excerpt_len', $ta)) ?></label>
                <input type="number" id="card_excerpt_len" name="card_excerpt_len" min="20" max="300" value="<?= (int)($settings['card_excerpt_len'] ?? 85) ?>">
            </div>
        </div>
    </div>

    <div class="adm-card adm-card--dense adm-settings-section" id="appearance-quick-buy">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-bolt"></i> <?= htmlspecialchars($sections['appearance-quick-buy'] ?? sh_settings_admin_label('store_quick_buy_section', $ta)) ?></h2>
            <a href="<?= sh_admin_url('quick-leads.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-list"></i> <?= htmlspecialchars(sh_settings_admin_label('quick_buy_view_leads', $ta)) ?>
            </a>
        </div>
        <div class="adm-card-body padded-compact">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('store_quick_buy_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'quick_buy_enabled', 'label' => sh_settings_admin_label('quick_buy_enabled', $ta), 'checked' => !empty($settings['quick_buy_enabled'])],
                    ['name' => 'quick_buy_show_after_phone', 'label' => sh_settings_admin_label('quick_buy_after_phone', $ta), 'checked' => !empty($settings['quick_buy_show_after_phone'])],
                ],
                'bolt'
            ); ?>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>