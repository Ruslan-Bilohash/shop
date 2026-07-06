<?php /** @var array $settings @var array $ta */ ?>
<form method="post" class="adm-settings-form">
    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-shield-alt" style="color:var(--adm-accent);margin-right:8px"></i> <?= htmlspecialchars(bh_cms_admin_label('recaptcha_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(bh_cms_admin_label('recaptcha_help', $ta)) ?></p>
            <div class="adm-form-grid">
                <div class="adm-field adm-field-check adm-field-full">
                    <label><input type="checkbox" name="recaptcha_enabled" value="1" <?= !empty($settings['recaptcha_enabled']) ? 'checked' : '' ?>> <?= htmlspecialchars(bh_cms_admin_label('recaptcha_enabled', $ta)) ?></label>
                </div>
                <div class="adm-field adm-field-full">
                    <label><?= htmlspecialchars(bh_cms_admin_label('recaptcha_site_key', $ta)) ?></label>
                    <input type="text" name="recaptcha_site_key" value="<?= htmlspecialchars($settings['recaptcha_site_key'] ?? '') ?>" autocomplete="off" placeholder="6Lc...">
                </div>
                <div class="adm-field adm-field-full">
                    <label><?= htmlspecialchars(bh_cms_admin_label('recaptcha_secret_key', $ta)) ?></label>
                    <input type="password" name="recaptcha_secret_key" value="<?= htmlspecialchars($settings['recaptcha_secret_key'] ?? '') ?>" autocomplete="off" placeholder="6Lc...">
                </div>
            </div>
        </div>
    </div>
    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(bh_cms_admin_label('save', $ta)) ?></button>
    </div>
</form>