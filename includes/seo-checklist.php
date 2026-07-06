<?php

/** @return 'good'|'warn'|'bad' */
function sh_checklist_rate_length(int $len, int $goodMin, int $goodMax, int $warnMin = 0): string
{
    if ($len === 0) {
        return 'bad';
    }
    if ($len >= $goodMin && $len <= $goodMax) {
        return 'good';
    }
    if ($len >= $warnMin) {
        return 'warn';
    }
    return 'bad';
}

/** @param list<array{status:string,weight?:int}> $items */
function sh_checklist_score(array $items): int
{
    if ($items === []) {
        return 0;
    }
    $total = 0;
    $weightSum = 0;
    foreach ($items as $item) {
        $w = max(1, (int) ($item['weight'] ?? 1));
        $status = $item['status'] ?? 'bad';
        $points = match ($status) {
            'good' => 100,
            'warn' => 55,
            default => 0,
        };
        $total += $points * $w;
        $weightSum += $w;
    }
    return (int) round($total / max(1, $weightSum));
}

function sh_checklist_grade(int $score, array $labels): array
{
    if ($score >= 90) {
        return ['key' => 'excellent', 'label' => $labels['grade_excellent'] ?? 'Excellent'];
    }
    if ($score >= 75) {
        return ['key' => 'good', 'label' => $labels['grade_good'] ?? 'Good'];
    }
    if ($score >= 50) {
        return ['key' => 'fair', 'label' => $labels['grade_fair'] ?? 'Needs work'];
    }
    return ['key' => 'poor', 'label' => $labels['grade_poor'] ?? 'Poor'];
}

/** @return list<array{key:string,ok:bool,label:string,url:string}> */
function sh_admin_health_checks(array $ta): array
{
    require_once __DIR__ . '/payment-settings.php';
    require_once __DIR__ . '/site-settings.php';
    require_once __DIR__ . '/category-storage.php';

    $settings = sh_load_settings();
    $dash = $ta['dashboard_page'] ?? [];
    $checks = $dash['health'] ?? [];
    $stats = sh_admin_dashboard_stats();

    $paymentsOk = false;
    foreach (['stripe', 'paypal', 'vipps', 'cod'] as $provider) {
        if (!empty($settings[$provider]['enabled']) && sh_payment_is_configured($provider, $settings)) {
            $paymentsOk = true;
            break;
        }
    }

    $activeProducts = sh_products(false);
    $withImages = 0;
    foreach ($activeProducts as $p) {
        if (sh_product_image($p) !== '') {
            $withImages++;
        }
    }
    $imageRatio = count($activeProducts) > 0 ? $withImages / count($activeProducts) : 0;

    $items = [
        ['key' => 'shop_open', 'ok' => $stats['shop_open'], 'url' => 'settings-store.php'],
        ['key' => 'payments', 'ok' => $paymentsOk, 'url' => 'payments.php'],
        ['key' => 'seo', 'ok' => trim($settings['seo_site_name'] ?? '') !== '', 'url' => 'settings-seo.php'],
        ['key' => 'seo_og', 'ok' => trim($settings['seo_default_og_image'] ?? '') !== '', 'url' => 'settings-seo.php'],
        ['key' => 'sitemap', 'ok' => !empty($settings['sitemap_enabled']), 'url' => 'settings-seo.php'],
        ['key' => 'schema', 'ok' => !empty($settings['seo_schema_product']) && !empty($settings['seo_schema_organization']), 'url' => 'settings-seo.php'],
        ['key' => 'recaptcha', 'ok' => !empty($settings['recaptcha_enabled']) && trim($settings['recaptcha_site_key'] ?? '') !== '', 'url' => 'settings-recaptcha.php'],
        ['key' => 'tracking', 'ok' => trim($settings['tracking_gtag_id'] ?? '') !== '' || trim($settings['tracking_meta_pixel'] ?? '') !== '', 'url' => 'settings-analytics.php'],
        ['key' => 'cookie', 'ok' => !empty($stats['cookie_consent']), 'url' => 'settings-advanced.php'],
        ['key' => 'categories', 'ok' => count(sh_category_records(true)) >= 3, 'url' => 'categories.php'],
        ['key' => 'product_images', 'ok' => count($activeProducts) === 0 || $imageRatio >= 0.8, 'url' => 'products.php'],
    ];

    foreach ($items as &$item) {
        $item['label'] = $checks[$item['key']] ?? $item['key'];
        $item['url'] = sh_admin_url($item['url']);
    }
    unset($item);

    return $items;
}

