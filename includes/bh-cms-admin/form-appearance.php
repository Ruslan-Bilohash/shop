<?php /** @var array $settings @var array $ta */ ?>
<form method="post" class="adm-settings-form">
    <div class="adm-card">
        <div class="adm-card-head"><h2><?= htmlspecialchars(bh_cms_admin_label('settings_appearance', $ta)) ?></h2></div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(bh_cms_admin_label('appearance_help', $ta)) ?></p>
            <div class="adm-form-grid adm-color-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(bh_cms_admin_label('color_primary', $ta)) ?></label>
                    <input type="color" name="color_primary" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_primary'] ?? '#2563eb')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(bh_cms_admin_label('color_button', $ta)) ?></label>
                    <input type="color" name="color_button" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_button'] ?? ($settings['color_primary'] ?? '#2563eb'))) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(bh_cms_admin_label('color_button_hover', $ta)) ?></label>
                    <input type="color" name="color_button_hover" value="<?= htmlspecialchars(bh_cms_hex_color($settings['color_button_hover'] ?? '#1d4ed8')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(bh_cms_admin_label('bg_color', $ta)) ?></label>
                    <input type="color" name="bg_color" value="<?= htmlspecialchars($settings['bg_color'] !== '' ? bh_cms_hex_color($settings['bg_color'], '#f5f5f5') : '#f5f5f5') ?>">
                </div>
                <div class="adm-field adm-field-full">
                    <label><?= htmlspecialchars(bh_cms_admin_label('bg_image', $ta)) ?></label>
                    <input type="url" name="bg_image" value="<?= htmlspecialchars($settings['bg_image'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>
    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(bh_cms_admin_label('save', $ta)) ?></button>
    </div>
</form>