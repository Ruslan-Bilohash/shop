<?php

require_once __DIR__ . '/category-storage.php';

/** @return array<string, array{label:string,ext:string,mime:string,delimiter?:string,import?:bool,export?:bool,group?:string}> */
function sh_product_io_formats(): array
{
    return [
        'shop_json' => [
            'label' => 'Shop CMS (JSON)',
            'ext'   => 'json',
            'mime'  => 'application/json; charset=utf-8',
            'group' => 'native',
        ],
        'shop_csv' => [
            'label' => 'Shop CMS (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'native',
        ],
        'woocommerce_csv' => [
            'label' => 'WooCommerce / WordPress',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'platform',
        ],
        'shopify_csv' => [
            'label' => 'Shopify',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'platform',
        ],
        'opencart_csv' => [
            'label' => 'OpenCart',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'platform',
        ],
        'prestashop_csv' => [
            'label' => 'PrestaShop',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ';',
            'group' => 'platform',
        ],
        'magento_csv' => [
            'label' => 'Magento / Adobe Commerce',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'platform',
        ],
        'rozetka_csv' => [
            'label' => 'Rozetka',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ';',
            'group' => 'marketplace',
        ],
        'prom_ua_csv' => [
            'label' => 'Prom.ua',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ';',
            'group' => 'marketplace',
        ],
        'generic_csv' => [
            'label' => 'Generic marketplace (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'marketplace',
        ],
        'google_merchant_csv' => [
            'label' => 'Google Merchant Center',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'feeds',
            'export' => true,
            'import' => false,
        ],
        'facebook_catalog_csv' => [
            'label' => 'Facebook / Meta Catalog',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
            'group' => 'feeds',
            'export' => true,
            'import' => false,
        ],
        'auto' => [
            'label' => 'Auto-detect (import)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'group' => 'native',
            'export' => false,
            'import' => true,
        ],
    ];
}

/** @return array<string, list<string>> */
function sh_product_io_format_groups(): array
{
    return [
        'native'      => ['shop_json', 'shop_csv', 'auto'],
        'platform'    => ['woocommerce_csv', 'shopify_csv', 'opencart_csv', 'prestashop_csv', 'magento_csv'],
        'marketplace' => ['rozetka_csv', 'prom_ua_csv', 'generic_csv'],
        'feeds'       => ['google_merchant_csv', 'facebook_catalog_csv'],
    ];
}

/** @return list<string> */
function sh_product_io_import_formats(): array
{
    $out = [];
    foreach (sh_product_io_formats() as $key => $meta) {
        if (($meta['import'] ?? true) !== false) {
            $out[] = $key;
        }
    }
    return $out;
}

/** @return list<string> */
function sh_product_io_export_formats(): array
{
    $out = [];
    foreach (sh_product_io_formats() as $key => $meta) {
        if (($meta['export'] ?? true) !== false) {
            $out[] = $key;
        }
    }
    return $out;
}

function sh_product_io_merchant_price(array $product, array $settings): string
{
    $price = function_exists('sh_product_price') ? sh_product_price($product) : (int) ($product['price'] ?? 0);
    $currency = strtoupper(trim((string) ($settings['site_currency'] ?? 'NOK'))) ?: 'NOK';
    return $price . ' ' . $currency;
}

/** @param array<string, mixed> $opts */
function sh_product_io_filter_products(array $products, array $opts): array
{
    $activeOnly = !empty($opts['active_only']);
    $featuredOnly = !empty($opts['featured_only']);
    $inStockOnly = !empty($opts['in_stock_only']);
    $category = trim((string) ($opts['category'] ?? ''));

    return array_values(array_filter($products, static function (array $p) use ($activeOnly, $featuredOnly, $inStockOnly, $category): bool {
        if ($activeOnly && ($p['active'] ?? true) === false) {
            return false;
        }
        if ($featuredOnly && empty($p['featured'])) {
            return false;
        }
        if ($inStockOnly && (int) ($p['stock'] ?? 0) <= 0) {
            return false;
        }
        if ($category !== '' && (string) ($p['category'] ?? '') !== $category) {
            return false;
        }
        return true;
    }));
}

