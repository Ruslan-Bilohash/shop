<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/shop-mode.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'store';
$storeSections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_store_settings($settings);
$mode = sh_merge_shop_mode_settings($settings);
$shopOpen = empty($mode['shop_maintenance_enabled']);
$currencyGroups = sh_currency_preset_groups();
$currencyPresets = sh_currency_presets();
$currentCurrency = strtoupper((string) ($settings['site_currency'] ?? 'NOK'));
$presetMatch = isset($currencyPresets[$currentCurrency]);
?>
<form method="post" class="adm-settings-form" id="shStoreForm">
    <div class="adm-card adm-store-status-card adm-settings-section" id="store-status">
        <div class="adm-card-body padded">
            <div class="adm-store-status">
                <div class="adm-store-status-info">
                    <span class="adm-store-status-badge <?= $shopOpen ? 'is-open' : 'is-closed' ?>"
                          data-open-label="<?= htmlspecialchars(sh_settings_admin_label('store_status_open', $ta)) ?>"
                          data-closed-label="<?= htmlspecialchars(sh_settings_admin_label('store_status_closed', $ta)) ?>">
                        <i class="fas <?= $shopOpen ? 'fa-store' : 'fa-hard-hat' ?>"></i>
                        <span class="adm-store-status-label"><?= htmlspecialchars(sh_settings_admin_label($shopOpen ? 'store_status_open' : 'store_status_closed', $ta)) ?></span>
                    </span>
                    <p class="adm-help adm-store-status-text"
                       data-open="<?= htmlspecialchars(sh_settings_admin_label('store_status_open_help', $ta)) ?>"
                       data-closed="<?= htmlspecialchars(sh_settings_admin_label('store_status_closed_help', $ta)) ?>">
                        <?= htmlspecialchars(sh_settings_admin_label($shopOpen ? 'store_status_open_help' : 'store_status_closed_help', $ta)) ?>
                    </p>
                </div>
                <div class="adm-store-status-actions">
                    <label class="adm-toggle adm-toggle--lg" title="<?= htmlspecialchars(sh_settings_admin_label('store_open_toggle', $ta)) ?>">
                        <input type="checkbox" name="shop_open" value="1" id="shShopOpenToggle" <?= $shopOpen ? 'checked' : '' ?>>
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('store_open_toggle', $ta)) ?></span>
                    </label>
                    <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                        <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('store_view_shopfront', $ta)) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="store-locale">
        <div class="adm-card-head"><h2><i class="fas fa-globe"></i> <?= htmlspecialchars($storeSections['store-locale'] ?? sh_settings_admin_label('store_locale_section', $ta)) ?></h2></div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shDefaultLang"><?= htmlspecialchars(sh_settings_admin_label('site_default_lang', $ta)) ?></label>
                    <select name="site_default_lang" id="shDefaultLang">
                        <?php
                        $defaultLang = function_exists('sh_site_default_lang') ? sh_site_default_lang($settings) : 'no';
                        foreach (sh_active_langs($settings) as $code => $info):
                        ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $defaultLang === $code ? 'selected' : '' ?>>
                            <?= ($info['flag'] ?? '🌐') . ' ' . htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label'] ?? strtoupper($code)) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('site_default_lang_hint', $ta)) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="store-developer">
        <div class="adm-card-head"><h2><i class="fas fa-bug"></i> <?= htmlspecialchars($storeSections['store-developer'] ?? sh_settings_admin_label('store_developer_section', $ta)) ?></h2></div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('store_developer_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'shop_dev_errors', 'label' => sh_settings_admin_label('shop_dev_errors', $ta), 'checked' => !empty($mode['shop_dev_errors'])],
                ],
                'bug'
            ); ?>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="store-currency">
        <div class="adm-card-head"><h2><i class="fas fa-coins"></i> <?= htmlspecialchars($storeSections['store-currency'] ?? sh_settings_admin_label('store_currency_section', $ta)) ?></h2></div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shCurrencyPreset"><?= htmlspecialchars(sh_settings_admin_label('currency_preset', $ta)) ?></label>
                    <select id="shCurrencyPreset" data-custom-label="<?= htmlspecialchars(sh_settings_admin_label('currency_preset_custom', $ta)) ?>">
                        <?php foreach ($currencyGroups as $group): ?>
                        <optgroup label="<?= htmlspecialchars(sh_settings_admin_label($group['label_key'], $ta)) ?>">
                            <?php foreach ($group['presets'] as $code => $preset): ?>
                            <option value="<?= htmlspecialchars($code) ?>"
                                    data-symbol="<?= htmlspecialchars($preset['symbol']) ?>"
                                    data-decimals="<?= (int) $preset['decimals'] ?>"
                                    <?= $currentCurrency === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($code) ?> — <?= htmlspecialchars($preset['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                        <option value="custom" <?= $presetMatch ? '' : 'selected' ?>><?= htmlspecialchars(sh_settings_admin_label('currency_preset_custom', $ta)) ?></option>
                    </select>
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('currency_preset_hint', $ta)) ?></small>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('site_currency', $ta)) ?></label>
                    <input type="text" name="site_currency" id="shSiteCurrency" value="<?= htmlspecialchars($settings['site_currency'] ?? 'NOK') ?>" maxlength="3" placeholder="NOK">
                    <?php sh_admin_render_field_hint($tab, 'site_currency', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('currency_symbol', $ta)) ?></label>
                    <input type="text" name="currency_symbol" id="shCurrencySymbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? 'kr') ?>" placeholder="kr">
                    <?php sh_admin_render_field_hint($tab, 'currency_symbol', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('currency_decimals', $ta)) ?></label>
                    <select name="currency_decimals" id="shCurrencyDecimals">
                        <?php foreach ([0, 1, 2] as $d): ?>
                        <option value="<?= $d ?>" <?= (int)($settings['currency_decimals'] ?? 0) === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>