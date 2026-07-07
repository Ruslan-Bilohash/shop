<?php /** @var array $settings @var array $ta */ ?>
<form method="post" class="adm-settings-form">
    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-robot" style="color:var(--adm-accent);margin-right:8px"></i> <?= htmlspecialchars(bh_cms_admin_label('chat_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(bh_cms_admin_label('chat_help', $ta)) ?></p>
            <div class="adm-form-grid">
                <div class="adm-field adm-field-check adm-field-full">
                    <label><input type="checkbox" name="chat_enabled" value="1" <?= !empty($settings['chat_enabled']) ? 'checked' : '' ?>> <?= htmlspecialchars(bh_cms_admin_label('chat_enabled', $ta)) ?></label>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(bh_cms_admin_label('chat_provider', $ta)) ?></label>
                    <select name="chat_provider">
                        <option value="none" <?= ($settings['chat_provider'] ?? '') === 'none' ? 'selected' : '' ?>><?= htmlspecialchars(bh_cms_admin_label('chat_provider_none', $ta)) ?></option>
                        <option value="grok" <?= ($settings['chat_provider'] ?? '') === 'grok' ? 'selected' : '' ?>><?= htmlspecialchars(bh_cms_admin_label('chat_provider_grok', $ta)) ?></option>
                        <option value="gpt" <?= ($settings['chat_provider'] ?? '') === 'gpt' ? 'selected' : '' ?>><?= htmlspecialchars(bh_cms_admin_label('chat_provider_gpt', $ta)) ?></option>
                    </select>
                </div>
                <div class="adm-field adm-field-full">
                    <label><?= htmlspecialchars(bh_cms_admin_label('chat_api_key', $ta)) ?></label>
                    <input type="password" name="chat_api_key" value="<?= htmlspecialchars($settings['chat_api_key'] ?? '') ?>" autocomplete="off" placeholder="xai-... or sk-...">
                    <small class="adm-field-hint"><?= htmlspecialchars(bh_cms_admin_label('chat_api_key_help', $ta)) ?></small>
                </div>
                <div class="adm-field adm-field-full">
                    <label><?= htmlspecialchars(bh_cms_admin_label('chat_instructions', $ta)) ?></label>
                    <textarea name="chat_instructions" rows="6"><?= htmlspecialchars($settings['chat_instructions'] ?? '') ?></textarea>
                    <small class="adm-field-hint"><?= htmlspecialchars(bh_cms_admin_label('chat_instructions_help', $ta)) ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(bh_cms_admin_label('save', $ta)) ?></button>
    </div>
</form>