function sh_product_io_detect_format(string $content): string
{
    $trim = ltrim($content);
    if ($trim !== '' && ($trim[0] === '[' || $trim[0] === '{')) {
        return 'shop_json';
    }

    $firstLine = mb_strtolower(trim(explode("\n", preg_replace('/^\xEF\xBB\xBF/', '', $content))[0] ?? ''), 'UTF-8');
    if (str_contains($firstLine, 'назва_позиції') || str_contains($firstLine, 'nazva_pozitsiyi')) {
        return 'prom_ua_csv';
    }
    if (str_contains($firstLine, 'variant sku') && str_contains($firstLine, 'handle')) {
        return 'shopify_csv';
    }

    $parsed = sh_product_io_parse_csv($content, null);
    $headers = $parsed['headers'];
    if ($headers === []) {
        return 'generic_csv';
    }

    $set = array_flip($headers);
    $has = static function (string ...$keys) use ($set): bool {
        foreach ($keys as $k) {
            if (isset($set[$k])) {
                return true;
            }
        }
        return false;
    };

    if ($has('handle', 'variant_sku', 'body_html')) {
        return 'shopify_csv';
    }
    if ($has('regular_price', 'type') || $has('sku', 'published', 'regular_price')) {
        return 'woocommerce_csv';
    }
    if ($has('product_id', 'model', 'quantity')) {
        return 'opencart_csv';
    }
    if ($has('price_tax_excluded', 'quantity', 'active')) {
        return 'prestashop_csv';
    }
    if ($has('qty', 'sku', 'categories') && !$has('regular_price')) {
        return 'magento_csv';
    }
    if ($has('vendor_code') || $has('available')) {
        return 'rozetka_csv';
    }
    if ($has('nazva_pozitsiyi', 'nayavnist', 'posylannya_na_zobrazhennya')) {
        return 'prom_ua_csv';
    }
    if ($has('image_link', 'google_product_category')) {
        return 'google_merchant_csv';
    }
    if ($has('image_link', 'condition') && $has('availability', 'price')) {
        return 'facebook_catalog_csv';
    }
    if ($has('name_en', 'name_no') || $has('long_desc_en')) {
        return 'shop_csv';
    }

    return 'generic_csv';
}

function sh_product_io_slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s_-]+/u', '', $text) ?? '';
    $text = preg_replace('/[\s_]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    if ($text === '' || !preg_match('/^[a-z]/', $text)) {
        $text = 'product-' . $text;
    }
    return substr($text, 0, 49);
}

function sh_product_io_unique_id(string $base, array $used): string
{
    $id = sh_product_io_slugify($base);
    if ($id === '' || !sh_product_id_valid($id)) {
        $id = 'product-' . substr(md5($base . microtime(true)), 0, 8);
    }
    $orig = $id;
    $n = 2;
    while (in_array($id, $used, true) || sh_product_by_id($id, true) !== null) {
        $suffix = '-' . $n;
        $id = substr($orig, 0, 49 - strlen($suffix)) . $suffix;
        $n++;
    }
    return $id;
}

function sh_product_io_resolve_category(string $raw): string
{
    $raw = trim($raw);
    $cats = sh_categories();
    if ($raw === '') {
        return $cats[0] ?? 'laptops-computers';
    }

    if (str_contains($raw, '>')) {
        $parts = array_map('trim', explode('>', $raw));
        $raw = (string) end($parts);
    }

    $slug = sh_product_io_slugify($raw);
    if (in_array($slug, $cats, true)) {
        return $slug;
    }

    require_once __DIR__ . '/category-storage.php';
    foreach (sh_category_records(true) as $cat) {
        $catSlug = (string) ($cat['slug'] ?? '');
        if ($catSlug !== '' && (stripos($catSlug, $slug) !== false || stripos($slug, $catSlug) !== false)) {
            return $catSlug;
        }
        foreach (sh_langs() as $code => $_info) {
            $label = sh_localized($cat, 'name', $code);
            if ($label !== '' && (mb_stripos($label, $raw, 'UTF-8') !== false || mb_stripos($raw, $label, 'UTF-8') !== false)) {
                return $catSlug;
            }
        }
    }

    return $cats[0] ?? 'laptops-computers';
}

function sh_product_io_parse_images(string $raw): array
{
    $raw = trim($raw);
    if ($raw === '') {
        return [];
    }
    if (str_starts_with($raw, '[')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }
    }
    $parts = preg_split('/[|,]/', $raw) ?: [];
    return array_values(array_filter(array_map('trim', $parts)));
}

