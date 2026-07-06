<?php
require_once __DIR__ . '/ecosystem-load.php';
sh_require_ecosystem('cms-contact.php');
require_once __DIR__ . '/category-storage.php';
require_once __DIR__ . '/site-integrations.php';
sh_boot_public_integrations();
$page_title = $page_title ?? $t['meta']['title'];
$page_desc  = $page_desc ?? $t['meta']['description'];
$canonical  = $canonical ?? $site_url . '/';
$body_class = $body_class ?? '';
$seo_schemas = $seo_schemas ?? [];
$seo_og_image = $seo_og_image ?? null;
$seo_og_type  = $seo_og_type ?? 'website';
$seo_keywords = $seo_keywords ?? '';
$cart_count   = sh_cart_count();
require_once __DIR__ . '/menu-settings.php';
$sh_menu_cfg  = sh_menu_settings(sh_site_settings());
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_meta['html']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php sh_render_public_stylesheets(); ?>
    <?php
    require_once __DIR__ . '/store-settings.php';
    sh_render_shop_theme_styles(sh_site_settings());
    sh_render_custom_head_html(sh_site_settings());
    ?>
    <?php sh_render_seo_head($page_title, $page_desc, $canonical, $seo_schemas, $seo_og_image, $seo_og_type, !empty($seo_noindex), $seo_keywords); ?>
    <?php if ((!empty($cms_prefix) && $cms_prefix !== 'fl') || ($current_page ?? '') === 'contact'): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(cms_contact_stylesheet_href()) ?>">
    <?php endif; ?>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23ffffff' width='100' height='100' rx='12'/><text x='50' y='62' font-size='40' text-anchor='middle' fill='%232563eb' font-family='sans-serif' font-weight='bold'>S</text></svg>">
</head>
<body class="<?= htmlspecialchars($body_class) ?>">

<div class="sh-top-bar">
    <div class="sh-demo-strip" role="status">
        <i class="fas fa-store" aria-hidden="true"></i>
        <span><?= htmlspecialchars($t['demo_strip']['text']) ?></span>
        <a href="https://bilohash.com/shop/site/"><?= htmlspecialchars($t['demo_strip']['cms']) ?> →</a>
    </div>

    <header class="sh-header" id="shHeader" itemscope itemtype="https://schema.org/WPHeader">
        <div class="sh-header-inner">
            <a href="<?= sh_url('index.php') ?>" class="sh-logo" itemprop="url">
                <span class="sh-logo-icon"><i class="fas fa-store"></i></span>
                <span class="sh-logo-text" itemprop="name"><?= htmlspecialchars($t['meta']['site_name']) ?></span>
            </a>

            <div class="sh-header-panel" id="shHeaderPanel">
                <div class="sh-panel-head">
                    <span class="sh-panel-title"><?= htmlspecialchars($t['meta']['site_name']) ?></span>
                    <button type="button" class="sh-menu-close" id="shMenuClose" aria-label="<?= htmlspecialchars($t['nav']['menu_close'] ?? 'Close menu') ?>">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <nav class="sh-nav" aria-label="<?= htmlspecialchars($t['nav']['main_nav'] ?? 'Main') ?>">
                    <a href="<?= sh_url('index.php') ?>" class="<?= ($current_page ?? '') === 'home' ? 'active' : '' ?>"><?= htmlspecialchars($t['nav']['shop']) ?></a>
                    <details class="sh-nav-cats">
                        <summary class="<?= ($current_page ?? '') === 'search' ? 'active' : '' ?>"><?= htmlspecialchars($t['nav']['categories']) ?></summary>
                        <div class="sh-nav-cats-menu">
                            <a href="<?= sh_url('search.php') ?>"><?= htmlspecialchars($t['search_page']['all_cats'] ?? $t['search']['all_cats'] ?? 'All categories') ?></a>
                            <?php foreach (sh_categories() as $catSlug): ?>
                            <a href="<?= sh_url('search.php?category=' . urlencode($catSlug)) ?>"><?= htmlspecialchars(sh_category_label($catSlug, $lang)) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <?php foreach (($sh_menu_cfg['header_nav_links'] ?? []) as $navLink):
                        if (empty($navLink['active'])) {
                            continue;
                        }
                        $navHref = sh_header_nav_link_href($navLink);
                        $navLabel = sh_header_nav_link_label($navLink, $lang);
                        $navActive = sh_header_nav_link_active($navLink, $current_page ?? '') ? 'active' : '';
                    ?>
                    <a href="<?= htmlspecialchars($navHref) ?>"
                       class="<?= $navActive ?>"
                       <?= !empty($navLink['external']) ? 'rel="noopener noreferrer" target="_blank"' : '' ?>>
                        <?= htmlspecialchars($navLabel) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <div class="sh-header-actions">
                    <?php if (function_exists('sh_customer_auth_enabled') && sh_customer_auth_enabled() && !empty($sh_menu_cfg['menu_show_signin'])): ?>
                        <?php if (sh_customer_logged_in()): ?>
                        <a href="<?= sh_url('logout.php') ?>" class="sh-btn-outline sh-btn-compact sh-auth-btn" title="<?= htmlspecialchars($t['customer_auth']['logout'] ?? 'Log out') ?>">
                            <i class="fas fa-user-check"></i>
                            <span class="sh-auth-name"><?= htmlspecialchars(sh_customer_display_name()) ?></span>
                        </a>
                        <?php else: ?>
                        <a href="<?= sh_url('login.php') ?>" class="sh-btn-outline sh-btn-compact sh-auth-btn <?= ($current_page ?? '') === 'login' ? 'active' : '' ?>">
                            <i class="fas fa-user"></i> <span><?= htmlspecialchars($t['nav']['signin'] ?? 'Sign in') ?></span>
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?= sh_url('cart.php') ?>" class="sh-btn-outline sh-cart-btn <?= ($current_page ?? '') === 'cart' ? 'active' : '' ?>" id="shCartBtn">
                        <i class="fas fa-shopping-cart"></i>
                        <span><?= htmlspecialchars($t['nav']['cart']) ?></span>
                        <span class="sh-cart-badge" id="shCartBadge"<?= $cart_count > 0 ? '' : ' hidden' ?>><?= (int)$cart_count ?></span>
                    </a>

                    <a href="<?= sh_url('admin/login.php') ?>" class="sh-btn-outline sh-btn-compact" title="<?= htmlspecialchars($t['nav']['admin'] ?? 'Admin') ?>"><i class="fas fa-user-shield"></i></a>
                    <?php $lang_dropdown_variant = 'header'; require __DIR__ . '/lang-dropdown.php'; unset($lang_dropdown_variant); ?>
                </div>
            </div>

            <div class="sh-header-mobile-tools">
                <?php $lang_dropdown_variant = 'mobile'; require __DIR__ . '/lang-dropdown.php'; unset($lang_dropdown_variant); ?>
                <button type="button" class="sh-menu-toggle" id="shMenuBtn" aria-label="<?= htmlspecialchars($t['nav']['menu'] ?? 'Menu') ?>" aria-expanded="false" aria-controls="shHeaderPanel">
                    <i class="fas fa-bars sh-menu-icon-open" aria-hidden="true"></i>
                    <i class="fas fa-times sh-menu-icon-close" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </header>
</div>

<div class="sh-overlay" id="shOverlay" hidden aria-hidden="true"></div>