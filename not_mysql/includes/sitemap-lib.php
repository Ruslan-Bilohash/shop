<?php

require_once __DIR__ . '/vertical-lib.php';

function sh_sitemap_settings(): array
{
    require_once __DIR__ . '/payment-settings.php';
    return sh_load_settings();
}

function sh_sitemap_base_url(): string
{
    return rtrim(sh_absolute_url(sh_url('')), '/');
}

/** @return list<string> */
function sh_sitemap_hreflang_codes(): array
{
    return array_keys(sh_langs());
}

function sh_sitemap_lastmod(?int $timestamp = null): string
{
    if ($timestamp === null) {
        $settings = sh_sitemap_settings();
        $stored = trim((string) ($settings['sitemap_last_generated'] ?? ''));
        if ($stored !== '') {
            return substr($stored, 0, 10);
        }
        $timestamp = time();
    }
    return gmdate('Y-m-d', $timestamp);
}

function sh_sitemap_emit_headers(): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    header('X-Robots-Tag: noindex');
}

function sh_sitemap_open_urlset(): void
{
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
}

function sh_sitemap_close_urlset(): void
{
    echo '</urlset>';
}

function sh_sitemap_open_index(): void
{
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
}

function sh_sitemap_close_index(): void
{
    echo '</sitemapindex>';
}

function sh_sitemap_lang_url(string $path, string $lang): string
{
    if ($lang === 'no') {
        return sh_absolute_url($path);
    }
    $sep = str_contains($path, '?') ? '&' : '?';
    return sh_absolute_url($path . $sep . 'lang=' . rawurlencode($lang));
}

function sh_sitemap_render_hreflang(string $path): void
{
    ?>
        <xhtml:link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(sh_sitemap_lang_url($path, 'no')) ?>"/>
        <?php foreach (sh_sitemap_hreflang_codes() as $code): ?>
        <xhtml:link rel="alternate" hreflang="<?= $code === 'uk' ? 'uk' : htmlspecialchars($code) ?>" href="<?= htmlspecialchars(sh_sitemap_lang_url($path, $code)) ?>"/>
        <?php endforeach; ?>
    <?php
}

function sh_sitemap_render_url(string $loc, string $priority, string $changefreq, ?string $hreflang_path = null, ?string $lastmod = null): void
{
    ?>
    <url>
        <loc><?= htmlspecialchars($loc) ?></loc>
        <?php if ($lastmod): ?>
        <lastmod><?= htmlspecialchars($lastmod) ?></lastmod>
        <?php endif; ?>
        <changefreq><?= htmlspecialchars($changefreq) ?></changefreq>
        <priority><?= htmlspecialchars($priority) ?></priority>
        <?php if ($hreflang_path !== null) {
            sh_sitemap_render_hreflang($hreflang_path);
        } ?>
    </url>
    <?php
}

/** @return list<array{loc:string,priority:string,changefreq:string,hreflang_path:?string}> */
function sh_sitemap_page_entries(array $settings): array
{
    $homePriority = $settings['sitemap_priority_home'] ?? '1.0';
    $entries = [
        ['loc' => sh_sitemap_lang_url(sh_url('index.php'), 'no'), 'priority' => $homePriority, 'changefreq' => 'weekly', 'hreflang_path' => sh_url('index.php')],
        ['loc' => sh_sitemap_lang_url(sh_url('site/'), 'no'), 'priority' => '0.95', 'changefreq' => 'weekly', 'hreflang_path' => sh_url('site/')],
        ['loc' => sh_sitemap_lang_url(sh_url('site/order.php'), 'no'), 'priority' => '0.9', 'changefreq' => 'monthly', 'hreflang_path' => sh_url('site/order.php')],
        ['loc' => sh_sitemap_lang_url(sh_url('contact.php'), 'no'), 'priority' => '0.88', 'changefreq' => 'monthly', 'hreflang_path' => sh_url('contact.php')],
    ];
    require_once __DIR__ . '/service-pages.php';
    $settings = sh_merge_service_settings($settings);
    foreach (sh_service_page_slugs($settings) as $slug) {
        $page = $settings['service_pages'][$slug] ?? null;
        if (!is_array($page) || ($page['active'] ?? true) === false) {
            continue;
        }
        $path = sh_url('page.php?slug=' . rawurlencode($slug));
        $entries[] = [
            'loc' => sh_sitemap_lang_url($path, 'no'),
            'priority' => '0.84',
            'changefreq' => 'monthly',
            'hreflang_path' => $path,
        ];
    }
    $entries = array_merge($entries, [
        ['loc' => sh_sitemap_lang_url(sh_url('solutions.php'), 'no'), 'priority' => '0.92', 'changefreq' => 'weekly', 'hreflang_path' => sh_url('solutions.php')],
        ['loc' => sh_sitemap_lang_url(sh_url('search.php'), 'no'), 'priority' => '0.87', 'changefreq' => 'daily', 'hreflang_path' => sh_url('search.php')],
        ['loc' => sh_sitemap_lang_url(sh_url('news.php'), 'no'), 'priority' => '0.82', 'changefreq' => 'weekly', 'hreflang_path' => sh_url('news.php')],
        ['loc' => sh_sitemap_lang_url(sh_url('cart.php'), 'no'), 'priority' => '0.5', 'changefreq' => 'monthly', 'hreflang_path' => sh_url('cart.php')],
        ['loc' => sh_absolute_url(sh_url('llms.txt')), 'priority' => '0.4', 'changefreq' => 'monthly', 'hreflang_path' => null],
    ]);
    return $entries;
}

