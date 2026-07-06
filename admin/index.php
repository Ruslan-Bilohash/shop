<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/admin-dashboard.php';
require_once dirname(__DIR__) . '/includes/site-settings.php';
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
sh_admin_require();

$admin_page = 'dashboard';
$page_title = $ta['dashboard'] ?? 'Dashboard';
$dp = $ta['dashboard_page'] ?? [];

$products = sh_products();
$stats = sh_admin_dashboard_stats();
$chart = sh_admin_category_chart();
$featured = array_values(array_filter($products, fn($p) => !empty($p['featured'])));
$health = sh_admin_health_checks($ta);
$health_all_ok = sh_admin_health_all_ok($health);
$groups = sh_settings_tab_groups();
$tabs = sh_settings_tabs();

require __DIR__ . '/includes/layout.php';
?>

<?php if (defined('SH_DEMO_MODE') && SH_DEMO_MODE): ?>
<div class="adm-alert adm-alert-info">
    <i class="fas fa-flask"></i> <?= htmlspecialchars($ta['add_note'] ?? 'Demo data — products loaded from JSON seed.') ?>
</div>
<?php endif; ?>

<div class="adm-dash-hero <?= $stats['shop_open'] ? 'is-open' : 'is-closed' ?>">
    <div class="adm-dash-hero-main">
        <span class="adm-dash-hero-badge">
            <i class="fas <?= $stats['shop_open'] ? 'fa-store' : 'fa-hard-hat' ?>"></i>
            <?= htmlspecialchars($stats['shop_open']
                ? ($dp['shop_open'] ?? 'Storefront is open')
                : ($dp['shop_closed'] ?? 'Storefront closed — maintenance')) ?>
        </span>
        <p class="adm-dash-hero-text">
            <?= htmlspecialchars($stats['shop_open']
                ? ($dp['shop_open_hint'] ?? 'Customers can browse the catalog.')
                : ($dp['shop_closed_hint'] ?? 'Enable the store in Settings → Store.')) ?>
        </p>
    </div>
    <div class="adm-dash-hero-actions">
        <a href="<?= sh_admin_url('settings-store.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm">
            <i class="fas fa-cog"></i> <?= htmlspecialchars($dp['manage_store'] ?? 'Store settings') ?>
        </a>
        <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
            <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($ta['view_site'] ?? 'View site') ?>
        </a>
    </div>
</div>

