<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/store-settings.php';
sh_admin_require();

$admin_page = 'code-editor';
$page_title = $ta['code_editor'] ?? 'Code editor';
$flash = '';
$settings = sh_load_settings();
$settings = sh_merge_store_settings($settings);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = sh_advanced_settings_apply_post($_POST, $settings);
    $saved = sh_save_settings($settings);
    $flash = $saved ? 'success' : 'error';
    $settings = sh_load_settings();
    $settings = sh_merge_store_settings($settings);
}

$admin_flash = $flash !== '' ? $flash : null;

require __DIR__ . '/includes/layout.php';
?>

<p class="adm-lead adm-lead-compact"><?= htmlspecialchars($ta['code_editor_lead'] ?? '') ?></p>

<form method="post" class="adm-settings-form" id="shCodeEditorForm">
    <div class="adm-card adm-settings-section">
        <div class="adm-card-head">
            <h2><i class="fas fa-code"></i> <?= htmlspecialchars($ta['code_editor_head_section'] ?? sh_settings_admin_label('custom_head_html', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('custom_head_html', $ta)) ?></label>
                <div class="adm-cm-wrap">
                    <textarea name="custom_head_html" rows="10" class="adm-code-input adm-code-mirror" data-mode="htmlmixed" id="shCodeHead"><?= htmlspecialchars($settings['custom_head_html'] ?? '') ?></textarea>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('custom_head_html_hint', $ta)) ?></p>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-code"></i> <?= htmlspecialchars($ta['code_editor_footer_section'] ?? sh_settings_admin_label('custom_footer_js', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('custom_footer_js', $ta)) ?></label>
                <div class="adm-cm-wrap">
                    <textarea name="custom_footer_js" rows="10" class="adm-code-input adm-code-mirror" data-mode="javascript" id="shCodeFooter"><?= htmlspecialchars($settings['custom_footer_js'] ?? '') ?></textarea>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('custom_footer_js_hint', $ta)) ?></p>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>

<?php require __DIR__ . '/includes/layout-end.php';