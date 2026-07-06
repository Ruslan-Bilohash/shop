<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/tax-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';

$tab = 'taxes';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_tax_merge_settings($settings);
$catalog = sh_tax_country_catalog();
$country = (string) ($settings['tax_country'] ?? 'NO');
$rate = (float) ($settings['tax_rate'] ?? 0);
$preview = sh_tax_breakdown(10000, $settings, $lang ?? 'en');
?>
<form method="post" class="adm-settings-form" id="shTaxForm">
    <div class="adm-card adm-settings-section" id="taxes-enable">
        <div class="adm-card-head">
            <h2><i class="fas fa-percent"></i> <?= htmlspecialchars($sections['taxes-enable'] ?? sh_settings_admin_label('tax_section_enable', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('tax_intro', $ta)) ?></p>
            <?php sh_admin_toggle_section('', [
                ['name' => 'tax_enabled', 'label' => sh_settings_admin_label('tax_enabled', $ta), 'checked' => !empty($settings['tax_enabled'])],
                ['name' => 'tax_show_in_catalog', 'label' => sh_settings_admin_label('tax_show_in_catalog', $ta), 'checked' => !empty($settings['tax_show_in_catalog'])],
                ['name' => 'tax_show_breakdown', 'label' => sh_settings_admin_label('tax_show_breakdown', $ta), 'checked' => !empty($settings['tax_show_breakdown'])],
            ], 'receipt'); ?>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="taxes-country">
        <div class="adm-card-head">
            <h2><i class="fas fa-flag"></i> <?= htmlspecialchars($sections['taxes-country'] ?? sh_settings_admin_label('tax_section_country', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shTaxCountry"><?= htmlspecialchars(sh_settings_admin_label('tax_country', $ta)) ?></label>
                    <select name="tax_country" id="shTaxCountry">
                        <?php foreach ($catalog as $code => $meta): ?>
                        <option value="<?= htmlspecialchars($code) ?>"
                                data-rate="<?= htmlspecialchars((string) $meta['rate']) ?>"
                                data-currency="<?= htmlspecialchars($meta['currency']) ?>"
                                <?= $country === $code ? 'selected' : '' ?>>
                            <?= htmlspecialchars($meta['country']) ?> (<?= htmlspecialchars($code) ?>) — <?= htmlspecialchars((string) $meta['rate']) ?>%
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php sh_admin_render_field_hint($tab, 'tax_country', $ta); ?>
                </div>
                <div class="adm-field">
                    <label for="shTaxRate"><?= htmlspecialchars(sh_settings_admin_label('tax_rate', $ta)) ?></label>
                    <input type="number" name="tax_rate" id="shTaxRate" value="<?= htmlspecialchars((string) $rate) ?>" min="0" max="100" step="0.01">
                    <?php sh_admin_render_field_hint($tab, 'tax_rate', $ta); ?>
                </div>
                <div class="adm-field">
                    <label for="shTaxMode"><?= htmlspecialchars(sh_settings_admin_label('tax_mode', $ta)) ?></label>
                    <select name="tax_mode" id="shTaxMode">
                        <option value="inclusive" <?= ($settings['tax_mode'] ?? '') === 'inclusive' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('tax_mode_inclusive', $ta)) ?></option>
                        <option value="exclusive" <?= ($settings['tax_mode'] ?? '') === 'exclusive' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('tax_mode_exclusive', $ta)) ?></option>
                    </select>
                    <?php sh_admin_render_field_hint($tab, 'tax_mode', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shTaxLabel"><?= htmlspecialchars(sh_settings_admin_label('tax_custom_label', $ta)) ?></label>
                    <input type="text" name="tax_custom_label" id="shTaxLabel" value="<?= htmlspecialchars($settings['tax_custom_label'] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_tax_label($settings, $lang ?? 'en')) ?>">
                    <?php sh_admin_render_field_hint($tab, 'tax_custom_label', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shTaxBusinessId"><?= htmlspecialchars(sh_settings_admin_label('tax_business_id', $ta)) ?></label>
                    <input type="text" name="tax_business_id" id="shTaxBusinessId" value="<?= htmlspecialchars($settings['tax_business_id'] ?? '') ?>" placeholder="999 999 999">
                    <?php sh_admin_render_field_hint($tab, 'tax_business_id', $ta); ?>
                </div>
            </div>
            <div class="adm-tax-preview" id="shTaxPreview" data-label-net="<?= htmlspecialchars(sh_settings_admin_label('tax_preview_net', $ta)) ?>" data-label-tax="<?= htmlspecialchars(sh_settings_admin_label('tax_preview_tax', $ta)) ?>" data-label-total="<?= htmlspecialchars(sh_settings_admin_label('tax_preview_total', $ta)) ?>">
                <strong><?= htmlspecialchars(sh_settings_admin_label('tax_preview_title', $ta)) ?></strong>
                <p class="adm-help adm-tax-preview-lines">
                    <?= htmlspecialchars(sh_settings_admin_label('tax_preview_example', $ta)) ?>:
                    <span id="shTaxPreviewText">
                        <?= htmlspecialchars(
                            sh_format_price($preview['net'], $settings)
                            . ' + '
                            . sh_format_price($preview['tax'], $settings)
                            . ' = '
                            . sh_format_price($preview['total'], $settings)
                        ) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>