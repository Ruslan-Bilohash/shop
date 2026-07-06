<?php

function sh_llms_file_path(): string
{
    return dirname(__DIR__) . '/llms.txt';
}

function sh_llms_public_url(): string
{
    return sh_absolute_url(sh_url('llms.txt'));
}

/** Build llms.txt from live shop data. */
function sh_build_llms_txt(?array $settings = null): string
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    require_once __DIR__ . '/version.php';
    require_once __DIR__ . '/store-settings.php';

    global $site_url;
    $base = rtrim($site_url ?? 'https://bilohash.com/shop', '/');
    $siteName = function_exists('sh_seo_site_name') ? sh_seo_site_name($settings) : 'Shop CMS';
    $orgName = function_exists('sh_seo_org_name') ? sh_seo_org_name($settings) : $siteName;
    $currency = sh_site_currency($settings);
    $products = sh_products();
    $categories = sh_categories();
    $langs = sh_active_langs($settings);
    $langList = implode(', ', array_map(fn($l) => $l['label'] ?? '', $langs));

    $payments = [];
    if (function_exists('sh_payment_is_configured')) {
        foreach (['stripe', 'paypal', 'vipps'] as $p) {
            if (sh_payment_is_configured($p, $settings ?? []) && !empty(($settings[$p] ?? [])['enabled'])) {
                $payments[] = ucfirst($p);
            }
        }
    }
    if ($payments === []) {
        $payments = ['Stripe', 'PayPal', 'Vipps (demo)', 'Cash on delivery'];
    }

    $custom = trim((string) ($settings['llms_custom_note'] ?? ''));

    $lines = [
        '# ' . $siteName . ' — LLM / AI agent context',
        '# ' . $base . '/',
        '',
        '> ' . $orgName . ' — PHP e-commerce storefront (Shop CMS v' . sh_version() . ', ' . sh_version_date() . '). '
        . 'Multilingual catalog, session cart, customer accounts, SEO & Schema.org.',
        '',
        '## Store',
        '- Name: ' . $siteName,
        '- Organization: ' . $orgName,
        '- Version: ' . sh_version() . ' (' . sh_version_date() . ')',
        '- Currency: ' . $currency,
        '- Languages: ' . $langList,
        '- Active products: ' . count($products),
        '- Categories: ' . count($categories),
        '- Geo: ' . trim((string) ($settings['seo_geo_placename'] ?? 'Norway')) . ' (' . trim((string) ($settings['seo_geo_region'] ?? 'NO')) . ')',
        '',
        '## URLs',
        '- Storefront: ' . $base . '/',
        '- Product catalog: ' . $base . '/search.php',
        '- Customer sign-in: ' . $base . '/login.php',
        '- Customer account: ' . $base . '/account.php',
        '- Checkout: ' . $base . '/checkout.php',
        '- Contact: ' . $base . '/contact.php',
        '- Admin: ' . $base . '/admin/',
        '- Sitemap: ' . $base . '/sitemap.xml',
        '- llms.txt: ' . $base . '/llms.txt',
        '- Product site: https://bilohash.com/shop/site/',
        '',
        '## Catalog sample',
    ];

    foreach (array_slice($products, 0, 8) as $p) {
        $name = sh_localized($p, 'name', 'en');
        $price = sh_format_price(sh_product_price($p), $settings);
        $lines[] = '- ' . $name . ' — ' . $price . ' — ' . $base . '/product.php?id=' . rawurlencode($p['id'] ?? '');
    }
    if (count($products) > 8) {
        $lines[] = '- … and ' . (count($products) - 8) . ' more products';
    }

    $lines[] = '';
    $lines[] = '## Payments';
    foreach ($payments as $p) {
        $lines[] = '- ' . $p;
    }

    $lines[] = '';
    $lines[] = '## SEO & AI agents';
    $lines[] = '- Schema.org: Product, Offer, Organization, WebSite, BreadcrumbList, ItemList';
    $lines[] = '- Open Graph & Twitter Cards on product and category pages';
    $lines[] = '- XML sitemap with hreflang alternates';
    $lines[] = '- This file (llms.txt) describes the store for LLM crawlers';

    if ($custom !== '') {
        $lines[] = '';
        $lines[] = '## Additional notes';
        $lines[] = $custom;
    }

    $lines[] = '';
    $lines[] = '## Bilohash ecosystem';
    $lines[] = '- Booking CMS: https://bilohash.com/booking/';
    $lines[] = '- Auction CMS: https://bilohash.com/auction/';
    $lines[] = '- Freelance CMS: https://bilohash.com/freelance/';

    return implode("\n", $lines) . "\n";
}

function sh_write_llms_file(string $content): bool
{
    return file_put_contents(sh_llms_file_path(), $content, LOCK_EX) !== false;
}

/**
 * @return array{ok:bool,content:string,demo:bool,error:string}
 */
function sh_ai_enhance_llms(string $baseContent, ?array $settings = null): array
{
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    require_once __DIR__ . '/ai.php';
    if (!sh_ai_enabled($settings)) {
        return ['ok' => true, 'content' => $baseContent, 'demo' => true, 'error' => ''];
    }
    $prompt = "Improve this llms.txt file for AI agents and LLM crawlers. Keep markdown structure, "
        . "be factual, do not invent products or URLs. Output plain text only:\n\n" . $baseContent;
    $ai = sh_ai_settings($settings);
    $result = sh_ai_call_chat($ai, $prompt, 2500);
    if (!$result['ok'] || trim($result['text']) === '') {
        return ['ok' => true, 'content' => $baseContent, 'demo' => false, 'error' => $result['error']];
    }
    return ['ok' => true, 'content' => trim($result['text']) . "\n", 'demo' => false, 'error' => ''];
}