<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/sms.php';

$auth = sh_customer_auth_settings();
$current_page = 'login';
$error = '';
$success = '';

if (sh_customer_logged_in()) {
    header('Location: ' . sh_url('index.php'), true, 302);
    exit;
}

if (!sh_customer_auth_enabled($auth)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'phone';
    if ($action === 'verify_otp' && sh_customer_sms_login_enabled($auth)) {
        require_once __DIR__ . '/includes/sms.php';
        $phone = trim($_POST['phone'] ?? '');
        $code = trim($_POST['sms_code'] ?? '');
        if (sh_sms_verify_code($phone, $code) && sh_customer_login_phone($phone)) {
            header('Location: ' . sh_customer_post_login_url(), true, 302);
            exit;
        }
        $error = $t['customer_auth']['sms_invalid'] ?? 'Invalid or expired code.';
    } elseif ($action === 'phone' && sh_customer_phone_login_enabled($auth) && !sh_customer_sms_login_enabled($auth)) {
        $phone = trim($_POST['phone'] ?? '');
        if (sh_customer_login_phone($phone)) {
            header('Location: ' . sh_customer_post_login_url(), true, 302);
            exit;
        }
        $error = $t['customer_auth']['phone_invalid'] ?? 'Enter a valid phone number';
    } elseif ($action === 'demo_customer') {
        sh_customer_login_demo();
        header('Location: ' . sh_url('index.php'), true, 302);
        exit;
    } elseif ($action === 'google_demo' && sh_customer_google_login_available($auth)) {
        sh_customer_login_oauth('google', 'demo-' . bin2hex(random_bytes(4)), 'Google demo user');
        header('Location: ' . sh_url('index.php'), true, 302);
        exit;
    } elseif ($action === 'apple_demo' && sh_customer_apple_login_available($auth)) {
        sh_customer_login_oauth('apple', 'demo-' . bin2hex(random_bytes(4)), 'Apple demo user');
        header('Location: ' . sh_url('index.php'), true, 302);
        exit;
    }
}

$show_oauth = sh_customer_google_login_available($auth) || sh_customer_apple_login_available($auth);

$canonical = $site_url . '/login.php';
$page_title = $t['customer_auth']['page_title'] ?? 'Sign in';
$page_desc  = $t['customer_auth']['page_desc'] ?? '';
require __DIR__ . '/includes/header.php';
?>

