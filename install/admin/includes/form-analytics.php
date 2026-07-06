<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once __DIR__ . '/admin-field-help.php';
$tab = 'analytics';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'analytics-tracking', $sections['analytics-tracking'] ?? sh_settings_admin_label('analytics_section', $ta), 'chart-pie', $ta); ?>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('tracking_gtag_id', $ta)) ?></label>
                    <input type="text" name="tracking_gtag_id" value="<?= htmlspecialchars($settings['tracking_gtag_id'] ?? '') ?>" placeholder="G-XXXXXXXX">
                    <?php sh_admin_render_field_hint($tab, 'tracking_gtag_id', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('tracking_meta_pixel', $ta)) ?></label>
                    <input type="text" name="tracking_meta_pixel" value="<?= htmlspecialchars($settings['tracking_meta_pixel'] ?? '') ?>" placeholder="Meta Pixel ID">
                    <?php sh_admin_render_field_hint($tab, 'tracking_meta_pixel', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>