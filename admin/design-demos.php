<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/design-themes.php';

$admin_page = 'design-demos';
$ta = $t['admin'] ?? [];
$dp = $ta['design_demos_page'] ?? [];
$page_title = $dp['title'] ?? 'Design demos';

$extra_css = [sh_asset('css/admin-design-demos.css') . '?v=2'];
$admin_extra_js = [sh_asset('js/admin-design-demos.js') . '?v=1'];

$demos = sh_design_themes($dp);

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-dd-page">
    <div class="adm-card">
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($dp['intro'] ?? 'Preview storefront moods. Apply colors in Settings → Appearance or Block builder.') ?></p>
        </div>
    </div>

    <div class="adm-dd-workspace">
        <aside class="adm-dd-sidebar">
            <label class="adm-dd-search-label" for="admDdSearch">
                <i class="fas fa-search"></i>
                <?= htmlspecialchars($dp['search_label'] ?? 'Search design') ?>
            </label>
            <input type="search" id="admDdSearch" class="adm-dd-search"
                   placeholder="<?= htmlspecialchars($dp['search_ph'] ?? 'minimal, dark, sale…') ?>"
                   autocomplete="off">

            <div class="adm-dd-grid adm-dd-list" id="admDdList">
                <?php foreach ($demos as $demo):
                    $searchHay = strtolower(implode(' ', array_merge(
                        [$demo['title'], $demo['desc'], $demo['id']],
                        $demo['tags'] ?? []
                    )));
                ?>
                <article class="adm-dd-card <?= htmlspecialchars($demo['class']) ?>"
                         data-search="<?= htmlspecialchars($searchHay) ?>"
                         data-theme-class="<?= htmlspecialchars($demo['class']) ?>"
                         data-title="<?= htmlspecialchars($demo['title']) ?>"
                         data-desc="<?= htmlspecialchars($demo['desc']) ?>"
                         role="button" tabindex="0">
                    <div class="adm-dd-preview adm-dd-preview--sm">
                        <div class="adm-dd-mock-header"><span></span><span></span><span></span></div>
                        <div class="adm-dd-mock-hero"></div>
                        <div class="adm-dd-mock-products">
                            <div></div><div></div><div></div>
                        </div>
                    </div>
                    <div class="adm-dd-meta">
                        <h3><?= htmlspecialchars($demo['title']) ?></h3>
                        <p><?= htmlspecialchars($demo['desc']) ?></p>
                        <div class="adm-dd-tags">
                            <?php foreach (array_slice($demo['tags'] ?? [], 0, 4) as $tag): ?>
                            <span class="adm-dd-tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </aside>

        <section class="adm-dd-stage">
            <div class="adm-dd-stage-head">
                <h2 id="admDdPreviewTitle"><?= htmlspecialchars($demos[0]['title'] ?? '') ?></h2>
                <p id="admDdPreviewDesc"><?= htmlspecialchars($demos[0]['desc'] ?? '') ?></p>
            </div>
            <div class="adm-dd-live-preview <?= htmlspecialchars($demos[0]['class'] ?? 'adm-dd--nordic') ?>" id="admDdPreview">
                <div class="adm-dd-live-chrome">
                    <span></span><span></span><span></span>
                    <em><?= htmlspecialchars($dp['preview_url'] ?? 'bilohash.com/shop') ?></em>
                </div>
                <div class="adm-dd-live-nav">
                    <span></span><span></span><span></span><span></span>
                </div>
                <div class="adm-dd-live-hero"></div>
                <div class="adm-dd-live-grid">
                    <div></div><div></div><div></div><div></div>
                </div>
            </div>
            <a href="<?= htmlspecialchars(sh_admin_url('settings-appearance.php')) ?>" class="adm-btn adm-btn-outline">
                <i class="fas fa-palette"></i> <?= htmlspecialchars($dp['apply'] ?? 'Use in Appearance') ?>
            </a>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>