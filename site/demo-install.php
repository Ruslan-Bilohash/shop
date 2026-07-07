<?php
require_once __DIR__ . '/init.php';

$di = $t['demo_install'] ?? [];
$page_title = $di['page_title'] ?? 'Download Shop CMS — 30-day demo';
$page_desc  = $di['meta_description'] ?? '';
$canonical  = $site_url . '/demo-install.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = shs_absolute_url($canonical);
$seo_schemas = shs_seo_schemas($canon_abs, $page_title, $page_desc);

$ecoPricing = dirname(__DIR__, 2) . '/includes/ecosystem-pricing.php';
if (is_file($ecoPricing)) {
    require_once $ecoPricing;
}
$fxNote = function_exists('ecosystem_pricing_fx_note')
    ? ecosystem_pricing_fx_note($lang === 'uk' ? 'ua' : ($lang === 'no' ? 'no' : 'en'))
    : '';
$scriptBadge = function_exists('ecosystem_script_pricing_badge')
    ? ecosystem_script_pricing_badge($lang === 'uk' ? 'ua' : ($lang === 'no' ? 'no' : 'en'))
    : '';
$fullBadge = function_exists('ecosystem_full_pricing_badge')
    ? ecosystem_full_pricing_badge($lang === 'uk' ? 'ua' : ($lang === 'no' ? 'no' : 'en'))
    : '';

$cabinetLang = $lang === 'uk' ? 'ua' : ($lang === 'no' ? 'no' : 'en');
$cabinetFile = dirname(__DIR__, 2) . '/includes/license-cabinet-i18n.php';
$cabinetUrl = '/ecosystem/cabinet.php' . ($cabinetLang !== 'en' ? '?lang=' . urlencode($cabinetLang === 'ua' ? 'uk' : $cabinetLang) : '');
if (is_file($cabinetFile)) {
    require_once $cabinetFile;
    $cabinetUrl = license_cabinet_url($cabinetLang);
}

$formResult = null;
$formValues = [
    'contact_name' => '',
    'email'        => '',
    'domain'       => '',
    'ftp_host'     => '',
    'ftp_user'     => '',
    'ftp_pass'     => '',
    'ftp_path'     => '/public_html/shop',
    'license_key'  => '',
];
$csrf = '';

