<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/telegram-notify.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';

$tab = 'telegram';
$sections = sh_admin_settings_sections($tab, $ta);
$tg = sh_telegram_merge_settings($settings);
$tokenSet = trim((string) ($tg['telegram_bot_token'] ?? '')) !== '';
?>
<form method="post" class="adm-settings-form" id="shTelegramForm">
    <div class="adm-card adm-settings-section" id="telegram-enable">
        <div class="adm-card-head">
            <h2><i class="fab fa-telegram"></i> <?= htmlspecialchars($sections['telegram-enable'] ?? sh_settings_admin_label('telegram_section_enable', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('telegram_intro', $ta)) ?></p>
            <?php sh_admin_toggle_section('', [
                ['name' => 'telegram_enabled', 'label' => sh_settings_admin_label('telegram_enabled', $ta), 'checked' => !empty($tg['telegram_enabled'])],
                ['name' => 'telegram_notify_orders', 'label' => sh_settings_admin_label('telegram_notify_orders', $ta), 'checked' => !empty($tg['telegram_notify_orders'])],
                ['name' => 'telegram_notify_quick_buy', 'label' => sh_settings_admin_label('telegram_notify_quick_buy', $ta), 'checked' => !empty($tg['telegram_notify_quick_buy'])],
            ], 'bell'); ?>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="telegram-bot">
        <div class="adm-card-head">
            <h2><i class="fas fa-robot"></i> <?= htmlspecialchars($sections['telegram-bot'] ?? sh_settings_admin_label('telegram_section_bot', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shTgToken"><?= htmlspecialchars(sh_settings_admin_label('telegram_bot_token', $ta)) ?></label>
                    <?php if ($tokenSet): ?>
                    <p class="adm-help adm-help-compact"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('secret_saved', $ta)) ?></p>
                    <?php endif; ?>
                    <div class="adm-secret-field">
                        <input type="password" name="telegram_bot_token" id="shTgToken" class="adm-secret-input"
                               value="" autocomplete="new-password"
                               placeholder="<?= htmlspecialchars($tokenSet ? sh_settings_admin_label('secret_keep', $ta) : '123456789:AA...') ?>">
                        <button type="button" class="adm-btn adm-btn-outline adm-btn-sm adm-secret-toggle" data-target="shTgToken" aria-label="Toggle visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php sh_admin_render_field_hint($tab, 'telegram_bot_token', $ta); ?>
                </div>
                <div class="adm-field">
                    <label for="shTgChatId"><?= htmlspecialchars(sh_settings_admin_label('telegram_chat_id', $ta)) ?></label>
                    <input type="text" name="telegram_chat_id" id="shTgChatId" value="<?= htmlspecialchars($tg['telegram_chat_id'] ?? '') ?>" placeholder="-1001234567890">
                    <?php sh_admin_render_field_hint($tab, 'telegram_chat_id', $ta); ?>
                </div>
                <div class="adm-field">
                    <label for="shTgParse"><?= htmlspecialchars(sh_settings_admin_label('telegram_parse_mode', $ta)) ?></label>
                    <select name="telegram_parse_mode" id="shTgParse">
                        <?php foreach (['HTML' => 'HTML', 'Markdown' => 'Markdown', 'MarkdownV2' => 'MarkdownV2'] as $val => $lbl): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= ($tg['telegram_parse_mode'] ?? 'HTML') === $val ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <p class="adm-help adm-help-compact">
                <i class="fas fa-info-circle"></i>
                <?= htmlspecialchars(sh_settings_admin_label('telegram_setup_steps', $ta)) ?>
            </p>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
        <button type="submit" name="telegram_test" value="1" class="adm-btn adm-btn-outline" formnovalidate>
            <i class="fab fa-telegram"></i> <?= htmlspecialchars(sh_settings_admin_label('telegram_test_btn', $ta)) ?>
        </button>
    </div>
</form>