<?php

function sh_categories_file(): string
{
    return sh_data_path('categories.json');
}

function sh_default_categories_from_seed(): ?array
{
    $seedFile = __DIR__ . '/../data/categories.php';
    if (!is_readable($seedFile)) {
        return null;
    }
    $defaults = require $seedFile;
    return is_array($defaults) ? $defaults : null;
}

/** @return array<string, string> Old demo slugs → current Rozetka-style slugs */
function sh_category_legacy_slug_map(): array
{
    return [
        'electronics' => 'smartphones-tv-electronics',
        'fashion'     => 'fashion-shoes-jewelry',
        'home'        => 'home-goods',
        'sports'      => 'sports-hobbies',
        'beauty'      => 'beauty-health',
        'food'        => 'alcohol-food',
    ];
}

/** Fill missing language labels from the first available translation. */
function sh_category_normalize_names(array $names): array
{
    if (!function_exists('sh_langs')) {
        require_once __DIR__ . '/store-settings.php';
    }
    $fallback = '';
    foreach (array_merge(['en', 'no', 'uk', 'ru', 'sv'], array_keys($names)) as $code) {
        $val = trim((string) ($names[$code] ?? ''));
        if ($val !== '') {
            $fallback = $val;
            break;
        }
    }
    foreach (array_keys(sh_langs()) as $code) {
        if (trim((string) ($names[$code] ?? '')) === '' && $fallback !== '') {
            $names[$code] = $fallback;
        }
    }
    return $names;
}

function sh_category_migrate_legacy_data(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $map = sh_category_legacy_slug_map();
    $legacySlugs = array_keys($map);
    $jsonFile = sh_categories_file();

    require_once __DIR__ . '/storage.php';
    $products = sh_load_products_raw();
    $productsChanged = false;
    foreach ($products as &$product) {
        $cat = (string) ($product['category'] ?? '');
        if (isset($map[$cat])) {
            $product['category'] = $map[$cat];
            $productsChanged = true;
        }
    }
    unset($product);
    if ($productsChanged) {
        sh_save_products($products);
    }

    if (!is_readable($jsonFile)) {
        return;
    }
    $existing = json_decode(file_get_contents($jsonFile) ?: '[]', true);
    if (!is_array($existing) || $existing === []) {
        return;
    }

    $hasLegacy = false;
    foreach ($existing as $cat) {
        if (in_array((string) ($cat['slug'] ?? ''), $legacySlugs, true)) {
            $hasLegacy = true;
            break;
        }
    }

    $needsSave = $hasLegacy;
    $newList = [];
    foreach ($existing as $cat) {
        $slug = (string) ($cat['slug'] ?? '');
        if (in_array($slug, $legacySlugs, true)) {
            continue;
        }
        $before = is_array($cat['name'] ?? null) ? $cat['name'] : [];
        $after = sh_category_normalize_names($before);
        if (json_encode($before) !== json_encode($after)) {
            $needsSave = true;
        }
        $cat['name'] = $after;
        $newList[] = $cat;
    }

    if ($needsSave) {
        sh_save_categories($newList);
    }
}

function sh_ensure_categories_json(): void
{
    $json = sh_categories_file();
    $defaults = sh_default_categories_from_seed();
    if ($defaults === null) {
        return;
    }
    if (!is_file($json)) {
        sh_save_categories($defaults);
        sh_category_migrate_legacy_data();
        return;
    }
    $existing = json_decode(file_get_contents($json) ?: '[]', true);
    if (!is_array($existing) || $existing === []) {
        sh_save_categories($defaults);
    }
    sh_category_migrate_legacy_data();
}

function sh_load_categories_raw(): array
{
    sh_ensure_categories_json();
    $file = sh_categories_file();
    if (is_readable($file)) {
        $data = json_decode(file_get_contents($file) ?: '[]', true);
        if (is_array($data) && $data !== []) {
            return $data;
        }
    }

    $seed = sh_default_categories_from_seed();
    return $seed ?? [];
}

