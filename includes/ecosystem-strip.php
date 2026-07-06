<?php
/** Bilohash ecosystem product cards — expects $t['ecosystem'] */
if (empty($t['ecosystem']['items'])) {
    return;
}
$eco = $t['ecosystem'];
$items = $eco['items'];
$visible = array_slice($items, 0, 3);
$hidden  = array_slice($items, 3);
$more_n  = count($hidden);
$ft = $t['footer'] ?? [];
$more_label = sprintf($ft['eco_show_more'] ?? 'Show more (%d)', $more_n);
$less_label = $ft['eco_show_less'] ?? 'Show less';
$list_id = 'shEcoStripMore';

$render_card = static function (array $item, array $eco): void { ?>
    <article class="sh-ecosystem-card">
        <div class="sh-ecosystem-icon">
            <?php if (($item['icon'] ?? '') === 'wordpress'): ?>
            <i class="fab fa-wordpress" aria-hidden="true"></i>
            <?php else: ?>
            <i class="fas fa-<?= htmlspecialchars($item['icon']) ?>" aria-hidden="true"></i>
            <?php endif; ?>
        </div>
        <h3><?= htmlspecialchars($item['name']) ?></h3>
        <p><?= htmlspecialchars($item['desc']) ?></p>
        <div class="sh-ecosystem-links">
            <a href="<?= htmlspecialchars($item['url']) ?>" class="sh-btn-outline-dark sh-btn-sm" rel="related"><?= htmlspecialchars($eco['product_btn']) ?></a>
            <a href="<?= htmlspecialchars($item['demo']) ?>" class="sh-btn-outline sh-btn-sm" rel="related"><?= htmlspecialchars($eco['demo_btn']) ?></a>
        </div>
    </article>
<?php };
?>
<section class="sh-ecosystem-section" aria-labelledby="shEcoStripTitle">
    <div class="sh-container">
        <div class="sh-ecosystem-head">
            <h2 class="sh-ecosystem-title" id="shEcoStripTitle">
                <i class="fas fa-layer-group" aria-hidden="true"></i>
                <?= htmlspecialchars($eco['title']) ?>
            </h2>
            <?php if (!empty($eco['subtitle'])): ?>
            <p class="sh-ecosystem-sub"><?= htmlspecialchars($eco['subtitle']) ?></p>
            <?php endif; ?>
        </div>
        <div class="sh-ecosystem-grid">
            <?php foreach ($visible as $item) { $render_card($item, $eco); } ?>
        </div>
        <?php if ($more_n > 0): ?>
        <div
            class="sh-ecosystem-grid sh-ecosystem-more"
            id="<?= htmlspecialchars($list_id) ?>"
            data-eco-more-list
            hidden
            aria-hidden="true"
        >
            <?php foreach ($hidden as $item) { $render_card($item, $eco); } ?>
        </div>
        <div class="sh-ecosystem-more-actions">
            <button
                type="button"
                class="sh-ecosystem-more-btn"
                data-eco-more-btn
                aria-expanded="false"
                aria-controls="<?= htmlspecialchars($list_id) ?>"
                data-label-more="<?= htmlspecialchars($more_label) ?>"
                data-label-less="<?= htmlspecialchars($less_label) ?>"
            ><?= htmlspecialchars($more_label) ?></button>
        </div>
        <?php endif; ?>
    </div>
</section>