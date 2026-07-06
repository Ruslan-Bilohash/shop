<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'posten';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);
?>
<form method="post" class="adm-settings-form">
    <div class="adm-card adm-settings-section" id="posten-main">
        <div class="adm-card-head">
            <h2><i class="fas fa-truck"></i> <?= htmlspecialchars($sections['posten-main'] ?? sh_settings_admin_label('store_posten_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('store_posten_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'posten_enabled', 'label' => sh_settings_admin_label('posten_enabled', $ta), 'checked' => !empty($settings['posten_enabled'])],
                    ['name' => 'posten_demo_mode', 'label' => sh_settings_admin_label('posten_demo_mode', $ta), 'checked' => !empty($settings['posten_demo_mode'])],
                ],
                'truck'
            ); ?>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('posten_client_id', $ta)) ?></label>
                    <input type="text" name="posten_client_id" value="<?= htmlspecialchars($settings['posten_client_id'] ?? '') ?>" autocomplete="off">
                    <?php sh_admin_render_field_hint($tab, 'posten_client_id', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('posten_api_key', $ta)) ?></label>
                    <input type="password" name="posten_api_key" placeholder="<?= htmlspecialchars(sh_settings_admin_label('secret_keep', $ta)) ?>" autocomplete="new-password">
                    <?php sh_admin_render_field_hint($tab, 'posten_api_key', $ta); ?>
                </div>
            </div>
            <p><a href="<?= sh_url('track.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('posten_track_page', $ta)) ?></a></p>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>