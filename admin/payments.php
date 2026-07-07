<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
sh_admin_require();

$admin_page = 'settings';
$settings_tab = 'payments';
require_once dirname(__DIR__) . '/includes/site-settings.php';
$tp = $ta['payments_page'] ?? [];
$guides = $tp['guides'] ?? [];
$fields = $tp['fields'] ?? [];

$payment_tab = $_GET['tab'] ?? 'paypal';
if (!sh_payment_tab_valid($payment_tab)) {
    $payment_tab = 'paypal';
}

$tab_labels = $tp['tabs'] ?? [];
$page_title = $tp['title'] ?? 'Payment settings';
$settings = sh_load_settings();
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = sh_payment_apply_post($payment_tab, $_POST, $settings);
    $flash = sh_save_settings($settings) ? 'success' : 'error';
    $settings = sh_load_settings();
}

$cfg = $settings[$payment_tab] ?? [];
$guide = $guides[$payment_tab] ?? [];
$configured = sh_payment_is_configured($payment_tab, $settings);
$demo_mode = defined('SH_DEMO_MODE') && SH_DEMO_MODE;

if ($flash === 'success') {
    $admin_flash = ['type' => 'success', 'msg' => $tp['saved'] ?? 'Settings saved.'];
} elseif ($flash === 'error') {
    $admin_flash = ['type' => 'error', 'msg' => $tp['save_error'] ?? 'Could not save settings.'];
}

require __DIR__ . '/includes/layout.php';
sh_render_settings_tabs('sh_admin_url', $ta);
require __DIR__ . '/includes/payment-tabs.php';
?>

<?php if ($demo_mode): ?>
<div class="adm-alert adm-alert-info">
    <i class="fas fa-flask"></i> <?= htmlspecialchars($tp['demo_note'] ?? 'Demo mode — keys are stored in the database. Checkout remains simulated until production integration.') ?>
</div>
<?php endif; ?>

<div class="adm-payment-status">
    <span class="adm-badge <?= $configured ? 'adm-badge--green' : 'adm-badge--orange' ?>">
        <i class="fas fa-<?= $configured ? 'check-circle' : 'clock' ?>"></i>
        <?= htmlspecialchars($configured ? ($tp['status_configured'] ?? 'Configured') : ($tp['status_pending'] ?? 'Not configured')) ?>
    </span>
    <?php if (!empty($cfg['enabled'])): ?>
    <span class="adm-badge adm-badge--blue"><i class="fas fa-power-off"></i> <?= htmlspecialchars($tp['enabled'] ?? 'Enabled') ?></span>
    <?php endif; ?>
</div>

