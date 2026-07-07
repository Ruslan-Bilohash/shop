<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/site-health-console.php';
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/pagespeed-insights.php';

$admin_page = 'health-console';
$ta = $t['admin'] ?? [];
$hp = $ta['health_console_page'] ?? [];
$page_title = $hp['title'] ?? 'Site health';

$settings = sh_load_settings();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['save_pagespeed_key'])) {
    if (trim($_POST['pagespeed_api_key'] ?? '') !== '') {
        $settings['pagespeed_api_key'] = trim($_POST['pagespeed_api_key']);
    }
    if (sh_save_settings($settings)) {
        $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $hp['psi_key_saved'] ?? 'PageSpeed API key saved'];
    }
    header('Location: ' . sh_admin_url('health-console.php'));
    exit;
}
$labels = $hp['labels'] ?? [];
$report = sh_health_composite_report($settings, $labels, $lang, $ta);
$gradeLabels = $hp['grades'] ?? $ta['security_console_page']['grades'] ?? [];
$gradeKey = $report['grade']['key'] ?? 'fair';

$psiUrl = sh_psi_default_url();
$psi = sh_psi_get($psiUrl, 'mobile', false, [], $settings);
$psiLabels = $hp['pagespeed'] ?? [];

$admin_extra_js = [sh_asset('js/admin-health-console.js') . '?v=3'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-hc-console" id="shHealthConsole"
     data-trend="<?= htmlspecialchars(json_encode($report['trend'], JSON_UNESCAPED_UNICODE)) ?>"
     data-psi-api="<?= htmlspecialchars(sh_admin_url('api/pagespeed.php')) ?>"
     data-psi-url="<?= htmlspecialchars($psiUrl) ?>"
     data-psi-fetched="<?= htmlspecialchars($psiLabels['fetched'] ?? 'Updated: {time}') ?>"
     data-psi-badge-live="<?= htmlspecialchars($psiLabels['live_badge'] ?? 'Live PageSpeed') ?>"
     data-psi-badge-cached="<?= htmlspecialchars($psiLabels['cached_badge'] ?? 'Cached live result') ?>"
     data-psi-badge-needs-key="<?= htmlspecialchars($psiLabels['needs_key_badge'] ?? 'API key required') ?>"
     data-psi-badge-demo="<?= htmlspecialchars($psiLabels['demo_badge'] ?? 'Marketing demo scores') ?>">

    <div class="adm-hc-hero">
        <div class="adm-hc-hero-gauge">
            <?php $g = sh_health_gauge_dash($report['overall']); ?>
            <svg class="adm-hc-gauge-svg" viewBox="0 0 120 120" aria-hidden="true">
                <circle class="adm-hc-gauge-track" cx="60" cy="60" r="54" fill="none" stroke-width="10"/>
                <circle class="adm-hc-gauge-fill adm-hc-gauge-fill--<?= htmlspecialchars($gradeKey) ?>"
                        cx="60" cy="60" r="54" fill="none" stroke-width="10"
                        stroke-dasharray="<?= $g['circumference'] ?>"
                        stroke-dashoffset="<?= $g['offset'] ?>"
                        transform="rotate(-90 60 60)"/>
            </svg>
            <div class="adm-hc-gauge-center">
                <span class="adm-hc-gauge-score"><?= (int) $report['overall'] ?></span>
                <span class="adm-hc-gauge-label"><?= htmlspecialchars($hp['overall_label'] ?? 'Health') ?></span>
            </div>
        </div>
        <div class="adm-hc-hero-text">
            <h2><?= htmlspecialchars($hp['score_title'] ?? 'Site health score') ?></h2>
            <p class="adm-hc-grade adm-hc-grade--<?= htmlspecialchars($gradeKey) ?>">
                <?= htmlspecialchars($gradeLabels[$gradeKey] ?? $report['grade']['label'] ?? '') ?>
            </p>
            <p class="adm-muted"><?= htmlspecialchars($hp['hero_help'] ?? 'Composite score: SEO, security, content quality and conversion psychology.') ?></p>
            <div class="adm-hc-hero-links">
                <a href="<?= htmlspecialchars(sh_admin_url('seo-agent-console.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="fas fa-robot"></i> <?= htmlspecialchars($hp['link_seo_agent'] ?? 'AI SEO Agent') ?>
                </a>
                <a href="<?= htmlspecialchars(sh_admin_url('security-console.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="fas fa-shield-halved"></i> <?= htmlspecialchars($hp['link_security'] ?? 'Security') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="adm-hc-pillars">
        <?php
        $pillars = [
            ['key' => 'seo', 'icon' => 'magnifying-glass-chart', 'data' => $report['pillars']['seo']],
            ['key' => 'security', 'icon' => 'shield-halved', 'data' => $report['pillars']['security']],
            ['key' => 'content', 'icon' => 'file-lines', 'data' => $report['pillars']['content']],
            ['key' => 'conversion', 'icon' => 'bullseye', 'data' => $report['pillars']['conversion']],
        ];
        foreach ($pillars as $p):
            $pk = $p['key'];
            $pd = $p['data'];
            $ps = (int) ($pd['score'] ?? 0);
            $pg = $pd['grade']['key'] ?? sh_seo_score_grade_key($ps);
        ?>
        <div class="adm-hc-pillar adm-hc-pillar--<?= htmlspecialchars($pg) ?>">
            <div class="adm-hc-pillar-head">
                <i class="fas fa-<?= htmlspecialchars($p['icon']) ?>"></i>
                <span><?= htmlspecialchars($hp['pillar_' . $pk] ?? ucfirst($pk)) ?></span>
            </div>
            <div class="adm-hc-pillar-score"><?= $ps ?><small>/100</small></div>
            <div class="adm-hc-pillar-bar"><span style="width:<?= $ps ?>%"></span></div>
            <?php if ($pk === 'security' && ($pd['issues'] ?? 0) > 0): ?>
            <small class="adm-hc-pillar-meta"><?= htmlspecialchars(sprintf($hp['security_issues'] ?? '%d issues', $pd['issues'])) ?></small>
            <?php elseif ($pk === 'content' && ($pd['weak_count'] ?? 0) > 0): ?>
            <small class="adm-hc-pillar-meta"><?= htmlspecialchars(sprintf($hp['content_weak'] ?? '%d weak products', $pd['weak_count'])) ?></small>
            <?php elseif ($pk === 'seo'): ?>
            <small class="adm-hc-pillar-meta"><?= (int) ($pd['products'] ?? 0) ?> <?= htmlspecialchars($hp['seo_products'] ?? 'products') ?> · <?= (int) ($pd['pages'] ?? 0) ?> <?= htmlspecialchars($hp['seo_pages'] ?? 'pages') ?></small>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="adm-card adm-hc-psi" id="shHealthPsi">
        <div class="adm-card-head">
            <h2><i class="fas fa-gauge-high"></i> <?= htmlspecialchars($psiLabels['title'] ?? 'PageSpeed Insights') ?></h2>
            <div class="adm-hc-psi-actions">
                <select id="shPsiStrategy" class="adm-select adm-select-sm" aria-label="<?= htmlspecialchars($psiLabels['strategy'] ?? 'Strategy') ?>">
                    <option value="mobile"><?= htmlspecialchars($psiLabels['mobile'] ?? 'Mobile') ?></option>
                    <option value="desktop"><?= htmlspecialchars($psiLabels['desktop'] ?? 'Desktop') ?></option>
                </select>
                <button type="button" class="adm-btn adm-btn-sm adm-btn-outline" id="shPsiRefresh">
                    <i class="fas fa-rotate"></i> <?= htmlspecialchars($psiLabels['refresh'] ?? 'Refresh') ?>
                </button>
            </div>
        </div>
        <div class="adm-card-body padded">
            <div id="shPsiBadge">
            <?php if (!empty($psi['cached'])): ?>
            <span class="adm-badge adm-badge-info"><i class="fas fa-database"></i> <?= htmlspecialchars($psiLabels['cached_badge'] ?? 'Cached live result') ?></span>
            <?php elseif (!empty($psi['needs_key'])): ?>
            <span class="adm-badge adm-badge-warn"><i class="fas fa-key"></i> <?= htmlspecialchars($psiLabels['needs_key_badge'] ?? 'API key required') ?></span>
            <?php elseif ($psi['demo']): ?>
            <span class="adm-demo-pill"><i class="fas fa-flask"></i> <?= htmlspecialchars($psiLabels['demo_badge'] ?? 'Marketing demo scores') ?></span>
            <?php elseif ($psi['ok']): ?>
            <span class="adm-badge adm-badge-green"><i class="fas fa-signal"></i> <?= htmlspecialchars($psiLabels['live_badge'] ?? 'Live PageSpeed') ?></span>
            <?php endif; ?>
            </div>
            <form method="post" class="adm-hc-psi-key-form" style="margin:12px 0">
                <input type="hidden" name="save_pagespeed_key" value="1">
                <label class="adm-muted" style="font-size:12px;display:block;margin-bottom:4px"><?= htmlspecialchars($psiLabels['api_key_label'] ?? 'Google PageSpeed API key') ?></label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <input type="password" name="pagespeed_api_key" class="adm-input" style="flex:1;min-width:200px" placeholder="<?= !empty($settings['pagespeed_api_key']) ? '••••••••' : 'AIza…' ?>" autocomplete="off">
                    <button type="submit" class="adm-btn adm-btn-sm adm-btn-outline"><i class="fas fa-save"></i> <?= htmlspecialchars($psiLabels['api_key_save'] ?? 'Save key') ?></button>
                </div>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($psiLabels['api_key_help'] ?? 'Create a free key in Google Cloud Console → PageSpeed Insights API. Required for real Lighthouse scores (no fake demo).') ?></p>
            </form>
            <p class="adm-muted adm-hc-psi-url"><code><?= htmlspecialchars($psi['url']) ?></code></p>
            <p class="adm-login-error" id="shPsiError" style="margin-bottom:12px;<?= (!$psi['ok'] && ($psi['error'] ?? '') !== '') ? '' : 'display:none' ?>"><?= htmlspecialchars($psi['error'] ?? '') ?></p>
            <div class="adm-hc-psi-grid" id="shPsiScores">
                <?php foreach ($psi['scores'] as $sc):
                    $ring = min(100, max(0, (int) ($sc['value'] ?? 0)));
                ?>
                <div class="adm-hc-psi-card <?= !empty($sc['highlight']) ? 'is-highlight' : '' ?>">
                    <div class="adm-hc-psi-ring" style="--adm-psi:<?= $ring ?>"><span><?= $ring ?></span></div>
                    <strong><?= htmlspecialchars($sc['label']) ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="adm-hc-psi-meta" id="shPsiMeta">
                <?= $psi['fetched_at'] !== '' ? htmlspecialchars(strtr($psiLabels['fetched'] ?? 'Updated: {time}', ['{time}' => $psi['fetched_at']])) : '' ?>
            </p>
        </div>
    </div>

    <div class="adm-hc-grid">
        <div class="adm-card adm-hc-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-chart-line"></i> <?= htmlspecialchars($hp['trend_title'] ?? '14-day trend') ?></h2>
                <span class="adm-badge adm-badge-info"><?= htmlspecialchars($hp['trend_demo'] ?? 'Live + demo blend') ?></span>
            </div>
            <div class="adm-card-body padded">
                <canvas id="shHealthTrendChart" class="adm-hc-chart" height="200" aria-label="<?= htmlspecialchars($hp['trend_title'] ?? 'Trend chart') ?>"></canvas>
                <div class="adm-hc-chart-legend">
                    <span><i class="adm-hc-dot adm-hc-dot--overall"></i> <?= htmlspecialchars($hp['legend_overall'] ?? 'Overall') ?></span>
                    <span><i class="adm-hc-dot adm-hc-dot--seo"></i> SEO</span>
                    <span><i class="adm-hc-dot adm-hc-dot--security"></i> <?= htmlspecialchars($hp['legend_security'] ?? 'Security') ?></span>
                    <span><i class="adm-hc-dot adm-hc-dot--content"></i> <?= htmlspecialchars($hp['legend_content'] ?? 'Content') ?></span>
                    <span><i class="adm-hc-dot adm-hc-dot--conversion"></i> <?= htmlspecialchars($hp['legend_conversion'] ?? 'Conversion') ?></span>
                </div>
            </div>
        </div>

        <div class="adm-card adm-hc-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-radar"></i> <?= htmlspecialchars($hp['radar_title'] ?? 'Pillar balance') ?></h2>
            </div>
            <div class="adm-card-body padded adm-hc-radar-wrap">
                <canvas id="shHealthRadarChart" class="adm-hc-chart adm-hc-chart--radar" height="220" aria-label="<?= htmlspecialchars($hp['radar_title'] ?? 'Radar') ?>"></canvas>
            </div>
        </div>
    </div>

    <?php if (($report['pillars']['conversion']['items'] ?? []) !== []): ?>
    <div class="adm-card adm-hc-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-bullseye"></i> <?= htmlspecialchars($hp['conversion_title'] ?? 'Conversion psychology') ?></h2>
            <span class="adm-badge adm-badge--<?= ($report['pillars']['conversion']['score'] ?? 0) >= 70 ? 'green' : 'orange' ?>">
                <?= (int) ($report['pillars']['conversion']['score'] ?? 0) ?>/100
            </span>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($hp['conversion_help'] ?? 'Trust signals, urgency, social proof and friction — what makes visitors buy.') ?></p>
            <ul class="adm-hc-checklist">
                <?php foreach ($report['pillars']['conversion']['items'] as $item):
                    $st = $item['status'] ?? 'bad';
                ?>
                <li class="adm-hc-check adm-hc-check--<?= htmlspecialchars($st) ?>">
                    <i class="fas fa-<?= $st === 'good' ? 'check-circle' : ($st === 'warn' ? 'circle-exclamation' : 'circle-xmark') ?>"></i>
                    <div>
                        <strong><?= htmlspecialchars($item['label']) ?></strong>
                        <?php if (!empty($item['hint'])): ?>
                        <p><?= htmlspecialchars($item['hint']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="adm-card adm-hc-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($hp['recs_title'] ?? 'Priority actions') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <ul class="adm-hc-recs">
                <?php foreach ($report['recommendations'] as $rec): ?>
                <li class="adm-hc-rec adm-hc-rec--<?= htmlspecialchars($rec['priority']) ?>">
                    <div class="adm-hc-rec-body">
                        <strong><?= htmlspecialchars($rec['title']) ?></strong>
                        <p><?= htmlspecialchars($rec['detail']) ?></p>
                    </div>
                    <?php if (!empty($rec['url'])): ?>
                    <a href="<?= htmlspecialchars($rec['url']) ?>" class="adm-health-link">
                        <?= htmlspecialchars($hp['rec_fix'] ?? 'Open') ?> <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>