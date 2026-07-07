<?php
require_once __DIR__ . '/ecosystem-load.php';
sh_require_ecosystem('cms-contact.php');
/** @var string $slug @var array $vertical @var array $v @var string $canonical @var array $seo_schemas */
$current_page = 'vertical';
$hub_label = sh_vertical_hub_label($lang);
$region_note = ['no' => 'Norge · Europa · Ukraina', 'en' => 'Norway · Europe · Ukraine', 'uk' => 'Норвегія · Європа · Україна', 'ru' => 'Норвегия · Европа · Украина'][$lang] ?? 'Norway · Europe · Ukraine';
$benefits_title = ['no' => 'Fordeler', 'en' => 'Benefits', 'uk' => 'Переваги', 'ru' => 'Преимущества'][$lang] ?? 'Benefits';
$features_title = ['no' => 'Funksjoner i Shop CMS', 'en' => 'Shop CMS features', 'uk' => 'Можливості Shop CMS', 'ru' => 'Возможности Shop CMS'][$lang] ?? 'Shop CMS features';
$faq_title = ['no' => 'Ofte stilte spørsmål', 'en' => 'FAQ', 'uk' => 'Часті питання', 'ru' => 'Частые вопросы'][$lang] ?? 'FAQ';
$related_title = ['no' => 'Andre nettbutikkløsninger', 'en' => 'More e-commerce solutions', 'uk' => 'Інші рішення для магазину', 'ru' => 'Другие решения для магазина'][$lang] ?? 'More e-commerce solutions';
$product_page = ['no' => 'Produktside', 'en' => 'Product page', 'uk' => 'Сторінка продукту', 'ru' => 'Страница продукта'][$lang] ?? 'Product page';
$all = sh_verticals_all();
$demo_q = trim($vertical['demo_param'] ?? '');
$demo_url = sh_url('search.php' . ($demo_q ? '?' . $demo_q : ''));
$canon_abs = sh_absolute_url($canonical);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_meta['html']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php sh_render_vertical_seo_head($page_title, $page_desc, $canonical, $seo_schemas, $v['keywords'] ?? null); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/style.css')) ?>?v=5">
</head>
<body class="sh-vertical-page">

<div class="sh-top-bar">
<div class="sh-demo-strip" role="status">
    <i class="fas fa-store" aria-hidden="true"></i>
    <span><?= htmlspecialchars($t['demo_strip']['text']) ?></span>
    <a href="https://bilohash.com/shop/site/"><?= htmlspecialchars($t['demo_strip']['cms']) ?> →</a>
</div>
<header class="sh-header" id="shHeader">
    <div class="sh-header-inner">
        <a href="<?= sh_url('index.php') ?>" class="sh-logo">
            <span class="sh-logo-icon"><i class="fas fa-store"></i></span>
            <span class="sh-logo-text"><?= htmlspecialchars($t['meta']['site_name']) ?></span>
        </a>
        <div class="sh-header-panel" id="shHeaderPanel">
            <div class="sh-panel-head">
                <span class="sh-panel-title"><?= htmlspecialchars($t['meta']['site_name']) ?></span>
                <button type="button" class="sh-menu-close" id="shMenuClose" aria-label="<?= htmlspecialchars($t['nav']['menu_close'] ?? 'Close menu') ?>">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <nav class="sh-nav" aria-label="<?= htmlspecialchars($t['nav']['main_nav'] ?? 'Main') ?>">
                <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['nav']['shop']) ?></a>
                <a href="<?= sh_url('solutions.php') ?>"><?= htmlspecialchars($hub_label) ?></a>
                <a href="<?= sh_url('contact.php') ?>"><?= htmlspecialchars(cms_contact_texts('shop', $lang)['nav_discuss']) ?></a>
            </nav>
            <div class="sh-header-actions">
                <?php $lang_dropdown_variant = 'header'; require __DIR__ . '/lang-dropdown.php'; unset($lang_dropdown_variant); ?>
                <a href="<?= sh_url('solutions.php') ?>" class="sh-btn-outline"><?= htmlspecialchars($hub_label) ?></a>
                <?php if (function_exists('sh_admin_public_link_visible') && sh_admin_public_link_visible()): ?>
                <a href="<?= sh_url('admin/login.php') ?>" class="sh-btn-outline"><i class="fas fa-user-shield"></i> Admin</a>
                <?php endif; ?>
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
<div class="sh-overlay" id="shOverlay" hidden aria-hidden="true"></div>
</div>