function sh_product_io_localized_fill(string $value, string $sourceLang): array
{
    $out = [];
    foreach (array_keys(sh_langs()) as $code) {
        $out[$code] = $value;
    }
    if ($sourceLang !== '' && isset($out[$sourceLang])) {
        $out[$sourceLang] = $value;
    }
    return $out;
}

/** @return list<array<string, mixed>> */
function sh_product_io_export_rows(string $format, array $products, string $lang, array $settings = []): array
{
    $rows = [];
    $settings = $settings !== [] ? $settings : (function_exists('sh_load_settings') ? sh_load_settings() : []);
    $brand = trim((string) ($settings['store_name'] ?? 'BILOHASH'));
    foreach ($products as $product) {
        $id = (string) ($product['id'] ?? '');
        $name = sh_localized($product, 'name', $lang);
        $desc = sh_localized($product, 'desc', $lang);
        $long = function_exists('sh_product_long_desc') ? sh_product_long_desc($product, $lang) : $desc;
        $price = (int) ($product['price'] ?? 0);
        $sale = (int) ($product['sale_price'] ?? 0);
        $regular = $sale > 0 && $sale < $price ? $price : ($sale > 0 ? $sale : $price);
        $salePrice = $sale > 0 && $sale < $price ? $sale : '';
        $images = sh_product_images($product);
        $imageStr = implode('|', $images);
        $catLabel = sh_category_label($product['category'] ?? '', $lang);
        $sku = (string) ($product['sku'] ?? $id);
        $stock = (int) ($product['stock'] ?? 0);
        $active = ($product['active'] ?? true) !== false;

        switch ($format) {
            case 'rozetka_csv':
                $rows[] = [
                    'vendor_code' => $sku,
                    'name'        => $name,
                    'price'       => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'old_price'   => $sale > 0 && $sale < $price ? (string) $price : '',
                    'category'    => $catLabel,
                    'stock'       => (string) $stock,
                    'description' => $long,
                    'images'      => $imageStr,
                    'available'   => $stock > 0 && $active ? '1' : '0',
                    'id'          => $id,
                ];
                break;
            case 'woocommerce_csv':
                $rows[] = [
                    'ID'                => $id,
                    'Type'              => 'simple',
                    'SKU'               => $sku,
                    'Name'              => $name,
                    'Published'         => $active ? '1' : '0',
                    'Regular price'     => (string) $regular,
                    'Sale price'        => $salePrice !== '' ? (string) $salePrice : '',
                    'Categories'        => $catLabel,
                    'Short description' => $desc,
                    'Description'       => $long,
                    'Images'            => $imageStr,
                    'In stock?'         => $stock > 0 ? '1' : '0',
                    'Stock'             => (string) $stock,
                ];
                break;
            case 'opencart_csv':
                $rows[] = [
                    'product_id'  => $id,
                    'model'       => $sku,
                    'name'        => $name,
                    'price'       => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'quantity'    => (string) $stock,
                    'status'      => $active ? '1' : '0',
                    'image'       => $images[0] ?? '',
                    'category'    => $catLabel,
                    'description' => $long,
                ];
                break;
            case 'shopify_csv':
                $handle = $id;
                $rows[] = [
                    'Handle'                         => $handle,
                    'Title'                          => $name,
                    'Body (HTML)'                    => $long,
                    'Vendor'                         => $brand,
                    'Type'                           => $catLabel,
                    'Tags'                           => $catLabel,
                    'Published'                      => $active ? 'TRUE' : 'FALSE',
                    'Option1 Name'                   => 'Title',
                    'Option1 Value'                  => 'Default Title',
                    'Variant SKU'                    => $sku,
                    'Variant Grams'                  => '0',
                    'Variant Inventory Tracker'      => 'shopify',
                    'Variant Inventory Qty'          => (string) $stock,
                    'Variant Inventory Policy'       => 'deny',
                    'Variant Fulfillment Service'    => 'manual',
                    'Variant Price'                  => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'Variant Compare At Price'       => $sale > 0 && $sale < $price ? (string) $price : '',
                    'Variant Requires Shipping'      => 'TRUE',
                    'Variant Taxable'                => 'TRUE',
                    'Image Src'                      => $images[0] ?? '',
                    'Image Position'                 => '1',
                    'Status'                         => $active ? 'active' : 'draft',
                ];
                break;
            case 'prestashop_csv':
                $rows[] = [
                    'ID'                   => $id,
                    'Active'               => $active ? '1' : '0',
                    'Name'                 => $name,
                    'Categories'           => $catLabel,
                    'Price tax excluded'   => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'Quantity'             => (string) $stock,
                    'Description'          => $long,
                    'Image URLs'           => $imageStr,
                    'Reference'            => $sku,
                ];
                break;
            case 'magento_csv':
                $rows[] = [
                    'sku'          => $sku,
                    'name'         => $name,
                    'price'        => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'qty'          => (string) $stock,
                    'status'       => $active ? '1' : '2',
                    'categories'   => $catLabel,
                    'description'  => $long,
                    'image'        => $images[0] ?? '',
                    'special_price'=> $sale > 0 && $sale < $price ? (string) $sale : '',
                ];
                break;
            case 'prom_ua_csv':
                $rows[] = [
                    'nazva_pozitsiyi'            => $name,
                    'opis'                       => $long,
                    'tsina'                      => (string) ($sale > 0 && $sale < $price ? $sale : $price),
                    'nayavnist'                  => $stock > 0 && $active ? '+' : '-',
                    'kilkist'                    => (string) $stock,
                    'artikul'                    => $sku,
                    'kategoriya'                 => $catLabel,
                    'posylannya_na_zobrazhennya' => $images[0] ?? '',
                ];
                break;
            case 'google_merchant_csv':
            case 'facebook_catalog_csv':
                $productUrl = function_exists('sh_product_canonical_url')
                    ? sh_product_canonical_url($product, $lang)
                    : sh_absolute_url(sh_url('product.php?id=' . rawurlencode($id) . ($lang !== 'no' ? '&lang=' . $lang : '')));
                $imgUrl = $images[0] ?? '';
                if ($imgUrl !== '' && function_exists('sh_absolute_url') && !preg_match('#^https?://#i', $imgUrl)) {
                    $imgUrl = sh_absolute_url($imgUrl);
                }
                $merchantPrice = sh_product_io_merchant_price($product, $settings);
                $availability = $stock > 0 && $active ? 'in stock' : 'out of stock';
                if ($format === 'google_merchant_csv') {
                    $rows[] = [
                        'id'                      => $sku,
                        'title'                   => $name,
                        'description'             => $long,
                        'link'                    => $productUrl,
                        'image_link'              => $imgUrl,
                        'availability'            => $availability,
                        'price'                   => $merchantPrice,
                        'brand'                   => $brand,
                        'condition'               => 'new',
                        'google_product_category' => $catLabel,
                    ];
                } else {
                    $rows[] = [
                        'id'            => $sku,
                        'title'         => $name,
                        'description'   => $long,
                        'availability'  => $availability,
                        'condition'     => 'new',
                        'price'         => $merchantPrice,
                        'link'          => $productUrl,
                        'image_link'    => $imgUrl,
                        'brand'         => $brand,
                    ];
                }
                break;
            case 'generic_csv':
            case 'shop_csv':
                $row = [
                    'id'          => $id,
                    'sku'         => $sku,
                    'category'    => (string) ($product['category'] ?? ''),
                    'price'       => (string) $regular,
                    'sale_price'  => $salePrice !== '' ? (string) $salePrice : '0',
                    'stock'       => (string) $stock,
                    'active'      => $active ? '1' : '0',
                    'featured'    => !empty($product['featured']) ? '1' : '0',
                    'image'       => $images[0] ?? '',
                    'images'      => $imageStr,
                ];
                foreach (sh_langs() as $code => $_info) {
                    $row['name_' . $code] = sh_localized($product, 'name', $code);
                    $row['desc_' . $code] = sh_localized($product, 'desc', $code);
                    $row['long_desc_' . $code] = sh_product_long_desc($product, $code);
                }
                $rows[] = $row;
                break;
            default:
                break;
        }
    }
    return $rows;
}

