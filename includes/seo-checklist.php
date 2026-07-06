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

/** @param list<array{key:string,status:string}> $items @return list<string> */
function sh_checklist_issue_tags(array $items): array
{
    $tags = [];
    foreach ($items as $item) {
        $st = $item['status'] ?? 'bad';
        if ($st === 'good') {
            continue;
        }
        $key = (string) ($item['key'] ?? '');
        if (str_starts_with($key, 'meta_title_')) {
            $tags[] = $st === 'bad' ? 'missing_title' : 'title_length';
        } elseif (str_starts_with($key, 'meta_desc_')) {
            $tags[] = $st === 'bad' ? 'missing_desc' : 'desc_length';
        } elseif (str_starts_with($key, 'meta_kw_')) {
            $tags[] = 'missing_keywords';
        } elseif (str_starts_with($key, 'intro_')) {
            $tags[] = $st === 'bad' ? 'missing_intro' : 'intro_length';
        } elseif ($key === 'og_image') {
            $tags[] = 'missing_og';
        } elseif ($key === 'brand') {
            $tags[] = 'missing_brand';
        } elseif ($key === 'identifiers') {
            $tags[] = 'missing_identifiers';
        } elseif ($key === 'schema') {
            $tags[] = 'missing_schema';
        } elseif ($key === 'sitemap') {
            $tags[] = 'missing_sitemap';
        } elseif ($key === 'site_name' || $key === 'org') {
            $tags[] = 'missing_global';
        }
    }
    return array_values(array_unique($tags));
}

/**
 * @param array<string, mixed>|null $product
 * @return array{id:string,name:string,category:string,active:bool,score:int,grade:array{key:string,label:string},issues:list<string>,bad_count:int,warn_count:int,report:array}
 */
function sh_seo_analysis_product_row(?array $product, array $labels, string $lang): array
{
    $product = is_array($product) ? $product : [];
    $report = sh_product_seo_checklist($product, $labels);
    $issues = sh_checklist_issue_tags($report['items']);
    $bad = 0;
    $warn = 0;
    foreach ($report['items'] as $item) {
        if (($item['status'] ?? '') === 'bad') {
            $bad++;
        } elseif (($item['status'] ?? '') === 'warn') {
            $warn++;
        }
    }
    return [
        'id'        => (string) ($product['id'] ?? ''),
        'name'      => sh_localized($product, 'name', $lang),
        'category'  => (string) ($product['category'] ?? ''),
        'active'    => ($product['active'] ?? true) !== false,
        'score'     => (int) ($report['score'] ?? 0),
        'grade'     => $report['grade'],
        'issues'    => $issues,
        'bad_count' => $bad,
        'warn_count'=> $warn,
        'report'    => $report,
    ];
}

/** @return list<array<string,mixed>> */
function sh_seo_analysis_products(array $labels, string $lang, bool $activeOnly = true): array
{
    $rows = [];
    foreach (sh_products(!$activeOnly) as $product) {
        if ($activeOnly && ($product['active'] ?? true) === false) {
            continue;
        }
        $rows[] = sh_seo_analysis_product_row($product, $labels, $lang);
    }
    usort($rows, static function (array $a, array $b): int {
        $cmp = ($a['score'] ?? 0) <=> ($b['score'] ?? 0);
        return $cmp !== 0 ? $cmp : strcmp($a['name'] ?? '', $b['name'] ?? '');
    });
    return $rows;
}

/**
 * @param array<string, mixed>|null $category
 * @return array{score:int,grade:array{key:string,label:string},items:list<array<string,mixed>>}
 */
