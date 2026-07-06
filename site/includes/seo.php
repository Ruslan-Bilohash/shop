<?php

require_once dirname(__DIR__, 2) . '/includes/version.php';

function shs_protocol(): string
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    ) ? 'https' : 'http';
}

if (!function_exists('shs_absolute_url')) {
    function shs_absolute_url(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $host = $_SERVER['HTTP_HOST'] ?? SH_DOMAIN;
        return shs_protocol() . '://' . $host . (str_starts_with($path, '/') ? $path : '/' . $path);
    }
}

if (!function_exists('shs_full_lang_url')) {
    function shs_full_lang_url(string $code): string
    {
        return shs_absolute_url(shs_lang_url($code, true));
    }
}

function shs_demo_absolute(string $path = ''): string
{
    return 'https://bilohash.com/shop/' . ltrim($path, '/');
}

function shs_seo_og_image(): string
{
    return 'https://bilohash.com/shop/screenshot/catalog_product.jpg';
}

/** @return array{region: string, place: string, area: string, service: string} */
function shs_seo_market(string $lang): array
{
    require_once __DIR__ . '/market.php';
    $m = shs_market($lang);
    return [
        'region'  => $m['region'],
        'place'   => $m['place_en'],
        'area'    => $m['place_en'],
        'service' => $m['service_en'],
    ];
}

function shs_seo_json(array $graphs): string
{
    $graphs = array_values(array_filter($graphs));
    $data = count($graphs) === 1
        ? array_merge(['@context' => 'https://schema.org'], $graphs[0])
        : ['@context' => 'https://schema.org', '@graph' => $graphs];
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function shs_site_style_version(): string
{
    return '11';
}

function shs_site_script_version(): string
{
    return '4';
}

function shs_meta_description_fit(string $text, int $min = 150, int $max = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) > $max) {
        $text = mb_substr($text, 0, $max);
        $cut = mb_strrpos($text, ' ');
        if ($cut !== false && $cut > (int) ($max * 0.7)) {
            $text = mb_substr($text, 0, $cut);
        }
        $text = rtrim($text, ".,;:!?—-–");
    }
    return $text;
}

function shs_critical_css(): string
{
    static $css = null;
    if ($css === null) {
        $path = __DIR__ . '/../assets/css/site-critical.css';
        $css = is_file($path) ? (string) file_get_contents($path) : '';
    }
    return $css;
}

function shs_font_awesome_href(): string
{
    return 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
}

function shs_render_stylesheets(): void
{
    $siteHref = shs_asset('css/site.css') . '?v=' . shs_site_style_version();
    $faHref = shs_font_awesome_href();
    $critical = shs_critical_css();
    ?>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <?php if ($critical !== ''): ?>
    <style id="shs-critical"><?= $critical ?></style>
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($siteHref) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($faHref) ?>" crossorigin>
    <script src="<?= htmlspecialchars(shs_asset('js/site.js')) ?>?v=<?= shs_site_script_version() ?>" defer></script>
    <?php
}

function shs_render_seo_head(string $page_title, string $page_desc, string $canonical, array $schema_graphs = []): void
{
    global $lang_meta, $lang;
    $canonical_abs = shs_absolute_url($canonical);
    $keywords = $GLOBALS['t']['meta']['keywords'] ?? '';
    $og_image = shs_seo_og_image();
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <?php if ($keywords !== ''): ?>
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Bilohash · Shop CMS">
    <?php $shs_market = shs_seo_market($lang); ?>
    <meta name="geo.region" content="<?= htmlspecialchars($shs_market['region']) ?>">
    <meta name="geo.placename" content="<?= htmlspecialchars($shs_market['place']) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_abs) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(shs_full_lang_url('en')) ?>">
    <?php foreach (shs_langs() as $code => $info): ?>
    <link rel="alternate" hreflang="<?= $code === 'uk' ? 'uk' : $code ?>" href="<?= htmlspecialchars(shs_full_lang_url($code)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" type="text/plain" href="https://bilohash.com/shop/llms.txt" title="LLM context">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_abs) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($GLOBALS['t']['meta']['site_name'] ?? 'Shop CMS') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:image:secure_url" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= htmlspecialchars($GLOBALS['t']['meta']['og_image_alt'] ?? $GLOBALS['t']['meta']['site_name'] ?? 'Shop CMS') ?>">
    <meta property="og:locale" content="<?= htmlspecialchars(str_replace('-', '_', $lang_meta['locale'])) ?>">
    <?php foreach (shs_langs() as $code => $info):
        if ($code === $lang) continue; ?>
    <meta property="og:locale:alternate" content="<?= htmlspecialchars(str_replace('-', '_', $info['locale'])) ?>">
    <?php endforeach; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($GLOBALS['t']['meta']['og_image_alt'] ?? $GLOBALS['t']['meta']['site_name'] ?? 'Shop CMS') ?>">
    <?php if (!empty($schema_graphs)): ?>
    <script type="application/ld+json"><?= shs_seo_json($schema_graphs) ?></script>
    <?php endif;
}

