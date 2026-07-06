<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/news-storage.php';
require_once __DIR__ . '/includes/service-pages.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$slug = trim($_GET['slug'] ?? '');
$article = $slug !== '' ? sh_news_by_slug($slug) : null;

if ($article === null) {
    http_response_code(404);
    $page_title = '404';
    $page_desc  = $t['meta']['description'];
    $canonical  = sh_url('news.php');
    require __DIR__ . '/includes/header.php';
    echo '<div class="sh-container"><div class="sh-form-card sh-empty-state"><i class="fas fa-newspaper"></i><p>' . htmlspecialchars($t['news_page']['not_found'] ?? 'Article not found.') . '</p><a href="' . htmlspecialchars(sh_url('news.php')) . '" class="sh-btn-primary">' . htmlspecialchars($t['footer']['news'] ?? 'News') . '</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$current_page = 'news-article';
$np = $t['news_page'] ?? [];
$title   = sh_localized($article, 'name', $lang);
$excerpt = sh_localized($article, 'excerpt', $lang);
$body    = sh_localized($article, 'body', $lang);
$canon_abs = sh_news_canonical($article, $lang);
$canonical = $canon_abs;
$page_title = sh_news_meta_title($article, $lang);
$page_desc  = sh_news_meta_description($article, $lang);
$seo_keywords = sh_news_meta_keywords($article, $lang);
$seo_settings = sh_seo_settings();
$body_class = 'sh-page-news-article';

$seo_schemas = [];
if (sh_seo_flag($seo_settings, 'seo_schema_organization', true)) {
    $seo_schemas[] = sh_seo_organization();
}
if (sh_news_schema_enabled($article, 'news_article', true)) {
    $seo_schemas[] = sh_seo_news_article($article, $lang, $canon_abs);
}
$seo_schemas[] = sh_seo_webpage($canon_abs, $page_title, $page_desc);
if (sh_news_schema_enabled($article, 'breadcrumb', true) && sh_seo_flag($seo_settings, 'seo_schema_breadcrumbs', true)) {
    $seo_schemas[] = sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $np['h1'] ?? ($t['footer']['news'] ?? 'News'), 'url' => sh_absolute_url(sh_url('news.php' . ($lang !== 'no' ? '?lang=' . $lang : '')))],
        ['name' => $title, 'url' => $canon_abs],
    ]);
}
$seo_og_image = sh_news_og_image($article);
$seo_og_type  = 'article';

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-news-article-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <a href="<?= sh_url('news.php' . ($lang !== 'no' ? '?lang=' . urlencode($lang) : '')) ?>"><?= htmlspecialchars($np['h1'] ?? ($t['footer']['news'] ?? 'News')) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($title) ?></span>
    </nav>

    <article class="sh-service-article sh-news-article" itemscope itemtype="https://schema.org/NewsArticle">
        <meta itemprop="datePublished" content="<?= htmlspecialchars((string) ($article['published_at'] ?? '')) ?>">
        <header class="sh-service-header sh-news-article-header">
            <time class="sh-news-date" datetime="<?= htmlspecialchars((string) ($article['published_at'] ?? '')) ?>">
                <?= htmlspecialchars(sh_news_published_label($article, $lang)) ?>
            </time>
            <h1 itemprop="headline"><?= htmlspecialchars($title) ?></h1>
            <?php if ($excerpt !== ''): ?>
            <p class="sh-news-lead" itemprop="description"><?= htmlspecialchars($excerpt) ?></p>
            <?php endif; ?>
        </header>

        <?php $cover = sh_news_image($article); if ($cover !== sh_placeholder_image()): ?>
        <figure class="sh-news-cover">
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($title) ?>" itemprop="image" loading="eager" width="1200" height="630">
        </figure>
        <?php endif; ?>

        <div class="sh-service-content sh-service-content--rich sh-news-body" itemprop="articleBody">
            <?= $body !== '' ? sh_service_page_content_html($body) : '<p>' . htmlspecialchars($np['empty_body'] ?? 'No content.') . '</p>' ?>
        </div>

        <footer class="sh-news-article-footer">
            <a href="<?= sh_url('news.php' . ($lang !== 'no' ? '?lang=' . urlencode($lang) : '')) ?>" class="sh-btn-outline">
                <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($np['back_list'] ?? 'All news') ?>
            </a>
        </footer>
    </article>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>