<?php

function sh_products(bool $include_inactive = false): array
{
    require_once __DIR__ . '/storage.php';
    $list = sh_load_products_raw();
    if (!$include_inactive) {
        $list = array_values(array_filter($list, fn($p) => ($p['active'] ?? true) !== false));
    }
    return $list;
}

function sh_product_by_id(string $id, bool $include_inactive = false): ?array
{
    foreach (sh_products($include_inactive) as $p) {
        if (($p['id'] ?? '') === $id) {
            return $p;
        }
    }
    return null;
}

/** Fill missing per-language strings from the first available translation. */
function sh_product_normalize_lang_map(array $map, ?string $preferLang = null): array
{
    if (!function_exists('sh_langs')) {
        require_once __DIR__ . '/store-settings.php';
    }
    $fallback = '';
    $order = array_unique(array_filter(array_merge(
        [$preferLang ?? ''],
        ['en', 'no', 'uk', 'ru', 'sv', 'lt'],
        array_keys($map),
        array_keys(sh_langs())
    )));
    foreach ($order as $code) {
        if ($code === '') {
            continue;
        }
        $val = trim((string) ($map[$code] ?? ''));
        if ($val !== '') {
            $fallback = $val;
            break;
        }
    }
    foreach (array_keys(sh_langs()) as $code) {
        if (trim((string) ($map[$code] ?? '')) === '' && $fallback !== '') {
            $map[$code] = $fallback;
        }
    }
    return $map;
}

/** Ensure product name, description and SEO meta fields exist for every active language. */
function sh_product_normalize_record(array $record, ?string $preferLang = null): array
{
    if (isset($record['name']) && is_array($record['name'])) {
        $record['name'] = sh_product_normalize_lang_map($record['name'], $preferLang);
    }
    if (isset($record['desc']) && is_array($record['desc'])) {
        $record['desc'] = sh_product_normalize_lang_map($record['desc'], $preferLang);
    }
    if (!empty($record['seo']) && is_array($record['seo'])) {
        foreach (['meta_title', 'meta_description', 'meta_keywords', 'intro'] as $seoKey) {
            if (!empty($record['seo'][$seoKey]) && is_array($record['seo'][$seoKey])) {
                $record['seo'][$seoKey] = sh_product_normalize_lang_map($record['seo'][$seoKey], $preferLang);
            }
        }
    }
    return $record;
}

function sh_localized(array $item, string $field, string $lang): string
{
    $val = $item[$field] ?? '';
    if (is_array($val)) {
        return $val[$lang] ?? $val['ru'] ?? $val['uk'] ?? $val['en'] ?? $val['no'] ?? '';
    }
    return (string) $val;
}

function sh_categories(): array
{
    require_once __DIR__ . '/category-storage.php';
    $slugs = array_column(sh_category_records(true), 'slug');
    return $slugs !== [] ? $slugs : ['laptops-computers', 'smartphones-tv-electronics', 'fashion-shoes-jewelry', 'home-goods', 'sports-hobbies', 'beauty-health', 'alcohol-food'];
}

function sh_search_params(): array
{
    return [
        'q'        => trim($_GET['q'] ?? ''),
        'category' => $_GET['category'] ?? '',
        'sort'     => $_GET['sort'] ?? 'featured',
        'min_price'=> (int)($_GET['min_price'] ?? 0),
        'max_price'=> (int)($_GET['max_price'] ?? 0),
        'sale'     => $_GET['sale'] ?? '',
    ];
}

function sh_filter_products(array $params, string $lang): array
{
    $items = sh_products();
    $q = bh_str_lower($params['q']);

    if ($q !== '') {
        $items = array_filter($items, function ($p) use ($q, $lang) {
            $hay = bh_str_lower(
                sh_localized($p, 'name', $lang) . ' '
                . sh_localized($p, 'desc', $lang) . ' '
                . ($p['category'] ?? '')
            );
            return str_contains($hay, $q);
        });
    }

    if ($params['category'] !== '' && in_array($params['category'], sh_categories(), true)) {
        $items = array_filter($items, fn($p) => ($p['category'] ?? '') === $params['category']);
    }

    if ($params['sale'] === '1') {
        $items = array_filter($items, fn($p) => !empty($p['sale_price']) && (int)$p['sale_price'] < (int)($p['price'] ?? 0));
    }

    if ($params['min_price'] > 0) {
        $items = array_filter($items, fn($p) => sh_product_price($p) >= $params['min_price']);
    }
    if ($params['max_price'] > 0) {
        $items = array_filter($items, fn($p) => sh_product_price($p) <= $params['max_price']);
    }

    $items = array_values($items);

    usort($items, function ($a, $b) use ($params) {
        return match ($params['sort']) {
            'price_low'  => sh_product_price($a) <=> sh_product_price($b),
            'price_high' => sh_product_price($b) <=> sh_product_price($a),
            'name'       => strcmp(sh_localized($a, 'name', 'en'), sh_localized($b, 'name', 'en')),
            'newest'     => strcmp($b['id'] ?? '', $a['id'] ?? ''),
            default      => (int)!empty($b['featured']) <=> (int)!empty($a['featured'])
                ?: sh_product_price($a) <=> sh_product_price($b),
        };
    });

    return $items;
}

