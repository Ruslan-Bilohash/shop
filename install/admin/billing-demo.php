<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/billing-demo.php';
require_once dirname(__DIR__) . '/includes/billing-demo-stats.php';
require_once dirname(__DIR__) . '/includes/admin-api-usage.php';

$admin_page = 'billing-demo';
$ta = $t['admin'] ?? [];
$bp = $ta['billing_demo_page'] ?? [];
$st = $bp['stats'] ?? [];
$page_title = $bp['title'] ?? 'Billing demo';

$pricing = sh_billing_pricing_for_lang($lang);
$state = sh_billing_demo_load();
$isDemoStaff = sh_admin_is_demo_user();
$activePlan = (string) ($state['plan'] ?? '');
$apiMonthly = (int) ($pricing['api_requests_monthly'] ?? SH_BILLING_API_REQUESTS_MONTHLY);
$apiYearly = (int) ($pricing['api_requests_yearly'] ?? SH_BILLING_API_REQUESTS_YEARLY);

if ($isDemoStaff) {
    $limit = SH_ADMIN_DEMO_API_LIMIT;
    $remaining = sh_admin_api_remaining();
    $used = max(0, $limit - ($remaining >= 0 ? $remaining : $limit));
    $showApi = true;
} else {
    $used = (int) ($state['api_requests_used'] ?? 0);
    $limit = (int) ($state['api_requests_limit'] ?? 0);
    $showApi = $activePlan !== '' && $limit > 0;
}
$pct = $limit > 0 ? min(100, (int) round($used / $limit * 100)) : 0;

sh_demo_stats_record('billing_page', [
    'lang' => $lang,
    'user' => (string) ($_SESSION['sh_admin_user'] ?? ''),
    'role' => function_exists('sh_admin_role') ? sh_admin_role() : '',
]);

$stats = sh_demo_stats_summary();
$fxNote = '';
if (!empty($pricing['fx_updated'])) {
    $fxNote = strtr($bp['fx_note'] ?? 'FX rate updated {date} ({source})', [
        '{date}'   => date('Y-m-d H:i', strtotime((string) $pricing['fx_updated'])),
        '{source}' => (string) ($pricing['fx_source'] ?? ''),
    ]);
}

