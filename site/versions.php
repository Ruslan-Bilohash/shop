<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/changelog.php';

$v = $t['version'] ?? [];
$split = shs_changelog_split_releases();
$older = $split['older'];
$older_count = count($older);

$page_title = sprintf($v['older_page_title'] ?? 'Older versions (%d) — Shop CMS', $older_count);
$page_desc  = sprintf(
    $v['older_page_meta'] ?? 'Changelog for %d older Shop CMS releases before %s.',
    $older_count,
    sh_version_label()
);
$canonical  = $site_url . '/versions.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = shs_absolute_url($canonical);
$seo_schemas = shs_seo_schemas($canon_abs, $page_title, $page_desc);

require __DIR__ . '/includes/header.php';
?>

<section class="shs-section shs-versions-page">
    <div class="shs-container">
        <p class="shs-versions-back">
            <a href="<?= shs_url('index.php#features') ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> <?= htmlspecialchars($v['back_to_product'] ?? 'Back to product page') ?></a>
        </p>
        <div class="shs-section-head">
            <h1><?= htmlspecialchars(sprintf($v['older_page_h1'] ?? 'Older versions (%d)', $older_count)) ?></h1>
            <p class="shs-section-sub"><?= htmlspecialchars(sprintf($v['older_page_intro'] ?? 'Release notes for all versions before %s.', sh_version_label())) ?></p>
        </div>
        <?php if ($older !== []): ?>
        <ol class="shs-changelog-list shs-changelog-list--older shs-changelog-list--page">
            <?php foreach ($older as $rel) {
                shs_changelog_render_release($rel, $t, false);
            } ?>
        </ol>
        <?php else: ?>
        <p class="shs-lead"><?= htmlspecialchars($v['older_page_empty'] ?? 'No older releases published yet.') ?></p>
        <?php endif; ?>
        <p class="shs-versions-current">
            <?= htmlspecialchars($v['current'] ?? 'Current version') ?>:
            <a href="<?= shs_url('index.php#version') ?>"><strong><?= htmlspecialchars(sh_version_label()) ?></strong></a>
        </p>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>