<div class="adm-stats adm-stats--dashboard">
    <div class="adm-stat">
        <div class="adm-stat-icon blue"><i class="fas fa-box"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['active_products'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($dp['stat_active'] ?? 'Active products') ?></div>
            <?php if ($stats['inactive_products'] > 0): ?>
            <div class="adm-stat-sub"><?= (int) $stats['inactive_products'] ?> <?= htmlspecialchars($dp['stat_inactive'] ?? 'inactive') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon green"><i class="fas fa-star"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['featured'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($ta['stats_featured'] ?? 'Featured') ?></div>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon orange"><i class="fas fa-percent"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['on_sale'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($dp['stat_on_sale'] ?? 'On sale') ?></div>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon gold"><i class="fas fa-layer-group"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['categories'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($ta['stats_cats'] ?? 'Categories') ?></div>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon blue"><i class="fas fa-coins"></i></div>
        <div>
            <div class="adm-stat-val"><?= sh_format_price((int) $stats['volume']) ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($ta['stats_vol'] ?? 'Catalog value') ?></div>
            <div class="adm-stat-sub"><?= htmlspecialchars($stats['currency']) ?> · <?= htmlspecialchars($dp['stat_avg'] ?? 'avg') ?> <?= sh_format_price((int) $stats['avg_price']) ?></div>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon orange"><i class="fas fa-box-open"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['out_of_stock'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($dp['stat_oos'] ?? 'Out of stock') ?></div>
        </div>
    </div>
    <div class="adm-stat">
        <div class="adm-stat-icon green"><i class="fas fa-bolt"></i></div>
        <div>
            <div class="adm-stat-val"><?= (int) $stats['new_leads'] ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($dp['stat_leads'] ?? 'New leads') ?></div>
            <?php if ($stats['total_leads'] > 0): ?>
            <div class="adm-stat-sub"><?= (int) $stats['total_leads'] ?> <?= htmlspecialchars($dp['stat_leads_total'] ?? 'total') ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="adm-dash-grid <?= $health_all_ok ? 'adm-dash-grid--2' : 'adm-dash-grid--3' ?>">
    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-chart-bar"></i> <?= htmlspecialchars($ta['by_category'] ?? 'By category') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <?php
            $chart = array_slice($chart, 0, 8);
            require __DIR__ . '/includes/category-stats-grid.php';
            ?>
        </div>
    </div>

    <?php if (!$health_all_ok): ?>
    <?php require __DIR__ . '/includes/launch-checklist.php'; ?>
    <?php endif; ?>

    <div class="adm-card">
        <div class="adm-card-head">
            <h2><?= htmlspecialchars($ta['featured_count'] ?? 'Featured products') ?></h2>
            <a href="<?= sh_admin_url('products.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><?= htmlspecialchars($ta['products'] ?? 'Products') ?></a>
        </div>
        <div class="adm-card-body padded adm-product-list">
            <?php if ($featured === []): ?>
            <p class="adm-help"><?= htmlspecialchars($dp['no_featured'] ?? 'No featured products yet.') ?></p>
            <?php else: ?>
            <?php foreach (array_slice($featured, 0, 5) as $fp): ?>
            <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($fp['id'])) ?>" class="adm-product-item">
                <img src="<?= htmlspecialchars(sh_product_image($fp)) ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
                <div>
                    <strong><?= htmlspecialchars(sh_localized($fp, 'name', $lang)) ?></strong>
                    <span><?= sh_format_price(sh_product_price($fp)) ?> · <?= htmlspecialchars(sh_category_label($fp['category'] ?? '', $lang)) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($health_all_ok): ?>
<?php require __DIR__ . '/includes/launch-checklist.php'; ?>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-head">
        <h2><i class="fas fa-sliders"></i> <?= htmlspecialchars($dp['settings_hub'] ?? 'Settings by category') ?></h2>
        <a href="<?= sh_admin_url('settings-store.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm"><?= htmlspecialchars($ta['site_settings'] ?? 'All settings') ?></a>
    </div>
    <div class="adm-card-body padded">
        <div class="adm-settings-hub">
            <?php foreach ($groups as $gkey => $group): ?>
            <div class="adm-settings-hub-group">
                <h3><i class="fas fa-<?= htmlspecialchars($group['icon']) ?>"></i> <?= htmlspecialchars(sh_settings_admin_label($group['label_key'], $ta)) ?></h3>
                <div class="adm-settings-hub-links">
                    <?php foreach ($group['tabs'] as $tabKey):
                        if (!isset($tabs[$tabKey])) continue;
                        $tab = $tabs[$tabKey];
                        ?>
                    <a href="<?= sh_admin_url($tab['file']) ?>" class="adm-settings-hub-link">
                        <i class="fas fa-<?= htmlspecialchars($tab['icon']) ?>"></i>
                        <span><?= htmlspecialchars(sh_settings_admin_label('settings_tab_' . $tabKey, $ta)) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-head"><h2><?= htmlspecialchars($ta['quick_actions'] ?? 'Quick actions') ?></h2></div>
    <div class="adm-card-body padded adm-quick-actions">
        <a href="<?= sh_admin_url('products.php') ?>" class="adm-btn adm-btn-primary"><i class="fas fa-box"></i> <?= htmlspecialchars($ta['products'] ?? 'Products') ?></a>
        <a href="<?= sh_admin_url('categories.php') ?>" class="adm-btn adm-btn-primary"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($ta['categories'] ?? 'Categories') ?></a>
        <a href="<?= sh_admin_url('payments.php') ?>" class="adm-btn adm-btn-primary"><i class="fas fa-credit-card"></i> <?= htmlspecialchars($ta['payments'] ?? 'Payments') ?></a>
        <a href="<?= sh_admin_url('quick-leads.php') ?>" class="adm-btn adm-btn-outline"><i class="fas fa-bolt"></i> <?= htmlspecialchars($ta['quick_leads'] ?? 'Quick purchase') ?></a>
        <a href="<?= sh_admin_url('settings-seo.php') ?>" class="adm-btn adm-btn-outline"><i class="fas fa-chart-line"></i> <?= htmlspecialchars($ta['settings_tab_seo'] ?? 'SEO') ?></a>
        <a href="<?= sh_url('search.php') ?>" class="adm-btn adm-btn-outline" target="_blank"><i class="fas fa-store"></i> <?= htmlspecialchars($ta['view_catalog'] ?? 'View catalog') ?></a>
        <a href="https://bilohash.com/shop/site/" class="adm-btn adm-btn-outline" target="_blank"><i class="fas fa-book"></i> <?= htmlspecialchars($ta['product_page'] ?? 'Product page') ?></a>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>