<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

if (!sh_admin_is_owner()) {
    if (function_exists('sh_admin_is_demo_user') && sh_admin_is_demo_user()) {
        header('Location: ' . sh_admin_url('my.php'), true, 302);
    } else {
        header('Location: ' . sh_admin_url('index.php'), true, 302);
    }
    exit;
}

require_once dirname(__DIR__) . '/includes/license-runtime.php';
require_once dirname(__DIR__) . '/includes/subscription-links.php';
require_once dirname(__DIR__) . '/includes/billing-pricing.php';

$admin_page = 'owner';
$op = $ta['owner_page'] ?? [];
$lp = $ta['license_page'] ?? [];
$bp = $ta['billing_demo_page'] ?? [];
$page_title = $op['title'] ?? 'My panel';

$status = sh_license_status();
$licenseInfo = sh_license_verify_current(false);
$host = sh_license_host();
$licensedDomain = trim((string) ($licenseInfo['domain'] ?? ''));
if ($licensedDomain === '') {
    $licensedDomain = trim((string) (sh_license_state()['license_domain'] ?? ''));
}

$pricing = sh_billing_pricing_for_lang($lang);
$apiMonthly = (int) ($pricing['api_requests_monthly'] ?? SH_BILLING_API_REQUESTS_MONTHLY);
$apiYearly = (int) ($pricing['api_requests_yearly'] ?? SH_BILLING_API_REQUESTS_YEARLY);
$subUrl = sh_subscription_url();
$cabinetUrl = sh_license_cabinet_url();
$tagline = sh_billing_subscription_tagline($lang);

$subHelp = (string) ($op['subscription_help'] ?? 'One BILOHASH subscription for all CMS scripts and AI — {tagline}.');
if (str_contains($subHelp, '{tagline}')) {
    $subHelp = str_replace('{tagline}', $tagline, $subHelp);
}

$statusLabel = $lp['status_trial'] ?? 'Trial: %d days left';
$statusBadge = 'adm-badge-info';
if ($status['status'] === 'licensed') {
    $statusLabel = $lp['status_licensed'] ?? 'Licensed';
    $statusBadge = 'adm-badge-green';
} elseif ($status['status'] === 'expired') {
    $statusLabel = $lp['status_expired'] ?? 'Trial expired — activate license';
    $statusBadge = 'adm-badge-warn';
} else {
    $statusLabel = sprintf($statusLabel, (int) ($status['trial_days_left'] ?? 0));
}

if (sh_admin_is_owner()) {
    $admin_extra_js = [sh_asset('js/admin-license-panel.js') . '?v=1'];
}

require __DIR__ . '/includes/layout.php';
?>
<?php require __DIR__ . '/includes/license-admin-panel.php'; ?>

