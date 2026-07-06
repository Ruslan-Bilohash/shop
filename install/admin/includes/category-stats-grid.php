<?php
/**
 * Responsive category product counts (replaces bar chart).
 * @var list<array{cat:string,count:int}> $chart
 * @var string $lang
 * @var array $tp optional labels from categories_page
 */
$tp = $tp ?? [];
$productsLabel = $tp['products'] ?? 'Products';
?>
<div class="adm-cat-stats-grid">
    <?php foreach ($chart as $row):
        $slug = (string) ($row['cat'] ?? '');
        if ($slug === '') {
            continue;
        }
        $cat = sh_category_by_slug($slug, false);
        $icon = $cat['icon'] ?? sh_category_icon($slug);
        $name = sh_category_label($slug, $lang);
        $count = (int) ($row['count'] ?? 0);
        $active = ($cat['active'] ?? true) !== false;
    ?>
    <a href="<?= htmlspecialchars(sh_url('search.php?category=' . urlencode($slug))) ?>"
       class="adm-cat-stat-card<?= $active ? '' : ' is-inactive' ?>"
       target="_blank" rel="noopener">
        <span class="adm-cat-stat-icon" aria-hidden="true"><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i></span>
        <span class="adm-cat-stat-body">
            <strong class="adm-cat-stat-name"><?= htmlspecialchars($name) ?></strong>
            <span class="adm-cat-stat-meta"><?= (int) $count ?> <?= htmlspecialchars($productsLabel) ?></span>
        </span>
        <span class="adm-cat-stat-count" aria-label="<?= (int) $count ?>"><?= (int) $count ?></span>
    </a>
    <?php endforeach; ?>
</div>