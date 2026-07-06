<?php
/** Bilohash ecosystem product cards — expects $t['ecosystem'] */
if (empty($t['ecosystem']['items'])) {
    return;
}
$eco = $t['ecosystem'];
?>
<section class="sh-ecosystem-section">
    <div class="sh-container">
        <div class="sh-section-head sh-section-head-center">
            <div>
                <h2 class="sh-section-title"><?= htmlspecialchars($eco['title']) ?></h2>
                <p class="sh-section-sub"><?= htmlspecialchars($eco['subtitle']) ?></p>
            </div>
        </div>
        <div class="sh-ecosystem-grid">
            <?php foreach ($eco['items'] as $item): ?>
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
            <?php endforeach; ?>
        </div>
    </div>
</section>