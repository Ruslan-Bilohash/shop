<?php
/** @var array $settings @var array $ta */
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'recaptcha';
$sections = sh_admin_settings_sections($tab, $ta);
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'recaptcha-main', $sections['recaptcha-main'] ?? sh_settings_admin_label('recaptcha_section', $ta), 'shield-alt', $ta); ?>
            <div class="adm-form-grid adm-form-grid--settings">
                <?php sh_admin_toggle_section(
                    sh_settings_admin_label('recaptcha_section', $ta),
                    [
                        ['name' => 'recaptcha_enabled', 'label' => sh_settings_admin_label('recaptcha_enabled', $ta), 'checked' => !empty($settings['recaptcha_enabled'])],
                    ],
                    'shield-alt'
                ); ?>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('recaptcha_site_key', $ta)) ?></label>
                    <input type="text" name="recaptcha_site_key" value="<?= htmlspecialchars($settings['recaptcha_site_key'] ?? '') ?>" autocomplete="off" placeholder="6Lc...">
                    <?php sh_admin_render_field_hint($tab, 'recaptcha_site_key', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('recaptcha_secret_key', $ta)) ?></label>
                    <input type="password" name="recaptcha_secret_key" value="<?= htmlspecialchars($settings['recaptcha_secret_key'] ?? '') ?>" autocomplete="off" placeholder="6Lc...">
                    <?php sh_admin_render_field_hint($tab, 'recaptcha_secret_key', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>