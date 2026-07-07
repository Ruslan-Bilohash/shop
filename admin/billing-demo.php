<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/billing-demo.php';

$admin_page = 'billing-demo';
$ta = $t['admin'] ?? [];
$bp = $ta['billing_demo_page'] ?? [];
$page_title = $bp['title'] ?? 'Billing demo';

$pricing = sh_billing_pricing_for_lang($lang);
$state = sh_billing_demo_load();
$activePlan = (string) ($state['plan'] ?? '');
$used = (int) ($state['api_requests_used'] ?? 0);
$limit = (int) ($state['api_requests_limit'] ?? SH_BILLING_DEMO_REQUESTS);
$pct = $limit > 0 ? min(100, (int) round($used / $limit * 100)) : 0;

$admin_extra_js = [sh_asset('js/admin-billing-demo.js') . '?v=1'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-bd-page" id="shBillingDemo"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-api="<?= htmlspecialchars(sh_admin_url('api/billing-demo.php')) ?>">

    <div class="adm-card">
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($bp['intro'] ?? 'Simulate Shop CMS subscription and BILOHASH AI API — no real payment.') ?></p>
            <span class="adm-demo-pill"><i class="fas fa-flask"></i> <?= htmlspecialchars($bp['demo_badge'] ?? 'Demo payment') ?></span>
        </div>
    </div>

    <div class="adm-bd-grid">
        <article class="adm-bd-plan <?= $activePlan === 'monthly' ? 'is-active' : '' ?>">
            <h3><?= htmlspecialchars($bp['monthly_title'] ?? 'Monthly') ?></h3>
            <p class="adm-bd-price"><?= htmlspecialchars($pricing['monthly_fmt']) ?><small>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></small></p>
            <p class="adm-bd-eur">EUR <?= (int) SH_BILLING_EUR_MONTHLY ?>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></p>
            <ul class="adm-bd-features">
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_cms'] ?? 'Shop CMS license') ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $limit])) ?></li>
            </ul>
            <button type="button" class="adm-btn adm-btn-primary adm-bd-pay" data-plan="monthly" <?= $activePlan !== '' ? 'disabled' : '' ?>>
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($bp['pay_btn'] ?? 'Pay demo') ?>
            </button>
        </article>

        <article class="adm-bd-plan adm-bd-plan--yearly <?= $activePlan === 'yearly' ? 'is-active' : '' ?>">
            <span class="adm-bd-save"><?= htmlspecialchars($bp['yearly_badge'] ?? 'Best value') ?></span>
            <h3><?= htmlspecialchars($bp['yearly_title'] ?? 'Yearly') ?></h3>
            <p class="adm-bd-price"><?= htmlspecialchars($pricing['yearly_fmt']) ?><small>/<?= htmlspecialchars($bp['per_year'] ?? 'yr') ?></small></p>
            <p class="adm-bd-eur">EUR <?= (int) SH_BILLING_EUR_YEARLY ?>/<?= htmlspecialchars($bp['per_year'] ?? 'yr') ?></p>
            <ul class="adm-bd-features">
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_cms'] ?? 'Shop CMS license') ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $limit])) ?></li>
            </ul>
            <button type="button" class="adm-btn adm-btn-primary adm-bd-pay" data-plan="yearly" <?= $activePlan !== '' ? 'disabled' : '' ?>>
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($bp['pay_btn'] ?? 'Pay demo') ?>
            </button>
        </article>
    </div>

    <div class="adm-card adm-bd-status" id="shBillingStatus" <?= $activePlan === '' ? 'hidden' : '' ?>>
        <div class="adm-card-head">
            <h2><i class="fas fa-receipt"></i> <?= htmlspecialchars($bp['status_title'] ?? 'Subscription status') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-bd-active-plan" id="shBillingPlanLabel">
                <?= $activePlan !== '' ? htmlspecialchars(strtr($bp['active_plan'] ?? 'Active: {plan}', ['{plan}' => $activePlan === 'yearly' ? ($bp['yearly_title'] ?? 'Yearly') : ($bp['monthly_title'] ?? 'Monthly')])) : '' ?>
            </p>
            <p class="adm-bd-ref" id="shBillingRef"><?= !empty($state['payment_ref']) ? htmlspecialchars(strtr($bp['payment_ref'] ?? 'Ref: {ref}', ['{ref}' => (string) $state['payment_ref']])) : '' ?></p>
            <div class="adm-bd-api">
                <div class="adm-bd-api-head">
                    <strong><?= htmlspecialchars($bp['api_usage'] ?? 'BILOHASH AI API usage') ?></strong>
                    <span id="shBillingApiCount"><?= (int) $used ?> / <?= (int) $limit ?></span>
                </div>
                <div class="adm-bd-api-bar"><span id="shBillingApiBar" style="width:<?= $pct ?>%"></span></div>
                <button type="button" class="adm-btn adm-btn-sm adm-btn-outline" id="shBillingApiTest">
                    <i class="fas fa-bolt"></i> <?= htmlspecialchars($bp['api_test'] ?? 'Test API request') ?>
                </button>
            </div>
            <button type="button" class="adm-btn adm-btn-outline adm-bd-cancel" id="shBillingCancel">
                <i class="fas fa-times"></i> <?= htmlspecialchars($bp['cancel_btn'] ?? 'Cancel demo subscription') ?>
            </button>
        </div>
    </div>

    <p class="adm-bd-msg" id="shBillingMsg" hidden></p>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>