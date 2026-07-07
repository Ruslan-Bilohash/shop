<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/shop-mode.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'advanced';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);
$mode = sh_merge_shop_mode_settings($settings);
?>
<form method="post" class="adm-settings-form" id="shAdvancedForm">
    <div class="adm-card adm-card--dense adm-settings-section" id="advanced-maintenance">
        <div class="adm-card-head adm-card-head--compact">
            <h2><i class="fas fa-hard-hat"></i> <?= htmlspecialchars($sections['advanced-maintenance'] ?? sh_settings_admin_label('store_maintenance_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded-compact">
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'shop_maintenance_allow_admin', 'label' => sh_settings_admin_label('shop_maintenance_allow_admin', $ta), 'checked' => !empty($mode['shop_maintenance_allow_admin'])],
                    ['name' => 'cookie_consent_enabled', 'label' => sh_settings_admin_label('cookie_consent_enabled', $ta), 'checked' => !empty($mode['cookie_consent_enabled'])],
                ],
                'hard-hat'
            ); ?>
        </div>
    </div>

    <?php require __DIR__ . '/form-advanced-code.php'; ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>