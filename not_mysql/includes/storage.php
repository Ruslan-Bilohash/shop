<?php

require_once __DIR__ . '/database.php';

function sh_data_path(string $file): string
{
    return __DIR__ . '/../data/' . $file;
}

function sh_default_products_from_seed(): ?array
{
    $seedFile = __DIR__ . '/../data/products.php';
    if (!is_readable($seedFile)) {
        $seedFile = __DIR__ . '/../seed/products.php';
    }
    if (!is_readable($seedFile)) {
        $seedFile = __DIR__ . '/../install/seed/products.php';
    }
    if (!is_readable($seedFile)) {
        return null;
    }
    $defaults = require $seedFile;
    if (!is_array($defaults)) {
        return null;
    }
    foreach ($defaults as &$product) {
        $product['active'] = $product['active'] ?? true;
    }
    unset($product);
    return $defaults;
}

function sh_save_products(array $list): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    return sh_db_save_products(array_values($list));
}

function sh_load_products_raw(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        return sh_db_load_products();
    } catch (Throwable $e) {
        return [];
    }
}

/** Merge seed products by id when missing from catalog (safe for production). */
function sh_products_merge_missing_by_ids(array $ids): int
{
    if ($ids === [] || !sh_is_installed()) {
        return 0;
    }
    $seed = sh_default_products_from_seed();
    if (!is_array($seed) || $seed === []) {
        return 0;
    }
    $existing = sh_load_products_raw();
    $have = [];
    foreach ($existing as $row) {
        $pid = (string) ($row['id'] ?? '');
        if ($pid !== '') {
            $have[$pid] = true;
        }
    }
    $added = 0;
    foreach ($seed as $product) {
        $pid = (string) ($product['id'] ?? '');
        if ($pid === '' || !in_array($pid, $ids, true) || isset($have[$pid])) {
            continue;
        }
        $existing[] = $product;
        $have[$pid] = true;
        $added++;
    }
    if ($added > 0) {
        sh_save_products($existing);
    }
    return $added;
}

function sh_bootstrap_data(): void
{
    if (!sh_is_installed()) {
        return;
    }
    require_once __DIR__ . '/category-storage.php';
    require_once __DIR__ . '/news-storage.php';
    require_once __DIR__ . '/products-ecosystem-seed.php';
    $bootstrapIds = array_merge(
        [
            'shop-cms-api-monthly',
            'shop-cms-updates-yearly',
            'wireless-headphones-pro',
            'smartwatch-fitness',
            'scandinavian-table-lamp',
        ],
        sh_ecosystem_cms_product_ids()
    );
    sh_products_merge_missing_by_ids($bootstrapIds);
    if (!isset($_SESSION['sh_cart']) || !is_array($_SESSION['sh_cart'])) {
        $_SESSION['sh_cart'] = [];
    }
    if (is_file(__DIR__ . '/product-reviews-storage.php')) {
        require_once __DIR__ . '/product-reviews-storage.php';
        sh_product_reviews_seed_demo();
    }
}

function sh_cart_items(): array
{
    return $_SESSION['sh_cart'] ?? [];
}

function sh_cart_count(): int
{
    $count = 0;
    foreach (sh_cart_items() as $item) {
        $count += max(1, (int)($item['qty'] ?? 1));
    }
    return $count;
}

function sh_cart_add(string $product_id, int $qty = 1): bool
{
    $product = sh_product_by_id($product_id);
    if (!$product) {
        return false;
    }
    $qty = max(1, min(99, $qty));
    $cart = sh_cart_items();
    if (isset($cart[$product_id])) {
        $cart[$product_id]['qty'] = min(99, (int)$cart[$product_id]['qty'] + $qty);
    } else {
        $cart[$product_id] = [
            'id'    => $product_id,
            'qty'   => $qty,
            'price' => (int)($product['price'] ?? 0),
            'name'  => $product['name'] ?? [],
            'image' => $product['image'] ?? '',
        ];
    }
    $_SESSION['sh_cart'] = $cart;
    return true;
}