$diReqFile = dirname(__DIR__, 2) . '/includes/demo-install-request.php';
if (is_file($diReqFile)) {
    require_once $diReqFile;
    $csrf = demo_install_request_ensure_csrf();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto_install'])) {
        $formResult = demo_install_request_handle_post($lang);
        $formValues = $formResult['values'];
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="shs-order-hero">
    <div class="shs-container">
        <div class="shs-section-head">
            <h1><?= htmlspecialchars($di['h1'] ?? 'Self-install Shop CMS') ?></h1>
            <p class="shs-section-sub"><?= htmlspecialchars($di['subtitle'] ?? '') ?></p>
        </div>
        <?php if ($fxNote !== ''): ?>
        <p class="shs-order-intro"><i class="fas fa-exchange-alt"></i> <?= htmlspecialchars($fxNote) ?></p>
        <?php endif; ?>
        <div class="shs-di-plans">
            <span class="shs-di-plan-pill"><?= htmlspecialchars($scriptBadge) ?></span>
            <span class="shs-di-plan-pill shs-di-plan-pill--full"><?= htmlspecialchars($fullBadge) ?></span>
        </div>
    </div>
</section>

<section class="shs-section shs-order-body">
    <div class="shs-container shs-di-grid">
        <div class="shs-order-block">
            <h2 class="shs-order-heading"><i class="fas fa-list-ol"></i> <?= htmlspecialchars($di['steps_title'] ?? 'Installation steps') ?></h2>
            <ol class="shs-order-steps">
                <?php foreach ($di['steps'] ?? [] as $step): ?>
                <li><?= htmlspecialchars($step) ?></li>
                <?php endforeach; ?>
            </ol>
            <p class="shs-help"><?= htmlspecialchars(strtr($di['trial_note'] ?? 'Trial: {days} days on one domain.', ['{days}' => (string) (defined('SH_DEMO_TRIAL_DAYS') ? SH_DEMO_TRIAL_DAYS : 30)])) ?></p>
            <p class="shs-help"><?= htmlspecialchars($di['license_note'] ?? '') ?></p>
            <div class="shs-di-cabinet-alt">
                <h3 class="shs-order-heading shs-order-heading--sm"><i class="fas fa-door-open"></i> <?= htmlspecialchars($di['cabinet_only_title'] ?? 'Download in customer cabinet') ?></h3>
                <p class="shs-help"><?= htmlspecialchars($di['cabinet_only_text'] ?? '') ?></p>
                <a href="<?= htmlspecialchars($cabinetUrl) ?>" class="shs-btn-secondary">
                    <i class="fas fa-download"></i> <?= htmlspecialchars($di['cabinet_cta'] ?? 'Open customer cabinet') ?>
                </a>
            </div>
        </div>

        <div class="shs-order-block shs-di-form-block">
            <h2 class="shs-order-heading"><i class="fas fa-cloud-upload-alt"></i> <?= htmlspecialchars($di['auto_install_title'] ?? 'Auto-install on your domain') ?></h2>
            <p class="shs-help"><?= htmlspecialchars($di['auto_install_help'] ?? $di['ftp_help'] ?? '') ?></p>

            <?php if ($formResult !== null): ?>
            <p class="shs-di-message <?= $formResult['type'] === 'success' ? 'is-ok' : ($formResult['type'] === 'warn' ? 'shs-di-warn' : 'is-error') ?>">
                <?= htmlspecialchars($formResult['message']) ?>
            </p>
            <?php endif; ?>

            <form method="post" class="shs-di-form" autocomplete="off">
                <input type="hidden" name="auto_install" value="1">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="shs-field" aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden;">
                    <label for="di_website">Website</label>
                    <input type="text" id="di_website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="shs-field">
                    <label for="di_contact_name"><?= htmlspecialchars($di['contact_name'] ?? 'Your name') ?></label>
                    <input class="shs-input" type="text" id="di_contact_name" name="contact_name" required
                           value="<?= htmlspecialchars($formValues['contact_name']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_email"><?= htmlspecialchars($di['email'] ?? 'Email') ?></label>
                    <input class="shs-input" type="email" id="di_email" name="email" required
                           value="<?= htmlspecialchars($formValues['email']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_domain"><?= htmlspecialchars($di['domain'] ?? 'Your domain') ?></label>
                    <input class="shs-input" type="text" id="di_domain" name="domain" required placeholder="example.com"
                           value="<?= htmlspecialchars($formValues['domain']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_ftp_host"><?= htmlspecialchars($di['ftp_host'] ?? 'FTP host') ?></label>
                    <input class="shs-input" type="text" id="di_ftp_host" name="ftp_host" required placeholder="ftp.example.com"
                           value="<?= htmlspecialchars($formValues['ftp_host']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_ftp_user"><?= htmlspecialchars($di['ftp_user'] ?? 'FTP username') ?></label>
                    <input class="shs-input" type="text" id="di_ftp_user" name="ftp_user" required
                           value="<?= htmlspecialchars($formValues['ftp_user']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_ftp_pass"><?= htmlspecialchars($di['ftp_pass'] ?? 'FTP password') ?></label>
                    <input class="shs-input" type="password" id="di_ftp_pass" name="ftp_pass" required
                           value="<?= htmlspecialchars($formValues['ftp_pass']) ?>">
                </div>
                <div class="shs-field">
                    <label for="di_ftp_path"><?= htmlspecialchars($di['ftp_path'] ?? 'Remote path') ?></label>
                    <input class="shs-input" type="text" id="di_ftp_path" name="ftp_path"
                           value="<?= htmlspecialchars($formValues['ftp_path'] !== '' ? $formValues['ftp_path'] : '/public_html/shop') ?>">
                </div>
                <div class="shs-field">
                    <label for="di_license_key"><?= htmlspecialchars($di['license_optional'] ?? 'License key (optional)') ?></label>
                    <input class="shs-input" type="text" id="di_license_key" name="license_key"
                           value="<?= htmlspecialchars($formValues['license_key']) ?>">
                </div>

                <label class="shs-di-terms">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>
                        <?= htmlspecialchars($di['terms_label'] ?? 'I agree to the installation terms') ?>
                        <a href="<?= htmlspecialchars($di['terms_url'] ?? 'https://bilohash.com/ecosystem/install.php') ?>" target="_blank" rel="noopener"><?= htmlspecialchars($di['terms_link'] ?? 'Read terms') ?></a>
                    </span>
                </label>

                <button type="submit" class="shs-btn-primary shs-btn-lg">
                    <i class="fas fa-paper-plane"></i> <?= htmlspecialchars($di['submit_ftp'] ?? 'Submit auto-install request') ?>
                </button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>