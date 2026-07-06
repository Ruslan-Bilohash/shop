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
?>
<form method="post" class="adm-settings-form">
    <div class="adm-card adm-settings-section" id="customer-auth-main">
        <div class="adm-card-head">
            <h2><i class="fas fa-user-lock"></i> <?= htmlspecialchars($sections['customer-auth-main'] ?? sh_settings_admin_label('store_customer_auth_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('store_customer_auth_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'customer_auth_enabled', 'label' => sh_settings_admin_label('customer_auth_enabled', $ta), 'checked' => !empty($auth['customer_auth_enabled'])],
                    ['name' => 'customer_phone_login', 'label' => sh_settings_admin_label('customer_phone_login', $ta), 'checked' => !empty($auth['customer_phone_login'])],
                    ['name' => 'customer_google_login', 'label' => sh_settings_admin_label('customer_google_login', $ta), 'checked' => !empty($auth['customer_google_login'])],
                    ['name' => 'customer_apple_login', 'label' => sh_settings_admin_label('customer_apple_login', $ta), 'checked' => !empty($auth['customer_apple_login'])],
                ],
                'user-lock'
            ); ?>
            <p><a href="<?= sh_url('login.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('customer_auth_preview', $ta)) ?></a></p>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="customer-auth-google">
        <div class="adm-card-head">
            <h2><i class="fab fa-google"></i> <?= htmlspecialchars($sections['customer-auth-google'] ?? sh_settings_admin_label('customer_google_api_section', $ta)) ?></h2>
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
                    <textarea name="customer_apple_private_key" rows="6" class="adm-code-input" placeholder="<?= htmlspecialchars($appleKeySet ? sh_settings_admin_label('secret_keep', $ta) : '-----BEGIN PRIVATE KEY-----') ?>"><?= '' ?></textarea>
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