/** @return list<string> */
function sh_product_io_csv_headers(string $format): array
{
    return match ($format) {
        'rozetka_csv' => ['vendor_code', 'name', 'price', 'old_price', 'category', 'stock', 'description', 'images', 'available', 'id'],
        'woocommerce_csv' => ['ID', 'Type', 'SKU', 'Name', 'Published', 'Regular price', 'Sale price', 'Categories', 'Short description', 'Description', 'Images', 'In stock?', 'Stock'],
        'opencart_csv' => ['product_id', 'model', 'name', 'price', 'quantity', 'status', 'image', 'category', 'description'],
        'shopify_csv' => ['Handle', 'Title', 'Body (HTML)', 'Vendor', 'Type', 'Tags', 'Published', 'Option1 Name', 'Option1 Value', 'Variant SKU', 'Variant Grams', 'Variant Inventory Tracker', 'Variant Inventory Qty', 'Variant Inventory Policy', 'Variant Fulfillment Service', 'Variant Price', 'Variant Compare At Price', 'Variant Requires Shipping', 'Variant Taxable', 'Image Src', 'Image Position', 'Status'],
        'prestashop_csv' => ['ID', 'Active', 'Name', 'Categories', 'Price tax excluded', 'Quantity', 'Description', 'Image URLs', 'Reference'],
        'magento_csv' => ['sku', 'name', 'price', 'qty', 'status', 'categories', 'description', 'image', 'special_price'],
        'prom_ua_csv' => ['nazva_pozitsiyi', 'opis', 'tsina', 'nayavnist', 'kilkist', 'artikul', 'kategoriya', 'posylannya_na_zobrazhennya'],
        'google_merchant_csv' => ['id', 'title', 'description', 'link', 'image_link', 'availability', 'price', 'brand', 'condition', 'google_product_category'],
        'facebook_catalog_csv' => ['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'],
        'shop_csv', 'generic_csv' => array_merge(
            ['id', 'sku', 'category', 'price', 'sale_price', 'stock', 'active', 'featured', 'image', 'images'],
            array_reduce(array_keys(sh_langs()), static function (array $carry, string $code): array {
                $carry[] = 'name_' . $code;
                $carry[] = 'desc_' . $code;
                $carry[] = 'long_desc_' . $code;
                return $carry;
            }, [])
        ),
        default => [],
    };
}

