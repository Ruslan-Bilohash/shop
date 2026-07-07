<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/changelog-console.php';

$admin_page = 'changelog-console';
$ta = $t['admin'] ?? [];
$cp = $ta['changelog_console_page'] ?? [];
$page_title = $cp['title'] ?? 'Changelog';

$query = trim((string) ($_GET['q'] ?? ''));
$releases = sh_changelog_search($query);
$stats = sh_changelog_admin_stats();

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-cl-console">
    <div class="adm-cl-hero">
        <div class="adm-cl-hero-main">
            <span class="adm-cl-version">v<?= htmlspecialchars($stats['current_version']) ?></span>
            <div>
                <h2><?= htmlspecialchars($cp['hero_title'] ?? 'Release history') ?></h2>
                <p class="adm-muted">
                    <?= htmlspecialchars(sprintf($cp['hero_stats'] ?? '%d releases · %d changelog items', $stats['total_releases'], $stats['total_items'])) ?>
                    · <?= htmlspecialchars($cp['sync_note'] ?? 'Synced with product site /shop/site/') ?>
                </p>
            </div>
        </div>
        <form method="get" class="adm-cl-search">
            <input type="search" name="q" value="<?= htmlspecialchars($query) ?>" class="adm-input" placeholder="<?= htmlspecialchars($cp['search_ph'] ?? 'Search versions or changes…') ?>">
            <button type="submit" class="adm-btn adm-btn-outline adm-btn-sm"><i class="fas fa-search"></i></button>
            <?php if ($query !== ''): ?>
            <a href="<?= htmlspecialchars(sh_admin_url('changelog-console.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm"><?= htmlspecialchars($cp['clear'] ?? 'Clear') ?></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="adm-cl-timeline">
        <?php foreach ($releases as $rel): ?>
        <article class="adm-cl-release<?= !empty($rel['is_current']) ? ' is-current' : '' ?>">
            <div class="adm-cl-release-head">
                <div class="adm-cl-release-badge">
                    <strong>v<?= htmlspecialchars($rel['version']) ?></strong>
                    <?php if (!empty($rel['is_current'])): ?>
                    <span class="adm-badge adm-badge-green"><?= htmlspecialchars($cp['current_badge'] ?? 'Current') ?></span>
                    <?php endif; ?>
                </div>
                <time datetime="<?= htmlspecialchars($rel['date']) ?>"><?= htmlspecialchars($rel['date']) ?></time>
            </div>
            <?php if ($rel['items'] !== []): ?>
            <ul class="adm-cl-items">
                <?php foreach ($rel['items'] as $item): ?>
                <li><i class="fas fa-circle-check"></i> <?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="adm-muted adm-cl-empty"><?= htmlspecialchars($cp['no_items'] ?? 'No detailed notes for this release.') ?></p>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if ($releases === []): ?>
    <div class="adm-card">
        <div class="adm-card-body padded">
            <p class="adm-muted"><?= htmlspecialchars($cp['no_results'] ?? 'No releases match your search.') ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="adm-cl-foot">
        <a href="<?= htmlspecialchars(sh_url('site/versions.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
            <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($cp['view_public'] ?? 'Public changelog') ?>
        </a>
        <a href="<?= htmlspecialchars(sh_admin_url('health-console.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
            <i class="fas fa-heart-pulse"></i> <?= htmlspecialchars($cp['link_health'] ?? 'Site health') ?>
        </a>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>