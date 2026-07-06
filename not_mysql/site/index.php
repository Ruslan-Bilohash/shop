<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/vertical-lib.php';
require_once dirname(__DIR__, 2) . '/includes/cms-contact.php';
$canonical = $site_url . '/';
$page_title = $t['meta']['title'];
$page_desc  = $t['meta']['description'];
$canon_abs  = shs_absolute_url($canonical);
$seo_schemas = shs_seo_home_schemas($canon_abs, $page_title, $page_desc, $t);
$shs_market = shs_market($lang);
require __DIR__ . '/includes/header.php';
?>

<section class="shs-hero">
    <div class="shs-container shs-hero-inner">
        <div class="shs-hero-content">
            <div class="shs-hero-badges">
                <span class="shs-badge"><?= htmlspecialchars(sprintf($t['hero']['badge'] ?? 'PHP e-commerce · Shop CMS · %s', sh_version_label())) ?></span>
            </div>
            <h1><?= htmlspecialchars($t['hero']['title']) ?></h1>
            <p class="shs-hero-sub"><?= htmlspecialchars($t['hero']['subtitle']) ?></p>
            <div class="shs-hero-cta">
                <a href="<?= shs_url('order.php') ?>" class="shs-btn-primary shs-btn-lg"><i class="fas fa-laptop-code"></i> <?= htmlspecialchars($t['hero']['cta_order'] ?? $t['nav']['order'] ?? '') ?></a>
                <a href="<?= shs_demo_url() ?>" class="shs-btn-outline shs-btn-lg"><i class="fas fa-store"></i> <?= htmlspecialchars($t['hero']['cta_demo']) ?></a>
                <a href="<?= shs_demo_url('admin/login.php') ?>" class="shs-btn-ghost shs-btn-lg"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($t['hero']['cta_admin']) ?></a>
            </div>
        </div>
        <div class="shs-hero-preview" aria-hidden="true">
            <div class="shs-mockup">
                <div class="shs-mockup-header">
                    <span></span><span></span><span></span>
                    <span class="shs-mockup-title"><?= htmlspecialchars($t['mockup']['title'] ?? $t['home']['featured'] ?? 'Featured') ?></span>
                </div>
                <div class="shs-mockup-body">
                    <?php foreach ($t['mockup']['items'] ?? [] as $i => $item): ?>
                    <article class="shs-mockup-item<?= $i === 1 ? ' active' : '' ?>"><div class="shs-mock-thumb"></div><div><strong><?= htmlspecialchars($item['name']) ?></strong><span><?= htmlspecialchars($item['price']) ?></span></div></article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($t['pitch'])): ?>
<section class="shs-section shs-pitch" id="order">
    <div class="shs-container">
        <div class="shs-pitch-inner">
            <div class="shs-pitch-copy">
                <span class="shs-pitch-region"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?= htmlspecialchars($shs_market['country']) ?></span>
                <h2><?= htmlspecialchars($t['pitch']['title']) ?></h2>
                <p class="shs-lead"><?= htmlspecialchars($t['pitch']['text']) ?></p>
                <div class="shs-pitch-actions">
                    <a href="<?= shs_url('order.php') ?>" class="shs-btn-primary shs-btn-lg"><i class="fas fa-laptop-code"></i> <?= htmlspecialchars($t['pitch']['cta_order'] ?? '') ?></a>
                    <a href="<?= shs_demo_url() ?>" class="shs-btn-outline shs-btn-lg"><i class="fas fa-store"></i> <?= htmlspecialchars($t['pitch']['cta_demo'] ?? '') ?></a>
                </div>
            </div>
            <div class="shs-pitch-grid">
                <?php foreach ($t['pitch']['items'] ?? [] as $item): ?>
                <article class="shs-pitch-card">
                    <i class="fas fa-<?= htmlspecialchars($item['icon'] ?? 'check') ?>" aria-hidden="true"></i>
                    <h3><?= htmlspecialchars($item['title'] ?? '') ?></h3>
                    <p><?= htmlspecialchars($item['desc'] ?? '') ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="shs-section shs-intro">
    <div class="shs-container">
        <h2><?= htmlspecialchars($t['intro']['title']) ?></h2>
        <p class="shs-lead"><?= htmlspecialchars($t['intro']['text']) ?></p>
        <?php if (sh_use_case_slugs() !== []): ?>
        <p class="shs-use-label"><?= htmlspecialchars($t['intro']['use_label'] ?? '') ?> <span class="shs-use-region"><?= htmlspecialchars($shs_market['country']) ?></span></p>
        <div class="shs-usecases">
            <?php foreach (sh_use_case_slugs() as $slug):
                $vdef = sh_vertical_defs()[$slug] ?? null;
                if (!$vdef) continue;
                $label = $vdef[$lang] ?? $vdef['en'] ?? $slug;
            ?>
            <a href="<?= htmlspecialchars(shs_vertical_url($slug)) ?>" class="shs-usecase shs-usecase-link" rel="related">
                <i class="fas fa-<?= htmlspecialchars($vdef['icon'] ?? 'store') ?>" aria-hidden="true"></i>
                <?= htmlspecialchars($label) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="shs-use-more">
            <a href="<?= htmlspecialchars(shs_solutions_url()) ?>"><?= htmlspecialchars(sh_vertical_hub_label($lang)) ?> →</a>
        </p>
        <?php endif; ?>
    </div>
