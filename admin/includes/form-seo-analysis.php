<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/seo-checklist.php';
require_once dirname(__DIR__, 2) . '/includes/category-storage.php';
require_once __DIR__ . '/admin-field-help.php';

global $lang;
if (!is_string($lang ?? null) || $lang === '') {
    $lang = function_exists('sh_site_default_lang') ? sh_site_default_lang() : 'en';
}

$tab = 'seo_analysis';
$sections = sh_admin_settings_sections($tab, $ta);
$ap = $ta['seo_analysis_page'] ?? [];
$seoLabels = $ta['products_page']['seo_checklist'] ?? [];
$productRows = sh_seo_analysis_products($seoLabels, $lang, true);
$pageRows = sh_seo_pages_audit($settings, $ap['page_labels'] ?? [], $lang);
$issueMap = $ap['issue_labels'] ?? [];
$categories = sh_category_records(true);

$totalProducts = count($productRows);
$needsWork = count(array_filter($productRows, static fn(array $r): bool => ($r['score'] ?? 0) < 75));
$poorProducts = count(array_filter($productRows, static fn(array $r): bool => ($r['score'] ?? 0) < 50));
$avgScore = $totalProducts > 0
    ? (int) round(array_sum(array_column($productRows, 'score')) / $totalProducts)
    : 0;
