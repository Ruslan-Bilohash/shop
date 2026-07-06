<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/news-storage.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$current_page = 'news';
$np = $t['news_page'] ?? [];
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

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-news-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($np['h1'] ?? ($t['footer']['news'] ?? 'News')) ?></span>
    </nav>

    <header class="sh-news-header">
        <h1><?= htmlspecialchars($np['h1'] ?? ($t['footer']['news'] ?? 'News')) ?></h1>
        <?php if (!empty($np['subtitle'])): ?>
        <p class="sh-news-subtitle"><?= htmlspecialchars($np['subtitle']) ?></p>
        <?php endif; ?>
    </header>

    <?php if ($articles === []): ?>
    <div class="sh-form-card sh-empty-state">
        <i class="fas fa-newspaper"></i>
        <p><?= htmlspecialchars($np['empty'] ?? 'No articles published yet.') ?></p>
        <a href="<?= sh_url('index.php') ?>" class="sh-btn-primary"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
    </div>
    <?php else: ?>
    <div class="sh-news-grid">
        <?php foreach ($articles as $article):
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
                <h2><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($title) ?></a></h2>
                <?php if ($excerpt !== ''): ?>
                <p><?= htmlspecialchars($excerpt) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($url) ?>" class="sh-news-readmore"><?= htmlspecialchars($np['read_more'] ?? 'Read more') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>