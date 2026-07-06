<?php
/**
 * Canonical news release URLs and cross-links per CMS product.
 */
function bh_cms_news_map(): array
{
    return [
        'tavle'     => 'https://bilohash.com/news/tavle.html',
        'pizza'     => 'https://bilohash.com/news/pizza-cms.html',
        'booking'   => 'https://bilohash.com/news/booking-cms.html',
        'auction'   => 'https://bilohash.com/news/auction-cms.html',
        'shop'      => 'https://bilohash.com/shop/news.php',
        'freelance' => 'https://bilohash.com/news/freelance-cms.html',
        'gamehub'   => 'https://bilohash.com/news/gamehub-cms.html',
        'today'     => 'https://bilohash.com/news/today-cms.html',
        '3d'        => 'https://bilohash.com/news/3d-cms.html',
        'ai'        => 'https://bilohash.com/news/ai-norway-chat.html',
        'wordpress' => 'https://bilohash.com/news/wordpress.html',
    ];
}

function bh_cms_news_url(string $slug): ?string
{
    return bh_cms_news_map()[$slug] ?? null;
}

function bh_cms_solutions_url(string $slug): string
{
    $paths = [
        'tavle'     => 'https://bilohash.com/tavle/solutions.php',
        'pizza'     => 'https://bilohash.com/pizza/site/solutions',
        'booking'   => 'https://bilohash.com/booking/solutions.php',
        'auction'   => 'https://bilohash.com/auction/solutions.php',
        'shop'      => 'https://bilohash.com/shop/solutions.php',
        'freelance' => 'https://bilohash.com/freelance/solutions.php',
        'gamehub'   => 'https://bilohash.com/gamehub/solutions.php',
    ];
    return $paths[$slug] ?? 'https://bilohash.com/news/';
}

function bh_cms_demo_url(string $slug): string
{
    require_once __DIR__ . '/ecosystem-defs.php';
    $cat = bh_ecosystem_catalog();
    return $cat[$slug]['demo'] ?? 'https://bilohash.com/';
}

/** Echo footer <li> items: product news release + all releases hub. */
function bh_footer_cms_links(string $slug, string $newsLabel = 'Release notes'): void
{
    $news = bh_cms_news_url($slug);
    if ($news): ?>
    <li><a href="<?= htmlspecialchars($news) ?>" rel="related"><?= htmlspecialchars($newsLabel) ?></a></li>
    <?php endif; ?>
    <li><a href="https://bilohash.com/news/" rel="related">All releases</a></li>
    <?php
}