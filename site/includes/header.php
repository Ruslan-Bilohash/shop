<?php
require_once __DIR__ . '/seo.php';
$shs_cms_contact = dirname(__DIR__, 2) . '/includes/cms-contact.php';
if (!is_file($shs_cms_contact)) {
    $shs_cms_contact = dirname(__DIR__, 3) . '/includes/cms-contact.php';
}
require_once $shs_cms_contact;
$cms_nav_discuss = cms_contact_texts('shop', $lang)['nav_discuss'];
$page_title = $page_title ?? $t['meta']['title'];
$page_desc  = $page_desc ?? $t['meta']['description'];
$canonical  = $canonical ?? $site_url . '/';
$seo_schemas = $seo_schemas ?? shs_seo_schemas(shs_absolute_url($canonical), $page_title, $page_desc);
$shs_on_home = basename(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'))) === 'index.php';
$shs_section_prefix = $shs_on_home ? '' : shs_url('index.php');
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
        <div class="shs-header-end">
            <a href="<?= shs_url('order.php') ?>" class="shs-btn-primary shs-header-cta">
                <i class="fas fa-laptop-code" aria-hidden="true"></i>
                <span><?= htmlspecialchars($t['nav']['order'] ?? '') ?></span>
            </a>
            <?php require __DIR__ . '/lang-dropdown.php'; ?>
            <button class="shs-menu-toggle" id="shsMenuBtn" aria-label="<?= htmlspecialchars($t['a11y']['menu'] ?? 'Menu') ?>" type="button" aria-expanded="false" aria-controls="shsMobilePanel">
                <i class="fas fa-bars shs-menu-icon-open" aria-hidden="true"></i>
                <i class="fas fa-times shs-menu-icon-close" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="shs-mobile-panel" id="shsMobilePanel" hidden>
        <div class="shs-mobile-panel-head">
            <span class="shs-mobile-panel-title"><?= htmlspecialchars($t['nav']['menu'] ?? 'Menu') ?></span>
            <button type="button" class="shs-mobile-panel-close" id="shsMenuClose" aria-label="<?= htmlspecialchars($t['a11y']['close'] ?? 'Close') ?>">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <nav class="shs-nav-mobile" aria-label="<?= htmlspecialchars($t['nav']['main_nav'] ?? 'Main') ?>">
            <p class="shs-nav-group-label"><?= htmlspecialchars($t['nav']['page_sections'] ?? 'On this page') ?></p>
            <a href="<?= htmlspecialchars($shs_section_prefix . '#features') ?>"><?= htmlspecialchars($t['nav']['features']) ?></a>
            <a href="<?= htmlspecialchars($shs_section_prefix . '#screenshots') ?>"><?= htmlspecialchars($t['nav']['screenshots'] ?? 'Screenshots') ?></a>
            <a href="<?= htmlspecialchars($shs_section_prefix . '#seo') ?>"><?= htmlspecialchars($t['nav']['seo'] ?? 'SEO') ?></a>
            <a href="<?= htmlspecialchars($shs_section_prefix . '#demo') ?>"><?= htmlspecialchars($t['nav']['demo']) ?></a>

            <p class="shs-nav-group-label"><?= htmlspecialchars($t['nav']['try_demo'] ?? 'Try demo') ?></p>
            <a href="<?= shs_demo_url() ?>"><i class="fas fa-store" aria-hidden="true"></i> <?= htmlspecialchars($t['demo']['frontend']) ?></a>
            <a href="<?= shs_demo_url('admin/login.php') ?>"><i class="fas fa-lock" aria-hidden="true"></i> <?= htmlspecialchars($t['nav']['admin']) ?></a>
            <a href="<?= shs_demo_url('contact.php') ?>" class="shs-nav-mobile-accent"><i class="fas fa-comments" aria-hidden="true"></i> <?= htmlspecialchars($cms_nav_discuss) ?></a>
        </nav>
        <?php require __DIR__ . '/ecosystem-mobile-block.php'; ?>
    </div>
</header>
<div class="shs-overlay" id="shsOverlay" hidden></div>