function sh_category_seo_checklist(?array $category, array $labels): array
{
    $category = is_array($category) ? $category : [];
    $seo = is_array($category['seo'] ?? null) ? $category['seo'] : [];
    $defaultLang = sh_site_default_lang();
    $items = [];

    foreach (sh_langs() as $code => $_info) {
        $title = trim((string) ($seo['meta_title'][$code] ?? ''));
        $desc = trim((string) ($seo['meta_description'][$code] ?? ''));
        $kw = trim((string) ($seo['meta_keywords'][$code] ?? ''));
        $intro = trim((string) ($seo['intro'][$code] ?? ''));
        $w = $code === $defaultLang ? 3 : 2;

        $items[] = [
            'key' => 'meta_title_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($title), 30, 60, 12),
            'label' => ($labels['meta_title'] ?? 'Meta title') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_title_hint'] ?? '',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'meta_desc_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($desc), 120, 160, 40),
            'label' => ($labels['meta_desc'] ?? 'Meta description') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_desc_hint'] ?? '',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'meta_kw_' . $code,
            'status' => $kw !== '' ? 'good' : 'warn',
            'label' => ($labels['meta_keywords'] ?? 'Keywords') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_keywords_hint'] ?? '',
            'weight' => 1,
        ];
        $items[] = [
            'key' => 'intro_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($intro), 80, 400, 20),
            'label' => ($labels['intro'] ?? 'Category intro') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['intro_hint'] ?? '',
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
 * @param array<string, mixed> $page
 * @return array{score:int,grade:array{key:string,label:string},items:list<array<string,mixed>>}
 */
function sh_service_page_seo_checklist(array $page, array $labels): array
{
    $defaultLang = sh_site_default_lang();
    $items = [];

    foreach (sh_langs() as $code => $_info) {
        $title = trim((string) ($page['meta_title'][$code] ?? ''));
        $desc = trim((string) ($page['meta_description'][$code] ?? ''));
        $body = trim((string) ($page['content'][$code] ?? ''));
        $w = $code === $defaultLang ? 3 : 2;

        $items[] = [
            'key' => 'meta_title_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($title), 30, 60, 12),
            'label' => ($labels['meta_title'] ?? 'Meta title') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_title_hint'] ?? '',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'meta_desc_' . $code,
            'status' => sh_checklist_rate_length(mb_strlen($desc), 120, 160, 40),
            'label' => ($labels['meta_desc'] ?? 'Meta description') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['meta_desc_hint'] ?? '',
            'weight' => $w,
        ];
        $items[] = [
            'key' => 'content_' . $code,
            'status' => mb_strlen($body) >= 80 ? 'good' : ($body !== '' ? 'warn' : 'bad'),
            'label' => ($labels['content'] ?? 'Page content') . ' (' . strtoupper($code) . ')',
            'hint' => $labels['content_hint'] ?? '',
            'weight' => 1,
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
 * @param array<string, mixed> $settings
 * @return array{score:int,grade:array{key:string,label:string},items:list<array<string,mixed>>}
 */
function sh_site_global_seo_checklist(array $settings, array $labels): array
{
    $items = [
        [
            'key' => 'site_name',
            'status' => trim($settings['seo_site_name'] ?? '') !== '' ? 'good' : 'bad',
            'label' => $labels['site_name'] ?? 'Site name',
            'hint' => $labels['site_name_hint'] ?? '',
            'weight' => 3,
        ],
        [
            'key' => 'org',
            'status' => trim($settings['seo_org_name'] ?? '') !== '' ? 'good' : 'warn',
            'label' => $labels['org_name'] ?? 'Organization name',
            'hint' => $labels['org_name_hint'] ?? '',
            'weight' => 2,
        ],
        [
            'key' => 'og_image',
            'status' => trim($settings['seo_default_og_image'] ?? '') !== '' ? 'good' : 'bad',
            'label' => $labels['og_image'] ?? 'Default OG image',
            'hint' => $labels['og_image_hint'] ?? '',
            'weight' => 3,
        ],
        [
            'key' => 'sitemap',
            'status' => !empty($settings['sitemap_enabled']) ? 'good' : 'warn',
            'label' => $labels['sitemap'] ?? 'XML sitemap',
            'hint' => $labels['sitemap_hint'] ?? '',
            'weight' => 2,
        ],
        [
            'key' => 'schema',
            'status' => !empty($settings['seo_schema_product']) && !empty($settings['seo_schema_organization']) ? 'good' : 'bad',
            'label' => $labels['schema'] ?? 'Product + Organization schema',
            'hint' => $labels['schema_hint'] ?? '',
            'weight' => 3,
        ],
    ];

    $score = sh_checklist_score($items);
    return [
        'score' => $score,
        'grade' => sh_checklist_grade($score, $labels),
        'items' => $items,
    ];
}

/**
 * @return list<array{key:string,type:string,label:string,score:int,grade:array{key:string,label:string},issues:list<string>,edit_url:string,public_url:string}>
 */
function sh_seo_pages_audit(array $settings, array $labels, string $lang): array
{
    require_once __DIR__ . '/category-storage.php';
    require_once __DIR__ . '/service-pages.php';

    $pages = [];
    $global = sh_site_global_seo_checklist($settings, $labels['global'] ?? $labels);
    $pages[] = [
        'key'        => 'global',
        'type'       => 'settings',
        'label'      => $labels['page_global'] ?? 'Global SEO settings',
        'score'      => $global['score'],
        'grade'      => $global['grade'],
        'issues'     => sh_checklist_issue_tags($global['items']),
        'edit_url'   => sh_admin_url('settings-seo.php'),
        'public_url' => sh_url('index.php'),
    ];

    $pages[] = [
        'key'        => 'homepage',
        'type'       => 'system',
        'label'      => $labels['page_homepage'] ?? 'Homepage',
        'score'      => trim($settings['seo_site_name'] ?? '') !== '' ? 85 : 45,
        'grade'      => sh_checklist_grade(trim($settings['seo_site_name'] ?? '') !== '' ? 85 : 45, $labels),
        'issues'     => trim($settings['seo_site_name'] ?? '') !== '' ? [] : ['missing_global'],
        'edit_url'   => sh_admin_url('settings-homepage.php'),
        'public_url' => sh_url('index.php'),
    ];

    $catLabels = $labels['category'] ?? $labels;
    foreach (sh_category_records(true) as $category) {
        $slug = (string) ($category['slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        $report = sh_category_seo_checklist($category, $catLabels);
        $pages[] = [
            'key'        => 'category:' . $slug,
            'type'       => 'category',
            'label'      => sh_localized($category, 'name', $lang) . ' (' . $slug . ')',
            'score'      => $report['score'],
            'grade'      => $report['grade'],
            'issues'     => sh_checklist_issue_tags($report['items']),
            'edit_url'   => sh_admin_url('category-edit.php?slug=' . urlencode($slug) . '&tab=seo'),
            'public_url' => sh_url('category.php?cat=' . urlencode($slug)),
        ];
    }

    $pageLabels = $labels['service'] ?? $labels;
    $settings = sh_merge_service_settings($settings);
    foreach (sh_service_page_slugs($settings) as $slug) {
        $page = $settings['service_pages'][$slug] ?? null;
        if (!is_array($page) || ($page['active'] ?? true) === false) {
            continue;
        }
        $report = sh_service_page_seo_checklist($page, $pageLabels);
        $def = sh_service_page_defs($settings)[$slug] ?? [];
        $pages[] = [
            'key'        => 'page:' . $slug,
            'type'       => 'service',
            'label'      => (string) ($def['admin_label'] ?? $slug),
            'score'      => $report['score'],
            'grade'      => $report['grade'],
            'issues'     => sh_checklist_issue_tags($report['items']),
            'edit_url'   => sh_admin_url('settings-pages.php?page=' . urlencode($slug)),
            'public_url' => sh_url('page.php?slug=' . urlencode($slug)),
        ];
    }

    usort($pages, static fn(array $a, array $b): int => ($a['score'] ?? 0) <=> ($b['score'] ?? 0));

    return $pages;
}

/** @param list<string> $issues @param array<string, string> $map */
function sh_seo_analysis_issue_labels(array $issues, array $map): string
{
    $parts = [];
    foreach ($issues as $tag) {
        $parts[] = $map[$tag] ?? $tag;
    }
    return implode(', ', $parts);
}

function sh_seo_score_grade_key(int $score): string
{
    if ($score >= 90) {
        return 'excellent';
    }
    if ($score >= 75) {
        return 'good';
    }
    if ($score >= 50) {
        return 'fair';
    }
    return 'poor';
}