</section>

<section class="shs-section" id="features">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['features']['title']) ?></h2>
        <?php if (!empty($t['features_showcase']['items'])): ?>
        <div class="shs-featured-showcase" aria-label="<?= htmlspecialchars($t['features_showcase']['title'] ?? $t['mockup']['title'] ?? 'Featured products') ?>">
            <div class="shs-featured-showcase-bar">
                <span class="shs-featured-dot"></span><span class="shs-featured-dot"></span><span class="shs-featured-dot"></span>
                <span class="shs-featured-showcase-label"><i class="fas fa-star"></i> <?= htmlspecialchars($t['features_showcase']['title'] ?? 'Featured products') ?></span>
                <span class="shs-featured-showcase-live"><?= htmlspecialchars($t['features_showcase']['live'] ?? 'Live demo') ?></span>
            </div>
            <div class="shs-featured-showcase-grid">
                <?php foreach ($t['features_showcase']['items'] as $i => $fp): ?>
                <article class="shs-featured-product<?= !empty($fp['featured']) ? ' is-featured' : '' ?>" style="--shs-fp-hue:<?= (int)($fp['hue'] ?? (210 + $i * 37)) ?>">
                    <div class="shs-featured-product-img" role="img" aria-label="<?= htmlspecialchars($fp['name'] ?? '') ?>">
                        <?php if (!empty($fp['sale'])): ?><span class="shs-featured-sale"><?= htmlspecialchars($fp['sale']) ?></span><?php endif; ?>
                        <?php if (!empty($fp['badge'])): ?><span class="shs-featured-badge"><i class="fas fa-bolt"></i> <?= htmlspecialchars($fp['badge']) ?></span><?php endif; ?>
                    </div>
                    <div class="shs-featured-product-body">
                        <?php if (!empty($fp['category'])): ?><span class="shs-featured-cat"><?= htmlspecialchars($fp['category']) ?></span><?php endif; ?>
                        <strong><?= htmlspecialchars($fp['name'] ?? '') ?></strong>
                        <div class="shs-featured-price-row">
                            <span class="shs-featured-price"><?= htmlspecialchars($fp['price'] ?? '') ?></span>
                            <?php if (!empty($fp['old_price'])): ?><s class="shs-featured-old"><?= htmlspecialchars($fp['old_price']) ?></s><?php endif; ?>
                        </div>
                        <span class="shs-featured-cta"><i class="fas fa-cart-plus"></i> <?= htmlspecialchars($fp['cta'] ?? 'Add to cart') ?></span>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="shs-features-grid">
            <?php foreach ($t['features']['items'] as $f): ?>
            <article class="shs-feature-card">
                <div class="shs-feature-icon"><i class="fas fa-<?= htmlspecialchars($f['icon']) ?>"></i></div>
                <h3><?= htmlspecialchars($f['title']) ?></h3>
                <p><?= htmlspecialchars($f['desc']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/screenshots-section.php'; ?>

<section class="shs-section shs-tech" id="tech">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['tech']['title']) ?></h2>
        <ul class="shs-tech-list">
            <?php foreach ($t['tech']['items'] as $item): ?>
            <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="shs-section shs-seo-section" id="seo">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['seo']['title']) ?></h2>
        <p class="shs-lead shs-text-center"><?= htmlspecialchars($t['seo']['subtitle']) ?></p>
        <?php if (!empty($t['seo']['scores'])): ?>
        <div class="shs-lighthouse-panel">
            <h3 class="shs-seo-block-title"><i class="fas fa-gauge-high"></i> <?= htmlspecialchars($t['seo']['lighthouse_title']) ?></h3>
            <div class="shs-lighthouse-grid">
                <?php foreach ($t['seo']['scores'] as $sc):
                    $ring = min(100, max(0, (int) ($sc['value'] ?? 0)));
                ?>
                <div class="shs-lighthouse-card <?= !empty($sc['highlight']) ? 'is-highlight' : '' ?>">
                    <div class="shs-lighthouse-ring" style="--shs-score:<?= $ring ?>">
                        <span><?= htmlspecialchars($sc['value']) ?></span>
                    </div>
                    <strong><?= htmlspecialchars($sc['label']) ?></strong>
                    <?php if (!empty($sc['note'])): ?>
                    <small><?= htmlspecialchars($sc['note']) ?></small>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($t['seo']['vitals'])): ?>
            <div class="shs-vitals-row">
                <?php foreach ($t['seo']['vitals'] as $v): ?>
                <span class="shs-vital"><em><?= htmlspecialchars($v['label']) ?></em> <?= htmlspecialchars($v['boost']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <h3 class="shs-subtitle"><?= htmlspecialchars($t['seo']['markup_title']) ?></h3>
        <ul class="shs-tech-list">
            <?php foreach ($t['seo']['markup_items'] as $item): ?>
            <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="shs-section shs-version-section" id="version">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['version']['title']) ?></h2>
        <div class="shs-version-card">
            <div class="shs-version-current">
                <span class="shs-version-label"><?= htmlspecialchars($t['version']['current']) ?></span>
                <strong class="shs-version-num"><?= htmlspecialchars(sh_version_label()) ?></strong>
                <time class="shs-version-date" datetime="<?= htmlspecialchars(sh_version_date()) ?>">
                    <?= sprintf(htmlspecialchars($t['version']['released']), htmlspecialchars(sh_version_date())) ?>
                </time>
                <p class="shs-version-note"><?= htmlspecialchars($t['version']['script_note']) ?></p>
                <a href="<?= shs_demo_url('admin/login.php') ?>" class="shs-btn-outline shs-btn-sm">
                    <i class="fas fa-user-shield"></i> <?= htmlspecialchars($t['demo']['admin']) ?> — <?= htmlspecialchars(sh_version_label()) ?>
                </a>
            </div>
            <div class="shs-version-changelog">
                <h3><?= htmlspecialchars($t['version']['changelog_title']) ?></h3>
                <?php
                $shs_releases = sh_version_releases_public();
                $shs_current_rel = null;
                $shs_older_rels = [];
                foreach ($shs_releases as $shs_rel) {
                    if ($shs_rel['version'] === sh_version()) {
                        $shs_current_rel = $shs_rel;
                    } else {
                        $shs_older_rels[] = $shs_rel;
                    }
                }
                if ($shs_current_rel === null && $shs_releases !== []) {
                    $shs_current_rel = $shs_releases[0];
                    $shs_older_rels = array_slice($shs_releases, 1);
                }
                $shs_changelog_render = static function (array $rel, array $t, bool $is_current) {
                    $items = $t['changelog_items'][$rel['version']] ?? [];
                    if ($items === [] && !empty($t['changelog_notes'][$rel['version']])) {
                        $items = [$t['changelog_notes'][$rel['version']]];
                    }
                    ?>
                    <li class="<?= $is_current ? 'is-current' : '' ?>">
                        <div class="shs-changelog-head">
                            <strong>v<?= htmlspecialchars($rel['version']) ?></strong>
                            <time datetime="<?= htmlspecialchars($rel['date']) ?>"><?= htmlspecialchars($rel['date']) ?></time>
                        </div>
                        <?php if ($items !== []): ?>
                        <ul class="shs-changelog-items">
                            <?php foreach ($items as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php
                };
                ?>
                <?php if ($shs_current_rel !== null): ?>
                <ol class="shs-changelog-list shs-changelog-list--current">
                    <?php $shs_changelog_render($shs_current_rel, $t, true); ?>
                </ol>
                <?php endif; ?>
                <?php if ($shs_older_rels !== []): ?>
                <details class="shs-changelog-spoiler">
                    <summary><?= htmlspecialchars(sprintf($t['version']['older_versions'] ?? 'Older versions (%d)', count($shs_older_rels))) ?></summary>
                    <ol class="shs-changelog-list shs-changelog-list--older">
                        <?php foreach ($shs_older_rels as $rel) { $shs_changelog_render($rel, $t, false); } ?>
                    </ol>
                </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($t['crosslinks'])): $cl = $t['crosslinks']; ?>