function sh_admin_health_all_ok(array $checks): bool
{
    foreach ($checks as $check) {
        if (empty($check['ok'])) {
            return false;
        }
    }
    return $checks !== [];
}

/**
 * @param array<string, mixed>|null $product
 * @return array{score:int,grade:array{key:string,label:string},items:list<array{key:string,status:string,label:string,hint:string,weight:int}>}
 */
function sh_product_content_checklist(?array $product, array $labels): array
{
    $product = is_array($product) ? $product : [];
    $defaultLang = sh_site_default_lang();
    $items = [];

    $category = trim((string) ($product['category'] ?? ''));
    $items[] = [
        'key' => 'category',
        'status' => $category !== '' ? 'good' : 'bad',
        'label' => $labels['category'] ?? 'Category',
        'hint' => $labels['category_hint'] ?? '',
        'weight' => 2,
    ];

    $price = (int) ($product['price'] ?? 0);
    $items[] = [
        'key' => 'price',
        'status' => $price > 0 ? 'good' : 'bad',
        'label' => $labels['price'] ?? 'Price',
        'hint' => $labels['price_hint'] ?? '',
        'weight' => 2,
    ];

    $stock = (int) ($product['stock'] ?? 0);
    $items[] = [
        'key' => 'stock',
        'status' => $stock > 0 ? 'good' : ($stock === 0 ? 'warn' : 'bad'),
        'label' => $labels['stock'] ?? 'Stock',
        'hint' => $labels['stock_hint'] ?? '',
        'weight' => 1,
    ];

    $sku = trim((string) ($product['sku'] ?? ''));
    $items[] = [
        'key' => 'sku',
        'status' => $sku !== '' ? 'good' : 'warn',
        'label' => $labels['sku'] ?? 'SKU',
        'hint' => $labels['sku_hint'] ?? '',
        'weight' => 1,
    ];

    $images = function_exists('sh_product_images') ? sh_product_images($product) : [];
    $imgCount = count($images);
    $items[] = [
        'key' => 'images',
        'status' => $imgCount >= 2 ? 'good' : ($imgCount === 1 ? 'warn' : 'bad'),
        'label' => $labels['images'] ?? 'Product images',
        'hint' => $labels['images_hint'] ?? '',
        'weight' => 3,
    ];

    $sale = (int) ($product['sale_price'] ?? 0);
    $priceBase = (int) ($product['price'] ?? 0);
    if ($sale > 0 && $priceBase > 0) {
        $items[] = [
            'key' => 'sale',
            'status' => $sale < $priceBase ? 'good' : 'warn',
            'label' => $labels['sale'] ?? 'Sale price',
            'hint' => $labels['sale_hint'] ?? '',
            'weight' => 1,
        ];
    }

    foreach (sh_langs() as $code => $_info) {
        $name = trim((string) ($product['name'][$code] ?? ''));
        $desc = trim((string) ($product['desc'][$code] ?? ''));
        $w = $code === $defaultLang ? 3 : 2;
        $items[] = [
            'key' => 'name_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($name), 12, 120, 4),
            'label' => ($labels['name'] ?? 'Name') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['name_hint'] ?? '',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'desc_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($desc), 80, 600, 25),
            'label' => ($labels['desc'] ?? 'Description') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['desc_hint'] ?? '',
            'weight' => $w,
        ];
    }

    $score = sh_checklist_score($items);
    return [
        'score' => $score,
        'grade' => sh_checklist_grade($score, $labels),
        'items' => $items,
    ];
}

/**
 * @param array<string, mixed>|null $product
 * @return array{score:int,grade:array{key:string,label:string},items:list<array{key:string,status:string,label:string,hint:string,weight:int}>}
 */
