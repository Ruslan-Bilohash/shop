<?php
require_once __DIR__ . '/seo.php';
require_once dirname(__DIR__, 3) . '/includes/cms-contact.php';
$cms_nav_discuss = cms_contact_texts('shop', $lang)['nav_discuss'];
$page_title = $page_title ?? $t['meta']['title'];
$page_desc  = $page_desc ?? $t['meta']['description'];
$canonical  = $canonical ?? $site_url . '/';
$seo_schemas = $seo_schemas ?? shs_seo_schemas(shs_absolute_url($canonical), $page_title, $page_desc);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_meta['html']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php shs_render_seo_head($page_title, $page_desc, $canonical, $seo_schemas); ?>
    <?php shs_render_stylesheets(); ?>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%232563eb' width='100' height='100' rx='12'/><text x='50' y='58' font-size='36' text-anchor='middle' fill='%23ffffff' font-family='sans-serif' font-weight='bold'>S</text></svg>">
</head>
<body>

<header class="shs-header" id="shsHeader">
    <div class="shs-header-inner">
        <a href="<?= shs_url('index.php') ?>" class="shs-logo">
            <span class="shs-logo-icon">S</span>
            <span class="shs-logo-text">Shop <em>CMS</em></span>
        </a>
        <div class="shs-header-tools">
            <?php require __DIR__ . '/lang-dropdown.php'; ?>
            <button class="shs-menu-toggle" id="shsMenuBtn" aria-label="<?= htmlspecialchars($t['a11y']['menu'] ?? 'Menu') ?>" type="button" aria-expanded="false" aria-controls="shsMobilePanel">
                <i class="fas fa-bars shs-menu-icon-open" aria-hidden="true"></i>
                <i class="fas fa-times shs-menu-icon-close" aria-hidden="true"></i>
            </button>
        </div>
        <nav class="shs-nav-desktop" aria-label="<?= htmlspecialchars($t['nav']['main_nav'] ?? 'Main') ?>">
            <a href="<?= shs_url('index.php#features') ?>"><?= htmlspecialchars($t['nav']['features']) ?></a>
            <a href="<?= shs_url('index.php#screenshots') ?>"><?= htmlspecialchars($t['nav']['screenshots'] ?? 'Screenshots') ?></a>
            <a href="<?= shs_url('index.php#seo') ?>"><?= htmlspecialchars($t['nav']['seo'] ?? 'SEO') ?></a>
            <a href="<?= shs_url('index.php#demo') ?>"><?= htmlspecialchars($t['nav']['demo']) ?></a>
        </nav>
        <div class="shs-header-actions">
            <a href="<?= shs_demo_url() ?>" class="shs-btn-ghost"><i class="fas fa-store"></i> <?= htmlspecialchars($t['demo']['frontend']) ?></a>
            <a href="<?= shs_demo_url('admin/login.php') ?>" class="shs-btn-ghost"><i class="fas fa-lock"></i> <?= htmlspecialchars($t['nav']['admin']) ?></a>
            <a href="<?= shs_url('order.php') ?>" class="shs-btn-ghost"><i class="fas fa-laptop-code"></i> <?= htmlspecialchars($t['nav']['order'] ?? '') ?></a>
            <a href="<?= shs_demo_url('contact.php') ?>" class="shs-btn-primary"><i class="fas fa-comments"></i> <?= htmlspecialchars($cms_nav_discuss) ?></a>
        </div>
    </div>
    <div class="shs-mobile-panel" id="shsMobilePanel" hidden>
        <nav class="shs-nav-mobile" aria-label="<?= htmlspecialchars($t['a11y']['menu'] ?? 'Menu') ?>">
            <a href="<?= shs_demo_url() ?>"><i class="fas fa-store" aria-hidden="true"></i> <?= htmlspecialchars($t['demo']['frontend']) ?></a>
            <a href="<?= shs_demo_url('admin/login.php') ?>"><i class="fas fa-lock" aria-hidden="true"></i> <?= htmlspecialchars($t['nav']['admin']) ?></a>
            <a href="<?= shs_url('order.php') ?>"><i class="fas fa-laptop-code" aria-hidden="true"></i> <?= htmlspecialchars($t['nav']['order'] ?? '') ?></a>
        </nav>
        <?php require __DIR__ . '/ecosystem-mobile-block.php'; ?>
    </div>
</header>
<div class="shs-overlay" id="shsOverlay" hidden></div>