$pagesNeedWork = count(array_filter($pageRows, static fn(array $r): bool => ($r['score'] ?? 0) < 75));
?>
<div class="adm-seo-analysis adm-seo-analysis-page" id="shSeoAnalysis"
     data-label-none="<?= htmlspecialchars($ap['filter_none'] ?? 'No products match filters.') ?>"
     data-label-count="<?= htmlspecialchars($ap['filter_count'] ?? 'Showing {n} of {total}') ?>">

    <div class="adm-seo-analysis-stats adm-stats adm-stats--dashboard">
        <div class="adm-stat">
            <div class="adm-stat-icon blue"><i class="fas fa-box"></i></div>
            <div>
                <div class="adm-stat-val"><?= $totalProducts ?></div>
                <div class="adm-stat-label"><?= htmlspecialchars($ap['stat_products'] ?? 'Active products') ?></div>
            </div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-icon orange"><i class="fas fa-triangle-exclamation"></i></div>
            <div>
                <div class="adm-stat-val"><?= $needsWork ?></div>
                <div class="adm-stat-label"><?= htmlspecialchars($ap['stat_needs_work'] ?? 'Need SEO work') ?></div>
                <div class="adm-stat-sub"><?= htmlspecialchars($ap['stat_needs_work_hint'] ?? 'Score below 75') ?></div>
            </div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-icon gold"><i class="fas fa-gauge-high"></i></div>
            <div>
                <div class="adm-stat-val"><?= $avgScore ?></div>
                <div class="adm-stat-label"><?= htmlspecialchars($ap['stat_avg_score'] ?? 'Average SEO score') ?></div>
            </div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-icon green"><i class="fas fa-file-lines"></i></div>
            <div>
                <div class="adm-stat-val"><?= $pagesNeedWork ?></div>
                <div class="adm-stat-label"><?= htmlspecialchars($ap['stat_pages'] ?? 'Pages to improve') ?></div>
                <div class="adm-stat-sub"><?= count($pageRows) ?> <?= htmlspecialchars($ap['stat_pages_total'] ?? 'total') ?></div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="seo-analysis-products">
        <div class="adm-card-head">
            <h2><i class="fas fa-box-open"></i> <?= htmlspecialchars($sections['seo-analysis-products'] ?? ($ap['products_section'] ?? 'Products to optimize')) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($ap['products_help'] ?? 'Filter products with weak meta titles, descriptions or schema. Open the editor to fix issues.') ?></p>

            <div class="adm-seo-analysis-toolbar">
                <div class="adm-field adm-field--compact">
                    <label for="shSeoFilterSearch"><?= htmlspecialchars($ap['filter_search'] ?? 'Search') ?></label>
                    <input type="search" id="shSeoFilterSearch" class="adm-input" placeholder="<?= htmlspecialchars($ap['filter_search_ph'] ?? 'Product name or ID…') ?>">
                </div>
                <div class="adm-field adm-field--compact">
                    <label for="shSeoFilterScore"><?= htmlspecialchars($ap['filter_score'] ?? 'SEO score') ?></label>
                    <select id="shSeoFilterScore" class="adm-select">
                        <option value=""><?= htmlspecialchars($ap['filter_score_all'] ?? 'All scores') ?></option>
                        <option value="poor"><?= htmlspecialchars($ap['filter_score_poor'] ?? 'Poor (&lt;50)') ?></option>
                        <option value="fair"><?= htmlspecialchars($ap['filter_score_fair'] ?? 'Needs work (&lt;75)') ?></option>
                        <option value="good"><?= htmlspecialchars($ap['filter_score_good'] ?? 'Good (75+)') ?></option>
                        <option value="excellent"><?= htmlspecialchars($ap['filter_score_excellent'] ?? 'Excellent (90+)') ?></option>
                    </select>
                </div>
                <div class="adm-field adm-field--compact">
                    <label for="shSeoFilterCategory"><?= htmlspecialchars($ap['filter_category'] ?? 'Category') ?></label>
                    <select id="shSeoFilterCategory" class="adm-select">
                        <option value=""><?= htmlspecialchars($ap['filter_category_all'] ?? 'All categories') ?></option>
                        <?php foreach ($categories as $cat):
                            $slug = (string) ($cat['slug'] ?? '');
                            if ($slug === '') {
                                continue;
                            }
                        ?>
                        <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars(sh_localized($cat, 'name', $lang)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field adm-field--compact">
                    <label for="shSeoFilterIssue"><?= htmlspecialchars($ap['filter_issue'] ?? 'Issue type') ?></label>
                    <select id="shSeoFilterIssue" class="adm-select">
                        <option value=""><?= htmlspecialchars($ap['filter_issue_all'] ?? 'Any issue') ?></option>
                        <?php foreach ($issueMap as $tag => $label): ?>
                        <option value="<?= htmlspecialchars($tag) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <p class="adm-seo-analysis-count" id="shSeoFilterCount" aria-live="polite"></p>

            <div class="adm-table-wrap">
                <table class="adm-table adm-table--cards adm-seo-analysis-table" id="shSeoProductTable">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars($ap['col_product'] ?? 'Product') ?></th>
                            <th><?= htmlspecialchars($ap['col_category'] ?? 'Category') ?></th>
                            <th><?= htmlspecialchars($ap['col_score'] ?? 'Score') ?></th>
                            <th><?= htmlspecialchars($ap['col_issues'] ?? 'Issues') ?></th>
                            <th><?= htmlspecialchars($ap['col_actions'] ?? 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productRows as $row):
                            $gradeKey = sh_seo_score_grade_key((int) $row['score']);
                            $issuesText = sh_seo_analysis_issue_labels($row['issues'], $issueMap);
                            if ($issuesText === '') {
                                $issuesText = $ap['no_issues'] ?? '—';
                            }
                        ?>
                        <tr class="adm-seo-product-row"
                            data-id="<?= htmlspecialchars($row['id']) ?>"
                            data-name="<?= htmlspecialchars(mb_strtolower($row['name'])) ?>"
                            data-category="<?= htmlspecialchars($row['category']) ?>"
                            data-score="<?= (int) $row['score'] ?>"
                            data-grade="<?= htmlspecialchars($gradeKey) ?>"
                            data-issues="<?= htmlspecialchars(implode(',', $row['issues'])) ?>">
                            <td data-label="<?= htmlspecialchars($ap['col_product'] ?? 'Product') ?>">
                                <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                                <code class="adm-muted-inline"><?= htmlspecialchars($row['id']) ?></code>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['col_category'] ?? 'Category') ?>">
                                <?= htmlspecialchars($row['category'] !== '' ? sh_category_label($row['category'], $lang) : ($ap['no_category'] ?? '—')) ?>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['col_score'] ?? 'Score') ?>">
                                <span class="adm-seo-score-pill adm-seo-score-pill--<?= htmlspecialchars($gradeKey) ?>">
                                    <?= (int) $row['score'] ?>/100
                                </span>
                                <small class="adm-muted adm-seo-grade-label"><?= htmlspecialchars($row['grade']['label'] ?? '') ?></small>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['col_issues'] ?? 'Issues') ?>">
                                <span class="adm-seo-issues"><?= htmlspecialchars($issuesText) ?></span>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['col_actions'] ?? 'Actions') ?>">
                                <a href="<?= htmlspecialchars(sh_admin_url('product-edit.php?id=' . urlencode($row['id']) . '#product-section-seo')) ?>"
                                   class="adm-btn adm-btn-outline adm-btn-sm">
                                    <i class="fas fa-pen"></i> <?= htmlspecialchars($ap['edit_seo'] ?? 'Edit SEO') ?>
                                </a>
                                <a href="<?= htmlspecialchars(sh_url('product.php?id=' . urlencode($row['id']))) ?>"
                                   class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="adm-help adm-help-compact adm-seo-analysis-empty" id="shSeoFilterEmpty" hidden>
                <i class="fas fa-filter"></i> <?= htmlspecialchars($ap['filter_none'] ?? 'No products match filters.') ?>
            </p>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="seo-analysis-pages">
        <div class="adm-card-head">
            <h2><i class="fas fa-clipboard-check"></i> <?= htmlspecialchars($sections['seo-analysis-pages'] ?? ($ap['pages_section'] ?? 'SEO pages checklist')) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($ap['pages_help'] ?? 'Audit global settings, categories and service pages. Fix weak meta fields before publishing.') ?></p>
            <div class="adm-table-wrap">
                <table class="adm-table adm-table--cards adm-seo-pages-table">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars($ap['page_col_name'] ?? 'Page') ?></th>
                            <th><?= htmlspecialchars($ap['page_col_type'] ?? 'Type') ?></th>
                            <th><?= htmlspecialchars($ap['page_col_score'] ?? 'Score') ?></th>
                            <th><?= htmlspecialchars($ap['page_col_issues'] ?? 'Issues') ?></th>
                            <th><?= htmlspecialchars($ap['page_col_actions'] ?? 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $typeLabels = $ap['page_types'] ?? [];
                        foreach ($pageRows as $page):
                            $gradeKey = sh_seo_score_grade_key((int) $page['score']);
                            $issuesText = sh_seo_analysis_issue_labels($page['issues'], $issueMap);
                            if ($issuesText === '') {
                                $issuesText = $ap['no_issues'] ?? '—';
                            }
                            $typeLabel = $typeLabels[$page['type']] ?? $page['type'];
                        ?>
                        <tr>
                            <td data-label="<?= htmlspecialchars($ap['page_col_name'] ?? 'Page') ?>">
                                <strong><?= htmlspecialchars($page['label']) ?></strong>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['page_col_type'] ?? 'Type') ?>">
                                <span class="adm-badge adm-badge--muted"><?= htmlspecialchars($typeLabel) ?></span>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['page_col_score'] ?? 'Score') ?>">
                                <span class="adm-seo-score-pill adm-seo-score-pill--<?= htmlspecialchars($gradeKey) ?>">
                                    <?= (int) $page['score'] ?>/100
                                </span>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['page_col_issues'] ?? 'Issues') ?>">
                                <?= htmlspecialchars($issuesText) ?>
                            </td>
                            <td data-label="<?= htmlspecialchars($ap['page_col_actions'] ?? 'Actions') ?>">
                                <a href="<?= htmlspecialchars($page['edit_url']) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                                    <i class="fas fa-pen"></i> <?= htmlspecialchars($ap['edit_page'] ?? 'Edit') ?>
                                </a>
                                <a href="<?= htmlspecialchars($page['public_url']) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>