<?php

function sh_protocol(): string
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    ) ? 'https' : 'http';
}

function sh_host(): string
{
    return $_SERVER['HTTP_HOST'] ?? SH_DOMAIN;
}

function sh_absolute_url(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return sh_protocol() . '://' . sh_host() . (str_starts_with($path, '/') ? $path : '/' . $path);
}

function sh_full_lang_url(string $code): string
{
    return sh_absolute_url(sh_lang_url($code, true));
}

function sh_seo_settings(): array
{
    if (function_exists('sh_site_settings')) {
        return sh_site_settings();
    }
    require_once __DIR__ . '/site-settings.php';
    return sh_merge_site_settings([]);
}

function sh_seo_flag(array $settings, string $key, bool $default = true): bool
{
    if (!array_key_exists($key, $settings)) {
        return $default;
    }
    return (bool) $settings[$key];
}

function sh_seo_localized_field(array $entity, string $field, string $lang, string $fallback = ''): string
{
    $seo = is_array($entity['seo'] ?? null) ? $entity['seo'] : [];
    $localized = $seo[$field] ?? null;
    if (is_array($localized)) {
        $val = trim($localized[$lang] ?? $localized['en'] ?? $localized['no'] ?? '');
        if ($val !== '') {
            return $val;
        }
    }
    return $fallback;
}

function sh_product_meta_title(array $product, string $lang): string
{
    $name = sh_localized($product, 'name', $lang);
    $custom = sh_seo_localized_field($product, 'meta_title', $lang);
    if ($custom !== '') {
        return $custom;
    }
    return $name . ' — ' . sh_seo_site_name();
}

function sh_product_meta_description(array $product, string $lang): string
{
    $custom = sh_seo_localized_field($product, 'meta_description', $lang);
    if ($custom !== '') {
        return $custom;
    }
    $long = sh_product_long_desc($product, $lang);
    $short = sh_localized($product, 'desc', $lang);
    $source = strlen($long) >= strlen($short) ? $long : $short;
    return bh_str_sub($source, 0, 160);
}

function sh_product_meta_keywords(array $product, string $lang): string
{
    return sh_seo_localized_field($product, 'meta_keywords', $lang);
}

function sh_product_canonical(array $product, string $lang): string
{
    $seo = is_array($product['seo'] ?? null) ? $product['seo'] : [];
    $override = trim($seo['canonical_override'] ?? '');
    if ($override !== '') {
        return $override;
    }
    $id = urlencode($product['id'] ?? '');
    return sh_absolute_url(sh_url('product.php?id=' . $id . ($lang !== 'no' ? '&lang=' . $lang : '')));
}

function sh_product_og_image(array $product): string
{
    $seo = is_array($product['seo'] ?? null) ? $product['seo'] : [];
    $custom = trim($seo['og_image'] ?? '');
    if ($custom !== '') {
        return sh_absolute_url($custom);
    }
    return sh_absolute_url(sh_product_image($product));
}

function sh_product_schema_enabled(array $product, string $key, bool $default = true): bool
{
    $seo = is_array($product['seo'] ?? null) ? $product['seo'] : [];
    $schema = is_array($seo['schema'] ?? null) ? $seo['schema'] : [];
    if (!array_key_exists($key, $schema)) {
        return $default;
    }
    return (bool) $schema[$key];
}

function sh_category_meta_title(array $category, string $lang): string
{
    $name = sh_localized($category, 'name', $lang);
    $custom = sh_seo_localized_field($category, 'meta_title', $lang);
    if ($custom !== '') {
        return $custom;
    }
    return $name . ' — ' . sh_seo_site_name();
}

function sh_category_meta_description(array $category, string $lang): string
{
    $custom = sh_seo_localized_field($category, 'meta_description', $lang);
    if ($custom !== '') {
        return $custom;
    }
    $intro = sh_seo_localized_field($category, 'intro', $lang);
    if ($intro !== '') {
        return bh_str_sub($intro, 0, 160);
    }
    return bh_str_sub(sh_localized($category, 'name', $lang) . ' — ' . ($GLOBALS['t']['search_page']['title'] ?? 'Products'), 0, 160);
}

