<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/menu-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'menu';
$sections = sh_admin_settings_sections($tab, $ta);
$menu = sh_menu_settings($settings);
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'menu-api', $sections['menu-api'] ?? sh_settings_admin_label('menu_api_section', $ta), 'plug', $ta, sh_settings_admin_label('menu_api_help', $ta)); ?>
            <div class="adm-alert adm-alert-warning adm-alert-compact">
                <i class="fas fa-flask"></i> <?= htmlspecialchars(sh_settings_admin_label('menu_api_demo_note', $ta)) ?>
            </div>
            <?php sh_admin_toggle_section(
                sh_settings_admin_label('menu_api_section', $ta),
                [
                    ['name' => 'menu_api_enabled', 'label' => sh_settings_admin_label('menu_api_enabled', $ta), 'checked' => !empty($menu['menu_api_enabled'])],
                ],
                'plug'
            ); ?>
            <div class="adm-form-grid adm-form-grid--settings">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('menu_api_url', $ta)) ?></label>
                    <input type="url" name="menu_api_url" value="<?= htmlspecialchars($menu['menu_api_url'] ?? '') ?>" placeholder="https://api.example.com/v1/menu">
                    <?php sh_admin_render_field_hint($tab, 'menu_api_url', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('menu_api_key', $ta)) ?></label>
                    <input type="password" name="menu_api_key" value="" placeholder="<?= !empty($menu['menu_api_key']) ? '••••••••' : 'sk-...' ?>" autocomplete="new-password">
                    <?php sh_admin_render_field_hint($tab, 'menu_api_key', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('menu_api_cache_ttl', $ta)) ?></label>
                    <input type="number" name="menu_api_cache_ttl" min="60" max="86400" value="<?= (int)($menu['menu_api_cache_ttl'] ?? 300) ?>">
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'menu-header', $sections['menu-header'] ?? sh_settings_admin_label('menu_header_section', $ta), 'bars', $ta); ?>
            <?php sh_admin_toggle_section(
                sh_settings_admin_label('menu_header_section', $ta),
                [
                    ['name' => 'menu_show_sale', 'label' => sh_settings_admin_label('menu_show_sale', $ta), 'checked' => !empty($menu['menu_show_sale'])],
                    ['name' => 'menu_show_track', 'label' => sh_settings_admin_label('menu_show_track', $ta), 'checked' => !empty($menu['menu_show_track'])],
                    ['name' => 'menu_show_solutions', 'label' => sh_settings_admin_label('menu_show_solutions', $ta), 'checked' => !empty($menu['menu_show_solutions'])],
                ],
                'bars'
            ); ?>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>