<section class="shs-section shs-crosslinks" id="explore">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($cl['title'] ?? '') ?></h2>
        <?php if (!empty($cl['subtitle'])): ?>
        <p class="shs-lead shs-text-center"><?= htmlspecialchars($cl['subtitle']) ?></p>
        <?php endif; ?>
        <div class="shs-crosslinks-grid">
            <a href="<?= shs_demo_url() ?>" class="shs-crosslink-card" rel="related">
                <span class="shs-crosslink-icon"><i class="fas fa-store" aria-hidden="true"></i></span>
                <strong><?= htmlspecialchars($cl['demo_title'] ?? '') ?></strong>
                <p><?= htmlspecialchars($cl['demo_desc'] ?? '') ?></p>
                <span class="shs-crosslink-cta"><?= htmlspecialchars($cl['demo_btn'] ?? '') ?> →</span>
            </a>
            <a href="<?= htmlspecialchars(shs_solutions_url()) ?>" class="shs-crosslink-card" rel="related">
                <span class="shs-crosslink-icon"><i class="fas fa-th-large" aria-hidden="true"></i></span>
                <strong><?= htmlspecialchars($cl['solutions_title'] ?? '') ?></strong>
                <p><?= htmlspecialchars($cl['solutions_desc'] ?? '') ?></p>
                <span class="shs-crosslink-cta"><?= htmlspecialchars($cl['solutions_btn'] ?? '') ?> →</span>
            </a>
            <a href="<?= shs_url('order.php') ?>" class="shs-crosslink-card" rel="related">
                <span class="shs-crosslink-icon"><i class="fas fa-laptop-code" aria-hidden="true"></i></span>
                <strong><?= htmlspecialchars($cl['order_title'] ?? '') ?></strong>
                <p><?= htmlspecialchars($cl['order_desc'] ?? '') ?></p>
                <span class="shs-crosslink-cta"><?= htmlspecialchars($cl['order_btn'] ?? '') ?> →</span>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="shs-section shs-demo-block" id="demo">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['demo']['title']) ?></h2>
        <div class="shs-demo-grid">
            <article class="shs-demo-card">
                <h3><i class="fas fa-store"></i> <?= htmlspecialchars($t['demo']['frontend']) ?></h3>
                <p><?= htmlspecialchars($t['demo']['frontend_desc']) ?></p>
                <a href="<?= shs_demo_url() ?>" class="shs-btn-primary"><?= htmlspecialchars($t['demo']['open']) ?> →</a>
            </article>
            <article class="shs-demo-card">
                <h3><i class="fas fa-user-shield"></i> <?= htmlspecialchars($t['demo']['admin']) ?></h3>
                <p><?= htmlspecialchars($t['demo']['admin_desc']) ?></p>
                <a href="<?= shs_demo_url('admin/login.php') ?>" class="shs-btn-outline"><?= htmlspecialchars($t['demo']['open']) ?> →</a>
            </article>
        </div>
    </div>
</section>

<?php if (!empty($t['faq']['items'])): ?>
<section class="shs-section shs-faq" id="faq">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($t['faq']['title'] ?? 'FAQ') ?></h2>
        <div class="shs-faq-list">
            <?php foreach ($t['faq']['items'] as $i => $item): ?>
            <details class="shs-faq-item"<?= $i === 0 ? ' open' : '' ?>>
                <summary><?= htmlspecialchars($item['q'] ?? '') ?></summary>
                <p><?= htmlspecialchars($item['a'] ?? '') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="shs-cta-band">
    <div class="shs-container shs-cta-inner">
        <div>
            <h2><?= htmlspecialchars($t['cta']['title']) ?></h2>
            <p><?= htmlspecialchars($t['cta']['text']) ?></p>
        </div>
        <a href="<?= shs_url('order.php') ?>" class="shs-btn-primary shs-btn-lg"><i class="fas fa-laptop-code"></i> <?= htmlspecialchars($t['nav']['order'] ?? $t['cta']['btn']) ?></a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>