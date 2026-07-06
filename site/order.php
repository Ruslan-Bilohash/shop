<?php
require_once __DIR__ . '/init.php';

$o = $t['order'] ?? [];

$page_title = $o['page_title'] ?? 'Order e-commerce website development | Shop CMS';
$page_desc  = $o['meta_description'] ?? '';
$canonical  = $site_url . '/order.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = shs_absolute_url($canonical);
$seo_schemas = shs_seo_schemas($canon_abs, $page_title, $page_desc);

require __DIR__ . '/includes/header.php';
?>

<section class="shs-order-hero">
    <div class="shs-container">
        <div class="shs-section-head">
            <h1><?= htmlspecialchars($o['h1'] ?? '') ?></h1>
            <p class="shs-section-sub"><?= htmlspecialchars($o['subtitle'] ?? '') ?></p>
        </div>
        <p class="shs-order-intro"><?= htmlspecialchars($o['intro'] ?? '') ?></p>
        <div class="shs-page-cta">
            <a href="<?= shs_demo_url() ?>" class="shs-btn-primary shs-btn-lg"><i class="fas fa-store"></i> <?= htmlspecialchars($o['cta_demo'] ?? '') ?></a>
            <a href="<?= shs_url('index.php#features') ?>" class="shs-btn-outline shs-btn-lg"><i class="fas fa-list"></i> <?= htmlspecialchars($o['cta_product'] ?? '') ?></a>
            <a href="https://bilohash.com/" rel="author" class="shs-btn-ghost shs-btn-lg"><i class="fas fa-globe"></i> <?= htmlspecialchars($o['cta_portfolio'] ?? 'bilohash.com') ?></a>
        </div>
    </div>
</section>

<section class="shs-section shs-order-body">
    <div class="shs-container">
        <div class="shs-order-block">
            <h2 class="shs-order-heading"><?= htmlspecialchars($o['benefits_title'] ?? '') ?></h2>
            <div class="shs-features-grid shs-features-grid--order">
                <?php foreach ($o['benefits'] ?? [] as $b): ?>
                <article class="shs-feature-card">
                    <h3><?= htmlspecialchars($b['title'] ?? '') ?></h3>
                    <p><?= htmlspecialchars($b['text'] ?? '') ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="shs-order-block">
            <h2 class="shs-order-heading"><?= htmlspecialchars($o['steps_title'] ?? '') ?></h2>
            <ol class="shs-order-steps">
                <?php foreach ($o['steps'] ?? [] as $step): ?>
                <li><?= htmlspecialchars($step) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="shs-order-block">
            <h2 class="shs-order-heading"><?= htmlspecialchars($o['crosslinks_title'] ?? '') ?></h2>
            <div class="shs-demo-grid shs-demo-grid--order">
                <a href="<?= shs_demo_url() ?>" class="shs-demo-card shs-demo-card--link"><i class="fas fa-store" aria-hidden="true"></i><span><?= htmlspecialchars($o['cta_demo'] ?? '') ?></span></a>
                <a href="<?= shs_url('index.php#features') ?>" class="shs-demo-card shs-demo-card--link"><i class="fas fa-list" aria-hidden="true"></i><span><?= htmlspecialchars($o['cta_product'] ?? '') ?></span></a>
                <a href="<?= shs_solutions_url() ?>" class="shs-demo-card shs-demo-card--link" rel="related"><i class="fas fa-th-large" aria-hidden="true"></i><span><?= htmlspecialchars($t['footer']['solutions'] ?? 'Solutions') ?></span></a>
                <a href="<?= shs_url('order.php') ?>" class="shs-demo-card shs-demo-card--link"><i class="fas fa-laptop-code" aria-hidden="true"></i><span><?= htmlspecialchars($t['footer']['order_page'] ?? $t['nav']['order'] ?? '') ?></span></a>
                <a href="https://bilohash.com/news/shop-cms.html" rel="related" class="shs-demo-card shs-demo-card--link"><i class="fas fa-newspaper" aria-hidden="true"></i><span><?= htmlspecialchars($t['footer']['news'] ?? 'News') ?></span></a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>