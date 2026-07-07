<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

if (!sh_admin_is_demo_user()) {
    header('Location: ' . sh_admin_url('index.php'));
    exit;
}

require_once dirname(__DIR__) . '/includes/admin-api-usage.php';
require_once dirname(__DIR__) . '/includes/billing-pricing.php';
require_once dirname(__DIR__) . '/includes/subscription-links.php';

$admin_page = 'my';
$mp = $ta['demo_my_page'] ?? [];
$bp = $ta['billing_demo_page'] ?? [];
$page_title = $mp['title'] ?? 'My demo panel';

$apiLimit = sh_admin_api_limit();
$apiRemaining = sh_admin_api_remaining();
$apiUsed = max(0, $apiLimit - ($apiRemaining >= 0 ? $apiRemaining : $apiLimit));
$apiPct = $apiLimit > 0 ? min(100, (int) round($apiUsed / $apiLimit * 100)) : 0;

$pricing = sh_billing_pricing_for_lang($lang);
$apiMonthly = (int) ($pricing['api_requests_monthly'] ?? SH_BILLING_API_REQUESTS_MONTHLY);
$apiYearly = (int) ($pricing['api_requests_yearly'] ?? SH_BILLING_API_REQUESTS_YEARLY);
$subUrl = sh_subscription_url();
$tagline = sh_billing_subscription_tagline($lang);

$subHelp = (string) ($mp['subscription_help'] ?? 'One BILOHASH subscription for all CMS scripts and AI — {tagline}.');
if (str_contains($subHelp, '{tagline}')) {
    $subHelp = str_replace('{tagline}', $tagline, $subHelp);
}

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-my-console">
    <div class="adm-my-hero">
        <div class="adm-my-hero-main">
            <span class="adm-my-avatar" aria-hidden="true"><i class="fas fa-user-circle"></i></span>
            <div>
                <h2 class="adm-my-hero-title">
                    <i class="fas fa-id-badge"></i>
                    <?= htmlspecialchars($mp['hero_title'] ?? $mp['badge'] ?? 'Demo account') ?>
                </h2>
                <p class="adm-my-hero-text"><?= htmlspecialchars($mp['intro'] ?? 'Limited demo access — browse the shop, test AI tools and explore billing demo.') ?></p>
                <p class="adm-ai-hero-subscribe">
                    <a href="<?= htmlspecialchars($subUrl) ?>" class="adm-btn adm-btn-primary adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                        <i class="fas fa-crown"></i> <?= htmlspecialchars($mp['subscribe_btn'] ?? $mp['get_license'] ?? 'BILOHASH subscription') ?>
                    </a>
                    <span class="adm-muted adm-ai-hero-tagline"><?= htmlspecialchars($tagline) ?></span>
                </p>
            </div>
        </div>
        <div class="adm-my-hero-actions">
            <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
                <i class="fas fa-store"></i> <?= htmlspecialchars($mp['view_shop'] ?? 'View shop') ?>
            </a>
            <a href="<?= sh_url('site/') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($mp['view_product'] ?? 'Product page') ?>
            </a>
            <a href="<?= sh_admin_url('billing-demo.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($mp['billing_demo'] ?? 'Billing demo') ?>
            </a>
        </div>
    </div>

    <div class="adm-my-grid">
        <div class="adm-card adm-my-api-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-bolt"></i> <?= htmlspecialchars($mp['api_title'] ?? $mp['api_quota'] ?? 'AI API test quota') ?></h2>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($mp['api_hint'] ?? 'Shared across AI features in admin.') ?></p>
                <div class="adm-bd-api">
                    <div class="adm-bd-api-head">
                        <span><?= htmlspecialchars($mp['api_usage'] ?? 'Used') ?></span>
                        <span><?= (int) $apiUsed ?> / <?= (int) $apiLimit ?></span>
                    </div>
                    <div class="adm-bd-api-bar"><span style="width:<?= $apiPct ?>%"></span></div>
                </div>
                <p class="adm-muted adm-my-api-note"><?= htmlspecialchars($mp['api_test_hint'] ?? 'Demo quota — subscribe for production limits.') ?></p>
            </div>
        </div>

        <div class="adm-card adm-my-sub-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-crown"></i> <?= htmlspecialchars($mp['subscription_title'] ?? 'BILOHASH subscription') ?></h2>
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
                        <i class="fas fa-arrow-up-right-from-square"></i> <?= htmlspecialchars($mp['subscribe_btn'] ?? $mp['get_license'] ?? 'Get subscription') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-my-split">
        <div class="adm-card">
            <div class="adm-card-head"><h2><i class="fas fa-list-check"></i> <?= htmlspecialchars($mp['can_title'] ?? 'What you can do') ?></h2></div>
            <div class="adm-card-body padded">
                <ul class="adm-help-list">
                    <?php foreach (($mp['can_items'] ?? []) as $item): ?>
                    <li><i class="fas fa-check text-green"></i> <?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><h2><i class="fas fa-ban"></i> <?= htmlspecialchars($mp['cannot_title'] ?? 'Demo limitations') ?></h2></div>
            <div class="adm-card-body padded">
                <ul class="adm-help-list adm-help-list--muted">
                    <?php foreach (($mp['cannot_items'] ?? []) as $item): ?>
                    <li><i class="fas fa-minus-circle"></i> <?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>