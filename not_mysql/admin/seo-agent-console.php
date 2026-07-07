<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/seo-checklist.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';

$admin_page = 'seo-agent-console';
$ta = $t['admin'] ?? [];
$sp = $ta['seo_agent_page'] ?? [];
$page_title = $sp['title'] ?? 'AI SEO Agent';

$settings = sh_load_settings();
$pageLabels = $ta['seo_analysis_page']['page_labels'] ?? [];
$issueMap = $ta['seo_analysis_page']['issue_labels'] ?? [];
$pageRows = sh_seo_pages_audit($settings, $pageLabels, $lang);

$avgScore = $pageRows !== []
    ? (int) round(array_sum(array_column($pageRows, 'score')) / count($pageRows))
    : 0;
$weakCount = count(array_filter($pageRows, static fn($r) => ($r['score'] ?? 0) < 60));

$admin_extra_js = [sh_asset('js/admin-seo-agent.js') . '?v=2'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-sa-console" id="shSeoAgent"
     data-api="<?= htmlspecialchars(sh_admin_url('api/ai-seo-agent.php')) ?>"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-scanning="<?= htmlspecialchars($sp['scanning'] ?? 'Analyzing…') ?>"
     data-error="<?= htmlspecialchars($sp['error_generic'] ?? 'Request failed') ?>"
     data-demo="<?= htmlspecialchars($sp['demo_badge'] ?? 'Demo mode') ?>"
     data-updated="<?= htmlspecialchars($sp['updated_at'] ?? 'Just updated · {time}') ?>"
     data-scan-progress="<?= htmlspecialchars($sp['scan_progress'] ?? 'Scanning {current} of {total}…') ?>"
     data-scan-done="<?= htmlspecialchars($sp['scan_done'] ?? 'Batch scan complete') ?>">

    <div class="adm-sa-hero">
        <div class="adm-sa-hero-main">
            <span class="adm-sa-score adm-sa-score--<?= htmlspecialchars(sh_seo_score_grade_key($avgScore)) ?>">
                <?= $avgScore ?>
            </span>
            <div>
                <h2 class="adm-sa-hero-title"><?= htmlspecialchars($sp['score_title'] ?? 'Average page SEO') ?></h2>
                <p class="adm-sa-hero-text">
                    <?= htmlspecialchars(sprintf($sp['pages_count'] ?? '%d pages audited', count($pageRows))) ?>
                    · <?= htmlspecialchars(sprintf($sp['weak_count'] ?? '%d need work', $weakCount)) ?>
                </p>
            </div>
        </div>
        <div class="adm-sa-hero-actions">
            <p class="adm-sa-batch-progress" id="shSeoAgentProgress" hidden></p>
            <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shSeoAgentScanAll">
                <i class="fas fa-robot"></i> <?= htmlspecialchars($sp['scan_all'] ?? 'AI scan all weak pages') ?>
            </button>
            <a href="<?= htmlspecialchars(sh_admin_url('settings-seo-analysis.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-chart-simple"></i> <?= htmlspecialchars($sp['link_analysis'] ?? 'Full SEO analysis') ?>
            </a>
        </div>
    </div>

    <div class="adm-card adm-sa-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-lines"></i> <?= htmlspecialchars($sp['pages_title'] ?? 'Per-page AI audit') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($sp['pages_help'] ?? 'Click «AI suggest» on any page. The agent reviews meta, schema and selling copy — then proposes fixes.') ?></p>

            <div class="adm-sa-toolbar">
                <input type="search" id="shSeoAgentSearch" class="adm-input" placeholder="<?= htmlspecialchars($sp['search_ph'] ?? 'Filter pages…') ?>">
                <select id="shSeoAgentFilter" class="adm-select">
                    <option value=""><?= htmlspecialchars($sp['filter_all'] ?? 'All scores') ?></option>
                    <option value="poor"><?= htmlspecialchars($sp['filter_poor'] ?? 'Below 50') ?></option>
                    <option value="fair"><?= htmlspecialchars($sp['filter_fair'] ?? '50–74') ?></option>
                    <option value="good"><?= htmlspecialchars($sp['filter_good'] ?? '75+') ?></option>
                </select>
            </div>

            <div class="adm-sa-pages">
                <?php foreach ($pageRows as $row):
                    $gradeKey = sh_seo_score_grade_key((int) $row['score']);
                    $issuesText = sh_seo_analysis_issue_labels($row['issues'], $issueMap);
                    $pageJson = htmlspecialchars(json_encode([
                        'key'        => $row['key'],
                        'type'       => $row['type'],
                        'label'      => $row['label'],
                        'score'      => $row['score'],
                        'issues'     => $row['issues'],
                        'edit_url'   => $row['edit_url'],
                        'public_url' => $row['public_url'],
                    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                <article class="adm-sa-page" data-score="<?= (int) $row['score'] ?>" data-name="<?= htmlspecialchars(mb_strtolower($row['label'])) ?>">
                    <div class="adm-sa-page-head">
                        <div class="adm-sa-page-info">
                            <span class="adm-sa-type adm-sa-type--<?= htmlspecialchars($row['type']) ?>"><?= htmlspecialchars($row['type']) ?></span>
                            <strong><?= htmlspecialchars($row['label']) ?></strong>
                            <span class="adm-seo-score-pill adm-seo-score-pill--<?= htmlspecialchars($gradeKey) ?>"><?= (int) $row['score'] ?>/100</span>
                            <span class="adm-sa-updated-badge" hidden></span>
                        </div>
                        <div class="adm-sa-page-actions">
                            <a href="<?= htmlspecialchars($row['edit_url']) ?>" class="adm-btn adm-btn-outline adm-btn-xs">
                                <i class="fas fa-pen"></i> <?= htmlspecialchars($sp['edit'] ?? 'Edit') ?>
                            </a>
                            <a href="<?= htmlspecialchars($row['public_url']) ?>" class="adm-btn adm-btn-outline adm-btn-xs" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button type="button" class="adm-btn adm-btn-primary adm-btn-xs sh-sa-analyze" data-page="<?= $pageJson ?>">
                                <i class="fas fa-robot"></i> <?= htmlspecialchars($sp['ai_suggest'] ?? 'AI suggest') ?>
                            </button>
                        </div>
                    </div>
                    <?php if ($issuesText !== ''): ?>
                    <p class="adm-sa-issues"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($issuesText) ?></p>
                    <?php endif; ?>
                    <div class="adm-sa-result" hidden></div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>