<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/nova-poshta.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'nova_poshta';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);
$np = sh_nova_poshta_settings($settings);
$apiKeySet = trim((string) ($settings['nova_poshta_api_key'] ?? '')) !== '';
?>
<form method="post" class="adm-settings-form" id="shNovaPoshtaForm"
      data-lookup-url="<?= htmlspecialchars(sh_admin_url('api/nova-poshta-lookup.php')) ?>"
      data-test-url="<?= htmlspecialchars(sh_admin_url('api/nova-poshta-test.php')) ?>"
      data-city-ref="<?= htmlspecialchars($np['sender_city_ref']) ?>"
      data-warehouse-ref="<?= htmlspecialchars($np['sender_warehouse_ref']) ?>">
    <div class="adm-card adm-settings-section" id="nova-poshta-main">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-warehouse"></i> <?= htmlspecialchars($sections['nova-poshta-main'] ?? sh_settings_admin_label('nova_poshta_section', $ta)) ?></h2>
            <div class="adm-inline-actions">
                <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" id="shNpTestBtn">
                    <i class="fas fa-plug"></i> <?= htmlspecialchars(sh_settings_admin_label('nova_poshta_test_btn', $ta)) ?>
                </button>
                <span id="shNpTestStatus" class="adm-ai-status" hidden></span>
            </div>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'nova_poshta_enabled', 'label' => sh_settings_admin_label('nova_poshta_enabled', $ta), 'checked' => !empty($settings['nova_poshta_enabled'])],
                    ['name' => 'nova_poshta_track_enabled', 'label' => sh_settings_admin_label('nova_poshta_track_enabled', $ta), 'checked' => !empty($settings['nova_poshta_track_enabled'])],
                    ['name' => 'nova_poshta_checkout_enabled', 'label' => sh_settings_admin_label('nova_poshta_checkout_enabled', $ta), 'checked' => !empty($settings['nova_poshta_checkout_enabled'])],
                    ['name' => 'nova_poshta_demo_mode', 'label' => sh_settings_admin_label('nova_poshta_demo_mode', $ta), 'checked' => !empty($settings['nova_poshta_demo_mode'])],
                ],
                'warehouse'
            ); ?>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_api_key', $ta)) ?></label>
                    <input type="password" name="nova_poshta_api_key" autocomplete="new-password"
                           placeholder="<?= htmlspecialchars($apiKeySet ? sh_settings_admin_label('secret_keep', $ta) : 'API key from novaposhta.ua') ?>">
                    <?php sh_admin_render_field_hint($tab, 'nova_poshta_api_key', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_sender_phone', $ta)) ?></label>
                    <input type="tel" name="nova_poshta_sender_phone" value="<?= htmlspecialchars($settings['nova_poshta_sender_phone'] ?? '') ?>" placeholder="+380…">
                    <?php sh_admin_render_field_hint($tab, 'nova_poshta_sender_phone', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_default_weight', $ta)) ?></label>
                    <input type="number" name="nova_poshta_default_weight_kg" min="0.1" max="30" step="0.1"
                           value="<?= htmlspecialchars((string) ($settings['nova_poshta_default_weight_kg'] ?? 1)) ?>">
                </div>
            </div>
            <p class="adm-inline-actions">
                <a href="<?= sh_url('track-np.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('nova_poshta_track_page', $ta)) ?>
                </a>
            </p>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="nova-poshta-sender">
        <div class="adm-card-head">
            <h2><i class="fas fa-building"></i> <?= htmlspecialchars($sections['nova-poshta-sender'] ?? sh_settings_admin_label('nova_poshta_sender_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_sender_help', $ta)) ?></p>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shNpCitySearch"><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_city_search', $ta)) ?></label>
                    <input type="search" id="shNpCitySearch" class="adm-input-lg"
                           value="<?= htmlspecialchars($settings['nova_poshta_sender_city_name'] ?? '') ?>"
                           placeholder="<?= htmlspecialchars(sh_settings_admin_label('nova_poshta_city_ph', $ta)) ?>" autocomplete="off">
                    <input type="hidden" name="nova_poshta_sender_city_ref" id="shNpCityRef" value="<?= htmlspecialchars($settings['nova_poshta_sender_city_ref'] ?? '') ?>">
                    <input type="hidden" name="nova_poshta_sender_city_name" id="shNpCityName" value="<?= htmlspecialchars($settings['nova_poshta_sender_city_name'] ?? '') ?>">
                    <ul class="adm-np-suggest" id="shNpCitySuggest" hidden></ul>
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shNpWarehouseSelect"><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_warehouse', $ta)) ?></label>
                    <select id="shNpWarehouseSelect" disabled>
                        <option value=""><?= htmlspecialchars(sh_settings_admin_label('nova_poshta_warehouse_pick', $ta)) ?></option>
                    </select>
                    <input type="hidden" name="nova_poshta_sender_warehouse_ref" id="shNpWarehouseRef" value="<?= htmlspecialchars($settings['nova_poshta_sender_warehouse_ref'] ?? '') ?>">
                    <input type="hidden" name="nova_poshta_sender_warehouse_name" id="shNpWarehouseName" value="<?= htmlspecialchars($settings['nova_poshta_sender_warehouse_name'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>