/** @return array<string, mixed> */
function shs_organization_schema(): array
{
    return [
        '@type'       => 'Organization',
        '@id'         => 'https://bilohash.com/shop/#organization',
        'name'        => 'Bilohash · Shop CMS',
        'legalName'   => 'Bilohash',
        'url'         => 'https://bilohash.com/',
        'logo'        => [
            '@type' => 'ImageObject',
            'url'   => 'https://bilohash.com/favicon.ico',
        ],
        'image'       => shs_seo_og_image(),
        'email'       => 'info@bilohash.com',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Drammen',
            'addressRegion'   => 'Viken',
            'postalCode'      => '3044',
            'addressCountry'  => 'NO',
        ],
        'areaServed'  => [
            ['@type' => 'Country', 'name' => 'Norway'],
            ['@type' => 'Country', 'name' => 'Ukraine'],
            ['@type' => 'Place', 'name' => 'Scandinavia'],
            ['@type' => 'Place', 'name' => 'Europe'],
        ],
        'knowsAbout'  => [
            'PHP e-commerce development',
            'Online shop development Norway',
            'Schema.org Product and Offer markup',
            'Stripe PayPal Vipps checkout integration',
            'Multilingual hreflang SEO',
        ],
        'sameAs'      => [
            'https://bilohash.com/',
            'https://github.com/Ruslan-Bilohash/shop',
        ],
    ];
}

function shs_parse_demo_price(string $price): string
{
    $digits = preg_replace('/\D+/', '', $price);
    return $digits !== '' && $digits !== '0' ? $digits : '0';
}

/** @return list<array<string, mixed>> */
function shs_seo_product_examples(array $t, string $lang): array
{
    require_once __DIR__ . '/market.php';
    $currency = shs_market($lang)['currency'];
    $demoUrl = shs_demo_absolute();
    $out = [];
    foreach (array_slice($t['features_showcase']['items'] ?? [], 0, 4) as $i => $fp) {
        if (empty($fp['name'])) {
            continue;
        }
        $out[] = [
            '@type'       => 'Product',
            '@id'         => 'https://bilohash.com/shop/site/#demo-product-' . ($i + 1),
            'name'        => $fp['name'],
            'description' => trim(($fp['category'] ?? '') . ' — ' . ($fp['name'] ?? '')),
            'category'    => $fp['category'] ?? 'Product',
            'image'       => shs_seo_og_image(),
            'brand'       => ['@type' => 'Brand', 'name' => 'Shop CMS Demo'],
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => $demoUrl,
                'priceCurrency' => $currency,
                'price'         => shs_parse_demo_price((string) ($fp['price'] ?? '0')),
                'availability'  => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller'        => ['@id' => 'https://bilohash.com/shop/#organization'],
            ],
        ];
    }
    return $out;
}

