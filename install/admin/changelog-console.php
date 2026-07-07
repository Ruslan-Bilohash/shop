<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/version.php';
require_once dirname(__DIR__) . '/includes/changelog-console.php';
require_once dirname(__DIR__) . '/includes/license-runtime.php';
require_once dirname(__DIR__) . '/includes/shop-updates.php';

$admin_page = 'changelog-console';
$ta = $t['admin'] ?? [];
$cp = $ta['changelog_console_page'] ?? [];
$page_title = $cp['title'] ?? 'Changelog';

$query = trim((string) ($_GET['q'] ?? ''));
$releases = sh_changelog_search($query);
$stats = sh_changelog_admin_stats();
$licenseGate = sh_license_can_check_updates();
$updateCheck = $licenseGate['allowed'] ? sh_update_check(false) : [
    'ok'               => false,
    'blocked'          => true,
    'license_reason'   => $licenseGate['reason'],
    'license_message'  => $licenseGate['message'],
    'current_version'  => sh_version(),
    'latest_version'   => sh_version(),
    'update_available' => false,
    'release_url'      => '',
    'release_name'     => '',
    'release_date'     => '',
    'checked_at'       => '',
    'cached'           => false,
    'error'            => $licenseGate['message'],
];
$up = $cp['update_check'] ?? [];

$admin_extra_js = [sh_asset('js/admin-changelog-console.js') . '?v=1'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-cl-console" id="shChangelogConsole"
     data-update-api="<?= htmlspecialchars(sh_admin_url('api/shop-update-check.php')) ?>"
     data-license-url="<?= htmlspecialchars(sh_admin_url('license.php')) ?>"
     data-label-checking="<?= htmlspecialchars($up['checking'] ?? 'Checking for updates…') ?>"
     data-label-up-to-date="<?= htmlspecialchars($up['up_to_date'] ?? 'You are on the latest version') ?>"
     data-label-available="<?= htmlspecialchars($up['available'] ?? 'Update available: v{version}') ?>"
     data-label-blocked="<?= htmlspecialchars($up['blocked'] ?? 'Update check blocked — license required') ?>"
     data-label-cached="<?= htmlspecialchars($up['cached_badge'] ?? 'Cached result') ?>"
     data-label-error="<?= htmlspecialchars($up['error'] ?? 'Could not check for updates') ?>"
     data-label-checked="<?= htmlspecialchars($up['checked_at'] ?? 'Checked: {time}') ?>">

    <div class="adm-card adm-cl-update" id="shUpdateCheckPanel">
        <div class="adm-card-head">
            <h2><i class="fas fa-cloud-arrow-down"></i> <?= htmlspecialchars($up['title'] ?? 'Check for updates') ?></h2>
            <?php if (!empty($updateCheck['cached'])): ?>
            <span class="adm-badge adm-badge-info" id="shUpdateCachedBadge"><?= htmlspecialchars($up['cached_badge'] ?? 'Cached result') ?></span>
            <?php else: ?>
            <span class="adm-badge adm-badge-info" id="shUpdateCachedBadge" style="display:none"><?= htmlspecialchars($up['cached_badge'] ?? 'Cached result') ?></span>
            <?php endif; ?>
        </div>
        <div class="adm-card-body padded">
            <?php if (!$licenseGate['allowed']): ?>
            <div class="adm-cl-update-blocked" id="shUpdateBlocked">
                <p class="adm-login-error"><i class="fas fa-lock"></i> <?= htmlspecialchars($licenseGate['message'] ?: ($up['blocked'] ?? 'Update check blocked — activate license')) ?></p>
                <a href="<?= htmlspecialchars(sh_admin_url('license.php')) ?>" class="adm-btn adm-btn-primary adm-btn-sm">
                    <i class="fas fa-key"></i> <?= htmlspecialchars($up['activate'] ?? 'Activate license') ?>
                </a>
            </div>
            <?php else: ?>
            <div class="adm-cl-update-status" id="shUpdateStatus">
                <p class="adm-cl-update-msg<?= ($updateCheck['ok'] && $updateCheck['update_available']) ? ' adm-cl-update-msg--warn' : (($updateCheck['ok']) ? ' adm-cl-update-msg--ok' : ' adm-cl-update-msg--err') ?>" id="shUpdateMsg">
                    <?php if ($updateCheck['ok'] && $updateCheck['update_available']): ?>
                    <i class="fas fa-arrow-circle-up"></i> <?= htmlspecialchars(strtr($up['available'] ?? 'Update available: v{version}', ['{version}' => $updateCheck['latest_version']])) ?>
                    <?php elseif ($updateCheck['ok']): ?>
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($up['up_to_date'] ?? 'You are on the latest version') ?>
                    <?php elseif (($updateCheck['error'] ?? '') !== ''): ?>
                    <i class="fas fa-circle-xmark"></i> <?= htmlspecialchars($updateCheck['error']) ?>
                    <?php endif; ?>
                </p>
                <p class="adm-muted adm-cl-update-versions">
                    <?= htmlspecialchars($up['current'] ?? 'Installed') ?>: <strong>v<?= htmlspecialchars($updateCheck['current_version']) ?></strong>
                    <?php if ($updateCheck['ok']): ?>
                    · <?= htmlspecialchars($up['latest'] ?? 'Latest') ?>: <strong id="shUpdateLatestVer">v<?= htmlspecialchars($updateCheck['latest_version']) ?></strong>
                    <?php endif; ?>
                </p>
                <a href="<?= htmlspecialchars($updateCheck['release_url'] ?: '#') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener" id="shUpdateReleaseLink"<?= ($updateCheck['ok'] && $updateCheck['release_url'] !== '') ? '' : ' style="display:none"' ?>>
                    <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($up['release_notes'] ?? 'Release on GitHub') ?>
                </a>
                <p class="adm-muted adm-cl-update-meta" id="shUpdateMeta">
                    <?= $updateCheck['checked_at'] !== '' ? htmlspecialchars(strtr($up['checked_at'] ?? 'Checked: {time}', ['{time}' => $updateCheck['checked_at']])) : '' ?>
                </p>
            </div>
            <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" id="shUpdateCheckBtn">
                <i class="fas fa-rotate"></i> <?= htmlspecialchars($up['btn_check'] ?? 'Check for updates') ?>
            </button>
            <?php endif; ?>
        </div>
    </div>

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