function sh_product_seo_checklist(?array $product, array $labels): array
{
    $product = is_array($product) ? $product : [];
    $seo = is_array($product['seo'] ?? null) ? $product['seo'] : [];
    $schema = is_array($seo['schema'] ?? null) ? $seo['schema'] : [];
    $defaultLang = sh_site_default_lang();
    $items = [];

    foreach (sh_langs() as $code => $_info) {
        $title = trim((string) ($seo['meta_title'][$code] ?? ''));
        $desc = trim((string) ($seo['meta_description'][$code] ?? ''));
        $kw = trim((string) ($seo['meta_keywords'][$code] ?? ''));
        $w = $code === $defaultLang ? 3 : 2;

        $items[] = [
            'key' => 'meta_title_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($title), 30, 60, 12),
            'label' => ($labels['meta_title'] ?? 'Meta title') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_title_hint'] ?? '30–60 characters is ideal.',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'meta_desc_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($desc), 120, 160, 40),
            'label' => ($labels['meta_desc'] ?? 'Meta description') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_desc_hint'] ?? '120–160 characters for Google snippets.',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'meta_kw_' . $code,
            'status' => $kw !== '' ? 'good' : 'warn',
            'label' => ($labels['meta_keywords'] ?? 'Keywords') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_keywords_hint'] ?? '',
            'weight' => 1,
        ];
    }

    $og = trim((string) ($seo['og_image'] ?? ''));
    $hasProductImage = function_exists('sh_product_image') && sh_product_image($product) !== '';
    $items[] = [
        'key' => 'og_image',
        'status' => $og !== '' ? 'good' : ($hasProductImage ? 'warn' : 'bad'),
        'label' => $labels['og_image'] ?? 'OG image',
        'hint' => $labels['og_image_hint'] ?? '',
        'weight' => 2,
    ];

    $brand = trim((string) ($seo['brand'] ?? ''));
    $items[] = [
        'key' => 'brand',
        'status' => $brand !== '' ? 'good' : 'warn',
        'label' => $labels['brand'] ?? 'Brand (Schema)',
        'hint' => $labels['brand_hint'] ?? '',
        'weight' => 2,
    ];

    $gtin = trim((string) ($seo['gtin'] ?? ''));
    $mpn = trim((string) ($seo['mpn'] ?? ''));
    $items[] = [
        'key' => 'identifiers',
        'status' => ($gtin !== '' || $mpn !== '') ? 'good' : 'warn',
        'label' => $labels['identifiers'] ?? 'GTIN / MPN',
        'hint' => $labels['identifiers_hint'] ?? '',
        'weight' => 1,
    ];

    $schemaOk = !empty($schema['product']) && !empty($schema['offer']);
    $items[] = [
        'key' => 'schema',
        'status' => $schemaOk ? 'good' : 'bad',
        'label' => $labels['schema'] ?? 'Product + Offer schema',
        'hint' => $labels['schema_hint'] ?? '',
        'weight' => 3,
    ];

    $score = sh_checklist_score($items);
    return [
        'score' => $score,
        'grade' => sh_checklist_grade($score, $labels),
        'items' => $items,
    ];
}

/** @param array{score:int,grade:array{key:string,label:string},items:list<array<string,mixed>>} $report */
function sh_admin_render_checklist_panel(array $report, array $labels, string $panelId, string $title, string $icon = 'clipboard-check'): void
{
    $score = (int) ($report['score'] ?? 0);
    $grade = $report['grade'] ?? ['key' => 'poor', 'label' => ''];
    $gradeKey = $grade['key'] ?? 'poor';
    ?>
    <div class="adm-checklist-panel" id="<?= htmlspecialchars($panelId) ?>" data-checklist-panel>
        <div class="adm-checklist-head">
            <h3><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($title) ?></h3>
            <div class="adm-checklist-score adm-checklist-score--<?= htmlspecialchars($gradeKey) ?>" data-checklist-score>
                <span class="adm-checklist-score-val" data-checklist-score-val><?= $score ?></span>
                <span class="adm-checklist-score-suffix">/100</span>
            </div>
        </div>
        <p class="adm-checklist-grade adm-checklist-grade--<?= htmlspecialchars($gradeKey) ?>" data-checklist-grade>
            <?= htmlspecialchars($grade['label'] ?? '') ?>
        </p>
        <ul class="adm-checklist-items" data-checklist-items>
            <?php foreach ($report['items'] as $item):
                $st = $item['status'] ?? 'bad';
                $iconClass = match ($st) {
                    'good' => 'check-circle',
                    'warn' => 'triangle-exclamation',
                    default => 'circle-xmark',
                };
            ?>
            <li class="adm-checklist-item adm-checklist-item--<?= htmlspecialchars($st) ?>"
                data-check-key="<?= htmlspecialchars($item['key'] ?? '') ?>"
                data-check-status="<?= htmlspecialchars($st) ?>"
                <?= !empty($item['hint']) ? ' tabindex="0" role="button" title="' . htmlspecialchars($labels['hint_click'] ?? 'Click for tip') . '"' : '' ?>>
                <span class="adm-checklist-item-icon"><i class="fas fa-<?= $iconClass ?>"></i></span>
                <span class="adm-checklist-item-body">
                    <strong class="adm-checklist-item-label"><?= htmlspecialchars($item['label'] ?? '') ?></strong>
                    <?php if (!empty($item['hint'])): ?>
                    <small class="adm-checklist-item-hint"><?= htmlspecialchars($item['hint']) ?></small>
                    <?php endif; ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}