function sh_save_categories(array $list): bool
{
    usort($list, fn($a, $b) => ($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
    $json = json_encode(array_values($list), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents(sh_categories_file(), $json, LOCK_EX) !== false;
}

function sh_category_records(bool $active_only = false): array
{
    $list = sh_load_categories_raw();
    if ($active_only) {
        $list = array_values(array_filter($list, fn($c) => ($c['active'] ?? true) !== false));
    }
    usort($list, fn($a, $b) => ($a['sort'] ?? 99) <=> ($b['sort'] ?? 99));
    return $list;
}

function sh_category_by_slug(string $slug, bool $include_inactive = true): ?array
{
    foreach (sh_load_categories_raw() as $cat) {
        if (($cat['slug'] ?? '') === $slug) {
            if (!$include_inactive && ($cat['active'] ?? true) === false) {
                return null;
            }
            return $cat;
        }
    }
    return null;
}

function sh_category_label(string $slug, string $lang): string
{
    $cat = sh_category_by_slug($slug, false);
    if ($cat !== null) {
        $name = sh_localized($cat, 'name', $lang);
        if ($name !== '') {
            return $name;
        }
    }

    global $t;
    return $t['categories'][$slug] ?? $slug;
}

function sh_category_slug_valid(string $slug): bool
{
    return (bool) preg_match('/^[a-z][a-z0-9_-]{1,31}$/', $slug);
}

function sh_category_icon_options(): array
{
    return [
        'laptop', 'mobile-screen-button', 'gamepad', 'blender', 'house', 'car',
        'screwdriver-wrench', 'faucet', 'seedling', 'football', 'shirt', 'spa',
        'baby', 'paw', 'book', 'wine-bottle', 'pump-soap', 'solar-panel', 'gift',
        'fire', 'percent', 'couch', 'dumbbell', 'basket-shopping', 'tag', 'gem',
        'music', 'camera', 'utensils', 'leaf', 'heart', 'star', 'bolt',
        'comments', 'headset', 'robot', 'message', 'comment-dots', 'envelope',
        'circle-question', 'store', 'cart-shopping', 'user', 'phone',
    ];
}

function sh_category_product_count(string $slug): int
{
    $count = 0;
    foreach (sh_products(true) as $product) {
        if (($product['category'] ?? '') === $slug) {
            $count++;
        }
    }
    return $count;
}

function sh_category_upsert(array $record): bool
{
    $slug = trim($record['slug'] ?? '');
    if (!sh_category_slug_valid($slug)) {
        return false;
    }

    $list = sh_load_categories_raw();
    $found = false;
    $existing = null;
    foreach ($list as $cat) {
        if (($cat['slug'] ?? '') === $slug) {
            $existing = $cat;
            break;
        }
    }

    $payload = [
        'slug'   => $slug,
        'icon'   => trim($record['icon'] ?? 'tag') ?: 'tag',
        'active' => ($record['active'] ?? true) !== false,
        'sort'   => max(1, (int)($record['sort'] ?? 99)),
        'name'   => sh_category_normalize_names(is_array($record['name'] ?? null) ? $record['name'] : []),
    ];
    if (!empty($record['seo']) && is_array($record['seo'])) {
        $payload['seo'] = $record['seo'];
    } elseif (is_array($existing['seo'] ?? null)) {
        $payload['seo'] = $existing['seo'];
    }

    foreach ($list as $i => $cat) {
        if (($cat['slug'] ?? '') === $slug) {
            $list[$i] = $payload;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $list[] = $payload;
    }

    return sh_save_categories($list);
}

function sh_category_unlink_products(string $slug): int
{
    require_once __DIR__ . '/storage.php';
    $slug = trim($slug);
    if ($slug === '') {
        return 0;
    }
    $list = sh_load_products_raw();
    $changed = 0;
    foreach ($list as &$product) {
        if (($product['category'] ?? '') === $slug) {
            $product['category'] = '';
            $changed++;
        }
    }
    unset($product);
    if ($changed > 0) {
        sh_save_products($list);
    }
    return $changed;
}

function sh_category_delete(string $slug): bool
{
    $slug = trim($slug);
    if ($slug === '' || sh_category_by_slug($slug, true) === null) {
        return false;
    }

    sh_category_unlink_products($slug);

    $list = array_values(array_filter(
        sh_load_categories_raw(),
        fn($c) => ($c['slug'] ?? '') !== $slug
    ));

    return sh_save_categories($list);
}

/** @param list<string> $orderedSlugs */
function sh_category_reorder(array $orderedSlugs): bool
{
    $orderedSlugs = array_values(array_filter(array_map(
        static fn($slug): string => trim((string) $slug),
        $orderedSlugs
    )));
    if ($orderedSlugs === []) {
        return false;
    }

    $list = sh_load_categories_raw();
    $bySlug = [];
    foreach ($list as $cat) {
        $slug = (string) ($cat['slug'] ?? '');
        if ($slug !== '') {
            $bySlug[$slug] = $cat;
        }
    }

    $reordered = [];
    $sort = 1;
    foreach ($orderedSlugs as $slug) {
        if (!isset($bySlug[$slug])) {
            continue;
        }
        $cat = $bySlug[$slug];
        $cat['sort'] = $sort++;
        $reordered[] = $cat;
        unset($bySlug[$slug]);
    }
    foreach ($bySlug as $cat) {
        $cat['sort'] = $sort++;
        $reordered[] = $cat;
    }

    return sh_save_categories($reordered);
}