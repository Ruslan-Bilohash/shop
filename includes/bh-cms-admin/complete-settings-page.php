<?php
/**
 * Full settings page (appearance / recaptcha / chat).
 * Set before require: $settings_tab, $bh_cms_load_settings, $bh_cms_save_settings,
 * $bh_cms_admin_url, $bh_cms_layout, $bh_cms_layout_end, $admin_page='settings', $ta
 */
$admin_page = $admin_page ?? 'settings';
$page_title = bh_cms_admin_label('settings_tab_' . $settings_tab, $ta);
require dirname(__DIR__) . '/bh-cms-admin/page-shell.php';
require $bh_cms_layout;
bh_cms_render_settings_tabs($bh_cms_admin_url, $ta);
if ($flash === 'success'): ?>
<div class="adm-alert adm-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(bh_cms_admin_label('settings_saved', $ta)) ?></div>
<?php elseif ($flash === 'error'): ?>
<div class="adm-alert adm-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(bh_cms_admin_label('error', $ta)) ?></div>
<?php endif;
bh_cms_render_settings_form($settings_tab, $settings, $ta);
require $bh_cms_layout_end;