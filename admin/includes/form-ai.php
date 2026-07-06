<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'ai';
$sections = sh_admin_settings_sections($tab, $ta);
$ai = sh_ai_settings($settings);
$resolved = sh_ai_resolve_config($ai);
$providers = sh_ai_providers();
?>
<form method="post" class="adm-settings-form" id="sh-ai-settings-form">
    <?php sh_admin_section_open($tab, 'ai-connection', $sections['ai-connection'] ?? sh_settings_admin_label('ai_section', $ta), 'wand-magic-sparkles', $ta); ?>
            <div class="adm-alert adm-alert-info adm-alert-compact">
                <i class="fas fa-info-circle"></i>
                <?= htmlspecialchars(sh_settings_admin_label('ai_independent_note', $ta)) ?>
            </div>
            <div class="adm-form-grid adm-form-grid--settings">
                <?php sh_admin_toggle_section(
                    sh_settings_admin_label('ai_section', $ta),
                    [
                        ['name' => 'ai_enabled', 'label' => sh_settings_admin_label('ai_enabled', $ta), 'checked' => !empty($ai['ai_enabled'])],
                    ],
                    'wand-magic-sparkles'
                ); ?>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_provider', $ta)) ?></label>
                    <select name="ai_provider" id="sh-ai-provider">
                        <?php foreach ($providers as $key => $preset): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= ($ai['ai_provider'] ?? 'grok') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($preset['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_model', $ta)) ?></label>
                    <select name="ai_model_select" id="sh-ai-model-select"></select>
                    <input type="text" name="ai_model" id="sh-ai-model-custom" class="adm-input-model"
                           value="<?= htmlspecialchars($resolved['model']) ?>"
                           placeholder="<?= htmlspecialchars(sh_settings_admin_label('ai_model_custom', $ta)) ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_api_base', $ta)) ?></label>
                    <input type="url" name="ai_api_base" id="sh-ai-api-base" value="<?= htmlspecialchars($resolved['api_base']) ?>"
                           placeholder="https://api.x.ai/v1" inputmode="url">
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('ai_api_base_hint', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_api_key', $ta)) ?></label>
                    <input type="password" name="ai_api_key" value="" autocomplete="new-password"
                           placeholder="<?= !empty($ai['ai_api_key']) ? '••••••••' : 'xai-... or sk-...' ?>">
                    <?php sh_admin_render_field_hint($tab, 'ai_api_key', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_source_lang', $ta)) ?></label>
                    <select name="ai_source_lang">
                        <?php foreach (sh_langs() as $code => $info): ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= ($ai['ai_source_lang'] ?? 'en') === $code ? 'selected' : '' ?>>
                            <?= htmlspecialchars($info['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php sh_admin_render_field_hint($tab, 'ai_source_lang', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_product', $ta)) ?></label>
                    <textarea name="ai_prompt_product" rows="6"><?= htmlspecialchars($ai['ai_prompt_product'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'ai_prompt_product', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'ai-usage', $sections['ai-usage'] ?? sh_settings_admin_label('ai_usage_title', $ta), 'list-check', $ta); ?>
            <ul class="adm-guide-steps">
                <li><?= htmlspecialchars(sh_settings_admin_label('ai_usage_product', $ta)) ?></li>
                <li><?= htmlspecialchars(sh_settings_admin_label('ai_usage_translate', $ta)) ?></li>
                <li><?= htmlspecialchars(sh_settings_admin_label('ai_usage_demo', $ta)) ?></li>
            </ul>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>
<script>
window.SH_AI_PROVIDERS = <?= json_encode($providers, JSON_UNESCAPED_UNICODE) ?>;
</script>