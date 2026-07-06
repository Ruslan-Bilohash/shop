<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/invoice-settings.php';
require_once __DIR__ . '/admin-field-help.php';

$tab = 'invoice';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_invoice_merge_settings($settings);
$lang = $lang ?? 'en';
?>
<form method="post" class="adm-settings-form" id="shInvoiceForm">
    <div class="adm-card adm-settings-section" id="invoice-enable">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-invoice"></i> <?= htmlspecialchars($sections['invoice-enable'] ?? sh_settings_admin_label('invoice_section_enable', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('invoice_intro', $ta)) ?></p>
            <label class="adm-toggle">
                <input type="checkbox" name="invoice_enabled" value="1" <?= !empty($settings['invoice_enabled']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('invoice_enabled', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="invoice_auto_send" value="1" <?= !empty($settings['invoice_auto_send']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('invoice_auto_send', $ta)) ?></span>
            </label>
            <?php sh_admin_render_field_hint($tab, 'invoice_auto_send', $ta); ?>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="invoice-company">
        <div class="adm-card-head">
            <h2><i class="fas fa-building"></i> <?= htmlspecialchars($sections['invoice-company'] ?? sh_settings_admin_label('invoice_section_company', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shInvCompanyName"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_name', $ta)) ?></label>
                    <input type="text" name="invoice_company_name" id="shInvCompanyName" value="<?= htmlspecialchars($settings['invoice_company_name'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvOrgNr"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_org_nr', $ta)) ?></label>
                    <input type="text" name="invoice_company_org_nr" id="shInvOrgNr" value="<?= htmlspecialchars($settings['invoice_company_org_nr'] ?? '') ?>" placeholder="<?= htmlspecialchars($settings['tax_business_id'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvVatNr"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_vat_nr', $ta)) ?></label>
                    <input type="text" name="invoice_company_vat_nr" id="shInvVatNr" value="<?= htmlspecialchars($settings['invoice_company_vat_nr'] ?? '') ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shInvAddress"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_address', $ta)) ?></label>
                    <input type="text" name="invoice_company_address" id="shInvAddress" value="<?= htmlspecialchars($settings['invoice_company_address'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvCity"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_city', $ta)) ?></label>
                    <input type="text" name="invoice_company_city" id="shInvCity" value="<?= htmlspecialchars($settings['invoice_company_city'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvPostal"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_postal', $ta)) ?></label>
                    <input type="text" name="invoice_company_postal" id="shInvPostal" value="<?= htmlspecialchars($settings['invoice_company_postal'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvCountry"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_country', $ta)) ?></label>
                    <input type="text" name="invoice_company_country" id="shInvCountry" value="<?= htmlspecialchars($settings['invoice_company_country'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvEmail"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_email', $ta)) ?></label>
                    <input type="email" name="invoice_company_email" id="shInvEmail" value="<?= htmlspecialchars($settings['invoice_company_email'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvPhone"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_phone', $ta)) ?></label>
                    <input type="text" name="invoice_company_phone" id="shInvPhone" value="<?= htmlspecialchars($settings['invoice_company_phone'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvBank"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_bank', $ta)) ?></label>
                    <input type="text" name="invoice_company_bank" id="shInvBank" value="<?= htmlspecialchars($settings['invoice_company_bank'] ?? '') ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shInvIban"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_iban', $ta)) ?></label>
                    <input type="text" name="invoice_company_iban" id="shInvIban" value="<?= htmlspecialchars($settings['invoice_company_iban'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvBic"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_bic', $ta)) ?></label>
                    <input type="text" name="invoice_company_bic" id="shInvBic" value="<?= htmlspecialchars($settings['invoice_company_bic'] ?? '') ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shInvLogo"><?= htmlspecialchars(sh_settings_admin_label('invoice_company_logo', $ta)) ?></label>
                    <input type="url" name="invoice_company_logo_url" id="shInvLogo" value="<?= htmlspecialchars($settings['invoice_company_logo_url'] ?? '') ?>" placeholder="https://…/logo.png">
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="invoice-numbering">
        <div class="adm-card-head">
            <h2><i class="fas fa-hashtag"></i> <?= htmlspecialchars($sections['invoice-numbering'] ?? sh_settings_admin_label('invoice_section_numbering', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field">
                    <label for="shInvPrefix"><?= htmlspecialchars(sh_settings_admin_label('invoice_prefix', $ta)) ?></label>
                    <input type="text" name="invoice_prefix" id="shInvPrefix" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'INV') ?>">
                </div>
                <div class="adm-field">
                    <label for="shInvNext"><?= htmlspecialchars(sh_settings_admin_label('invoice_next_number', $ta)) ?></label>
                    <input type="number" name="invoice_next_number" id="shInvNext" value="<?= (int) ($settings['invoice_next_number'] ?? 1001) ?>" min="1">
                </div>
                <div class="adm-field">
                    <label for="shInvDue"><?= htmlspecialchars(sh_settings_admin_label('invoice_due_days', $ta)) ?></label>
                    <input type="number" name="invoice_due_days" id="shInvDue" value="<?= (int) ($settings['invoice_due_days'] ?? 14) ?>" min="0" max="90">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shInvNotes"><?= htmlspecialchars(sh_settings_admin_label('invoice_notes', $ta)) ?></label>
                    <textarea name="invoice_notes" id="shInvNotes" rows="3"><?= htmlspecialchars($settings['invoice_notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="invoice-print">
        <div class="adm-card-head">
            <h2><i class="fas fa-print"></i> <?= htmlspecialchars($sections['invoice-print'] ?? sh_settings_admin_label('invoice_section_print', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('invoice_print_help', $ta)) ?></p>
            <div class="adm-field adm-field--wide">
                <span class="adm-field-label"><?= htmlspecialchars(sh_settings_admin_label('invoice_print_design', $ta)) ?></span>
                <div class="adm-inv-design-picker">
                    <?php sh_inv_render_design_picker('invoice_print_design', (string) ($settings['invoice_print_design'] ?? 'classic-blue'), $lang); ?>
                </div>
            </div>
            <div class="adm-form-grid">
                <div class="adm-field">
                    <label for="shInvFormat"><?= htmlspecialchars(sh_settings_admin_label('invoice_print_format', $ta)) ?></label>
                    <select name="invoice_print_format" id="shInvFormat">
                        <?php foreach (sh_inv_print_formats() as $fid => $meta): ?>
                        <option value="<?= htmlspecialchars($fid) ?>" <?= ($settings['invoice_print_format'] ?? 'a4') === $fid ? 'selected' : '' ?>>
                            <?= htmlspecialchars(sh_inv_localized_name($meta, $lang)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field">
                    <label for="shInvMargin"><?= htmlspecialchars(sh_settings_admin_label('invoice_print_margin', $ta)) ?></label>
                    <select name="invoice_print_margin" id="shInvMargin">
                        <?php foreach (sh_inv_print_margins() as $mid => $mn): ?>
                        <option value="<?= htmlspecialchars($mid) ?>" <?= ($settings['invoice_print_margin'] ?? '8mm') === $mid ? 'selected' : '' ?>>
                            <?= htmlspecialchars(sh_inv_localized_name(['name' => $mn], $lang)) ?>
                        </option>
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