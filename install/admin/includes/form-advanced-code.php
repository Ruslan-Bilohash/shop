<?php
/** Custom head HTML + footer JS — included from form-advanced.php */
/** @var array $settings @var array $ta @var array $sections */
?>
    <div class="adm-card adm-settings-section" id="advanced-custom-code">
        <div class="adm-card-head adm-card-head--compact">
            <h2><i class="fas fa-code"></i> <?= htmlspecialchars($sections['advanced-custom-code'] ?? ($ta['code_editor_custom_section'] ?? 'Custom HTML & JS')) ?></h2>
        </div>
        <div class="adm-card-body padded-compact">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($ta['code_editor_custom_help'] ?? 'Injected on every storefront page — verification tags, analytics, custom scripts.') ?></p>
            <div class="adm-field adm-field--wide">
                <label for="shAdvCustomHead"><?= htmlspecialchars(sh_settings_admin_label('custom_head_html', $ta)) ?></label>
                <div class="adm-cm-wrap">
                    <textarea name="custom_head_html" rows="8" class="adm-code-input adm-code-mirror" data-mode="htmlmixed" id="shAdvCustomHead"><?= htmlspecialchars($settings['custom_head_html'] ?? '') ?></textarea>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('custom_head_html_hint', $ta)) ?></p>
            </div>
            <div class="adm-field adm-field--wide">
                <label for="shAdvCustomFooter"><?= htmlspecialchars(sh_settings_admin_label('custom_footer_js', $ta)) ?></label>
                <div class="adm-cm-wrap">
                    <textarea name="custom_footer_js" rows="8" class="adm-code-input adm-code-mirror" data-mode="javascript" id="shAdvCustomFooter"><?= htmlspecialchars($settings['custom_footer_js'] ?? '') ?></textarea>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('custom_footer_js_hint', $ta)) ?></p>
            </div>
        </div>
    </div>