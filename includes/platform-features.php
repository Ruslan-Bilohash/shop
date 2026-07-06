<?php
/** Platform features grid — expects $t['platform_features'] */
if (empty($t['platform_features']['items'])) {
    return;
}
$pf = $t['platform_features'];
?>
<section class="sh-platform-section">
    <div class="sh-container">
        <div class="sh-section-head sh-section-head-center">
            <div>
                <h2 class="sh-section-title"><?= htmlspecialchars($pf['title']) ?></h2>
                <p class="sh-section-sub"><?= htmlspecialchars($pf['subtitle']) ?></p>
            </div>
        </div>
        <div class="sh-platform-grid">
            <?php foreach ($pf['items'] as $item): ?>
            <article class="sh-platform-card">
                <div class="sh-platform-icon"><i class="fas fa-<?= htmlspecialchars($item['icon']) ?>" aria-hidden="true"></i></div>
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p><?= htmlspecialchars($item['desc']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>