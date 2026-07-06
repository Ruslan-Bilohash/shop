<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/menu-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'header';
$sections = sh_admin_settings_sections($tab, $ta);
$menu = sh_menu_settings($settings);
?>
<form method="post" class="adm-settings-form">
    <div class="adm-card adm-settings-section" id="header-nav">
        <div class="adm-card-head">
            <h2><i class="fas fa-bars"></i> <?= htmlspecialchars($sections['header-nav'] ?? sh_settings_admin_label('header_nav_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('header_nav_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'menu_show_sale', 'label' => sh_settings_admin_label('menu_show_sale', $ta), 'checked' => !empty($menu['menu_show_sale'])],
                    ['name' => 'menu_show_track', 'label' => sh_settings_admin_label('menu_show_track', $ta), 'checked' => !empty($menu['menu_show_track'])],
                    ['name' => 'menu_show_contact', 'label' => sh_settings_admin_label('menu_show_contact', $ta), 'checked' => !empty($menu['menu_show_contact'])],
                    ['name' => 'menu_show_solutions', 'label' => sh_settings_admin_label('menu_show_solutions', $ta), 'checked' => !empty($menu['menu_show_solutions'])],
                    ['name' => 'menu_show_signin', 'label' => sh_settings_admin_label('menu_show_signin', $ta), 'checked' => !empty($menu['menu_show_signin'])],
                ],
                'link'
            ); ?>
            <p><a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('header_preview', $ta)) ?></a></p>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>