function shs_seo_schemas(string $canonical, string $title, string $desc): array
{
    global $lang;
    require_once __DIR__ . '/market.php';
    $market = shs_market($lang);
    return [
        shs_organization_schema(),
        [
            '@type'                  => 'SoftwareApplication',
            '@id'                    => $canonical . '#software',
            'name'                   => 'Shop CMS',
            'applicationCategory'    => 'BusinessApplication',
            'applicationSubCategory' => 'E-commerce and online shop software',
            'operatingSystem'        => 'Web',
            'description'            => $desc,
            'url'                    => $canonical,
            'image'                  => shs_seo_og_image(),
            'inLanguage'             => array_values(array_map(
                static fn(array $info): string => $info['locale'],
                shs_langs()
            )),
            'offers'                 => [
                '@type'           => 'Offer',
                'price'           => '0',
                'priceCurrency'   => $market['currency'],
                'availability'    => 'https://schema.org/InStock',
                'url'             => shs_demo_absolute(),
                'priceValidUntil' => gmdate('Y-m-d', strtotime('+1 year')),
                'seller'          => ['@id' => 'https://bilohash.com/shop/#organization'],
            ],
            'author'                 => ['@id' => 'https://bilohash.com/shop/#organization'],
            'provider'               => ['@id' => 'https://bilohash.com/shop/#organization'],
            'downloadUrl'            => shs_demo_absolute(),
            'softwareVersion'        => sh_version(),
            'datePublished'          => '2026-06-15',
            'dateModified'           => sh_version_date(),
            'featureList'            => 'Product catalog, session cart, categories, Stripe PayPal Vipps COD, multilingual SEO, admin panel, Schema.org',
        ],
        [
            '@type'       => 'ProfessionalService',
            '@id'         => $canonical . '#service',
            'name'        => 'E-commerce website development — ' . (shs_seo_market($lang)['place'] ?? 'Europe'),
            'url'         => shs_absolute_url(shs_url('order.php')),
            'description' => shs_seo_market($lang)['service'],
            'serviceType' => 'E-commerce website development',
            'areaServed'  => shs_seo_area_served($lang),
            'provider'    => ['@id' => 'https://bilohash.com/shop/#organization'],
        ],
        [
            '@type'       => 'WebPage',
            '@id'         => $canonical . '#webpage',
            'url'         => $canonical,
            'name'        => $title,
            'description' => $desc,
            'inLanguage'  => shs_langs()[$lang]['locale'] ?? 'nb-NO',
            'isPartOf'    => ['@id' => 'https://bilohash.com/shop/site/#website'],
            'about'       => ['@id' => $canonical . '#software'],
        ],
    ];
}

/** @return list<array<string, mixed>> */
function shs_seo_area_served(string $lang): array
{
    require_once __DIR__ . '/market.php';
    $m = shs_market($lang);
    return [
        ['@type' => 'Country', 'name' => $m['place_en']],
        ['@type' => 'Place', 'name' => $m['area']],
    ];
}

/** @param array<string, mixed> $t */
function shs_seo_home_schemas(string $canonical, string $title, string $desc, array $t): array
{
    global $lang;
    require_once dirname(__DIR__, 2) . '/includes/vertical-lib.php';

    $graphs = shs_seo_schemas($canonical, $title, $desc);
    foreach ($graphs as &$g) {
        if (($g['@type'] ?? '') === 'ProfessionalService') {
            $g['areaServed'] = shs_seo_area_served($lang);
            $g['serviceType'] = 'E-commerce website development';
        }
    }
    unset($g);

    $graphs[] = [
        '@type' => 'WebSite',
        '@id'   => 'https://bilohash.com/shop/site/#website',
        'name'  => $t['meta']['site_name'] ?? 'Shop CMS',
        'url'   => 'https://bilohash.com/shop/site/',
        'publisher' => ['@id' => 'https://bilohash.com/shop/#organization'],
        'inLanguage' => array_values(array_map(
            static fn(array $info): string => $info['locale'],
            shs_langs()
        )),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => shs_demo_absolute('search.php?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $listItems = [];
    $pos = 1;
    foreach (sh_use_case_slugs() as $slug) {
        $vdef = sh_vertical_defs()[$slug] ?? null;
        if (!$vdef) {
            continue;
        }
        $label = $vdef[$lang] ?? $vdef['en'] ?? $slug;
        $listItems[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $label,
            'url'      => shs_absolute_url(shs_vertical_url($slug, $lang)),
        ];
    }
    if ($listItems !== []) {
        $graphs[] = [
            '@type'           => 'ItemList',
            '@id'             => $canonical . '#usecases',
            'name'            => $t['intro']['use_label'] ?? 'Ideal for',
            'itemListElement' => $listItems,
        ];
    }

    $graphs[] = [
        '@type' => 'BreadcrumbList',
        '@id'   => $canonical . '#breadcrumb',
        'itemListElement' => [
            [
                '@type'    => 'ListItem',
                'position' => 1,
                'name'     => $t['meta']['site_name'] ?? 'Shop CMS',
                'item'     => $canonical,
            ],
        ],
    ];

    foreach (shs_seo_product_examples($t, $lang) as $productSchema) {
        $graphs[] = $productSchema;
    }

    if (!empty($t['faq']['items'])) {
        require_once dirname(__DIR__, 2) . '/includes/vertical-lib.php';
        if (function_exists('sh_seo_faq_page')) {
            $faq = sh_seo_faq_page($t['faq']['items']);
            $faq['@id'] = $canonical . '#faq';
            $graphs[] = $faq;
        }
    }

    return $graphs;
}