function sh_cart_remove(string $product_id): void
{
    $cart = sh_cart_items();
    unset($cart[$product_id]);
    $_SESSION['sh_cart'] = $cart;
}

function sh_cart_update(string $product_id, int $qty): void
{
    $cart = sh_cart_items();
    if (!isset($cart[$product_id])) {
        return;
    }
    if ($qty <= 0) {
        unset($cart[$product_id]);
    } else {
        $cart[$product_id]['qty'] = max(1, min(99, $qty));
    }
    $_SESSION['sh_cart'] = $cart;
}

function sh_cart_clear(): void
{
    $_SESSION['sh_cart'] = [];
}

function sh_cart_total(): int
{
    $total = 0;
    foreach (sh_cart_items() as $item) {
        $total += (int)($item['price'] ?? 0) * max(1, (int)($item['qty'] ?? 1));
    }
    return $total;
}

function sh_product_id_valid(string $id): bool
{
    return (bool) preg_match('/^[a-z][a-z0-9_-]{1,48}$/', $id);
}

function sh_product_upsert(array $record): bool
{
    $id = trim($record['id'] ?? '');
    if (!sh_product_id_valid($id)) {
        return false;
    }

    $list = sh_load_products_raw();
    $found = false;
    $images = [];
    if (!empty($record['images']) && is_array($record['images'])) {
        $images = array_values(array_filter(array_map('trim', $record['images'])));
    }
    $primaryImage = $images[0] ?? trim($record['image'] ?? '');

    $payload = [
        'id'         => $id,
        'category'   => trim($record['category'] ?? ''),
        'featured'   => !empty($record['featured']),
        'active'     => ($record['active'] ?? true) !== false,
        'price'      => max(0, (int)($record['price'] ?? 0)),
        'sale_price' => max(0, (int)($record['sale_price'] ?? 0)),
        'sku'        => trim($record['sku'] ?? '') ?: $id,
        'stock'      => max(0, (int)($record['stock'] ?? 0)),
        'name'       => is_array($record['name'] ?? null) ? $record['name'] : [],
        'desc'       => is_array($record['desc'] ?? null) ? $record['desc'] : [],
        'image'      => $primaryImage,
        'images'     => $images !== [] ? $images : ($primaryImage !== '' ? [$primaryImage] : []),
    ];

    if (!empty($record['long_desc']) && is_array($record['long_desc'])) {
        $payload['long_desc'] = $record['long_desc'];
    }
    if (!empty($record['highlights']) && is_array($record['highlights'])) {
        $payload['highlights'] = $record['highlights'];
    }
    if (!empty($record['seo']) && is_array($record['seo'])) {
        $payload['seo'] = $record['seo'];
    }

    foreach ($list as $i => $product) {
        if (($product['id'] ?? '') === $id) {
            $list[$i] = array_merge($product, $payload);
            $found = true;
            break;
        }
    }

    if (!$found) {
        $list[] = $payload;
    }

    return sh_save_products($list);
}

function sh_product_delete(string $id): bool
{
    $list = array_values(array_filter(
        sh_load_products_raw(),
        fn($p) => ($p['id'] ?? '') !== $id
    ));
    return sh_save_products($list);
}

function sh_cart_lines(string $lang): array
{
    $lines = [];
    foreach (sh_cart_items() as $id => $item) {
        $product = sh_product_by_id($id);
        if (!$product) {
            continue;
        }
        $qty = max(1, (int)($item['qty'] ?? 1));
        $price = (int)($product['price'] ?? $item['price'] ?? 0);
        $lines[] = [
            'id'       => $id,
            'product'  => $product,
            'name'     => sh_localized($product, 'name', $lang),
            'qty'      => $qty,
            'price'    => $price,
            'subtotal' => $price * $qty,
            'image'    => sh_product_image($product),
        ];
    }
    return $lines;
}