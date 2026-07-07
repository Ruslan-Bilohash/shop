<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

$admin_page = 'design-demos';
$ta = $t['admin'] ?? [];
$dp = $ta['design_demos_page'] ?? [];
$page_title = $dp['title'] ?? 'Design demos';

$extra_css = [sh_asset('css/admin-design-demos.css') . '?v=1'];

require __DIR__ . '/includes/layout.php';

$demos = [
    [
        'id'    => 'nordic',
        'title' => $dp['demo_nordic_title'] ?? 'Nordic minimal',
        'desc'  => $dp['demo_nordic_desc'] ?? 'Clean whites, blue accent, airy product grid.',
        'class' => 'adm-dd--nordic',
    ],
    [
        'id'    => 'dark',
        'title' => $dp['demo_dark_title'] ?? 'Dark premium',
        'desc'  => $dp['demo_dark_desc'] ?? 'Charcoal background, gold CTA, luxury feel.',
        'class' => 'adm-dd--dark',
    ],
    [
        'id'    => 'fresh',
        'title' => $dp['demo_fresh_title'] ?? 'Fresh market',
        'desc'  => $dp['demo_fresh_desc'] ?? 'Green accents, rounded cards, organic typography.',
        'class' => 'adm-dd--fresh',
    ],
    [
        'id'    => 'bold',
        'title' => $dp['demo_bold_title'] ?? 'Bold sale',
        'desc'  => $dp['demo_bold_desc'] ?? 'High contrast reds, urgency banners, promo blocks.',
        'class' => 'adm-dd--bold',
    ],
];
?>

<div class="adm-dd-page">
    <div class="adm-card">
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($dp['intro'] ?? 'Preview storefront moods. Apply colors in Settings → Appearance or Block builder.') ?></p>
        </div>
    </div>

    <div class="adm-dd-grid">
        <?php foreach ($demos as $demo): ?>
        <article class="adm-dd-card <?= htmlspecialchars($demo['class']) ?>">
            <div class="adm-dd-preview">
                <div class="adm-dd-mock-header"><span></span><span></span><span></span></div>
                <div class="adm-dd-mock-hero"></div>
                <div class="adm-dd-mock-products">
                    <div></div><div></div><div></div>
                </div>
            </div>
            <div class="adm-dd-meta">
                <h3><?= htmlspecialchars($demo['title']) ?></h3>
                <p><?= htmlspecialchars($demo['desc']) ?></p>
                <a href="<?= htmlspecialchars(sh_admin_url('settings-appearance.php')) ?>" class="adm-btn adm-btn-sm adm-btn-outline">
                    <i class="fas fa-palette"></i> <?= htmlspecialchars($dp['apply'] ?? 'Use in Appearance') ?>
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>