function sh_product_price(array $product): int
{
    $sale = (int)($product['sale_price'] ?? 0);
    $price = (int)($product['price'] ?? 0);
    if ($sale > 0 && $sale < $price) {
        return $sale;
    }
    return $price;
}

function sh_product_original_price(array $product): int
{
    return (int)($product['price'] ?? 0);
}

function sh_product_on_sale(array $product): bool
{
    $sale = (int)($product['sale_price'] ?? 0);
    $price = (int)($product['price'] ?? 0);
    return $sale > 0 && $sale < $price;
}

function sh_featured_products(int $limit = 6): array
{
    $featured = array_values(array_filter(sh_products(), fn($p) => !empty($p['featured'])));
    if (count($featured) < $limit) {
        $featured = array_merge($featured, sh_products());
    }
    $seen = [];
    $out = [];
    foreach ($featured as $p) {
        $id = $p['id'] ?? '';
        if ($id === '' || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $out[] = $p;
        if (count($out) >= $limit) {
            break;
        }
    }
    return $out;
}

function sh_new_arrivals(int $limit = 4): array
{
    $items = sh_products();
    usort($items, fn($a, $b) => strcmp($b['id'] ?? '', $a['id'] ?? ''));
    return array_slice($items, 0, $limit);
}

function sh_category_counts(): array
{
    $counts = array_fill_keys(sh_categories(), 0);
    foreach (sh_products() as $p) {
        $cat = $p['category'] ?? '';
        if (isset($counts[$cat])) {
            $counts[$cat]++;
        }
    }
    return $counts;
}

function sh_platform_stats(): array
{
    $products = sh_products();
    $cart = sh_cart_items();
    return [
        'products'  => count($products),
        'featured'  => count(array_filter($products, fn($p) => !empty($p['featured']))),
        'categories'=> count(array_filter(sh_category_counts())),
        'cart_items'=> sh_cart_count(),
        'volume'    => array_sum(array_map(fn($p) => sh_product_price($p), $products)),
    ];
}

function sh_product_images(array $product): array
{
    if (!empty($product['images']) && is_array($product['images'])) {
        $list = array_values(array_filter(array_map('trim', $product['images'])));
        if ($list !== []) {
            return $list;
        }
    }
    $single = trim($product['image'] ?? '');
    return $single !== '' ? [$single] : [];
}

function sh_product_image(array $product): string
{
    $images = sh_product_images($product);
    return $images[0] ?? sh_placeholder_image();
}

function sh_related_products(array $product, int $limit = 4): array
{
    $cat = $product['category'] ?? '';
    $related = array_values(array_filter(
        sh_products(),
        fn($p) => ($p['id'] ?? '') !== ($product['id'] ?? '') && ($p['category'] ?? '') === $cat
    ));
    if (count($related) < $limit) {
        $extra = array_values(array_filter(
            sh_products(),
            fn($p) => ($p['id'] ?? '') !== ($product['id'] ?? '')
        ));
        $related = array_merge($related, $extra);
    }
    return array_slice($related, 0, $limit);
}

function sh_product_long_desc(array $product, string $lang): string
{
    $long = trim(sh_localized($product, 'long_desc', $lang));
    if ($long !== '') {
        return $long;
    }
    return trim(sh_localized($product, 'desc', $lang));
}

function sh_product_highlights(array $product, string $lang): array
{
    if (!empty($product['highlights'][$lang]) && is_array($product['highlights'][$lang])) {
        return $product['highlights'][$lang];
    }
    if (!empty($product['highlights']['en']) && is_array($product['highlights']['en'])) {
        return $product['highlights']['en'];
    }
    return match ($lang) {
        'no' => ['Demo produkt — ingen ekte betaling', 'NOK-priser', 'EU-frakt (demo)'],
        'uk' => ['Демо-товар — без реальної оплати', 'Ціни в NOK', 'Доставка по ЄС (демо)'],
        'ru' => ['Демо-товар — без реальной оплаты', 'Цены в NOK', 'Доставка по ЕС (демо)'],
        default => ['Demo product — no real payment', 'NOK pricing', 'EU shipping (demo)'],
    };
}

function sh_lang_url(string $code, bool $for_hreflang = false): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: sh_url('index.php');
    parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? '', $q);
    if ($code === 'no' && $for_hreflang) {
        unset($q['lang']);
    } else {
        $q['lang'] = $code;
    }
    $qs = http_build_query($q);
    return $path . ($qs !== '' ? '?' . $qs : '');
}

function sh_category_icon(string $cat): string
{
    require_once __DIR__ . '/category-storage.php';
    $record = sh_category_by_slug($cat, false);
    if ($record && !empty($record['icon'])) {
        return (string) $record['icon'];
    }
    return match ($cat) {
        'electronics' => 'laptop',
        'fashion'     => 'shirt',
        'home'        => 'couch',
        'sports'      => 'dumbbell',
        'beauty'      => 'spa',
        'food'        => 'basket-shopping',
        default       => 'tag',
    };
}

function sh_admin_category_chart(): array
{
    $counts = sh_category_counts();
    $max = max(1, max($counts));
    $chart = [];
    foreach ($counts as $cat => $n) {
        $chart[] = ['cat' => $cat, 'count' => $n, 'pct' => round(($n / $max) * 100)];
    }
    usort($chart, fn($a, $b) => $b['count'] <=> $a['count']);
    return $chart;
}