function sh_product_io_csv_row(array $headers, array $row): array
{
    $line = [];
    foreach ($headers as $key) {
        $line[] = (string) ($row[$key] ?? '');
    }
    return $line;
}

function sh_product_io_export_content(string $format, array $products, string $lang, array $opts = []): string
{
    if ($format === 'shop_json') {
        $out = array_values($products);
        if (empty($opts['include_seo'])) {
            $out = array_map(static function (array $p): array {
                if (isset($p['seo'])) {
                    unset($p['seo']);
                }
                return $p;
            }, $out);
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '[]';
    }

    $formats = sh_product_io_formats();
    $delimiter = $formats[$format]['delimiter'] ?? ',';
    $headers = sh_product_io_csv_headers($format);
    $settings = function_exists('sh_load_settings') ? sh_load_settings() : [];
    $rows = sh_product_io_export_rows($format, $products, $lang, $settings);

    $fp = fopen('php://temp', 'r+');
    if ($fp === false) {
        return '';
    }
    fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($fp, $headers, $delimiter);
    foreach ($rows as $row) {
        fputcsv($fp, sh_product_io_csv_row($headers, $row), $delimiter);
    }
    rewind($fp);
    $content = stream_get_contents($fp) ?: '';
    fclose($fp);
    return $content;
}

/** @return array{headers:list<string>,rows:list<array<string,string>>} */
function sh_product_io_parse_csv(string $content, ?string $delimiter = null): array
{
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
    $lines = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];
    if ($lines === []) {
        return ['headers' => [], 'rows' => []];
    }

    $first = $lines[0];
    if ($delimiter === null) {
        $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
    }

    $fp = fopen('php://temp', 'r+');
    if ($fp === false) {
        return ['headers' => [], 'rows' => []];
    }
    fwrite($fp, implode("\n", $lines));
    rewind($fp);

    $headers = fgetcsv($fp, 0, $delimiter) ?: [];
    $headers = array_map(static fn($h) => trim((string) $h), $headers);
    $norm = [];
    foreach ($headers as $h) {
        $norm[] = sh_product_io_normalize_header($h);
    }

    $rows = [];
    while (($data = fgetcsv($fp, 0, $delimiter)) !== false) {
        if ($data === [null] || $data === []) {
            continue;
        }
        $assoc = [];
        foreach ($norm as $i => $key) {
            if ($key === '') {
                continue;
            }
            $assoc[$key] = trim((string) ($data[$i] ?? ''));
        }
        if (implode('', $assoc) !== '') {
            $rows[] = $assoc;
        }
    }
    fclose($fp);

    return ['headers' => $norm, 'rows' => $rows];
}

