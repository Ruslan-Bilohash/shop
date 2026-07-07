<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/news-storage.php';
require_once __DIR__ . '/includes/site-integrations.php';
require_once __DIR__ . '/includes/billing-pricing.php';
require_once __DIR__ . '/includes/subscription-links.php';
sh_boot_public_integrations();

$current_page = 'news';
$np = $t['news_page'] ?? [];
if (!isset($np['pricing_license_price']) || str_contains((string) $np['pricing_license_price'], '184')) {
    $np['pricing_license_price'] = sh_billing_subscription_script_label($lang);
}
$articles = sh_news_active_list(true);

$page_title = $np['title'] ?? ($t['footer']['news'] ?? 'News') . ' — ' . sh_seo_site_name();
$page_desc  = $np['meta_description'] ?? ($np['subtitle'] ?? 'Shop CMS news and release notes.');
$canonical  = sh_url('news.php' . ($lang !== 'no' ? '?lang=' . $lang : ''));
$canon_abs  = sh_absolute_url($canonical);
$body_class = 'sh-page-news';

$seo_schemas = [
    sh_seo_organization(),
    sh_seo_collection_page($canon_abs, $page_title, $page_desc),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $np['h1'] ?? ($t['footer']['news'] ?? 'News'), 'url' => $canon_abs],
    ]),
];

