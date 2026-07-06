<?php
/**
 * Screenshot gallery fragment — loaded lazily via screenshots-carousel.php
 */
require_once dirname(__DIR__, 2) . '/includes/product-screenshots.php';

$ss = $t['screenshots'] ?? [];
$groups = $ss['groups'] ?? [];
$items_meta = $ss['items'] ?? [];
$manifest = array_values(array_filter(
    sh_product_screenshot_manifest(),
    static fn(array $row): bool => sh_product_screenshot_exists($row['file'] ?? '')
));
$by_group = [];
$lightbox_items = [];
foreach ($manifest as $row) {
    $g = $row['group'];
    $by_group[$g][] = $row;
    $id = $row['id'];
    $meta = $items_meta[$id] ?? [];
    $lightbox_items[] = [
        'url'     => shs_product_screenshot_url($row['file']),
        'caption' => $meta['caption'] ?? $id,
        'alt'     => $meta['alt'] ?? ($meta['caption'] ?? $id),
        'group'   => $groups[$g] ?? $g,
    ];
}
$lb = [
    'prev'    => $ss['lightbox_prev'] ?? 'Previous',
    'next'    => $ss['lightbox_next'] ?? 'Next',
    'close'   => $ss['lightbox_close'] ?? 'Close',
    'counter' => $ss['lightbox_counter'] ?? '%1$d / %2$d',
];
?>
<?php if (!empty($t['admin_panel']['groups'])): ?>
<div class="shs-admin-overview">
    <h3 class="shs-subtitle"><?= htmlspecialchars($t['admin_panel']['title'] ?? 'Admin panel') ?></h3>
    <?php if (!empty($t['admin_panel']['intro'])): ?>
    <p class="shs-muted-block"><?= htmlspecialchars($t['admin_panel']['intro']) ?></p>
    <?php endif; ?>
    <div class="shs-admin-groups">
        <?php foreach ($t['admin_panel']['groups'] as $grp): ?>
        <article class="shs-admin-group-card">
            <div class="shs-admin-group-head">
                <i class="fas fa-<?= htmlspecialchars($grp['icon'] ?? 'folder') ?>" aria-hidden="true"></i>
                <h4><?= htmlspecialchars($grp['title'] ?? '') ?></h4>
            </div>
            <ul>
                <?php foreach ($grp['items'] ?? [] as $line): ?>
                <li><?= htmlspecialchars($line) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
$global_idx = 0;
foreach ($by_group as $groupKey => $rows):
    if ($rows === []) {
        continue;
    }
    $groupLabel = $groups[$groupKey] ?? $groupKey;
?>
<div class="shs-screenshot-group">
    <h3 class="shs-screenshot-group-title"><i class="fas fa-images" aria-hidden="true"></i> <?= htmlspecialchars($groupLabel) ?></h3>
    <div class="shs-screenshot-grid">
        <?php foreach ($rows as $row):
            $id = $row['id'];
            $meta = $items_meta[$id] ?? [];
            $caption = $meta['caption'] ?? $id;
            $alt = $meta['alt'] ?? $caption;
            $url = shs_product_screenshot_url($row['file']);
            $idx = $global_idx++;
        ?>
        <figure class="shs-screenshot-card">
            <button type="button" class="shs-screenshot-link" data-shs-lightbox="<?= (int) $idx ?>" aria-label="<?= htmlspecialchars($caption) ?>">
                <img
                    src="<?= htmlspecialchars($url) ?>"
                    alt="<?= htmlspecialchars($alt) ?>"
                    loading="lazy"
                    decoding="async"
                    width="1400"
                    height="788"
                >
            </button>
            <figcaption><?= htmlspecialchars($caption) ?></figcaption>
        </figure>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if ($lightbox_items !== []): ?>
<div class="shs-lightbox" id="shsLightbox" hidden aria-hidden="true">
    <div class="shs-lightbox-backdrop" data-shs-lightbox-close></div>
    <div class="shs-lightbox-dialog" role="dialog" aria-modal="true" aria-labelledby="shsLightboxCaption">
        <button type="button" class="shs-lightbox-close" data-shs-lightbox-close aria-label="<?= htmlspecialchars($lb['close']) ?>">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
        <button type="button" class="shs-lightbox-nav shs-lightbox-prev" id="shsLightboxPrev" aria-label="<?= htmlspecialchars($lb['prev']) ?>">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="shs-lightbox-nav shs-lightbox-next" id="shsLightboxNext" aria-label="<?= htmlspecialchars($lb['next']) ?>">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
        <div class="shs-lightbox-stage">
            <img id="shsLightboxImg" src="" alt="" decoding="async">
        </div>
        <div class="shs-lightbox-meta">
            <p class="shs-lightbox-caption" id="shsLightboxCaption"></p>
            <p class="shs-lightbox-counter" id="shsLightboxCounter" data-format="<?= htmlspecialchars($lb['counter']) ?>"></p>
        </div>
    </div>
</div>
<script type="application/json" id="shsLightboxData"><?= json_encode($lightbox_items, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>