<main class="sh-container sh-vertical-main">
    <nav class="sh-vertical-crumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        → <a href="<?= sh_url('solutions.php') ?>"><?= htmlspecialchars($hub_label) ?></a>
        → <?= htmlspecialchars($v['h1']) ?>
    </nav>

    <section class="sh-vertical-hero">
        <div class="sh-vertical-hero-icon"><i class="fas fa-<?= htmlspecialchars($vertical['icon'] ?? 'store') ?>"></i></div>
        <h1><?= htmlspecialchars($v['h1']) ?></h1>
        <p class="sh-vertical-subtitle"><?= htmlspecialchars($v['subtitle']) ?></p>
        <p class="sh-vertical-intro"><?= htmlspecialchars($v['intro']) ?></p>
        <p class="sh-vertical-region"><i class="fas fa-globe-europe" aria-hidden="true"></i> <?= htmlspecialchars($region_note) ?></p>
        <div class="sh-vertical-cta-row">
            <a href="<?= htmlspecialchars($demo_url) ?>" class="sh-btn-primary"><i class="fas fa-play-circle"></i> Live demo</a>
            <a href="https://bilohash.com/shop/site/" class="sh-btn-outline-dark"><i class="fas fa-book"></i> <?= htmlspecialchars($product_page) ?></a>
        </div>
    </section>

    <section class="sh-vertical-section">
        <h2><?= htmlspecialchars($benefits_title) ?></h2>
        <div class="sh-vertical-benefits">
            <?php foreach ($v['benefits'] ?? [] as $b): ?>
            <article class="sh-vertical-benefit">
                <h3><?= htmlspecialchars($b['title']) ?></h3>
                <p><?= htmlspecialchars($b['text']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="sh-vertical-section">
        <h2><?= htmlspecialchars($features_title) ?></h2>
        <ul class="sh-vertical-features">
            <?php foreach ($v['features'] ?? [] as $f): ?>
            <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($f) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php if (!empty($v['faq'])): ?>
    <section class="sh-vertical-section sh-vertical-faq">
        <h2><?= htmlspecialchars($faq_title) ?></h2>
        <div class="sh-faq-list">
            <?php foreach ($v['faq'] as $i => $item): ?>
            <details class="sh-faq-item"<?= $i === 0 ? ' open' : '' ?>>
                <summary><?= htmlspecialchars($item['q']) ?></summary>
                <p><?= htmlspecialchars($item['a']) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="sh-vertical-section sh-vertical-related">
        <h2><?= htmlspecialchars($related_title) ?></h2>
        <div class="sh-vertical-links">
            <?php
            $vdefs = sh_vertical_defs();
            foreach ($all as $s => $item):
                if ($s === $slug) continue;
                $short = $vdefs[$s][$lang] ?? $vdefs[$s]['en'] ?? $s;
            ?>
            <a href="<?= htmlspecialchars(sh_vertical_url($s)) ?>" class="sh-vertical-link-card">
                <i class="fas fa-<?= htmlspecialchars($item['icon'] ?? 'tag') ?>"></i>
                <strong><?= htmlspecialchars($short) ?></strong>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="sh-vertical-cta-band">
        <p><?= htmlspecialchars($v['cta']) ?></p>
        <a href="<?= sh_url('contact.php') ?>" class="sh-btn-primary"><i class="fas fa-envelope"></i> <?= htmlspecialchars(cms_contact_texts('shop', $lang)['nav_discuss']) ?></a>
    </section>
</main>

<script src="<?= htmlspecialchars(sh_asset('js/main.js')) ?>?v=<?= sh_public_script_version() ?>" defer></script>
<?php
$sh_skip_ecosystem = true;
require __DIR__ . '/footer.php';