<main class="sh-container sh-auth-page">
    <div class="sh-auth-card">
        <h1><i class="fas fa-user-circle"></i> <?= htmlspecialchars($t['customer_auth']['h1'] ?? 'Sign in') ?></h1>
        <p class="sh-auth-lead"><?= htmlspecialchars($t['customer_auth']['lead'] ?? '') ?></p>

        <?php if ($error !== ''): ?>
        <div class="sh-auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (sh_customer_phone_login_enabled($auth)): ?>
        <?php $smsAuth = function_exists('sh_customer_sms_login_enabled') && sh_customer_sms_login_enabled($auth); ?>
        <form method="post" class="sh-auth-form" id="shSmsAuthForm"
              data-sms-url="<?= htmlspecialchars(sh_url('api/sms-send.php')) ?>">
            <input type="hidden" name="action" value="<?= $smsAuth ? 'send_otp' : 'phone' ?>">
            <input type="hidden" name="phone" value="">
            <p id="shSmsAuthStatus" class="sh-auth-status" hidden></p>
            <div id="shSmsPhoneStep">
                <label for="shAuthPhone"><?= htmlspecialchars($t['customer_auth']['phone_label'] ?? 'Phone number') ?></label>
                <input type="tel" id="shAuthPhone" name="phone_input" required autocomplete="tel"
                       placeholder="<?= htmlspecialchars($t['customer_auth']['phone_placeholder'] ?? '+47 XX XX XX XX') ?>"
                       inputmode="tel" pattern="[\d\s+\-()]{8,}">
                <?php if ($smsAuth): ?>
                <button type="button" class="sh-btn-primary sh-btn-block" id="shSmsSendBtn"
                        data-need-phone="<?= htmlspecialchars($t['customer_auth']['phone_invalid'] ?? '') ?>"
                        data-sending="<?= htmlspecialchars($t['customer_auth']['sms_sending'] ?? 'Sending code…') ?>"
                        data-sent="<?= htmlspecialchars($t['customer_auth']['sms_sent'] ?? 'SMS code sent.') ?>"
                        data-demo-sent="<?= htmlspecialchars($t['customer_auth']['sms_demo_sent'] ?? 'Demo code: %s') ?>">
                    <i class="fas fa-sms"></i> <?= htmlspecialchars($t['customer_auth']['sms_send'] ?? 'Send SMS code') ?>
                </button>
                <?php else: ?>
                <button type="submit" class="sh-btn-primary sh-btn-block">
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($t['customer_auth']['phone_submit'] ?? 'Continue with phone') ?>
                </button>
                <?php endif; ?>
            </div>
            <?php if ($smsAuth): ?>
            <div id="shSmsCodeStep" hidden>
                <label for="shAuthSmsCode"><?= htmlspecialchars($t['customer_auth']['sms_code_label'] ?? 'SMS code') ?></label>
                <input type="text" id="shAuthSmsCode" name="sms_code" inputmode="numeric" pattern="[0-9]{4,8}" maxlength="8" autocomplete="one-time-code"
                       placeholder="<?= htmlspecialchars($t['customer_auth']['sms_code_ph'] ?? '6-digit code') ?>">
                <button type="submit" class="sh-btn-primary sh-btn-block" id="shSmsVerifyBtn">
                    <i class="fas fa-check"></i> <?= htmlspecialchars($t['customer_auth']['sms_verify'] ?? 'Verify and sign in') ?>
                </button>
                <button type="button" class="sh-btn-outline sh-btn-block" id="shSmsBackBtn">
                    <?= htmlspecialchars($t['customer_auth']['sms_back'] ?? 'Change phone number') ?>
                </button>
            </div>
            <?php endif; ?>
        </form>
        <?php if ($smsAuth): ?>
        <script src="<?= htmlspecialchars(sh_asset('js/sms-auth.js')) ?>?v=1" defer></script>
        <?php endif; ?>
        <?php endif; ?>

        <form method="post" class="sh-auth-demo-customer">
            <input type="hidden" name="action" value="demo_customer">
            <button type="submit" class="sh-btn-outline sh-btn-block sh-auth-demo-btn">
                <i class="fas fa-user-tag"></i>
                <?= htmlspecialchars($t['customer_auth']['demo_customer_btn'] ?? 'Sign in as demo customer') ?>
            </button>
        </form>
        <p class="sh-auth-hint"><?= htmlspecialchars($t['customer_auth']['demo_customer_hint'] ?? 'Demo account — no phone or OAuth required.') ?></p>

        <?php if ($show_oauth): ?>
        <div class="sh-auth-divider"><span><?= htmlspecialchars($t['customer_auth']['or'] ?? 'or') ?></span></div>
        <div class="sh-auth-oauth">
            <?php if (sh_customer_google_login_available($auth)): ?>
            <form method="post">
                <input type="hidden" name="action" value="google_demo">
                <button type="submit" class="sh-auth-oauth-btn sh-auth-oauth-google">
                    <i class="fab fa-google"></i>
                    <?= htmlspecialchars(
                        sh_customer_google_demo_only($auth)
                            ? ($t['customer_auth']['google_demo'] ?? $t['customer_auth']['google'] ?? 'Demo sign-in with Google')
                            : ($t['customer_auth']['google'] ?? 'Continue with Google')
                    ) ?>
                </button>
            </form>
            <?php if (sh_customer_google_demo_only($auth)): ?>
            <p class="sh-auth-hint"><?= htmlspecialchars($t['customer_auth']['google_demo_hint'] ?? '') ?></p>
            <?php elseif (!sh_customer_google_configured($auth)): ?>
            <p class="sh-auth-hint"><?= htmlspecialchars($t['customer_auth']['google_hint'] ?? '') ?></p>
            <?php endif; ?>
            <?php endif; ?>
            <?php if (sh_customer_apple_login_available($auth)): ?>
            <form method="post">
                <input type="hidden" name="action" value="apple_demo">
                <button type="submit" class="sh-auth-oauth-btn sh-auth-oauth-apple">
                    <i class="fab fa-apple"></i>
                    <?= htmlspecialchars(
                        sh_customer_apple_demo_only($auth)
                            ? ($t['customer_auth']['apple_demo'] ?? $t['customer_auth']['apple'] ?? 'Demo sign-in with Apple')
                            : ($t['customer_auth']['apple'] ?? 'Continue with Apple')
                    ) ?>
                </button>
            </form>
            <?php if (sh_customer_apple_demo_only($auth)): ?>
            <p class="sh-auth-hint"><?= htmlspecialchars($t['customer_auth']['apple_demo_hint'] ?? '') ?></p>
            <?php elseif (!sh_customer_apple_configured($auth)): ?>
            <p class="sh-auth-hint"><?= htmlspecialchars($t['customer_auth']['apple_hint'] ?? '') ?></p>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="sh-auth-demo-note"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($t['customer_auth']['demo_note'] ?? '') ?></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>