function sh_category_intro(array $category, string $lang): string
{
    return sh_seo_localized_field($category, 'intro', $lang);
}

function sh_category_schema_enabled(array $category, string $key, bool $default = true): bool
{
    $seo = is_array($category['seo'] ?? null) ? $category['seo'] : [];
    $schema = is_array($seo['schema'] ?? null) ? $seo['schema'] : [];
    if (!array_key_exists($key, $schema)) {
        return $default;
    }
    return (bool) $schema[$key];
}

function sh_seo_og_image(): string
{
    $settings = sh_seo_settings();
    $custom = trim($settings['seo_default_og_image'] ?? '');
    if ($custom !== '') {
        return sh_absolute_url($custom);
    }
    return sh_absolute_url(sh_placeholder_image());
}

function sh_seo_author(): array
{
    return [
        '@type' => 'Organization',
        'name'  => sh_seo_org_name(),
        'url'   => sh_absolute_url(sh_url('index.php')),
    ];
}

function sh_seo_organization(): array
{
    $base = sh_absolute_url(sh_url('index.php'));
    return [
        '@type' => 'Organization',
        '@id'   => rtrim($base, '/') . '#organization',
        'name'  => sh_seo_org_name(),
        'url'   => $base,
        'logo'  => sh_seo_og_image(),
        'areaServed' => [sh_seo_settings()['seo_default_country_code'] ?? 'NO', 'UA', 'EU'],
        'knowsAbout' => [
            'PHP e-commerce scripts',
            'Online shop development Norway',
            'Multilingual SEO',
            'Schema.org Product markup',
        ],
    ];
}

function sh_seo_software_app(string $canonical, string $description): array
{
    require_once __DIR__ . '/version.php';
    return [
        '@type'               => 'SoftwareApplication',
        '@id'                 => $canonical . '#software',
        'name'                => SH_SITE_NAME,
        'applicationCategory' => 'BusinessApplication',
        'applicationSubCategory' => 'E-commerce and online shop software',
        'operatingSystem'     => 'Web',
        'description'         => $description,
        'url'                 => $canonical,
        'image'               => sh_seo_og_image(),
        'inLanguage'          => ['nb-NO', 'en-GB', 'sv-SE', 'uk-UA', 'ru-RU'],
        'offers'              => [
            '@type'         => 'Offer',
            'price'         => '0',
            'priceCurrency' => 'NOK',
            'availability'  => 'https://schema.org/InStock',
            'url'           => 'https://bilohash.com/shop/',
        ],
        'author'    => sh_seo_author(),
        'publisher' => sh_seo_organization(),
        'featureList' => 'Multilingual storefront, product search, session cart, admin panel, JSON storage, Schema.org SEO, responsive light UI',
        'softwareVersion'     => sh_version(),
        'dateModified'        => sh_version_date(),
    ];
}

function sh_critical_css(): string
{
    static $css = null;
    if ($css === null) {
        $path = __DIR__ . '/../assets/css/critical.css';
        $css = is_file($path) ? (string) file_get_contents($path) : '';
    }
    return $css;
}

function sh_render_public_stylesheets(): void
{
    $href = sh_asset('css/style.css') . '?v=' . sh_public_style_version();
    $critical = sh_critical_css();
    ?>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <?php if ($critical !== ''): ?>
    <style id="sh-critical"><?= $critical ?></style>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin>
    <script src="<?= htmlspecialchars(sh_asset('js/main.js')) ?>?v=<?= sh_public_script_version() ?>" defer></script>
    <?php
}

