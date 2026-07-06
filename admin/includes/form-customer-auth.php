<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/customer-auth.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'customer_auth';
$sections = sh_admin_settings_sections($tab, $ta);
$auth = sh_customer_auth_settings($settings);
$googleRedirect = sh_customer_google_redirect_uri();
$appleRedirect = sh_customer_apple_redirect_uri();
$appleKeySet = trim((string) ($auth['customer_apple_private_key'] ?? '')) !== '';
$demoMode = defined('SH_DEMO_MODE') && SH_DEMO_MODE;
$googleConfigured = sh_customer_google_configured($auth);
$appleConfigured = sh_customer_apple_configured($auth);
?>
<form method="post" class="adm-settings-form">
    <?php if ($demoMode): ?>
    <div class="adm-alert adm-alert-info adm-customer-auth-demo-banner">
        <i class="fas fa-flask"></i>
        <?= htmlspecialchars(sh_settings_admin_label('customer_auth_demo_banner', $ta)) ?>
    </div>
    <?php endif; ?>

    <div class="adm-card adm-settings-section" id="customer-auth-main">
        <div class="adm-card-head">
            <h2><i class="fas fa-user-lock"></i> <?= htmlspecialchars($sections['customer-auth-main'] ?? sh_settings_admin_label('store_customer_auth_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('store_customer_auth_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                sh_settings_admin_label('customer_auth_methods_section', $ta),
                [
                    ['name' => 'customer_auth_enabled', 'label' => sh_settings_admin_label('customer_auth_enabled', $ta), 'checked' => !empty($auth['customer_auth_enabled'])],
                    ['name' => 'customer_phone_login', 'label' => sh_settings_admin_label('customer_phone_login', $ta), 'checked' => !empty($auth['customer_phone_login'])],
                    ['name' => 'customer_google_login', 'label' => sh_settings_admin_label('customer_google_login', $ta), 'checked' => !empty($auth['customer_google_login'])],
                    ['name' => 'customer_apple_login', 'label' => sh_settings_admin_label('customer_apple_login', $ta), 'checked' => !empty($auth['customer_apple_login'])],
                ],
                'user-lock'
            ); ?>

            <div class="adm-customer-auth-preview">
                <p class="adm-compact-kicker"><i class="fas fa-eye"></i> <?= htmlspecialchars(sh_settings_admin_label('customer_auth_preview_title', $ta)) ?></p>
                <div class="adm-customer-auth-preview-btns">
                    <span class="adm-oauth-preview adm-oauth-preview--phone">
                        <i class="fas fa-phone"></i> <?= htmlspecialchars(sh_settings_admin_label('customer_phone_login', $ta)) ?>
                    </span>
                    <span class="adm-oauth-preview adm-oauth-preview--google">
                        <i class="fab fa-google"></i>
                        <?= htmlspecialchars($demoMode && !sh_customer_google_login_enabled($auth)
                            ? sh_settings_admin_label('customer_google_demo_label', $ta)
                            : sh_settings_admin_label('customer_google_login', $ta)) ?>
                        <?php if ($demoMode && !$googleConfigured): ?>
                        <em class="adm-oauth-preview-tag"><?= htmlspecialchars(sh_settings_admin_label('customer_oauth_demo_tag', $ta)) ?></em>
                        <?php elseif ($googleConfigured): ?>
                        <em class="adm-oauth-preview-tag adm-oauth-preview-tag--ok"><?= htmlspecialchars(sh_settings_admin_label('customer_oauth_live_tag', $ta)) ?></em>
                        <?php endif; ?>
                    </span>
                    <span class="adm-oauth-preview adm-oauth-preview--apple">
                        <i class="fab fa-apple"></i>
                        <?= htmlspecialchars($demoMode && !sh_customer_apple_login_enabled($auth)
                            ? sh_settings_admin_label('customer_apple_demo_label', $ta)
                            : sh_settings_admin_label('customer_apple_login', $ta)) ?>
                        <?php if ($demoMode && !$appleConfigured): ?>
                        <em class="adm-oauth-preview-tag"><?= htmlspecialchars(sh_settings_admin_label('customer_oauth_demo_tag', $ta)) ?></em>
                        <?php elseif ($appleConfigured): ?>
                        <em class="adm-oauth-preview-tag adm-oauth-preview-tag--ok"><?= htmlspecialchars(sh_settings_admin_label('customer_oauth_live_tag', $ta)) ?></em>
                        <?php endif; ?>
                    </span>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('customer_auth_preview_hint', $ta)) ?></p>
            </div>

            <p class="adm-inline-actions">
                <a href="<?= sh_url('login.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('customer_auth_preview', $ta)) ?>
                </a>
            </p>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="customer-auth-google">
        <div class="adm-card-head">
            <h2><i class="fab fa-google"></i> <?= htmlspecialchars($sections['customer-auth-google'] ?? sh_settings_admin_label('customer_google_api_section', $ta)) ?></h2>
            <?php if ($googleConfigured): ?>
            <span class="adm-badge adm-badge--green"><i class="fas fa-check-circle"></i> API</span>
            <?php elseif ($demoMode): ?>
            <span class="adm-badge adm-badge--blue"><i class="fas fa-flask"></i> Demo</span>
            <?php endif; ?>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('customer_google_api_help', $ta)) ?></p>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_google_client_id', $ta)) ?></label>
                    <input type="text" name="customer_google_client_id" value="<?= htmlspecialchars($auth['customer_google_client_id'] ?? '') ?>" placeholder="123456789.apps.googleusercontent.com" autocomplete="off">
                    <?php sh_admin_render_field_hint($tab, 'customer_google_client_id', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_google_client_secret', $ta)) ?></label>
                    <input type="password" name="customer_google_client_secret" placeholder="<?= htmlspecialchars(sh_settings_admin_label('secret_keep', $ta)) ?>" autocomplete="new-password">
                    <?php sh_admin_render_field_hint($tab, 'customer_google_client_secret', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_google_redirect_uri', $ta)) ?></label>
                    <input type="text" value="<?= htmlspecialchars($googleRedirect) ?>" readonly class="adm-input-readonly" onclick="this.select()">
                    <?php sh_admin_render_field_hint($tab, 'customer_google_redirect_uri', $ta); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="customer-auth-apple">
        <div class="adm-card-head">
            <h2><i class="fab fa-apple"></i> <?= htmlspecialchars($sections['customer-auth-apple'] ?? sh_settings_admin_label('customer_apple_api_section', $ta)) ?></h2>
            <?php if ($appleConfigured): ?>
            <span class="adm-badge adm-badge--green"><i class="fas fa-check-circle"></i> API</span>
            <?php elseif ($demoMode): ?>
            <span class="adm-badge adm-badge--blue"><i class="fas fa-flask"></i> Demo</span>
            <?php endif; ?>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('customer_apple_api_help', $ta)) ?></p>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_apple_client_id', $ta)) ?></label>
                    <input type="text" name="customer_apple_client_id" value="<?= htmlspecialchars($auth['customer_apple_client_id'] ?? '') ?>" placeholder="com.example.shop" autocomplete="off">
                    <?php sh_admin_render_field_hint($tab, 'customer_apple_client_id', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_apple_team_id', $ta)) ?></label>
                    <input type="text" name="customer_apple_team_id" value="<?= htmlspecialchars($auth['customer_apple_team_id'] ?? '') ?>" placeholder="ABCDE12345" autocomplete="off">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_apple_key_id', $ta)) ?></label>
                    <input type="text" name="customer_apple_key_id" value="<?= htmlspecialchars($auth['customer_apple_key_id'] ?? '') ?>" placeholder="KEY123ABC" autocomplete="off">
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_apple_private_key', $ta)) ?></label>
                    <?php if ($appleKeySet): ?>
                    <p class="adm-help adm-help-compact"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('secret_saved', $ta)) ?></p>
                    <?php endif; ?>
                    <textarea name="customer_apple_private_key" rows="8" class="adm-code-input" placeholder="<?= htmlspecialchars($appleKeySet ? sh_settings_admin_label('secret_keep', $ta) : '-----BEGIN PRIVATE KEY-----') ?>"></textarea>
                    <?php sh_admin_render_field_hint($tab, 'customer_apple_private_key', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('customer_apple_redirect_uri', $ta)) ?></label>
                    <input type="text" value="<?= htmlspecialchars($appleRedirect) ?>" readonly class="adm-input-readonly" onclick="this.select()">
                    <?php sh_admin_render_field_hint($tab, 'customer_apple_redirect_uri', $ta); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>