function sh_product_io_normalize_header(string $header): string
{
    $h = mb_strtolower(trim($header), 'UTF-8');
    $map = [
        'артикул' => 'vendor_code', 'artikul' => 'sku', 'vendor code' => 'vendor_code', 'sku' => 'sku', 'model' => 'sku',
        'reference' => 'sku', 'variant sku' => 'sku', 'handle' => 'id',
        'назва' => 'name', 'name' => 'name', 'title' => 'name', 'product name' => 'name',
        'nazva_pozitsiyi' => 'name', 'назва_позиції' => 'name',
        'ціна' => 'price', 'price' => 'price', 'regular price' => 'regular_price',
        'price tax excluded' => 'price', 'variant price' => 'price', 'tsina' => 'price', 'цина' => 'price',
        'стара ціна' => 'old_price', 'old price' => 'old_price', 'sale price' => 'sale_price',
        'variant compare at price' => 'old_price', 'special_price' => 'sale_price',
        'категорія' => 'category', 'category' => 'category', 'categories' => 'category', 'type' => 'category',
        'kategoriya' => 'category', 'категория' => 'category',
        'кількість' => 'stock', 'stock' => 'stock', 'quantity' => 'stock', 'qty' => 'stock',
        'variant inventory qty' => 'stock', 'kilkist' => 'stock', 'кількість' => 'stock',
        'наявність' => 'stock', 'nayavnist' => 'available', 'наявність' => 'available',
        'опис' => 'description', 'description' => 'description', 'short description' => 'short_description',
        'body (html)' => 'description', 'body_html' => 'description', 'opis' => 'description',
        'зображення' => 'images', 'images' => 'images', 'image' => 'image', 'image src' => 'image',
        'image urls' => 'images', 'image_link' => 'image', 'posylannya_na_zobrazhennya' => 'image',
        'посилання на зображення' => 'images', 'available' => 'available', 'published' => 'published',
        'in stock?' => 'in_stock', 'status' => 'status', 'active' => 'active', 'id' => 'id', 'product_id' => 'id',
        'featured' => 'featured', 'availability' => 'available',
    ];
    if (isset($map[$h])) {
        return $map[$h];
    }
    if (preg_match('/^name_([a-z]{2})$/', $h, $m)) {
        return 'name_' . $m[1];
    }
    if (preg_match('/^desc_([a-z]{2})$/', $h, $m)) {
        return 'desc_' . $m[1];
    }
    if (preg_match('/^long_desc_([a-z]{2})$/', $h, $m)) {
        return 'long_desc_' . $m[1];
    }
    return preg_replace('/[^a-z0-9_]/', '_', $h) ?? $h;
}

/** @return array{products:list<array<string,mixed>>,errors:list<string>} */
function sh_product_io_rows_to_products(array $rows, string $format, string $sourceLang, bool $fillAllLangs): array
{
    $products = [];
    $errors = [];
    $usedIds = [];

    foreach ($rows as $lineNum => $row) {
        $line = $lineNum + 2;
        try {
            $product = sh_product_io_row_to_product($row, $format, $sourceLang, $fillAllLangs, $usedIds);
            if ($product === null) {
                $errors[] = 'Row ' . $line . ': empty or invalid product';
                continue;
            }
            $usedIds[] = (string) ($product['id'] ?? '');
            $products[] = $product;
        } catch (Throwable $e) {
            $errors[] = 'Row ' . $line . ': ' . $e->getMessage();
        }
    }

    return ['products' => $products, 'errors' => $errors];
}