function sh_seo_website(string $canonical): array
{
    global $site_url;
    return [
        '@type' => 'WebSite',
        '@id'   => rtrim($site_url, '/') . '/#website',
        'name'  => SH_SITE_NAME,
        'url'   => rtrim($site_url, '/') . '/',
        'inLanguage' => ['nb-NO', 'en-GB', 'uk-UA', 'ru-RU'],
        'publisher' => ['@id' => 'https://bilohash.com/shop/#organization'],
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => rtrim($site_url, '/') . '/search.php?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function sh_seo_webpage(string $canonical, string $title, string $description): array
{
    global $lang;
    return [
        '@type'       => 'WebPage',
        '@id'         => $canonical . '#webpage',
        'url'         => $canonical,
        'name'        => $title,
        'description' => $description,
        'isPartOf'    => ['@id' => rtrim($GLOBALS['site_url'], '/') . '/#website'],
        'about'       => ['@id' => rtrim($GLOBALS['site_url'], '/') . '/#software'],
        'inLanguage'  => sh_langs()[$lang]['locale'] ?? 'en-GB',
    ];
}

function sh_seo_breadcrumbs(array $items): array
{
    $list = [];
    $pos = 1;
    foreach ($items as $item) {
        $entry = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $item['name'],
        ];
        if (!empty($item['url'])) {
            $entry['item'] = $item['url'];
        }
        $list[] = $entry;
    }
    return [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
}

function sh_seo_product(array $product, string $lang, string $canonical): array
{
    $name = sh_localized($product, 'name', $lang);
    $metaCustom = sh_seo_localized_field($product, 'meta_description', $lang);
    $desc = $metaCustom !== ''
        ? sh_product_meta_description($product, $lang)
        : sh_product_long_desc($product, $lang);
    $price = sh_product_price($product);
    $seo = is_array($product['seo'] ?? null) ? $product['seo'] : [];
    $brandName = trim($seo['brand'] ?? '') ?: sh_seo_org_name();

    $images = sh_product_images($product);
    $imageUrls = array_values(array_filter(array_map(
        fn(string $img) => sh_absolute_url($img),
        $images
    )));
    if ($imageUrls === []) {
        $imageUrls = [sh_product_og_image($product)];
    }
    $schemaImage = count($imageUrls) === 1 ? $imageUrls[0] : $imageUrls;

    $graph = [
        '@type' => 'Product',
        '@id'   => $canonical . '#product',
        'name'  => $name,
        'description' => $desc,
        'url'   => $canonical,
        'image' => $schemaImage,
        'mainEntityOfPage' => ['@id' => $canonical . '#webpage'],
        'category' => $product['category'] ?? 'Product',
        'sku' => $product['sku'] ?? ($product['id'] ?? ''),
        'brand' => [
            '@type' => 'Brand',
            'name'  => $brandName,
        ],
    ];

    if (!empty($seo['gtin'])) {
        $graph['gtin'] = $seo['gtin'];
    }
    if (!empty($seo['mpn'])) {
        $graph['mpn'] = $seo['mpn'];
    }

    if (sh_product_schema_enabled($product, 'offer', true)) {
        $graph['offers'] = [
            '@type'           => 'Offer',
            '@id'             => $canonical . '#offer',
            'url'             => $canonical,
            'price'           => (string) $price,
            'priceCurrency'   => SH_CURRENCY,
            'availability'    => ((int)($product['stock'] ?? 0) > 0)
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition'   => 'https://schema.org/NewCondition',
            'seller'          => sh_seo_organization(),
            'eligibleRegion'  => [sh_seo_settings()['seo_default_country_code'] ?? 'NO', 'EU'],
        ];
    }

    if (sh_product_schema_enabled($product, 'aggregate_rating', false)
        && !empty($seo['rating_value'])
        && !empty($seo['rating_count'])) {
        $graph['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => $seo['rating_value'],
            'reviewCount' => (int) $seo['rating_count'],
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }

    return $graph;
}

function sh_news_meta_title(array $article, string $lang): string
{
    $name = sh_localized($article, 'name', $lang);
    $custom = sh_seo_localized_field($article, 'meta_title', $lang);
    if ($custom !== '') {
        return $custom;
    }
    return $name . ' — ' . sh_seo_site_name();
}

function sh_news_meta_description(array $article, string $lang): string
{
    $custom = sh_seo_localized_field($article, 'meta_description', $lang);
    if ($custom !== '') {
        return $custom;
    }
    $excerpt = sh_localized($article, 'excerpt', $lang);
    if ($excerpt !== '') {
        return bh_str_sub($excerpt, 0, 160);
    }
    return bh_str_sub(sh_localized($article, 'name', $lang), 0, 160);
}

function sh_news_meta_keywords(array $article, string $lang): string
{
    return sh_seo_localized_field($article, 'meta_keywords', $lang);
}

function sh_news_canonical(array $article, string $lang): string
{
    $seo = is_array($article['seo'] ?? null) ? $article['seo'] : [];
    $override = trim($seo['canonical_override'] ?? '');
    if ($override !== '') {
        return $override;
    }
    $slug = urlencode($article['slug'] ?? $article['id'] ?? '');
    return sh_absolute_url(sh_url('news-article.php?slug=' . $slug . ($lang !== 'no' ? '&lang=' . $lang : '')));
}

function sh_news_og_image(array $article): string
{
    $seo = is_array($article['seo'] ?? null) ? $article['seo'] : [];
    $custom = trim($seo['og_image'] ?? '');
    if ($custom !== '') {
        return sh_absolute_url($custom);
    }
    $image = trim((string) ($article['image'] ?? ''));
    if ($image !== '') {
        return sh_absolute_url($image);
    }
    return sh_seo_og_image();
}

function sh_news_schema_enabled(array $article, string $key, bool $default = true): bool
{
    $seo = is_array($article['seo'] ?? null) ? $article['seo'] : [];
    $schema = is_array($seo['schema'] ?? null) ? $seo['schema'] : [];
    if (!array_key_exists($key, $schema)) {
        return $default;
    }
    return (bool) $schema[$key];
}

function sh_seo_news_article(array $article, string $lang, string $canonical): array
{
    $headline = sh_localized($article, 'name', $lang);
    $description = sh_localized($article, 'excerpt', $lang);
    $body = sh_localized($article, 'body', $lang);
    $published = trim((string) ($article['published_at'] ?? ''));
    if ($published !== '' && !str_ends_with($published, 'Z') && !preg_match('/[+-]\d{2}:\d{2}$/', $published)) {
        $published .= 'Z';
    }

    $graph = [
        '@type'            => 'NewsArticle',
        '@id'              => $canonical . '#article',
        'headline'         => $headline,
        'description'      => $description,
        'url'              => $canonical,
        'image'            => [sh_news_og_image($article)],
        'datePublished'    => $published !== '' ? $published : gmdate('Y-m-d\TH:i:s\Z'),
        'dateModified'     => $published !== '' ? $published : gmdate('Y-m-d\TH:i:s\Z'),
        'author'           => sh_seo_author(),
        'publisher'        => sh_seo_organization(),
        'mainEntityOfPage' => ['@id' => $canonical . '#webpage'],
        'inLanguage'       => sh_langs()[$lang]['locale'] ?? 'en-GB',
        'isAccessibleForFree' => true,
    ];

    if ($body !== '') {
        $graph['articleBody'] = trim(strip_tags($body));
    }

    return $graph;
}

function sh_seo_collection_page(string $canonical, string $title, string $description): array
{
    return [
        '@type'       => 'CollectionPage',
        '@id'         => $canonical . '#collection',
        'url'         => $canonical,
        'name'        => $title,
        'description' => $description,
        'isPartOf'    => ['@id' => rtrim($GLOBALS['site_url'], '/') . '/#website'],
    ];
}

function sh_seo_item_list(array $products, string $lang, string $listUrl): array
{
    $elements = [];
    $pos = 1;
    foreach (array_slice($products, 0, 20) as $p) {
        $url = sh_absolute_url(sh_url('product.php?id=' . urlencode($p['id'])));
        $elements[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'url'      => $url,
            'name'     => sh_localized($p, 'name', $lang),
        ];
    }
    return [
        '@type'           => 'ItemList',
        'url'             => $listUrl,
        'numberOfItems'   => count($products),
        'itemListElement' => $elements,
    ];
}

function sh_seo_professional_service(): array
{
    return [
        '@type' => 'ProfessionalService',
        '@id'   => 'https://bilohash.com/shop/#service',
        'name'  => 'Custom e-commerce website development — Norway & Europe',
        'url'   => 'https://bilohash.com/shop/',
        'description' => 'Order development of online shops and PHP e-commerce scripts. Fashion, electronics, food, B2B catalogues. Norway and Europe.',
        'areaServed' => ['Norway', 'Ukraine', 'Lithuania', 'Europe'],
        'provider' => sh_seo_organization(),
    ];
}

function sh_seo_json(array $graphs): string
{
    $graphs = array_values(array_filter($graphs));
    if (count($graphs) === 1) {
        $data = array_merge(['@context' => 'https://schema.org'], $graphs[0]);
    } else {
        $data = [
            '@context' => 'https://schema.org',
            '@graph'   => $graphs,
        ];
    }
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function sh_render_seo_head(
    string $page_title,
    string $page_desc,
    string $canonical,
    array $schema_graphs = [],
    ?string $og_image = null,
    ?string $og_type = 'website',
    bool $noindex = false,
    string $keywords = ''
): void {
    global $lang_meta, $lang;
    $settings = sh_seo_settings();
    $og_image = $og_image ?: sh_seo_og_image();
    $canonical_abs = str_starts_with($canonical, 'http') ? $canonical : sh_absolute_url($canonical);
    if ($keywords === '') {
        $keywords = $GLOBALS['t']['meta']['keywords'] ?? '';
    }
    $robots = $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $site_name = sh_seo_site_name($settings);
    $twitter_site = trim($settings['seo_twitter_site'] ?? '');
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <?php if ($keywords !== ''): ?>
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= $robots ?>">
    <meta name="author" content="<?= htmlspecialchars($site_name) ?>">
    <meta name="geo.region" content="<?= htmlspecialchars($settings['seo_geo_region'] ?? 'NO') ?>">
    <meta name="geo.placename" content="<?= htmlspecialchars($settings['seo_geo_placename'] ?? 'Norway') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_abs) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(sh_full_lang_url('no')) ?>">
    <?php foreach (sh_langs() as $code => $info): ?>
    <link rel="alternate" hreflang="<?= $code === 'uk' ? 'uk' : $code ?>" href="<?= htmlspecialchars(sh_full_lang_url($code)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" type="text/plain" href="<?= htmlspecialchars(sh_absolute_url(sh_url('llms.txt'))) ?>" title="LLM context">
    <meta property="og:type" content="<?= htmlspecialchars($og_type) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_abs) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($site_name) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:locale" content="<?= htmlspecialchars(str_replace('-', '_', $lang_meta['locale'])) ?>">
    <?php foreach (sh_langs() as $code => $info):
        if ($code === $lang) continue; ?>
    <meta property="og:locale:alternate" content="<?= htmlspecialchars(str_replace('-', '_', $info['locale'])) ?>">
    <?php endforeach; ?>
    <meta name="twitter:card" content="summary_large_image">
    <?php if ($twitter_site !== ''): ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($twitter_site) ?>">
    <?php endif; ?>
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">

    <?php if (!empty($schema_graphs)): ?>
    <script type="application/ld+json"><?= sh_seo_json(array_values(array_filter($schema_graphs))) ?></script>
    <?php endif;
}