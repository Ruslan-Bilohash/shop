<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'chat';
$sections = sh_admin_settings_sections($tab, $ta);
$chatColor = trim((string) ($settings['chat_widget_color'] ?? ''));
$chatIcon = trim((string) ($settings['chat_widget_icon'] ?? 'comments')) ?: 'comments';
if ($chatColor === '') {
    $chatColor = bh_cms_hex_color($settings['color_primary'] ?? '#2563eb');
}
$providers = sh_ai_providers();
$chatModel = sh_chat_resolve_model($settings);
$chatProvider = (string) ($settings['chat_provider'] ?? 'grok');
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'chat-main', $sections['chat-main'] ?? sh_settings_admin_label('chat_section', $ta), 'comments', $ta); ?>
            <div class="adm-alert adm-alert-info adm-alert-compact">
                <i class="fas fa-info-circle"></i>
                <?= htmlspecialchars(sh_settings_admin_label('chat_independent_note', $ta)) ?>
            </div>
            <div class="adm-form-grid adm-form-grid--settings">
                <?php sh_admin_toggle_section(
                    sh_settings_admin_label('chat_section', $ta),
                    [
                        ['name' => 'chat_enabled', 'label' => sh_settings_admin_label('chat_enabled', $ta), 'checked' => !empty($settings['chat_enabled'])],
                    ],
                    'comments'
                ); ?>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('chat_provider', $ta)) ?></label>
                    <select name="chat_provider">
                        <option value="none" <?= ($settings['chat_provider'] ?? '') === 'none' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('chat_provider_none', $ta)) ?></option>
                        <option value="grok" <?= ($settings['chat_provider'] ?? '') === 'grok' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('chat_provider_grok', $ta)) ?></option>
                        <option value="gpt" <?= ($settings['chat_provider'] ?? '') === 'gpt' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('chat_provider_gpt', $ta)) ?></option>
                    </select>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('chat_api_key', $ta)) ?></label>
                    <input type="password" name="chat_api_key" value="<?= htmlspecialchars($settings['chat_api_key'] ?? '') ?>" autocomplete="off" placeholder="xai-... or sk-...">
                    <?php sh_admin_render_field_hint($tab, 'chat_api_key', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('chat_model', $ta)) ?></label>
                    <select name="chat_model_select" id="sh-chat-model-select"></select>
                    <input type="text" name="chat_model" id="sh-chat-model-custom" class="adm-input-model"
                           value="<?= htmlspecialchars(trim((string) ($settings['chat_model'] ?? ''))) ?>"
                           placeholder="<?= htmlspecialchars($chatModel !== '' ? $chatModel : sh_settings_admin_label('chat_model_empty', $ta)) ?>">
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('chat_model_hint', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('chat_instructions', $ta)) ?></label>
                    <textarea name="chat_instructions" rows="6"><?= htmlspecialchars($settings['chat_instructions'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'chat_instructions', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'chat-widget', $sections['chat-widget'] ?? sh_settings_admin_label('chat_widget_section', $ta), 'palette', $ta, sh_settings_admin_label('chat_widget_help', $ta)); ?>
            <div class="adm-alert adm-alert-warning adm-alert-compact">
                <i class="fas fa-flask"></i> <?= htmlspecialchars(sh_settings_admin_label('chat_widget_demo_note', $ta)) ?>
            </div>
            <div class="adm-form-grid adm-color-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('chat_widget_color', $ta)) ?></label>
                    <input type="color" name="chat_widget_color" value="<?= htmlspecialchars(bh_cms_hex_color($chatColor)) ?>">
                    <?php sh_admin_render_field_hint($tab, 'chat_widget_color', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <?php
                    $pickerPrefix = 'shChatIcon';
                    $inputName = 'chat_widget_icon';
                    $selectedIcon = $chatIcon;
                    $tp = [
                        'icon' => sh_settings_admin_label('chat_widget_icon', $ta),
                        'icon_pick' => sh_settings_admin_label('chat_icon_pick', $ta),
                        'icon_change' => sh_settings_admin_label('chat_icon_change', $ta),
                        'icon_hint_short' => sh_admin_field_hint($tab, 'chat_widget_icon', $ta),
                        'icon_search' => sh_settings_admin_label('icon_search', $ta),
                        'icon_loading' => sh_settings_admin_label('icon_loading', $ta),
                        'cancel' => sh_settings_admin_label('cancel', $ta),
                        'close' => sh_settings_admin_label('close', $ta),
                    ];
                    require __DIR__ . '/icon-picker-field.php';
                    ?>
                </div>
            </div>
            <div class="adm-chat-widget-preview">
                <span class="adm-chat-widget-preview-btn" id="shChatColorPreview" style="background:linear-gradient(135deg,<?= htmlspecialchars(bh_cms_hex_color($chatColor)) ?>,<?= htmlspecialchars(bh_cms_hex_color($chatColor)) ?>dd)">
                    <i class="fas fa-<?= htmlspecialchars($chatIcon) ?>"></i>
                </span>
                <span class="adm-muted-inline"><?= htmlspecialchars(sh_settings_admin_label('chat_widget_preview', $ta)) ?></span>
            </div>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>
<script>
window.SH_CHAT_PROVIDERS = <?= json_encode([
    'grok' => $providers['grok'] ?? ['models' => []],
    'gpt'  => $providers['openai'] ?? ['models' => []],
], JSON_UNESCAPED_UNICODE) ?>;
window.SH_CHAT_PROVIDER = <?= json_encode($chatProvider, JSON_UNESCAPED_UNICODE) ?>;
</script>