/** @param array<string, string> $row */
function sh_product_io_row_to_product(array $row, string $format, string $sourceLang, bool $fillAllLangs, array $usedIds): ?array
{
    $sku = trim($row['sku'] ?? $row['vendor_code'] ?? $row['model'] ?? $row['reference'] ?? '');
    $name = trim($row['name'] ?? $row['title'] ?? '');
    if ($name === '' && $sku === '') {
        return null;
    }
    if ($name === '') {
        $name = $sku;
    }

    $id = trim($row['id'] ?? $row['handle'] ?? $row['product_id'] ?? '');
    if ($id === '' || !sh_product_id_valid($id)) {
        $id = sh_product_io_unique_id($sku !== '' ? $sku : $name, $usedIds);
    }

    $price = (int) round((float) str_replace([',', ' '], ['.', ''], $row['price'] ?? $row['regular_price'] ?? '0'));
    $oldPrice = (int) round((float) str_replace([',', ' '], ['.', ''], $row['old_price'] ?? '0'));
    $salePrice = (int) round((float) str_replace([',', ' '], ['.', ''], $row['sale_price'] ?? '0'));
    if ($oldPrice > $price && $price > 0) {
        $salePrice = $price;
        $price = $oldPrice;
    } elseif ($salePrice > 0 && $salePrice < $price) {
        // ok
    } else {
        $salePrice = 0;
    }

    $stock = (int) ($row['stock'] ?? $row['quantity'] ?? 0);
    $category = sh_product_io_resolve_category($row['category'] ?? '');
    $images = sh_product_io_parse_images($row['images'] ?? $row['image'] ?? '');
    $shortDesc = trim($row['short_description'] ?? '');
    $longDesc = trim($row['description'] ?? $row['long_desc'] ?? '');
    if ($shortDesc === '' && $longDesc !== '') {
        $shortDesc = mb_substr($longDesc, 0, 200, 'UTF-8');
    }
    if ($shortDesc === '' && $name !== '') {
        $shortDesc = $name;
    }

    $names = [];
    $descs = [];
    $longDescs = [];
    $hasLangCols = false;
    foreach (sh_langs() as $code => $_info) {
        $n = trim($row['name_' . $code] ?? '');
        $d = trim($row['desc_' . $code] ?? '');
        $ld = trim($row['long_desc_' . $code] ?? '');
        if ($n !== '' || $d !== '') {
            $hasLangCols = true;
        }
        $names[$code] = $n;
        $descs[$code] = $d;
        $longDescs[$code] = $ld;
    }

    if ($hasLangCols) {
        foreach (sh_langs() as $code => $_info) {
            if ($names[$code] === '') {
                $names[$code] = $names[$sourceLang] ?? $name;
            }
            if ($descs[$code] === '') {
                $descs[$code] = $descs[$sourceLang] ?? $shortDesc;
            }
            if ($longDescs[$code] === '') {
                $longDescs[$code] = $longDescs[$sourceLang] ?? ($longDesc !== '' ? $longDesc : $descs[$code]);
            }
        }
    } elseif ($fillAllLangs) {
        $names = sh_product_io_localized_fill($name, $sourceLang);
        $descs = sh_product_io_localized_fill($shortDesc, $sourceLang);
        $longDescs = sh_product_io_localized_fill($longDesc !== '' ? $longDesc : $shortDesc, $sourceLang);
    } else {
        $names[$sourceLang] = $name;
        $descs[$sourceLang] = $shortDesc;
        $longDescs[$sourceLang] = $longDesc !== '' ? $longDesc : $shortDesc;
        foreach (sh_langs() as $code => $_info) {
            $names[$code] = $names[$code] ?? $name;
            $descs[$code] = $descs[$code] ?? $shortDesc;
            $longDescs[$code] = $longDescs[$code] ?? ($longDesc !== '' ? $longDesc : $shortDesc);
        }
    }

    $published = strtolower(trim($row['published'] ?? $row['available'] ?? $row['status'] ?? $row['active'] ?? '1'));
    if (in_array($published, ['-', 'out of stock', 'out_of_stock'], true)) {
        $active = false;
    } elseif (in_array($published, ['+', 'in stock', 'in_stock'], true)) {
        $active = true;
    } else {
        $active = !in_array($published, ['0', 'no', 'false', 'draft', 'inactive', '2'], true);
    }

    return [
        'id'         => $id,
        'sku'        => $sku !== '' ? $sku : $id,
        'category'   => $category,
        'price'      => max(0, $price),
        'sale_price' => max(0, $salePrice),
        'stock'      => max(0, $stock),
        'active'     => $active,
        'featured'   => !empty($row['featured']) && $row['featured'] !== '0',
        'image'      => $images[0] ?? '',
        'images'     => $images,
        'name'       => $names,
        'desc'       => $descs,
        'long_desc'  => $longDescs,
    ];
}

/**
 * @return array{created:int,updated:int,skipped:int,errors:list<string>,total:int}
 */
