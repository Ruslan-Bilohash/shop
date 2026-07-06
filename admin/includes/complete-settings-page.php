<?php
/**
 * Full settings page shell for Shop CMS.
 * Set before require: $settings_tab, $bh_cms_load_settings, $bh_cms_save_settings,
 * $bh_cms_admin_url, $bh_cms_layout, $bh_cms_layout_end, $admin_page='settings', $ta
 */
require_once __DIR__ . '/admin-field-help.php';

$admin_page = $admin_page ?? 'settings';
$page_title = sh_settings_admin_label('settings_tab_' . $settings_tab, $ta);
require __DIR__ . '/settings-page-shell.php';
require $bh_cms_layout;
sh_render_settings_tabs($bh_cms_admin_url, $ta);
if ($flash === 'success'): ?>
<div class="adm-alert adm-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('settings_saved', $ta)) ?></div>
<?php elseif ($flash === 'sitemap_ok'): ?>
<div class="adm-alert adm-alert-success"><i class="fas fa-sitemap"></i> <?= htmlspecialchars(sh_settings_admin_label('sitemap_regenerated', $ta)) ?></div>
<?php elseif ($flash === 'error'): ?>
<div class="adm-alert adm-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('error', $ta)) ?></div>
<?php endif;
sh_admin_render_settings_intro($settings_tab, $ta);
?>
<div class="adm-settings-layout">
    <div class="adm-settings-main">
        <?php sh_render_settings_form($settings_tab, $settings, $ta); ?>
    </div>
    <?php sh_admin_render_settings_guide($settings_tab, $ta); ?>
</div>
<?php
require $bh_cms_layout_end;