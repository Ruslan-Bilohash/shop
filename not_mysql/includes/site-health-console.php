<?php
/**
 * Shop CMS — composite site health: SEO, security, content quality, conversion psychology.
 */
declare(strict_types=1);

function sh_health_data_dir(): string
{
    return dirname(__DIR__) . '/data';
}

function sh_health_history_path(): string
{
    return sh_health_data_dir() . '/health-history.json';
}

/** @return list<array{date:string,overall:int,seo:int,security:int,content:int,conversion:int}> */
function sh_health_load_history(): array
{
    $path = sh_health_history_path();
    if (!is_file($path)) {
        return [];
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

/** @param list<array<string,mixed>> $history */
function sh_health_save_history(array $history): bool
{
    $dir = sh_health_data_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $path = sh_health_history_path();
    $json = json_encode(array_values($history), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents($path, $json . "\n", LOCK_EX) !== false;
}

/**
 * @param array{overall:int,seo:int,security:int,content:int,conversion:int} $scores
 */
function sh_health_record_snapshot(array $scores): void
{
    $today = date('Y-m-d');
    $history = sh_health_load_history();
    $found = false;
    foreach ($history as &$row) {
        if (($row['date'] ?? '') === $today) {
            $row = array_merge($row, $scores, ['date' => $today]);
            $found = true;
            break;
        }
    }
    unset($row);
    if (!$found) {
        $history[] = array_merge(['date' => $today], $scores);
    }
    usort($history, static fn(array $a, array $b): int => strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? '')));
    if (count($history) > 90) {
        $history = array_slice($history, -90);
    }
    sh_health_save_history($history);
}

/**
 * @return list<array{date:string,overall:int,seo:int,security:int,content:int,conversion:int}>
 */
function sh_health_trend_data(int $days = 14): array
{
    $history = sh_health_load_history();
    $cutoff = date('Y-m-d', strtotime('-' . max(1, $days) . ' days'));
    $filtered = array_values(array_filter(
        $history,
        static fn(array $r): bool => ($r['date'] ?? '') >= $cutoff
    ));

    if (count($filtered) >= 3) {
        return $filtered;
    }

    $base = $filtered !== [] ? (int) ($filtered[count($filtered) - 1]['overall'] ?? 65) : 65;
    $demo = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
        $wave = (int) round(sin($i * 0.7) * 4);
        $overall = max(20, min(98, $base + $wave + ($i % 3) - 1));
        $demo[] = [
            'date'       => $d,
            'overall'    => $overall,
            'seo'        => max(15, $overall + ($i % 5) - 3),
            'security'   => max(20, $overall - ($i % 4)),
            'content'    => max(25, $overall + ($i % 2)),
            'conversion' => max(30, $overall - 2 + ($i % 3)),
            'demo'       => true,
        ];
    }
    return $demo;
}

/**
 * Conversion psychology score — trust, urgency, social proof, friction reduction.
 *
 * @return array{score:int,grade:array{key:string,label:string},items:list<array{key:string,status:string,label:string,hint:string,weight:int}>}
 */