$admin_extra_js = [sh_asset('js/admin-billing-demo.js') . '?v=5'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-bd-page" id="shBillingDemo"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-api="<?= htmlspecialchars(sh_admin_url('api/billing-demo.php')) ?>"
     data-demo-staff="<?= $isDemoStaff ? '1' : '0' ?>">

    <div class="adm-card">
        <div class="adm-card-body padded">
            <?php if ($isDemoStaff): ?>
            <?php $demoIntro = strtr($bp['intro_demo_staff'] ?? 'Demo user: {n} BILOHASH AI API test requests (demo + live). Subscription simulation is for the administrator account.', ['{n}' => (string) SH_ADMIN_DEMO_API_LIMIT]); ?>
            <p class="adm-help"><?= htmlspecialchars($demoIntro) ?></p>
            <?php else: ?>
            <p class="adm-help"><?= htmlspecialchars($bp['intro'] ?? 'Simulate Shop CMS subscription and BILOHASH AI API — no real payment.') ?></p>
            <?php endif; ?>
            <span class="adm-demo-pill"><i class="fas fa-flask"></i> <?= htmlspecialchars($bp['demo_badge'] ?? 'Demo payment') ?></span>
            <?php if ($fxNote !== ''): ?>
            <p class="adm-bd-fx"><i class="fas fa-exchange-alt"></i> <?= htmlspecialchars($fxNote) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="adm-bd-grid" <?= $isDemoStaff ? 'hidden' : '' ?>>
        <article class="adm-bd-plan <?= $activePlan === 'monthly' ? 'is-active' : '' ?>">
            <h3><?= htmlspecialchars($bp['monthly_title'] ?? 'Monthly') ?></h3>
            <p class="adm-bd-price"><?= htmlspecialchars($pricing['monthly_fmt']) ?><small>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></small></p>
            <p class="adm-bd-base"><?= htmlspecialchars(strtr($bp['base_price'] ?? 'Base: {nok}/mo', ['{nok}' => (string) $pricing['monthly_nok_fmt']])) ?></p>
            <ul class="adm-bd-features">
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_script_monthly'] ?? '1 CMS script · 1 domain') ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_price_monthly'] ?? '~{price}/mo (FX daily)', ['{price}' => (string) $pricing['monthly_fmt']])) ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $apiMonthly])) ?></li>
            </ul>
            <button type="button" class="adm-btn adm-btn-primary adm-bd-pay" data-plan="monthly" <?= $activePlan !== '' ? 'disabled' : '' ?>>
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($bp['pay_btn'] ?? 'Pay demo') ?>
            </button>
        </article>

        <article class="adm-bd-plan adm-bd-plan--yearly <?= $activePlan === 'yearly' ? 'is-active' : '' ?>">
            <span class="adm-bd-save"><?= htmlspecialchars($bp['yearly_badge'] ?? 'Best value') ?></span>
            <h3><?= htmlspecialchars($bp['yearly_title'] ?? 'All CMS scripts') ?></h3>
            <p class="adm-bd-price"><?= htmlspecialchars((string) ($pricing['full_monthly_fmt'] ?? $pricing['yearly_fmt'])) ?><small>/<?= htmlspecialchars($bp['per_month'] ?? 'mo') ?></small></p>
            <p class="adm-bd-base"><?= htmlspecialchars(strtr($bp['base_price_yr'] ?? 'Base: {nok}/mo', ['{nok}' => (string) ($pricing['yearly_nok_fmt'] ?? $pricing['monthly_nok_fmt'])])) ?></p>
            <ul class="adm-bd-features">
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_all_yearly'] ?? 'All CMS scripts · releases & updates') ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($bp['feat_domain'] ?? '1 domain per license') ?></li>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars(strtr($bp['feat_api'] ?? '{n} BILOHASH AI API requests', ['{n}' => (string) $apiYearly])) ?></li>
            </ul>
            <button type="button" class="adm-btn adm-btn-primary adm-bd-pay" data-plan="yearly" <?= $activePlan !== '' ? 'disabled' : '' ?>>
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($bp['pay_btn'] ?? 'Pay demo') ?>
            </button>
        </article>
    </div>

    <?php if ($isDemoStaff && $showApi): ?>
    <div class="adm-card adm-bd-status" id="shBillingStatus">
        <div class="adm-card-head">
            <h2><i class="fas fa-bolt"></i> <?= htmlspecialchars($bp['demo_api_title'] ?? 'Demo API quota') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-bd-active-plan"><?= htmlspecialchars(strtr($bp['demo_api_plan'] ?? 'Test plan: {n} API requests', ['{n}' => (string) $limit])) ?></p>
            <div class="adm-bd-api" id="shBillingApiBlock">
                <div class="adm-bd-api-head">
                    <strong><?= htmlspecialchars($bp['api_usage'] ?? 'BILOHASH AI API usage') ?></strong>
                    <span id="shBillingApiCount"><?= (int) $used ?> / <?= (int) $limit ?></span>
                </div>
                <div class="adm-bd-api-bar"><span id="shBillingApiBar" style="width:<?= $pct ?>%"></span></div>
                <button type="button" class="adm-btn adm-btn-sm adm-btn-outline" id="shBillingApiTest">
                    <i class="fas fa-bolt"></i> <?= htmlspecialchars($bp['api_test'] ?? 'Test API request') ?>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="adm-card adm-bd-status" id="shBillingStatusOwner" <?= ($isDemoStaff || $activePlan === '') ? 'hidden' : '' ?>>
        <div class="adm-card-head">
            <h2><i class="fas fa-receipt"></i> <?= htmlspecialchars($bp['status_title'] ?? 'Subscription status') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-bd-active-plan" id="shBillingPlanLabel">
                <?= $activePlan !== '' ? htmlspecialchars(strtr($bp['active_plan'] ?? 'Active: {plan}', ['{plan}' => $activePlan === 'yearly' ? ($bp['yearly_title'] ?? 'Yearly') : ($bp['monthly_title'] ?? 'Monthly')])) : '' ?>
            </p>
            <p class="adm-bd-ref" id="shBillingRef"><?= !empty($state['payment_ref']) ? htmlspecialchars(strtr($bp['payment_ref'] ?? 'Ref: {ref}', ['{ref}' => (string) $state['payment_ref']])) : '' ?></p>
            <div class="adm-bd-api" id="shBillingApiBlock" <?= $showApi ? '' : 'hidden' ?>>
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

    <div class="adm-card adm-bd-stats">
        <div class="adm-card-head">
            <h2><i class="fas fa-chart-line"></i> <?= htmlspecialchars($st['title'] ?? 'Demo monitor') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-bd-stats-kpis">
                <div class="adm-bd-kpi">
                    <span class="adm-bd-kpi-val"><?= (int) $stats['total'] ?></span>
                    <span class="adm-bd-kpi-lbl"><?= htmlspecialchars($st['total_events'] ?? 'Events') ?></span>
                </div>
                <div class="adm-bd-kpi">
                    <span class="adm-bd-kpi-val"><?= (int) $stats['unique_ips'] ?></span>
                    <span class="adm-bd-kpi-lbl"><?= htmlspecialchars($st['unique_ips'] ?? 'Unique IPs') ?></span>
                </div>
                <div class="adm-bd-kpi adm-bd-kpi--wide">
                    <span class="adm-bd-kpi-lbl"><?= htmlspecialchars($st['top_countries'] ?? 'Top countries') ?></span>
                    <span class="adm-bd-kpi-tags">
                        <?php if (empty($stats['top_countries'])): ?>
                        <em><?= htmlspecialchars($st['no_data'] ?? 'No data yet') ?></em>
                        <?php else: ?>
                        <?php foreach ($stats['top_countries'] as $row): ?>
                        <span class="adm-bd-tag"><?= htmlspecialchars($row['code']) ?> <strong><?= (int) $row['count'] ?></strong></span>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <div class="adm-table-wrap">
                <table class="adm-table adm-bd-stats-table">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars($st['col_time'] ?? 'Time') ?></th>
                            <th><?= htmlspecialchars($st['col_action'] ?? 'Action') ?></th>
                            <th><?= htmlspecialchars($st['col_country'] ?? 'Country') ?></th>
                            <th><?= htmlspecialchars($st['col_ip'] ?? 'IP') ?></th>
                            <th><?= htmlspecialchars($st['col_user'] ?? 'User') ?></th>
                            <th><?= htmlspecialchars($st['col_lang'] ?? 'Lang') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['recent'])): ?>
                        <tr><td colspan="6" class="adm-muted"><?= htmlspecialchars($st['no_data'] ?? 'No data yet') ?></td></tr>
                        <?php else: ?>
                        <?php foreach ($stats['recent'] as $ev): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('m-d H:i', strtotime((string) ($ev['ts'] ?? 'now')))) ?></td>
                            <td><code><?= htmlspecialchars((string) ($ev['action'] ?? '')) ?></code></td>
                            <td><?= htmlspecialchars((string) ($ev['country'] ?? '—')) ?></td>
                            <td><code><?= htmlspecialchars((string) ($ev['ip'] ?? '')) ?></code></td>
                            <td><?= htmlspecialchars((string) ($ev['user'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars(strtoupper((string) ($ev['lang'] ?? ''))) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="adm-bd-msg" id="shBillingMsg" hidden></p>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>