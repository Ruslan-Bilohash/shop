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
    return 'https://bilohash.com/shop/assets/images/placeholder.svg';
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
    return '9';
}

function shs_site_script_version(): string
{
    return '4';
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
    <meta name="author" content="Shop CMS">
    <?php $shs_market = shs_seo_market($lang); ?>
    <meta name="geo.region" content="<?= htmlspecialchars($shs_market['region']) ?>">
    <meta name="geo.placename" content="<?= htmlspecialchars($shs_market['place']) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_abs) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(shs_full_lang_url('no')) ?>">
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
    <meta property="og:image:alt" content="<?= htmlspecialchars($GLOBALS['t']['meta']['site_name'] ?? 'Shop CMS') ?>">
    <meta property="og:locale" content="<?= htmlspecialchars(str_replace('-', '_', $lang_meta['locale'])) ?>">
    <?php foreach (shs_langs() as $code => $info):
        if ($code === $lang) continue; ?>
    <meta property="og:locale:alternate" content="<?= htmlspecialchars(str_replace('-', '_', $info['locale'])) ?>">
    <?php endforeach; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php if (!empty($schema_graphs)): ?>
    <script type="application/ld+json"><?= shs_seo_json($schema_graphs) ?></script>
    <?php endif;
}

function shs_seo_schemas(string $canonical, string $title, string $desc): array
{
    global $lang;
    require_once __DIR__ . '/market.php';
    return [
        [
            '@type' => 'Organization',
            '@id'   => 'https://bilohash.com/shop/#organization',
            'name'  => 'Shop CMS',
            'url'   => 'https://bilohash.com/shop/site/',
            'logo'  => 'https://bilohash.com/favicon.ico',
            'areaServed' => ['NO', 'UA', 'EU'],
            'knowsAbout' => [
                'PHP e-commerce scripts',
                'Online shop development Norway',
                'Schema.org Product markup',
                'Stripe PayPal Vipps checkout',
            ],
        ],
        [
            '@type'               => 'SoftwareApplication',
            '@id'                 => $canonical . '#software',
            'name'                => 'Shop CMS',
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'E-commerce and online shop software',
            'operatingSystem'     => 'Web',
            'description'         => $desc,
            'url'                 => $canonical,
            'image'               => shs_seo_og_image(),
            'inLanguage'          => ['nb-NO', 'en-GB', 'sv-SE', 'uk-UA', 'ru-RU', 'lt-LT'],
            'offers'              => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => shs_market($lang)['currency']],
            'author'              => ['@type' => 'Organization', 'name' => 'Shop CMS', 'url' => 'https://bilohash.com/shop/site/'],
            'downloadUrl'         => shs_demo_absolute(),
            'softwareVersion'     => sh_version(),
            'datePublished'       => '2026-06-15',
            'dateModified'        => sh_version_date(),
            'featureList'         => 'Product catalog, session cart, categories, Stripe PayPal Vipps COD payments, multilingual SEO, admin panel',
        ],
        [
            '@type' => 'ProfessionalService',
            '@id'   => $canonical . '#service',
            'name'  => 'Order e-commerce website development — ' . (shs_seo_market($lang)['place'] ?? 'Europe'),
            'url'   => $canonical,
            'description' => shs_seo_market($lang)['service'],
            'areaServed' => shs_seo_market($lang)['area'],
            'provider' => ['@id' => 'https://bilohash.com/shop/#organization'],
        ],
        [
            '@type' => 'WebPage',
            '@id'   => $canonical . '#webpage',
            'url'   => $canonical,
            'name'  => $title,
            'description' => $desc,
            'inLanguage' => shs_langs()[$lang]['locale'] ?? 'nb-NO',
            'isPartOf' => ['@id' => 'https://bilohash.com/shop/site/#website'],
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

    if (!empty($t['faq']['items']) && function_exists('sh_seo_faq_page')) {
        $graphs[] = sh_seo_faq_page($t['faq']['items']);
    }

    return $graphs;
}