function sh_health_conversion_score(array $settings, array $labels): array
{
    require_once __DIR__ . '/payment-settings.php';
    require_once __DIR__ . '/site-settings.php';

    $items = [];
    $paymentsOk = false;
    foreach (['stripe', 'paypal', 'vipps', 'revolut', 'cod'] as $provider) {
        if (!empty($settings[$provider]['enabled']) && sh_payment_is_configured($provider, $settings)) {
            $paymentsOk = true;
            break;
        }
    }
    $items[] = [
        'key' => 'payments',
        'status' => $paymentsOk ? 'good' : 'bad',
        'label' => $labels['payments'] ?? 'Payment methods',
        'hint' => $labels['payments_hint'] ?? '',
        'weight' => 4,
    ];

    $recaptcha = !empty($settings['recaptcha_enabled']) && trim($settings['recaptcha_site_key'] ?? '') !== '';
    $items[] = [
        'key' => 'trust_recaptcha',
        'status' => $recaptcha ? 'good' : 'warn',
        'label' => $labels['recaptcha'] ?? 'Bot protection',
        'hint' => $labels['recaptcha_hint'] ?? '',
        'weight' => 2,
    ];

    $tracking = trim($settings['tracking_gtag_id'] ?? '') !== '' || trim($settings['tracking_meta_pixel'] ?? '') !== '';
    $items[] = [
        'key' => 'analytics',
        'status' => $tracking ? 'good' : 'warn',
        'label' => $labels['analytics'] ?? 'Conversion tracking',
        'hint' => $labels['analytics_hint'] ?? '',
        'weight' => 2,
    ];

    $newsletter = !empty($settings['newsletter_enabled']);
    $items[] = [
        'key' => 'newsletter',
        'status' => $newsletter ? 'good' : 'warn',
        'label' => $labels['newsletter'] ?? 'Newsletter capture',
        'hint' => $labels['newsletter_hint'] ?? '',
        'weight' => 2,
    ];

    $blocks = is_array($settings['homepage_blocks'] ?? null) ? $settings['homepage_blocks'] : [];
    $hasTrust = false;
    $hasCta = false;
    $hasSale = false;
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }
        $type = (string) ($block['type'] ?? '');
        if ($type === 'trust' || $type === 'features') {
            $hasTrust = true;
        }
        if ($type === 'cta' || $type === 'hero') {
            $hasCta = true;
        }
        if ($type === 'sale' || $type === 'products') {
            $hasSale = true;
        }
    }
    $items[] = [
        'key' => 'trust_blocks',
        'status' => $hasTrust ? 'good' : 'warn',
        'label' => $labels['trust_blocks'] ?? 'Trust / features blocks',
        'hint' => $labels['trust_blocks_hint'] ?? '',
        'weight' => 3,
    ];
    $items[] = [
        'key' => 'cta_blocks',
        'status' => $hasCta ? 'good' : 'warn',
        'label' => $labels['cta_blocks'] ?? 'Hero / CTA on homepage',
        'hint' => $labels['cta_blocks_hint'] ?? '',
        'weight' => 3,
    ];
    $items[] = [
        'key' => 'urgency_blocks',
        'status' => $hasSale ? 'good' : 'warn',
        'label' => $labels['urgency_blocks'] ?? 'Sale / featured products',
        'hint' => $labels['urgency_blocks_hint'] ?? '',
        'weight' => 2,
    ];

    $activeProducts = sh_products(false);
    $withSale = 0;
    $withImages = 0;
    foreach ($activeProducts as $p) {
        if (($p['active'] ?? true) === false) {
            continue;
        }
        if ((int) ($p['sale_price'] ?? 0) > 0) {
            $withSale++;
        }
        if (function_exists('sh_product_image') && sh_product_image($p) !== '') {
            $withImages++;
        }
    }
    $activeCount = count(array_filter($activeProducts, static fn($p) => ($p['active'] ?? true) !== false));
    $saleRatio = $activeCount > 0 ? $withSale / $activeCount : 0;
    $imgRatio = $activeCount > 0 ? $withImages / $activeCount : 0;

    $items[] = [
        'key' => 'product_images',
        'status' => $activeCount === 0 ? 'warn' : ($imgRatio >= 0.85 ? 'good' : ($imgRatio >= 0.5 ? 'warn' : 'bad')),
        'label' => $labels['product_images'] ?? 'Product visuals',
        'hint' => $labels['product_images_hint'] ?? '',
        'weight' => 3,
    ];
    $items[] = [
        'key' => 'sale_prices',
        'status' => $saleRatio >= 0.15 ? 'good' : ($saleRatio > 0 ? 'warn' : 'warn'),
        'label' => $labels['sale_prices'] ?? 'Sale / scarcity signals',
        'hint' => $labels['sale_prices_hint'] ?? '',
        'weight' => 2,
    ];

    $chatEnabled = !empty($settings['chat_enabled']) && trim($settings['chat_widget_id'] ?? $settings['chat_script'] ?? '') !== '';
    $items[] = [
        'key' => 'live_chat',
        'status' => $chatEnabled ? 'good' : 'warn',
        'label' => $labels['live_chat'] ?? 'Live chat / support',
        'hint' => $labels['live_chat_hint'] ?? '',
        'weight' => 1,
    ];

    $score = sh_checklist_score($items);
    return [
        'score' => $score,
        'grade' => sh_checklist_grade($score, $labels),
        'items'  => $items,
    ];
}