<form method="post" class="adm-settings-form">
    <div class="adm-payment-grid">
        <div class="adm-payment-form">
            <div class="adm-card">
                <div class="adm-card-head">
                    <h2><?= htmlspecialchars($tab_labels[$payment_tab] ?? ucfirst($payment_tab)) ?></h2>
                </div>
                <div class="adm-card-body padded">
                    <?php
                    require_once __DIR__ . '/includes/toggle-field.php';
                    sh_admin_toggle_section(
                        $tp['status_section'] ?? 'Checkout',
                        [
                            ['name' => 'enabled', 'label' => $tp['enable_provider'] ?? 'Enable this payment method on checkout', 'checked' => !empty($cfg['enabled'])],
                        ],
                        'credit-card'
                    );
                    ?>

                    <?php if ($payment_tab === 'paypal'): ?>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['mode'] ?? 'Mode') ?></label>
                            <select name="mode">
                                <option value="sandbox" <?= ($cfg['mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                <option value="live" <?= ($cfg['mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                            </select>
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['currency'] ?? 'Currency') ?></label>
                            <input type="text" name="currency" value="<?= htmlspecialchars($cfg['currency'] ?? 'NOK') ?>" maxlength="3">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['client_id'] ?? 'Client ID') ?></label>
                            <input type="text" name="client_id" value="<?= htmlspecialchars($cfg['client_id'] ?? '') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['client_secret'] ?? 'Client Secret') ?></label>
                            <input type="password" name="client_secret" value="" placeholder="<?= htmlspecialchars($cfg['client_secret'] !== '' ? sh_secret_preview($cfg['client_secret']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                    </div>

                    <?php elseif ($payment_tab === 'stripe'): ?>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['mode'] ?? 'Mode') ?></label>
                            <select name="mode">
                                <option value="test" <?= ($cfg['mode'] ?? '') === 'test' ? 'selected' : '' ?>>Test</option>
                                <option value="live" <?= ($cfg['mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                            </select>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['publishable_key'] ?? 'Publishable key') ?></label>
                            <input type="text" name="publishable_key" value="<?= htmlspecialchars($cfg['publishable_key'] ?? '') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['secret_key'] ?? 'Secret key') ?></label>
                            <input type="password" name="secret_key" value="" placeholder="<?= htmlspecialchars($cfg['secret_key'] !== '' ? sh_secret_preview($cfg['secret_key']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['webhook_secret'] ?? 'Webhook signing secret') ?></label>
                            <input type="password" name="webhook_secret" value="" placeholder="<?= htmlspecialchars($cfg['webhook_secret'] !== '' ? sh_secret_preview($cfg['webhook_secret']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                    </div>

                    <?php elseif ($payment_tab === 'vipps'): ?>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['environment'] ?? 'Environment') ?></label>
                            <select name="environment">
                                <option value="test" <?= ($cfg['environment'] ?? '') === 'test' ? 'selected' : '' ?>>Test (apitest.vipps.no)</option>
                                <option value="production" <?= ($cfg['environment'] ?? '') === 'production' ? 'selected' : '' ?>>Production</option>
                            </select>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['client_id'] ?? 'Client ID') ?></label>
                            <input type="text" name="client_id" value="<?= htmlspecialchars($cfg['client_id'] ?? '') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['client_secret'] ?? 'Client Secret') ?></label>
                            <input type="password" name="client_secret" value="" placeholder="<?= htmlspecialchars($cfg['client_secret'] !== '' ? sh_secret_preview($cfg['client_secret']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['subscription_key'] ?? 'Ocp-Apim-Subscription-Key') ?></label>
                            <input type="text" name="subscription_key" value="<?= htmlspecialchars($cfg['subscription_key'] ?? '') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['merchant_serial'] ?? 'Merchant Serial Number (MSN)') ?></label>
                            <input type="text" name="merchant_serial" value="<?= htmlspecialchars($cfg['merchant_serial'] ?? '') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['callback_token'] ?? 'Callback auth token') ?></label>
                            <input type="password" name="callback_token" value="" placeholder="<?= htmlspecialchars($cfg['callback_token'] !== '' ? sh_secret_preview($cfg['callback_token']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                    </div>

                    <?php elseif ($payment_tab === 'paysera'): ?>
                    <p class="adm-help"><?= htmlspecialchars($tp['paysera_help'] ?? 'Paysera (WebToPay) — bank cards and online banking for Baltics and EU. Use test mode with sandbox.paysera.com first.') ?></p>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['mode'] ?? 'Mode') ?></label>
                            <select name="mode">
                                <option value="test" <?= ($cfg['mode'] ?? '') === 'test' ? 'selected' : '' ?>>Test (sandbox)</option>
                                <option value="live" <?= ($cfg['mode'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                            </select>
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['currency'] ?? 'Currency') ?></label>
                            <input type="text" name="currency" value="<?= htmlspecialchars($cfg['currency'] ?? 'EUR') ?>" maxlength="3">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['paysera_project_id'] ?? 'Project ID') ?></label>
                            <input type="text" name="project_id" value="<?= htmlspecialchars($cfg['project_id'] ?? '') ?>" autocomplete="off" placeholder="123456">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['paysera_sign_password'] ?? 'Sign password') ?></label>
                            <input type="password" name="sign_password" value="" placeholder="<?= htmlspecialchars($cfg['sign_password'] !== '' ? sh_secret_preview($cfg['sign_password']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="adm-guide-links adm-payment-api-links">
                        <strong><?= htmlspecialchars($tp['useful_links'] ?? 'Useful links') ?></strong>
                        <ul>
                            <li><a href="https://www.paysera.com/en/checkout/" target="_blank" rel="noopener">Paysera Checkout <i class="fas fa-external-link-alt"></i></a></li>
                            <li><a href="https://developers.paysera.com/" target="_blank" rel="noopener">Paysera Developers <i class="fas fa-external-link-alt"></i></a></li>
                        </ul>
                    </div>

                    <?php elseif ($payment_tab === 'revolut'): ?>
                    <p class="adm-help"><?= htmlspecialchars($tp['revolut_help'] ?? 'Revolut Merchant API — cards and Revolut Pay. Sandbox keys from Revolut Business → Merchant API.') ?></p>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['mode'] ?? 'Mode') ?></label>
                            <select name="mode">
                                <option value="sandbox" <?= ($cfg['mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                <option value="production" <?= ($cfg['mode'] ?? '') === 'production' ? 'selected' : '' ?>>Production</option>
                            </select>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['revolut_public_key'] ?? 'Public API key (optional)') ?></label>
                            <input type="text" name="public_key" value="<?= htmlspecialchars($cfg['public_key'] ?? '') ?>" autocomplete="off" placeholder="pk_…">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['secret_key'] ?? 'Secret API key') ?></label>
                            <input type="password" name="secret_key" value="" placeholder="<?= htmlspecialchars($cfg['secret_key'] !== '' ? sh_secret_preview($cfg['secret_key']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['webhook_secret'] ?? 'Webhook signing secret') ?></label>
                            <input type="password" name="webhook_secret" value="" placeholder="<?= htmlspecialchars($cfg['webhook_secret'] !== '' ? sh_secret_preview($cfg['webhook_secret']) : ($fields['secret_placeholder'] ?? 'Leave blank to keep current')) ?>" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="adm-guide-links adm-payment-api-links">
                        <strong><?= htmlspecialchars($tp['useful_links'] ?? 'Useful links') ?></strong>
                        <ul>
                            <li><a href="https://developer.revolut.com/docs/merchant/merchant-api/" target="_blank" rel="noopener">Revolut Merchant API <i class="fas fa-external-link-alt"></i></a></li>
                            <li><a href="https://business.revolut.com/merchant" target="_blank" rel="noopener">Revolut Business Merchant <i class="fas fa-external-link-alt"></i></a></li>
                        </ul>
                    </div>

                    <?php elseif ($payment_tab === 'google_pay'): ?>
                    <p class="adm-help"><?= htmlspecialchars($tp['google_pay_help'] ?? 'Google Pay uses Stripe or PayPal as the payment gateway. Register at Google Pay Business Console and enable the wallet in your gateway dashboard.') ?></p>
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['gateway'] ?? 'Payment gateway') ?></label>
                            <select name="gateway">
                                <option value="stripe" <?= ($cfg['gateway'] ?? 'stripe') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                                <option value="paypal" <?= ($cfg['gateway'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                            </select>
                            <small class="adm-field-hint"><?= htmlspecialchars($fields['gateway_hint'] ?? 'Complete Stripe/PayPal setup first — Google Pay routes charges through this gateway.') ?></small>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['google_merchant_id'] ?? $fields['merchant_id'] ?? 'Google Pay Merchant ID') ?></label>
                            <input type="text" name="merchant_id" value="<?= htmlspecialchars($cfg['merchant_id'] ?? '') ?>" placeholder="BCR2DN4T…" autocomplete="off">
                            <small class="adm-field-hint"><?= htmlspecialchars($fields['google_merchant_hint'] ?? 'From pay.google.com/business/console — Merchant profile ID.') ?></small>
                        </div>
                    </div>
                    <div class="adm-guide-links adm-payment-api-links">
                        <strong><?= htmlspecialchars($tp['useful_links'] ?? 'Useful links') ?></strong>
                        <ul>
                            <li><a href="https://pay.google.com/business/console/" target="_blank" rel="noopener">Google Pay Business Console <i class="fas fa-external-link-alt"></i></a></li>
                            <li><a href="https://stripe.com/docs/google-pay" target="_blank" rel="noopener">Stripe Google Pay <i class="fas fa-external-link-alt"></i></a></li>
                        </ul>
                    </div>

                    <?php elseif ($payment_tab === 'apple_pay'): ?>
                    <p class="adm-help"><?= htmlspecialchars($tp['apple_pay_help'] ?? 'Apple Pay on the web requires a Merchant ID, domain verification (Safari) and usually Stripe as the processor.') ?></p>
                    <div class="adm-form-grid">
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['apple_merchant_id'] ?? $fields['merchant_id'] ?? 'Apple Merchant ID') ?></label>
                            <input type="text" name="merchant_id" value="<?= htmlspecialchars($cfg['merchant_id'] ?? '') ?>" placeholder="merchant.com.example" autocomplete="off">
                            <small class="adm-field-hint"><?= htmlspecialchars($fields['apple_merchant_hint'] ?? 'Apple Developer → Identifiers → Merchant IDs.') ?></small>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['domain'] ?? 'Verified domain') ?></label>
                            <input type="text" name="domain" value="<?= htmlspecialchars($cfg['domain'] ?? '') ?>" placeholder="bilohash.com" autocomplete="off">
                            <small class="adm-field-hint"><?= htmlspecialchars($fields['apple_domain_hint'] ?? 'Host Apple domain verification file at /.well-known/apple-developer-merchantid-domain-association') ?></small>
                        </div>
                        <?php
                        sh_admin_toggle_section(
                            $tp['apple_verify_section'] ?? 'Apple Developer',
                            [
                                ['name' => 'verify_domain', 'label' => $fields['verify_domain'] ?? 'Domain verified in Apple Developer', 'checked' => !empty($cfg['verify_domain'])],
                            ],
                            'apple'
                        );
                        ?>
                    </div>
                    <div class="adm-guide-links adm-payment-api-links">
                        <strong><?= htmlspecialchars($tp['useful_links'] ?? 'Useful links') ?></strong>
                        <ul>
                            <li><a href="https://developer.apple.com/account/resources/identifiers/list/merchant" target="_blank" rel="noopener">Apple Merchant IDs <i class="fas fa-external-link-alt"></i></a></li>
                            <li><a href="https://stripe.com/docs/apple-pay" target="_blank" rel="noopener">Stripe Apple Pay <i class="fas fa-external-link-alt"></i></a></li>
                        </ul>
                    </div>

                    <?php elseif ($payment_tab === 'cod'): ?>
                    <div class="adm-form-grid">
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['cod_title'] ?? 'Checkout label') ?></label>
                            <input type="text" name="title" value="<?= htmlspecialchars($cfg['title'] ?? 'Cash on delivery') ?>" autocomplete="off">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars($fields['cod_instructions'] ?? 'Customer instructions') ?></label>
                            <textarea name="instructions" rows="4" placeholder="<?= htmlspecialchars($fields['cod_instructions_ph'] ?? 'Shown at checkout when COD is selected') ?>"><?= htmlspecialchars($cfg['instructions'] ?? '') ?></textarea>
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars($fields['cod_fee'] ?? 'Extra fee (NOK, demo)') ?></label>
                            <input type="number" name="fee" value="<?= (int)($cfg['fee'] ?? 0) ?>" min="0" step="1">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="adm-form-actions adm-form-actions-sticky">
                <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars($tp['save'] ?? 'Save') ?></button>
            </div>
        </div>

        <aside class="adm-payment-guide">
            <div class="adm-card">
                <div class="adm-card-head">
                    <h2><i class="fas fa-book-open"></i> <?= htmlspecialchars($guide['title'] ?? ($tp['guide_title'] ?? 'Setup guide')) ?></h2>
                </div>
                <div class="adm-card-body padded adm-guide-body">
                    <?php if (!empty($guide['intro'])): ?>
                    <p class="adm-guide-intro"><?= htmlspecialchars($guide['intro']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($guide['steps']) && is_array($guide['steps'])): ?>
                    <ol class="adm-guide-steps">
                        <?php foreach ($guide['steps'] as $step): ?>
                        <li><?= htmlspecialchars($step) ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>

                    <?php if (!empty($guide['links']) && is_array($guide['links'])): ?>
                    <div class="adm-guide-links">
                        <strong><?= htmlspecialchars($tp['useful_links'] ?? 'Useful links') ?></strong>
                        <ul>
                            <?php foreach ($guide['links'] as $link): ?>
                            <li>
                                <a href="<?= htmlspecialchars($link['url'] ?? '#') ?>" target="_blank" rel="noopener">
                                    <?= htmlspecialchars($link['label'] ?? '') ?> <i class="fas fa-external-link-alt"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($guide['note'])): ?>
                    <p class="adm-guide-note"><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($guide['note']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</form>

<?php require __DIR__ . '/includes/layout-end.php'; ?>