/** @param array<string, mixed> $opts */
function sh_product_io_import_apply(array $importProducts, string $mode = 'merge', array $opts = []): array
{
    $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [], 'total' => count($importProducts)];
    $updatePricesOnly = !empty($opts['update_prices_only']);
    $updateStockOnly = !empty($opts['update_stock_only']);
    $skipImages = !empty($opts['skip_images']);
    $preserveSeo = !empty($opts['preserve_seo']);

    if ($mode === 'replace') {
        if (!sh_save_products([])) {
            $stats['errors'][] = 'Could not clear product catalog.';
            return $stats;
        }
    }

    $existing = sh_load_products_raw();
    $byId = [];
    $bySku = [];
    foreach ($existing as $p) {
        $byId[(string) ($p['id'] ?? '')] = $p;
        $sku = trim((string) ($p['sku'] ?? ''));
        if ($sku !== '') {
            $bySku[$sku] = $p;
        }
    }

    foreach ($importProducts as $product) {
        $sku = trim((string) ($product['sku'] ?? ''));
        $match = null;
        if ($sku !== '' && isset($bySku[$sku])) {
            $match = $bySku[$sku];
        } elseif (isset($byId[(string) ($product['id'] ?? '')])) {
            $match = $byId[(string) $product['id']];
        }

        if ($mode === 'append' && $match !== null) {
            $stats['skipped']++;
            continue;
        }

        if ($match !== null) {
            $product['id'] = (string) ($match['id'] ?? $product['id']);

            if ($updateStockOnly) {
                $product = array_merge($match, [
                    'id'    => $product['id'],
                    'stock' => $product['stock'] ?? $match['stock'] ?? 0,
                ]);
            } elseif ($updatePricesOnly) {
                $product = array_merge($match, [
                    'id'         => $product['id'],
                    'price'      => $product['price'] ?? $match['price'] ?? 0,
                    'sale_price' => $product['sale_price'] ?? $match['sale_price'] ?? 0,
                    'stock'      => $product['stock'] ?? $match['stock'] ?? 0,
                ]);
            } else {
                if ($skipImages) {
                    $product['image'] = $match['image'] ?? '';
                    $product['images'] = $match['images'] ?? [];
                }
                if ($preserveSeo && !empty($match['seo'])) {
                    $product['seo'] = $match['seo'];
                } elseif (!empty($match['seo']) && empty($product['seo'])) {
                    $product['seo'] = $match['seo'];
                }
            }
        }

        if (!sh_product_upsert($product)) {
            $stats['errors'][] = 'Failed to save: ' . ($product['id'] ?? '?');
            continue;
        }

        if ($match !== null) {
            $stats['updated']++;
        } else {
            $stats['created']++;
            $byId[(string) $product['id']] = $product;
            if ($sku !== '') {
                $bySku[$sku] = $product;
            }
        }
    }

    return $stats;
}

/** @return array{products:list<array<string,mixed>>,errors:list<string>} */
function sh_product_io_parse_import(string $format, string $content, string $sourceLang, bool $fillAllLangs): array
{
    if ($format === 'auto') {
        $format = sh_product_io_detect_format($content);
    }

    if ($format === 'shop_json') {
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return ['products' => [], 'errors' => ['Invalid JSON file.']];
        }
        $products = [];
        $errors = [];
        $usedIds = [];
        foreach ($decoded as $i => $item) {
            if (!is_array($item)) {
                $errors[] = 'Item ' . ($i + 1) . ': not an object';
                continue;
            }
            $id = trim((string) ($item['id'] ?? ''));
            if ($id === '' || !sh_product_id_valid($id)) {
                $item['id'] = sh_product_io_unique_id((string) ($item['sku'] ?? $item['name']['en'] ?? 'product'), $usedIds);
            }
            $usedIds[] = (string) $item['id'];
            $item['category'] = sh_product_io_resolve_category((string) ($item['category'] ?? ''));
            if (empty($item['name']) || !is_array($item['name'])) {
                $n = is_string($item['name'] ?? null) ? $item['name'] : ($item['title'] ?? 'Product');
                $item['name'] = sh_product_io_localized_fill((string) $n, $sourceLang);
            }
            if (empty($item['desc']) || !is_array($item['desc'])) {
                $d = is_string($item['desc'] ?? null) ? $item['desc'] : '';
                $item['desc'] = sh_product_io_localized_fill($d, $sourceLang);
            }
            $products[] = $item;
        }
        return ['products' => $products, 'errors' => $errors];
    }

    $delimiter = sh_product_io_formats()[$format]['delimiter'] ?? null;
    $parsed = sh_product_io_parse_csv($content, $delimiter);
    return sh_product_io_rows_to_products($parsed['rows'], $format, $sourceLang, $fillAllLangs);
}