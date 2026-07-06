<?php

require_once __DIR__ . '/category-storage.php';

/** @return array<string, array{label:string,ext:string,mime:string,delimiter?:string}> */
function sh_product_io_formats(): array
{
    return [
        'shop_json' => [
            'label' => 'Shop CMS (JSON)',
            'ext'   => 'json',
            'mime'  => 'application/json; charset=utf-8',
        ],
        'shop_csv' => [
            'label' => 'Shop CMS (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
        ],
        'rozetka_csv' => [
            'label' => 'Rozetka (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ';',
        ],
        'woocommerce_csv' => [
            'label' => 'WooCommerce / WordPress (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
        ],
        'opencart_csv' => [
            'label' => 'OpenCart (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
        ],
        'generic_csv' => [
            'label' => 'Generic marketplace (CSV)',
            'ext'   => 'csv',
            'mime'  => 'text/csv; charset=utf-8',
            'delimiter' => ',',
        ],
    ];
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
function sh_product_io_export_rows(string $format, array $products, string $lang): array
{
    $rows = [];
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

function sh_product_io_export_content(string $format, array $products, string $lang): string
{
    if ($format === 'shop_json') {
        return json_encode(array_values($products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '[]';
    }

    $formats = sh_product_io_formats();
    $delimiter = $formats[$format]['delimiter'] ?? ',';
    $headers = sh_product_io_csv_headers($format);
    $rows = sh_product_io_export_rows($format, $products, $lang);

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
        'артикул' => 'vendor_code', 'vendor code' => 'vendor_code', 'sku' => 'sku', 'model' => 'sku',
        'назва' => 'name', 'name' => 'name', 'title' => 'name', 'product name' => 'name',
        'ціна' => 'price', 'price' => 'price', 'regular price' => 'regular_price',
        'стара ціна' => 'old_price', 'old price' => 'old_price', 'sale price' => 'sale_price',
        'категорія' => 'category', 'category' => 'category', 'categories' => 'category',
        'кількість' => 'stock', 'stock' => 'stock', 'quantity' => 'stock', 'наявність' => 'stock',
        'опис' => 'description', 'description' => 'description', 'short description' => 'short_description',
        'зображення' => 'images', 'images' => 'images', 'image' => 'image',
        'посилання на зображення' => 'images', 'available' => 'available', 'published' => 'published',
        'in stock?' => 'in_stock', 'status' => 'status', 'id' => 'id', 'product_id' => 'id',
        'type' => 'type', 'featured' => 'featured', 'active' => 'active',
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
    $sku = trim($row['sku'] ?? $row['vendor_code'] ?? $row['model'] ?? '');
    $name = trim($row['name'] ?? '');
    if ($name === '' && $sku === '') {
        return null;
    }
    if ($name === '') {
        $name = $sku;
    }

    $id = trim($row['id'] ?? '');
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
    $active = !in_array($published, ['0', 'no', 'false', 'draft', 'inactive'], true);

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
function sh_product_io_import_apply(array $importProducts, string $mode = 'merge'): array
{
    $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [], 'total' => count($importProducts)];

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
            if (!empty($match['seo']) && empty($product['seo'])) {
                $product['seo'] = $match['seo'];
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