/**
 * @return array{score:int,grade:array{key:string,label:string},product_count:int,weak_count:int}
 */
function sh_health_content_quality(array $labels, string $lang): array
{
    require_once __DIR__ . '/seo-checklist.php';

    $contentLabels = $labels['content'] ?? $labels;
    $products = sh_products(false);
    $scores = [];
    $weak = 0;
    foreach ($products as $product) {
        if (($product['active'] ?? true) === false) {
            continue;
        }
        $report = sh_product_content_checklist($product, $contentLabels);
        $s = (int) ($report['score'] ?? 0);
        $scores[] = $s;
        if ($s < 60) {
            $weak++;
        }
    }
    $avg = $scores !== [] ? (int) round(array_sum($scores) / count($scores)) : 0;
    return [
        'score'         => $avg,
        'grade'         => sh_checklist_grade($avg, $labels),
        'product_count' => count($scores),
        'weak_count'    => $weak,
    ];
}

/**
 * @return array{
 *   overall:int,
 *   grade:array{key:string,label:string},
 *   pillars:array{seo:array,security:array,content:array,conversion:array},
 *   trend:list<array<string,mixed>>,
 *   recommendations:list<array{key:string,priority:string,title:string,detail:string,url:string}>
 * }
 */
function sh_health_composite_report(array $settings, array $labels, string $lang, array $ta): array
{
    require_once __DIR__ . '/seo-checklist.php';
    require_once __DIR__ . '/security-console.php';

    $seoLabels = $ta['products_page']['seo_checklist'] ?? [];
    $pageLabels = $ta['seo_analysis_page']['page_labels'] ?? [];
    $productRows = sh_seo_analysis_products($seoLabels, $lang, true);
    $pageRows = sh_seo_pages_audit($settings, $pageLabels, $lang);

    $productScores = array_column($productRows, 'score');
    $pageScores = array_column($pageRows, 'score');
    $allSeo = array_merge($productScores, $pageScores);
    $seoScore = $allSeo !== [] ? (int) round(array_sum($allSeo) / count($allSeo)) : 0;

    $secLabels = $ta['security_console_page']['checks'] ?? [];
    $vulnChecks = sh_sec_vulnerability_checks($secLabels);
    $secResult = sh_sec_score($vulnChecks);
    $securityScore = (int) ($secResult['score'] ?? 0);

    $content = sh_health_content_quality($labels, $lang);
    $conversion = sh_health_conversion_score($settings, $labels['conversion'] ?? $labels);

    $weights = [
        'seo'        => 30,
        'security'   => 25,
        'content'    => 25,
        'conversion' => 20,
    ];
    $overall = (int) round(
        ($seoScore * $weights['seo']
            + $securityScore * $weights['security']
            + $content['score'] * $weights['content']
            + $conversion['score'] * $weights['conversion'])
        / array_sum($weights)
    );

    $snapshot = [
        'overall'    => $overall,
        'seo'        => $seoScore,
        'security'   => $securityScore,
        'content'    => $content['score'],
        'conversion' => $conversion['score'],
    ];
    sh_health_record_snapshot($snapshot);

    $recommendations = sh_health_build_recommendations(
        $overall,
        $seoScore,
        $securityScore,
        $content,
        $conversion,
        $secResult['failed'] ?? 0,
        $productRows,
        $pageRows,
        $labels
    );

    return [
        'overall'         => $overall,
        'grade'           => sh_checklist_grade($overall, $labels),
        'pillars'         => [
            'seo'        => ['score' => $seoScore, 'grade' => sh_checklist_grade($seoScore, $labels), 'products' => count($productRows), 'pages' => count($pageRows)],
            'security'   => ['score' => $securityScore, 'grade' => sh_checklist_grade($securityScore, $labels), 'issues' => (int) ($secResult['failed'] ?? 0)],
            'content'    => $content,
            'conversion' => $conversion,
        ],
        'trend'           => sh_health_trend_data(14),
        'recommendations' => $recommendations,
    ];
}