$news_initial = 3;
$news_visible = array_slice($articles, 0, $news_initial);
$news_hidden  = array_slice($articles, $news_initial);
$news_more_n  = count($news_hidden);
$news_more_id = 'shNewsMoreGrid';
$news_more_label = sprintf($np['show_more'] ?? 'Show more news (%d)', $news_more_n);
$news_less_label = $np['show_less'] ?? 'Show less';

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-news-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($np['h1'] ?? ($t['footer']['news'] ?? 'News')) ?></span>
    </nav>

    <section class="sh-news-hero">
        <div class="sh-news-hero-copy">
            <span class="sh-news-hero-badge"><i class="fas fa-bolt" aria-hidden="true"></i> <?= htmlspecialchars($np['hero_badge'] ?? sh_version_label()) ?></span>
            <h1><?= htmlspecialchars($np['hero_title'] ?? ($np['h1'] ?? 'News')) ?></h1>
            <p class="sh-news-hero-lead"><?= htmlspecialchars($np['hero_lead'] ?? ($np['subtitle'] ?? '')) ?></p>
            <div class="sh-news-hero-cta">
                <a href="<?= sh_url('index.php') ?>" class="sh-btn sh-btn-primary"><i class="fas fa-store" aria-hidden="true"></i> <?= htmlspecialchars($np['cta_demo'] ?? 'Live demo') ?></a>
                <a href="<?= sh_url('site/') ?>" class="sh-btn sh-btn-outline"><i class="fas fa-tag" aria-hidden="true"></i> <?= htmlspecialchars($np['cta_product'] ?? 'Product page') ?></a>
                <a href="<?= sh_url('contact.php') ?>" class="sh-btn sh-btn-outline"><i class="fas fa-comments" aria-hidden="true"></i> <?= htmlspecialchars($np['cta_contact'] ?? 'Contact') ?></a>
            </div>
        </div>
        <ul class="sh-news-hero-points" aria-label="<?= htmlspecialchars($np['hero_points_aria'] ?? 'Key benefits') ?>">
            <?php foreach (($np['hero_points'] ?? []) as $point): ?>
            <li><i class="fas fa-check-circle" aria-hidden="true"></i> <?= htmlspecialchars($point) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php if (!empty($np['pricing_title'])): ?>
    <section class="sh-news-pricing">
        <h2><?= htmlspecialchars($np['pricing_title']) ?></h2>
        <p><?= htmlspecialchars($np['pricing_lead'] ?? '') ?></p>
        <div class="sh-news-pricing-grid">
            <div class="sh-news-price-card">
                <h3><?= htmlspecialchars($np['pricing_demo_title'] ?? '30-day demo') ?></h3>
                <p class="sh-news-price"><?= htmlspecialchars($np['pricing_demo_price'] ?? 'Free') ?></p>
                <p><?= htmlspecialchars($np['pricing_demo_desc'] ?? '') ?></p>
            </div>
            <div class="sh-news-price-card sh-news-price-card--featured">
                <h3><?= htmlspecialchars($np['pricing_license_title'] ?? 'License') ?></h3>
                <p class="sh-news-price"><?= htmlspecialchars($np['pricing_license_price'] ?? '') ?></p>
                <p><?= htmlspecialchars($np['pricing_license_desc'] ?? '') ?></p>
                <a href="<?= htmlspecialchars(sh_subscription_url()) ?>" class="sh-btn sh-btn-primary sh-btn-sm" <?= sh_subscription_external_attrs() ?>><?= htmlspecialchars($np['cta_license'] ?? 'Get license') ?></a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <header class="sh-news-header">
        <h2><?= htmlspecialchars($np['releases_title'] ?? ($np['h1'] ?? 'News')) ?></h2>
        <?php if (!empty($np['releases_subtitle'])): ?>
        <p class="sh-news-subtitle"><?= htmlspecialchars($np['releases_subtitle']) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($articles === []): ?>
    <div class="sh-form-card sh-empty-state">
        <i class="fas fa-newspaper"></i>
        <p><?= htmlspecialchars($np['empty'] ?? 'No articles published yet.') ?></p>
        <a href="<?= sh_url('index.php') ?>" class="sh-btn-primary"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
    </div>
    <?php else: ?>
    <?php
    $render_news_card = static function (array $article) use ($lang, $np): void {
        $slug = (string) ($article['slug'] ?? $article['id'] ?? '');
        $title = sh_localized($article, 'name', $lang);
        $excerpt = sh_localized($article, 'excerpt', $lang);
        $url = sh_url('news-article.php?slug=' . urlencode($slug) . ($lang !== 'no' ? '&lang=' . urlencode($lang) : ''));
        ?>
        <article class="sh-news-card<?= !empty($article['featured']) ? ' is-featured' : '' ?>">
            <a href="<?= htmlspecialchars($url) ?>" class="sh-news-card-media">
                <img src="<?= htmlspecialchars(sh_news_image($article)) ?>" alt="" loading="lazy" width="640" height="360"
                     onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
            </a>
            <div class="sh-news-card-body">
                <time class="sh-news-date" datetime="<?= htmlspecialchars((string) ($article['published_at'] ?? '')) ?>">
                    <?= htmlspecialchars(sh_news_published_label($article, $lang)) ?>
                </time>
                <?php if (!empty($article['featured'])): ?>
                <span class="sh-badge sh-badge--gold"><i class="fas fa-star"></i> <?= htmlspecialchars($np['featured'] ?? 'Featured') ?></span>
                <?php endif; ?>
                <h3><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($title) ?></a></h3>
                <?php if ($excerpt !== ''): ?>
                <p><?= htmlspecialchars($excerpt) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($url) ?>" class="sh-news-readmore"><?= htmlspecialchars($np['read_more'] ?? 'Read more') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>
        <?php
    };
    ?>
    <div class="sh-news-grid">
        <?php foreach ($news_visible as $article) { $render_news_card($article); } ?>
    </div>
    <?php if ($news_more_n > 0): ?>
    <div
        class="sh-news-grid sh-news-more"
        id="<?= htmlspecialchars($news_more_id) ?>"
        data-news-more-list
        hidden
        aria-hidden="true"
    >
        <?php foreach ($news_hidden as $article) { $render_news_card($article); } ?>
    </div>
    <div class="sh-news-more-actions">
        <button
            type="button"
            class="sh-news-more-btn"
            data-news-more-btn
            aria-expanded="false"
            aria-controls="<?= htmlspecialchars($news_more_id) ?>"
            data-label-more="<?= htmlspecialchars($news_more_label) ?>"
            data-label-less="<?= htmlspecialchars($news_less_label) ?>"
        ><?= htmlspecialchars($news_more_label) ?></button>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <section class="sh-news-bottom-cta">
        <h2><?= htmlspecialchars($np['bottom_cta_title'] ?? '') ?></h2>
        <p><?= htmlspecialchars($np['bottom_cta_lead'] ?? '') ?></p>
        <div class="sh-news-hero-cta">
            <a href="<?= htmlspecialchars(sh_subscription_url()) ?>" class="sh-btn sh-btn-primary" <?= sh_subscription_external_attrs() ?>><?= htmlspecialchars($np['cta_license'] ?? 'Get license') ?></a>
            <a href="<?= sh_url('admin/login.php') ?>" class="sh-btn sh-btn-outline"><?= htmlspecialchars($np['cta_admin'] ?? 'Admin demo') ?></a>
        </div>
    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>