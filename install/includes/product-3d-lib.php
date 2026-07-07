<?php
/**
 * Shop CMS — 3D product presets for homepage viewers.
 */
declare(strict_types=1);

/** @return array{preset:string,color:string,model:?string} */
function sh_product_3d_config(array $product): array
{
    $id = (string) ($product['id'] ?? '');
    $model = trim((string) ($product['model_3d'] ?? $product['model3d'] ?? ''));
    $modelUrl = $model !== '' ? sh_product_3d_model_url($model) : null;

    $map = [
        'wireless-headphones-pro' => ['preset' => 'headphones', 'color' => '#2563eb'],
        'smartwatch-fitness'      => ['preset' => 'watch', 'color' => '#0f766e'],
        'scandinavian-table-lamp' => ['preset' => 'lamp', 'color' => '#d97706'],
        'merino-wool-sweater'     => ['preset' => 'apparel', 'color' => '#64748b'],
        'leather-crossbody-bag'   => ['preset' => 'bag', 'color' => '#92400e'],
        'ceramic-coffee-set'      => ['preset' => 'mug', 'color' => '#b45309'],
        'ceramic-dinner-set'      => ['preset' => 'mug', 'color' => '#78716c'],
        'yoga-mat-premium'        => ['preset' => 'mat', 'color' => '#7c3aed'],
        'trail-running-shoes'     => ['preset' => 'shoe', 'color' => '#dc2626'],
    ];

    if (isset($map[$id])) {
        return ['preset' => $map[$id]['preset'], 'color' => $map[$id]['color'], 'model' => $modelUrl];
    }

    $cat = (string) ($product['category'] ?? '');
    $preset = 'default';
    if (str_contains($cat, 'electronic') || str_contains($cat, 'smart')) {
        $preset = 'watch';
    } elseif (str_contains($cat, 'fashion') || str_contains($cat, 'cloth')) {
        $preset = 'apparel';
    } elseif (str_contains($cat, 'home') || str_contains($cat, 'kitchen')) {
        $preset = 'mug';
    } elseif (str_contains($cat, 'sport')) {
        $preset = 'mat';
    }

    $hue = crc32($id) % 360;
    $color = sprintf('hsl(%d, 62%%, 48%%)', $hue);

    return ['preset' => $preset, 'color' => $color, 'model' => $modelUrl];
}

function sh_product_3d_model_url(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return sh_url(ltrim($path, '/'));
}

/** Pinned demo products for homepage 3D row (always these three when available). */
function sh_homepage_3d_product_ids(): array
{
    return [
        'wireless-headphones-pro',
        'smartwatch-fitness',
        'scandinavian-table-lamp',
    ];
}

/** Test poster URLs for pinned homepage 3D demos (Unsplash). */
function sh_homepage_3d_demo_posters(): array
{
    return [
        'wireless-headphones-pro' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80',
        'smartwatch-fitness'      => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80',
        'scandinavian-table-lamp' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800&q=80',
    ];
}

function sh_homepage_3d_poster(array $product): string
{
    $id = (string) ($product['id'] ?? '');
    $demo = sh_homepage_3d_demo_posters();
    if ($id !== '' && isset($demo[$id])) {
        return $demo[$id];
    }
    require_once __DIR__ . '/helpers.php';
    return sh_product_image($product);
}

/** @return array<string, array<string, mixed>> */
function sh_homepage_3d_seed_index(): array
{
    static $index = null;
    if (is_array($index)) {
        return $index;
    }
    $index = [];
    require_once __DIR__ . '/storage.php';
    $seed = sh_default_products_from_seed();
    if (!is_array($seed)) {
        return $index;
    }
    foreach ($seed as $row) {
        $pid = (string) ($row['id'] ?? '');
        if ($pid !== '') {
            $index[$pid] = $row;
        }
    }
    return $index;
}

/** @return array<string, mixed>|null */
function sh_homepage_3d_resolve_product(string $id): ?array
{
    require_once __DIR__ . '/helpers.php';
    $product = sh_product_by_id($id);
    $seed = sh_homepage_3d_seed_index()[$id] ?? null;

    if (!is_array($product)) {
        return is_array($seed) ? $seed : null;
    }
    if (($product['active'] ?? true) === false) {
        return null;
    }
    if (!is_array($seed)) {
        return $product;
    }
    if (trim((string) ($product['image'] ?? '')) === '' && empty($product['images'])) {
        $product['image'] = $seed['image'] ?? '';
    }
    foreach (['name', 'desc', 'category', 'featured', 'price', 'sale_price', 'stock', 'sku'] as $field) {
        if (!isset($product[$field]) || $product[$field] === '' || $product[$field] === []) {
            if (array_key_exists($field, $seed)) {
                $product[$field] = $seed[$field];
            }
        }
    }
    return $product;
}

/** @return list<array<string, mixed>> */
function sh_homepage_3d_products(): array
{
    require_once __DIR__ . '/helpers.php';
    $out = [];
    foreach (sh_homepage_3d_product_ids() as $id) {
        $product = sh_homepage_3d_resolve_product($id);
        if (is_array($product)) {
            $out[] = $product;
        }
    }
    if (count($out) >= 3) {
        return array_slice($out, 0, 3);
    }
    foreach (sh_featured_products(12) as $product) {
        $id = (string) ($product['id'] ?? '');
        if ($id === '') {
            continue;
        }
        $seen = array_column($out, 'id');
        if (in_array($id, $seen, true)) {
            continue;
        }
        $out[] = $product;
        if (count($out) >= 3) {
            break;
        }
    }
    return $out;
}