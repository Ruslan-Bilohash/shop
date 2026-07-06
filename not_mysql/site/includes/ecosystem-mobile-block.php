<?php
$eco_items = $t['ecosystem']['items'] ?? [];
if ($eco_items === []) {
    return;
}
$eco_btn   = $t['ecosystem']['product_btn'] ?? 'Product page';
$eco_demo  = $t['ecosystem']['demo_btn'] ?? 'Live demo';
$eco_title = $t['ecosystem']['title'] ?? ($t['footer']['related_products'] ?? 'Other products');
?>
<details class="shs-mobile-eco" id="shsMobileEco">
    <summary class="shs-mobile-eco-summary">
        <span class="shs-mobile-eco-summary-label">
            <i class="fas fa-layer-group" aria-hidden="true"></i>
            <?= htmlspecialchars($eco_title) ?>
        </span>
        <i class="fas fa-chevron-down shs-mobile-eco-chevron" aria-hidden="true"></i>
    </summary>
    <ul class="shs-mobile-eco-list">
        <?php foreach ($eco_items as $eco): ?>
        <li class="shs-mobile-eco-row">
            <span class="shs-mobile-eco-name"><?= htmlspecialchars($eco['name']) ?></span>
            <span class="shs-mobile-eco-actions">
                <a href="<?= htmlspecialchars($eco['demo']) ?>" rel="related"><?= htmlspecialchars($eco_demo) ?></a>
                <span class="shs-mobile-eco-sep" aria-hidden="true">·</span>
                <a href="<?= htmlspecialchars($eco['url']) ?>" rel="related"><?= htmlspecialchars($eco_btn) ?></a>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>
</details>