<div class="adm-my-console">
    <div class="adm-my-hero">
        <div class="adm-my-hero-main">
            <span class="adm-my-avatar adm-my-avatar--owner" aria-hidden="true"><i class="fas fa-crown"></i></span>
            <div>
                <h2 class="adm-my-hero-title">
                    <i class="fas fa-user-shield"></i>
                    <?= htmlspecialchars($op['hero_title'] ?? 'Administrator panel') ?>
                </h2>
                <p class="adm-my-hero-text"><?= htmlspecialchars($op['intro'] ?? 'Manage BILOHASH subscription, domain, license and unlimited AI API for your shop.') ?></p>
                <p class="adm-ai-hero-subscribe">
                    <a href="<?= htmlspecialchars($subUrl) ?>" class="adm-btn adm-btn-primary adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                        <i class="fas fa-crown"></i> <?= htmlspecialchars($op['subscribe_btn'] ?? 'BILOHASH subscription') ?>
                    </a>
                    <a href="<?= htmlspecialchars($cabinetUrl) ?>" class="adm-btn adm-btn-outline adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                        <i class="fas fa-door-open"></i> <?= htmlspecialchars($op['cabinet_btn'] ?? ($lp['cabinet_btn'] ?? 'Customer cabinet')) ?>
                    </a>
                    <span class="adm-muted adm-ai-hero-tagline"><?= htmlspecialchars($tagline) ?></span>
                </p>
            </div>
        </div>
        <div class="adm-my-hero-actions">
            <a href="<?= sh_admin_url('license.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-key"></i> <?= htmlspecialchars($op['license_btn'] ?? 'Activate license') ?>
            </a>
            <a href="<?= sh_admin_url('settings-ai.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-robot"></i> <?= htmlspecialchars($op['ai_settings_btn'] ?? 'AI settings') ?>
            </a>
        </div>
    </div>

    <div class="adm-my-grid">
        <div class="adm-card adm-my-api-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-bolt"></i> <?= htmlspecialchars($op['api_title'] ?? 'BILOHASH AI API') ?></h2>
                <span class="adm-badge adm-badge-green"><?= htmlspecialchars($op['api_unlimited_badge'] ?? 'Unlimited') ?></span>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($op['api_hint'] ?? 'Owner account — real AI only, no demo fallbacks. Configure API key in Settings → AI.') ?></p>
                <p class="adm-muted adm-my-api-note"><?= htmlspecialchars($op['api_owner_note'] ?? 'Production plans include monthly API quotas per subscription tier (see plans below).') ?></p>
            </div>
        </div>

        <div class="adm-card adm-my-sub-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-globe"></i> <?= htmlspecialchars($op['domain_title'] ?? 'Domain & license') ?></h2>
                <span class="adm-badge <?= htmlspecialchars($statusBadge) ?>"><?= htmlspecialchars($statusLabel) ?></span>
            </div>
            <div class="adm-card-body padded">
                <dl class="adm-sec-dl adm-owner-domain-dl">
                    <div class="adm-sec-dl-row">
                        <dt><?= htmlspecialchars($op['domain_current'] ?? 'Current site') ?></dt>
                        <dd><code><?= htmlspecialchars($host) ?></code></dd>
                    </div>
                    <?php if ($licensedDomain !== '' && $licensedDomain !== $host): ?>
                    <div class="adm-sec-dl-row">
                        <dt><?= htmlspecialchars($op['domain_licensed'] ?? 'Licensed domain') ?></dt>
                        <dd><code><?= htmlspecialchars($licensedDomain) ?></code></dd>
                    </div>
                    <?php endif; ?>
                    <div class="adm-sec-dl-row">
                        <dt><?= htmlspecialchars($op['domain_manage'] ?? 'Manage domains') ?></dt>
                        <dd>
                            <a href="<?= htmlspecialchars($cabinetUrl) ?>" <?= sh_subscription_external_attrs() ?>>
                                <?= htmlspecialchars($op['domain_cabinet'] ?? 'Customer cabinet → downloads & domains') ?>
                            </a>
                        </dd>
                    </div>
                </dl>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($op['domain_help'] ?? 'One domain per script plan. Full library plan covers all CMS scripts on one domain.') ?></p>
            </div>
        </div>
    </div>

    <div class="adm-card adm-my-sub-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-receipt"></i> <?= htmlspecialchars($op['plans_title'] ?? ($lp['plans_title'] ?? 'Subscription plans')) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($subHelp) ?></p>
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
            <div class="adm-my-sub-cta">
                <a href="<?= htmlspecialchars($subUrl) ?>" class="adm-btn adm-btn-primary" <?= sh_subscription_external_attrs() ?>>
                    <i class="fas fa-arrow-up-right-from-square"></i> <?= htmlspecialchars($op['subscribe_btn'] ?? 'Get subscription') ?>
                </a>
                <a href="<?= htmlspecialchars($cabinetUrl) ?>" class="adm-btn adm-btn-outline" <?= sh_subscription_external_attrs() ?>>
                    <i class="fas fa-door-open"></i> <?= htmlspecialchars($op['cabinet_btn'] ?? 'Customer cabinet') ?>
                </a>
            </div>
            <p class="adm-help adm-help-compact" style="margin-top:12px"><?= htmlspecialchars($lp['cabinet_help'] ?? 'After subscribing, open your customer cabinet on bilohash.com to download CMS packages for your domain.') ?></p>
        </div>
    </div>

    <div class="adm-my-split">
        <div class="adm-card">
            <div class="adm-card-head"><h2><i class="fas fa-list-check"></i> <?= htmlspecialchars($op['can_title'] ?? 'Owner access') ?></h2></div>
            <div class="adm-card-body padded">
                <ul class="adm-help-list">
                    <?php foreach (($op['can_items'] ?? []) as $item): ?>
                    <li><i class="fas fa-check text-green"></i> <?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="adm-card">
            <div class="adm-card-head"><h2><i class="fas fa-key"></i> <?= htmlspecialchars($op['license_quick_title'] ?? 'License key') ?></h2></div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($op['license_quick_help'] ?? 'Activate BHSHOP.… key after subscribing. Unified license works across all BILOHASH CMS scripts.') ?></p>
                <a href="<?= sh_admin_url('license.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm">
                    <i class="fas fa-unlock"></i> <?= htmlspecialchars($op['license_btn'] ?? 'Activate license') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>