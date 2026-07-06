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
$aiKeySet = trim((string) ($ai['ai_api_key'] ?? '')) !== '';
$contextModels = [
    'default' => ['key' => 'ai_model', 'label' => sh_settings_admin_label('ai_model_default', $ta)],
    'product' => ['key' => 'ai_model_product', 'label' => sh_settings_admin_label('ai_model_product', $ta)],
    'chat'    => ['key' => 'ai_model_chat', 'label' => sh_settings_admin_label('ai_model_chat', $ta)],
    'news'    => ['key' => 'ai_model_news', 'label' => sh_settings_admin_label('ai_model_news', $ta)],
    'seo'     => ['key' => 'ai_model_seo', 'label' => sh_settings_admin_label('ai_model_seo', $ta)],
];
?>
<form method="post" class="adm-settings-form" id="sh-ai-settings-form">
    <?php sh_admin_section_open($tab, 'ai-connection', $sections['ai-connection'] ?? sh_settings_admin_label('ai_section_connection', $ta), 'plug', $ta); ?>
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
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_api_base', $ta)) ?></label>
                    <input type="url" name="ai_api_base" id="sh-ai-api-base" value="<?= htmlspecialchars($resolved['api_base']) ?>"
                           placeholder="https://api.x.ai/v1" inputmode="url">
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('ai_api_base_hint', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_api_key', $ta)) ?></label>
                    <?php if ($aiKeySet): ?>
                    <p class="adm-help adm-help-compact"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('secret_saved', $ta)) ?></p>
                    <?php endif; ?>
                    <div class="adm-secret-field">
                        <input type="password" name="ai_api_key" id="shAiApiKey" class="adm-secret-input"
                               value=""
                               autocomplete="new-password" spellcheck="false"
                               placeholder="<?= htmlspecialchars($aiKeySet ? sh_settings_admin_label('secret_keep', $ta) : 'xai-... or sk-...') ?>">
                        <button type="button" class="adm-btn adm-btn-outline adm-btn-sm adm-secret-toggle" data-target="shAiApiKey" aria-label="Toggle visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
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
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'ai-models', $sections['ai-models'] ?? sh_settings_admin_label('ai_section_models', $ta), 'microchip', $ta); ?>
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('ai_model_context_hint', $ta)) ?></p>
            <div class="adm-form-grid adm-form-grid--settings">
                <?php foreach ($contextModels as $ctx => $meta):
                    $fieldKey = $meta['key'];
                    $stored = trim((string) ($ai[$fieldKey] ?? ''));
                    $displayModel = $stored !== '' ? $stored : ($ctx === 'default' ? $resolved['model'] : '');
                    ?>
                <div class="adm-field adm-field--wide sh-ai-model-field" data-context="<?= htmlspecialchars($ctx) ?>">
                    <label><?= htmlspecialchars($meta['label']) ?></label>
                    <select name="<?= htmlspecialchars($fieldKey) ?>_select" class="sh-ai-model-select" data-context="<?= htmlspecialchars($ctx) ?>"></select>
                    <input type="text" name="<?= htmlspecialchars($fieldKey) ?>" class="sh-ai-model-custom adm-input-model"
                           data-context="<?= htmlspecialchars($ctx) ?>"
                           value="<?= htmlspecialchars($displayModel) ?>"
                           placeholder="<?= htmlspecialchars($ctx === 'default'
                               ? sh_settings_admin_label('ai_model_custom', $ta)
                               : sh_settings_admin_label('ai_model_context_empty', $ta)) ?>">
                </div>
                <?php endforeach; ?>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'ai-prompts', $sections['ai-prompts'] ?? sh_settings_admin_label('ai_section_prompts', $ta), 'message-lines', $ta); ?>
            <div class="adm-form-grid adm-form-grid--settings">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_product', $ta)) ?></label>
                    <textarea name="ai_prompt_product" rows="6"><?= htmlspecialchars($ai['ai_prompt_product'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'ai_prompt_product', $ta); ?>
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_placeholders', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_news', $ta)) ?></label>
                    <textarea name="ai_prompt_news" rows="5"><?= htmlspecialchars($ai['ai_prompt_news'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'ai_prompt_news', $ta); ?>
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_news_placeholders', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_seo', $ta)) ?></label>
                    <textarea name="ai_prompt_seo" rows="5"><?= htmlspecialchars($ai['ai_prompt_seo'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'ai_prompt_seo', $ta); ?>
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('ai_prompt_seo_placeholders', $ta)) ?></small>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'ai-instructions', $sections['ai-instructions'] ?? sh_settings_admin_label('ai_section_instructions', $ta), 'list-ol', $ta); ?>
            <ol class="adm-guide-steps">
                <?php for ($i = 1; $i <= 6; $i++):
                    $stepKey = 'ai_instruction_' . $i;
                    $step = sh_settings_admin_label($stepKey, $ta);
                    if ($step === $stepKey) {
                        continue;
                    }
                    ?>
                <li><?= htmlspecialchars($step) ?></li>
                <?php endfor; ?>
            </ol>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>
<script>
window.SH_AI_PROVIDERS = <?= json_encode($providers, JSON_UNESCAPED_UNICODE) ?>;
</script>