/** @return list<array{loc:string,priority:string,changefreq:string,hreflang_path:?string,lastmod:?string}> */
function sh_sitemap_news_entries(): array
{
    require_once __DIR__ . '/news-storage.php';
    $entries = [];
    foreach (sh_news_active_list() as $article) {
        $slug = (string) ($article['slug'] ?? $article['id'] ?? '');
        if ($slug === '') {
            continue;
        }
        $path = sh_url('news-article.php?slug=' . rawurlencode($slug));
        $lastmod = trim((string) ($article['published_at'] ?? ''));
        $entries[] = [
            'loc' => sh_sitemap_lang_url($path, 'no'),
            'priority' => '0.78',
            'changefreq' => 'monthly',
            'hreflang_path' => $path,
            'lastmod' => $lastmod !== '' ? substr($lastmod, 0, 10) : null,
        ];
    }
    return $entries;
}

/** @return list<array{loc:string,priority:string,changefreq:string,hreflang_path:?string}> */
function sh_sitemap_product_entries(array $settings): array
{
    $priority = $settings['sitemap_priority_product'] ?? '0.8';
    $entries = [];
    foreach (sh_products() as $product) {
        $id = $product['id'] ?? '';
        if ($id === '') {
            continue;
        }
        $path = sh_url('product.php?id=' . rawurlencode($id));
        $entries[] = [
            'loc' => sh_sitemap_lang_url($path, 'no'),
            'priority' => $priority,
            'changefreq' => 'weekly',
            'hreflang_path' => $path,
        ];
    }
    return $entries;
}

/** @return list<array{loc:string,priority:string,changefreq:string,hreflang_path:?string}> */
function sh_sitemap_category_entries(array $settings): array
{
    $priority = $settings['sitemap_priority_category'] ?? '0.85';
    $entries = [];
    foreach (sh_category_records(true) as $cat) {
        $slug = $cat['slug'] ?? '';
        if ($slug === '') {
            continue;
        }
        $path = sh_url('search.php?category=' . rawurlencode($slug));
        $entries[] = [
            'loc' => sh_sitemap_lang_url($path, 'no'),
            'priority' => $priority,
            'changefreq' => 'weekly',
            'hreflang_path' => $path,
        ];
    }
    return $entries;
}

/** @return list<array{loc:string,priority:string,changefreq:string,hreflang_path:?string}> */
function sh_sitemap_vertical_entries(): array
{
    $entries = [];
    foreach (array_keys(sh_vertical_defs()) as $vslug) {
        $path = sh_url($vslug . '/');
        $entries[] = [
            'loc' => sh_sitemap_lang_url($path, 'no'),
            'priority' => '0.86',
            'changefreq' => 'monthly',
            'hreflang_path' => $path,
        ];
    }
    return $entries;
}

function sh_sitemap_index_entries(array $settings): array
{
    $lastmod = sh_sitemap_lastmod();
    $maps = [
        ['loc' => sh_absolute_url(sh_url('sitemap-pages.xml')), 'lastmod' => $lastmod],
    ];
    if (!empty($settings['sitemap_include_products'])) {
        $maps[] = ['loc' => sh_absolute_url(sh_url('sitemap-products.xml')), 'lastmod' => $lastmod];
    }
    if (!empty($settings['sitemap_include_categories'])) {
        $maps[] = ['loc' => sh_absolute_url(sh_url('sitemap-categories.xml')), 'lastmod' => $lastmod];
    }
    if (!empty($settings['sitemap_include_verticals'])) {
        $maps[] = ['loc' => sh_absolute_url(sh_url('sitemap-verticals.xml')), 'lastmod' => $lastmod];
    }
    if (!empty($settings['sitemap_include_news']) && sh_sitemap_news_entries() !== []) {
        $maps[] = ['loc' => sh_absolute_url(sh_url('sitemap-news.xml')), 'lastmod' => $lastmod];
    }
    return $maps;
}

function sh_sitemap_render_entries(array $entries, ?string $lastmod = null): void
{
    $lastmod ??= sh_sitemap_lastmod();
    foreach ($entries as $entry) {
        sh_sitemap_render_url(
            $entry['loc'],
            $entry['priority'] ?? '0.5',
            $entry['changefreq'] ?? 'monthly',
            $entry['hreflang_path'] ?? null,
            $entry['lastmod'] ?? $lastmod
        );
    }
}