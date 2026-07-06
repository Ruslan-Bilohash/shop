<?php
require_once __DIR__ . '/init.php';

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
    if ($action === 'phone' && sh_customer_phone_login_enabled($auth)) {
        $phone = trim($_POST['phone'] ?? '');
        if (sh_customer_login_phone($phone)) {
            header('Location: ' . sh_url('index.php'), true, 302);
            exit;
        }
        $error = $t['customer_auth']['phone_invalid'] ?? 'Enter a valid phone number';
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
        <form method="post" class="sh-auth-form">
            <input type="hidden" name="action" value="phone">
            <label for="shAuthPhone"><?= htmlspecialchars($t['customer_auth']['phone_label'] ?? 'Phone number') ?></label>
            <input type="tel" id="shAuthPhone" name="phone" required autocomplete="tel"
                   placeholder="<?= htmlspecialchars($t['customer_auth']['phone_placeholder'] ?? '+380 XX XXX XX XX') ?>"
                   inputmode="tel" pattern="[\d\s+\-()]{8,}">
            <button type="submit" class="sh-btn-primary sh-btn-block">
                <i class="fas fa-phone"></i> <?= htmlspecialchars($t['customer_auth']['phone_submit'] ?? 'Continue with phone') ?>
            </button>
        </form>
        <?php endif; ?>

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