<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/smtp-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'smtp';
$sections = sh_admin_settings_sections($tab, $ta);
$smtp = sh_smtp_merge_settings($settings);
$passSet = trim((string) ($smtp['smtp_password'] ?? '')) !== '';
?>
<form method="post" class="adm-settings-form" id="sh-smtp-settings-form">
    <?php sh_admin_section_open($tab, 'smtp-connection', $sections['smtp-connection'] ?? sh_settings_admin_label('smtp_section_connection', $ta), 'envelope', $ta); ?>
    <div class="adm-form-grid adm-form-grid--settings">
        <?php sh_admin_toggle_section(
            sh_settings_admin_label('smtp_section', $ta),
            [
                ['name' => 'smtp_enabled', 'label' => sh_settings_admin_label('smtp_enabled', $ta), 'checked' => !empty($smtp['smtp_enabled'])],
            ],
            'envelope'
        ); ?>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_host', $ta)) ?></label>
            <input type="text" name="smtp_host" value="<?= htmlspecialchars((string) ($smtp['smtp_host'] ?? '')) ?>" placeholder="smtp.hostinger.com">
            <?php sh_admin_render_field_hint($tab, 'smtp_host', $ta); ?>
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_port', $ta)) ?></label>
            <input type="number" name="smtp_port" value="<?= (int) ($smtp['smtp_port'] ?? 465) ?>" min="1" max="65535">
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_encryption', $ta)) ?></label>
            <select name="smtp_encryption">
                <?php foreach (['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None'] as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($smtp['smtp_encryption'] ?? 'ssl') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_username', $ta)) ?></label>
            <input type="text" name="smtp_username" value="<?= htmlspecialchars((string) ($smtp['smtp_username'] ?? '')) ?>" autocomplete="off">
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_password', $ta)) ?></label>
            <input type="password" name="smtp_password" value="" placeholder="<?= $passSet ? '••••••••' : '' ?>" autocomplete="new-password">
            <?php sh_admin_render_field_hint($tab, 'smtp_password', $ta); ?>
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_from_email', $ta)) ?></label>
            <input type="email" name="smtp_from_email" value="<?= htmlspecialchars((string) ($smtp['smtp_from_email'] ?? '')) ?>">
        </div>
        <div class="adm-field">
            <label><?= htmlspecialchars(sh_settings_admin_label('smtp_from_name', $ta)) ?></label>
            <input type="text" name="smtp_from_name" value="<?= htmlspecialchars((string) ($smtp['smtp_from_name'] ?? 'Shop CMS')) ?>">
        </div>
    </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'newsletter', $sections['newsletter'] ?? sh_settings_admin_label('smtp_section_newsletter', $ta), 'paper-plane', $ta); ?>
    <div class="adm-form-grid adm-form-grid--settings">
        <?php sh_admin_toggle_section(
            sh_settings_admin_label('newsletter_section', $ta),
            [
                ['name' => 'newsletter_enabled', 'label' => sh_settings_admin_label('newsletter_enabled', $ta), 'checked' => !empty($smtp['newsletter_enabled'])],
            ],
            'paper-plane'
        ); ?>
        <div class="adm-field adm-field--wide">
            <label><?= htmlspecialchars(sh_settings_admin_label('newsletter_notify_email', $ta)) ?></label>
            <input type="email" name="newsletter_notify_email" value="<?= htmlspecialchars((string) ($smtp['newsletter_notify_email'] ?? '')) ?>" placeholder="admin@example.com">
            <?php sh_admin_render_field_hint($tab, 'newsletter_notify_email', $ta); ?>
        </div>
        <div class="adm-field adm-field--wide">
            <label><?= htmlspecialchars(sh_settings_admin_label('newsletter_welcome_subject', $ta)) ?></label>
            <input type="text" name="newsletter_welcome_subject" value="<?= htmlspecialchars((string) ($smtp['newsletter_welcome_subject'] ?? '')) ?>">
        </div>
        <div class="adm-field adm-field--wide">
            <label><?= htmlspecialchars(sh_settings_admin_label('newsletter_welcome_body', $ta)) ?></label>
            <textarea name="newsletter_welcome_body" rows="3" class="adm-textarea"><?= htmlspecialchars((string) ($smtp['newsletter_welcome_body'] ?? '')) ?></textarea>
        </div>
        <div class="adm-field adm-field--wide">
            <a href="<?= htmlspecialchars(sh_admin_url('subscribers.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-users"></i> <?= htmlspecialchars(sh_settings_admin_label('newsletter_view_subscribers', $ta)) ?>
            </a>
        </div>
    </div>
    <?php sh_admin_section_close(); ?>
</form>