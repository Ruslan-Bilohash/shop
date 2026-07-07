<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/license-runtime.php';
require_once dirname(__DIR__) . '/includes/subscription-links.php';
require_once dirname(__DIR__) . '/includes/billing-pricing.php';

$admin_page = 'license';
$ta = $t['admin'] ?? [];
$lp = $ta['license_page'] ?? [];
$bp = $ta['billing_demo_page'] ?? [];
$page_title = $lp['title'] ?? 'License';

$status = sh_license_status();
$state = sh_license_state();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim((string) ($_POST['license_key'] ?? ''));
    $result = sh_license_activate($key);
    if ($result['ok']) {
        $success = $lp['activated'] ?? 'License activated successfully';
        $status = sh_license_status();
        $state = sh_license_state();
    } else {
        $error = $result['error'] ?: ($lp['invalid'] ?? 'Invalid license key');
    }
}

if (sh_admin_is_owner()) {
    $admin_extra_js = [sh_asset('js/admin-license-panel.js') . '?v=1'];
}

$pricing = sh_billing_pricing_for_lang($lang);
$apiMonthly = (int) ($pricing['api_requests_monthly'] ?? SH_BILLING_API_REQUESTS_MONTHLY);
$apiYearly = (int) ($pricing['api_requests_yearly'] ?? SH_BILLING_API_REQUESTS_YEARLY);
$subUrl = sh_subscription_url();
$cabinetUrl = sh_license_cabinet_url();
$tagline = sh_billing_subscription_tagline($lang);

$subHelp = (string) ($lp['subscription_help'] ?? 'One BILOHASH subscription for all CMS scripts and AI — {tagline}.');
if (str_contains($subHelp, '{tagline}')) {
    $subHelp = str_replace('{tagline}', $tagline, $subHelp);
}

require __DIR__ . '/includes/layout.php';
?>
<?php require __DIR__ . '/includes/license-admin-panel.php'; ?>

<div class="adm-my-console">
    <div class="adm-my-hero">
        <div class="adm-my-hero-main">
            <span class="adm-my-avatar" aria-hidden="true"><i class="fas fa-key"></i></span>
            <div>
                <h2 class="adm-my-hero-title"><i class="fas fa-crown"></i> <?= htmlspecialchars($lp['subscription_title'] ?? 'BILOHASH subscription') ?></h2>
                <p class="adm-my-hero-text"><?= htmlspecialchars($subHelp) ?></p>
                <p class="adm-ai-hero-subscribe">
                    <a href="<?= htmlspecialchars($subUrl) ?>" class="adm-btn adm-btn-primary adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                        <i class="fas fa-layer-group"></i> <?= htmlspecialchars($lp['subscribe_btn'] ?? 'Get subscription') ?>
                    </a>
                    <a href="<?= htmlspecialchars($cabinetUrl) ?>" class="adm-btn adm-btn-outline adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                        <i class="fas fa-door-open"></i> <?= htmlspecialchars($lp['cabinet_btn'] ?? 'Customer cabinet') ?>
                    </a>
                    <span class="adm-muted adm-ai-hero-tagline"><?= htmlspecialchars($tagline) ?></span>
                </p>
            </div>
        </div>
    </div>

    <div class="adm-card adm-my-sub-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-receipt"></i> <?= htmlspecialchars($lp['plans_title'] ?? 'Subscription plans') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-my-plans">
                <article class="adm-bd-plan adm-my-plan">
                    <h3><?= htmlspecialchars($bp['monthly_title'] ?? '1 CMS script') ?></h3>
                    <p class="adm-bd-price"><?= htmlspecialchars($pricing['monthly_fmt']) ?><small>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></small></p>
                    <ul class="adm-bd-features">
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_script_monthly'] ?? '1 CMS script · 1 domain') ?></li>
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $apiMonthly])) ?></li>
                    </ul>
                </article>
                <article class="adm-bd-plan adm-bd-plan--yearly adm-my-plan">
                    <span class="adm-bd-save"><?= htmlspecialchars($bp['yearly_badge'] ?? 'Full library') ?></span>
                    <h3><?= htmlspecialchars($bp['yearly_title'] ?? 'All CMS scripts') ?></h3>
                    <p class="adm-bd-price"><?= htmlspecialchars((string) ($pricing['full_monthly_fmt'] ?? $pricing['yearly_fmt'])) ?><small>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></small></p>
                    <ul class="adm-bd-features">
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_all_yearly'] ?? 'All CMS scripts · releases & updates') ?></li>
                        <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $apiYearly])) ?></li>
                    </ul>
                </article>
            </div>
            <p class="adm-help adm-help-compact" style="margin-top:12px"><?= htmlspecialchars($lp['cabinet_help'] ?? 'After subscribing, open your customer cabinet on bilohash.com to view purchases and waitlist status.') ?></p>
        </div>
    </div>

    <div class="adm-card" style="max-width:720px">
        <div class="adm-card-head">
            <h2><i class="fas fa-key"></i> <?= htmlspecialchars($page_title) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <?php if ($status['status'] === 'licensed'): ?>
            <p class="adm-badge adm-badge-green"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($lp['status_licensed'] ?? 'Licensed') ?></p>
            <?php elseif ($status['status'] === 'trial'): ?>
            <p class="adm-badge adm-badge-info"><i class="fas fa-hourglass-half"></i> <?= htmlspecialchars(sprintf($lp['status_trial'] ?? 'Trial: %d days left', $status['trial_days_left'])) ?></p>
            <?php else: ?>
            <p class="adm-badge adm-badge-warn"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($lp['status_expired'] ?? 'Trial expired — activate license') ?></p>
            <?php endif; ?>

            <p class="adm-muted"><?= htmlspecialchars($lp['intro'] ?? 'Commercial Shop CMS includes 30 days demo. After trial, get the unified BILOHASH subscription and activate your key.') ?></p>

            <?php if ($error): ?>
            <div class="adm-login-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="adm-flash adm-flash-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" style="margin-top:16px">
                <div class="adm-field">
                    <label for="license_key"><?= htmlspecialchars($lp['key_label'] ?? 'License key (BHSHOP.…)') ?></label>
                    <input type="text" id="license_key" name="license_key" required autocomplete="off" placeholder="BHSHOP.…" value="">
                </div>
                <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-unlock"></i> <?= htmlspecialchars($lp['activate'] ?? 'Activate license') ?></button>
            </form>

            <?php if (!empty($state['activated_at'])): ?>
            <p class="adm-muted" style="margin-top:16px;font-size:12px">
                <?= htmlspecialchars($lp['activated_at'] ?? 'Activated') ?>: <?= htmlspecialchars((string) $state['activated_at']) ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/layout-end.php'; ?>