/**
 * @param list<array<string,mixed>> $productRows
 * @param list<array<string,mixed>> $pageRows
 * @return list<array{key:string,priority:string,title:string,detail:string,url:string}>
 */
function sh_health_build_recommendations(
    int $overall,
    int $seoScore,
    int $securityScore,
    array $content,
    array $conversion,
    int $secIssues,
    array $productRows,
    array $pageRows,
    array $labels
): array {
    $recs = [];
    $recLabels = $labels['recommendations'] ?? [];

    if ($securityScore < 75 || $secIssues > 0) {
        $recs[] = [
            'key'      => 'security',
            'priority' => $securityScore < 50 ? 'critical' : 'high',
            'title'    => $recLabels['security_title'] ?? 'Fix security issues',
            'detail'   => sprintf($recLabels['security_detail'] ?? '%d open issues — review Security console.', $secIssues),
            'url'      => sh_admin_url('security-console.php'),
        ];
    }
    if ($seoScore < 70) {
        $weakPages = count(array_filter($pageRows, static fn($r) => ($r['score'] ?? 0) < 60));
        $recs[] = [
            'key'      => 'seo',
            'priority' => 'high',
            'title'    => $recLabels['seo_title'] ?? 'Boost SEO across pages',
            'detail'   => sprintf($recLabels['seo_detail'] ?? '%d pages need meta fixes. Use AI SEO Agent.', $weakPages),
            'url'      => sh_admin_url('seo-agent-console.php'),
        ];
    }
    if (($content['weak_count'] ?? 0) > 0) {
        $recs[] = [
            'key'      => 'content',
            'priority' => 'medium',
            'title'    => $recLabels['content_title'] ?? 'Improve product descriptions',
            'detail'   => sprintf($recLabels['content_detail'] ?? '%d products have thin content.', $content['weak_count']),
            'url'      => sh_admin_url('products.php'),
        ];
    }
    if (($conversion['score'] ?? 0) < 65) {
        $recs[] = [
            'key'      => 'conversion',
            'priority' => 'medium',
            'title'    => $recLabels['conversion_title'] ?? 'Strengthen conversion psychology',
            'detail'   => $recLabels['conversion_detail'] ?? 'Add trust blocks, payment methods and urgency signals.',
            'url'      => sh_admin_url('settings-homepage.php'),
        ];
    }
    $weakProducts = array_filter($productRows, static fn($r) => ($r['score'] ?? 0) < 50);
    if (count($weakProducts) > 3) {
        $recs[] = [
            'key'      => 'products_seo',
            'priority' => 'medium',
            'title'    => $recLabels['products_seo_title'] ?? 'Critical product SEO gaps',
            'detail'   => sprintf($recLabels['products_seo_detail'] ?? '%d products score below 50.', count($weakProducts)),
            'url'      => sh_admin_url('settings-seo-analysis.php'),
        ];
    }
    if ($overall >= 85 && $recs === []) {
        $recs[] = [
            'key'      => 'maintain',
            'priority' => 'low',
            'title'    => $recLabels['maintain_title'] ?? 'Site health is strong',
            'detail'   => $recLabels['maintain_detail'] ?? 'Keep monitoring weekly and refresh seasonal offers.',
            'url'      => sh_admin_url('health-console.php'),
        ];
    }

    return $recs;
}

/** SVG ring gauge helper — returns stroke-dashoffset for score 0–100. */
function sh_health_gauge_dash(int $score, float $radius = 54): array
{
    $circ = 2 * M_PI * $radius;
    $pct = max(0, min(100, $score)) / 100;
    $offset = $circ * (1 - $pct);
    return ['circumference' => round($circ, 2), 'offset' => round($offset, 2)];
}