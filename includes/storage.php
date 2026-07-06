<?php

function sh_data_path(string $file): string
{
    return __DIR__ . '/../data/' . $file;
}

function sh_products_file(): string
{
    return sh_data_path('products.json');
}

function sh_default_products_from_seed(): ?array
{
    $seedFile = __DIR__ . '/../data/products.php';
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

function sh_ensure_products_json(): void
{
    $json = sh_products_file();
    if (!is_file($json)) {
        $defaults = sh_default_products_from_seed();
        if ($defaults !== null) {
            sh_save_products($defaults);
        }
        return;
    }

    $defaults = sh_default_products_from_seed();
    if ($defaults === null) {
        return;
    }

    $existing = json_decode(file_get_contents($json) ?: '[]', true);
    if (!is_array($existing)) {
        sh_save_products($defaults);
        return;
    }

    $ids = array_column($existing, 'id');
    $merged = $existing;
    $changed = false;
    foreach ($defaults as $product) {
        if (!in_array($product['id'] ?? '', $ids, true)) {
            $merged[] = $product;
            $changed = true;
        }
    }
    if ($changed) {
        sh_save_products($merged);
    }
}

function sh_save_products(array $list): bool
{
    $json = json_encode(array_values($list), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return file_put_contents(sh_products_file(), $json, LOCK_EX) !== false;
}

function sh_load_products_raw(): array
{
    $file = sh_products_file();
    if (is_readable($file)) {
        $data = json_decode(file_get_contents($file) ?: '[]', true);
        if (is_array($data) && $data !== []) {
            return $data;
        }
    }

    sh_ensure_products_json();
    if (is_readable($file)) {
        $data = json_decode(file_get_contents($file) ?: '[]', true);
        if (is_array($data) && $data !== []) {
            return $data;
        }
    }

    $seed = sh_default_products_from_seed();
    return $seed ?? [];
}

function sh_bootstrap_data(): void
{
    sh_ensure_products_json();
    require_once __DIR__ . '/category-storage.php';
    sh_ensure_categories_json();
    if (!isset($_SESSION['sh_cart']) || !is_array($_SESSION['sh_cart'])) {
        $_SESSION['sh_cart'] = [];
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