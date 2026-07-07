<?php
/**
 * BILOHASH ecosystem CMS scripts as Shop CMS catalog products (php-scripts category).
 */
declare(strict_types=1);

require_once __DIR__ . '/ecosystem-load.php';

function sh_ecosystem_cms_product_langs(): array
{
    return ['en', 'no', 'uk', 'ru', 'sv', 'lt'];
}

/** @return list<string> */
function sh_ecosystem_cms_product_ids(): array
{
    return array_column(sh_ecosystem_cms_products_seed(), 'id');
}

/** @return array<string, string> */
function sh_ecosystem_cms_images(): array
{
    return [
        'booking'   => 'https://images.unsplash.com/photo-1506784365847-bbad939e9335?w=800&q=80',
        'auction'   => 'https://images.unsplash.com/photo-1568667256549-948345e15ab5?w=800&q=80',
        'pizza'     => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
        'freelance' => 'https://images.unsplash.com/photo-1521737711862-e3b97375f902?w=800&q=80',
        '3d'        => 'https://images.unsplash.com/photo-1617791160505-6f00504e3519?w=800&q=80',
        'ai'        => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&q=80',
        'wordpress' => 'https://images.unsplash.com/photo-1611162617474-5b21e039e986?w=800&q=80',
        'today'     => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800&q=80',
        'gamehub'   => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&q=80',
        'tavle'     => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800&q=80',
        'faktura'   => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80',
        'news'      => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800&q=80',
    ];
}

/** @return array<string, string> */
function sh_ecosystem_cms_skus(): array
{
    return [
        'booking'   => 'BH-BOOK-049',
        'auction'   => 'BH-AUCT-049',
        'pizza'     => 'BH-PIZZ-049',
        'freelance' => 'BH-FREE-049',
        '3d'        => 'BH-3D-049',
        'ai'        => 'BH-AI-049',
        'wordpress' => 'BH-WP-049',
        'today'     => 'BH-TODAY-049',
        'gamehub'   => 'BH-GAME-049',
        'tavle'     => 'BH-BIL-049',
        'faktura'   => 'BH-FAKT-049',
        'news'      => 'BH-NEWS-049',
    ];
}

/**
 * @param array<string, array{name:string,desc:string}> $labels
 * @return array<string, string>
 */
function sh_ecosystem_cms_demo_phrase(string $lang, array $labels): string
{
    $name = $labels['name'] ?? 'CMS';
    return match ($lang) {
        'no' => "Demolisting: 49 kr/mnd for {$name} på 1 domene. Kun demo — ingen ekte abonnement. Live demo og produktside på bilohash.com.",
        'uk' => "Демо-товар: 49 кр/міс за {$name} на 1 домен. Лише демо — не справжня підписка. Живе демо та сторінка продукту на bilohash.com.",
        'ru' => "Демо-товар: 49 кр/мес за {$name} на 1 домен. Только демо — не реальная подписка. Живое демо и страница продукта на bilohash.com.",
        'sv' => "Demoannons: 49 kr/månad för {$name} på 1 domän. Endast demo — inget riktigt abonnemang. Live demo på bilohash.com.",
        'lt' => "Demo prekė: 49 kr/mėn. už {$name} 1 domenui. Tik demo — ne tikra prenumerata. Live demo bilohash.com.",
        default => "Demo listing: 49 kr/month for {$name} on 1 domain. Not a real subscription. Live demo and product page on bilohash.com.",
    };
}

/**
 * @param array<string, array{name:string,desc:string}> $labels
 * @return array<string, string>
 */
function sh_ecosystem_cms_long_desc(string $lang, array $labels, string $productUrl, string $demoUrl): string
{
    $desc = $labels['desc'] ?? '';
    $extra = match ($lang) {
        'no' => "Inkluderer installasjon på ditt domene, flerspråklig admin og BILOHASH-økosystem-integrasjon. Produkt: {$productUrl} · Demo: {$demoUrl}",
        'uk' => "Включає розгортання на вашому домені, багатомовну адмінку та інтеграцію з екосистемою BILOHASH. Продукт: {$productUrl} · Демо: {$demoUrl}",
        'ru' => "Включает развёртывание на вашем домене, многоязычную админку и интеграцию с экосистемой BILOHASH. Продукт: {$productUrl} · Демо: {$demoUrl}",
        'sv' => "Inkluderar installation på din domän, flerspråkig admin och BILOHASH-ekosystem. Produkt: {$productUrl} · Demo: {$demoUrl}",
        'lt' => "Apima diegimą jūsų domene, daugiakalbę admin ir BILOHASH ekosistemą. Produktas: {$productUrl} · Demo: {$demoUrl}",
        default => "Includes deployment on your domain, multilingual admin and BILOHASH ecosystem integration. Product: {$productUrl} · Demo: {$demoUrl}",
    };
    return trim($desc . ' ' . $extra);
}

/**
 * @param array<string, array{name:string,desc:string}> $labels
 * @return list<string>
 */
function sh_ecosystem_cms_highlights(string $lang, array $labels): array
{
    $name = $labels['name'] ?? 'CMS';
    return match ($lang) {
        'no' => [
            $labels['desc'] ?? $name,
            '49 kr/mnd · 1 domene (BILOHASH demo)',
            'Flerspråklig admin · Schema.org SEO',
            'Grok xAI og OpenAI GPT API-klar',
        ],
        'uk' => [
            $labels['desc'] ?? $name,
            '49 кр/міс · 1 домен (демо BILOHASH)',
            'Багатомовна адмінка · Schema.org SEO',
            'Підтримка Grok xAI та OpenAI GPT API',
        ],
        'ru' => [
            $labels['desc'] ?? $name,
            '49 кр/мес · 1 домен (демо BILOHASH)',
            'Многоязычная админка · Schema.org SEO',
            'Поддержка Grok xAI и OpenAI GPT API',
        ],
        'sv' => [
            $labels['desc'] ?? $name,
            '49 kr/månad · 1 domän (BILOHASH demo)',
            'Flerspråkig admin · Schema.org SEO',
            'Grok xAI och OpenAI GPT API',
        ],
        'lt' => [
            $labels['desc'] ?? $name,
            '49 kr/mėn. · 1 domenas (BILOHASH demo)',
            'Daugiakalbė admin · Schema.org SEO',
            'Grok xAI ir OpenAI GPT API',
        ],
        default => [
            $labels['desc'] ?? $name,
            '49 kr/month · 1 domain (BILOHASH demo)',
            'Multilingual admin · Schema.org SEO',
            'Grok xAI and OpenAI GPT API ready',
        ],
    };
}

/** @return list<array<string,mixed>> */
function sh_ecosystem_cms_products_seed(): array
{
    sh_require_ecosystem('ecosystem-defs.php');
    sh_require_ecosystem('ecosystem-i18n.php');
    if (!defined('ECOSYSTEM_SCRIPT_PRICE_NOK')) {
        try {
            sh_require_ecosystem('ecosystem-pricing.php');
        } catch (Throwable) {
            define('ECOSYSTEM_SCRIPT_PRICE_NOK', 49);
        }
    }

    $catalog  = bh_ecosystem_catalog();
    $images   = sh_ecosystem_cms_images();
    $skus     = sh_ecosystem_cms_skus();
    $featured = ['booking', 'auction', 'faktura', 'pizza', 'freelance', 'ai'];
    $price    = (int) ECOSYSTEM_SCRIPT_PRICE_NOK;
    $langs    = sh_ecosystem_cms_product_langs();
    $products = [];

    foreach ($catalog as $slug => $meta) {
        if ($slug === 'shop') {
            continue;
        }

        $id = 'bilohash-' . str_replace('_', '-', $slug) . '-cms';
        if ($slug === 'news') {
            $id = 'bilohash-news-hub';
        }
        if ($slug === '3d') {
            $id = 'bilohash-3d-cms';
        }
        if ($slug === 'ai') {
            $id = 'bilohash-ai-platform';
        }
        if ($slug === 'wordpress') {
            $id = 'bilohash-wordpress-ai';
        }
        if ($slug === 'tavle') {
            $id = 'bilohash-bilen-cms';
        }

        $productUrl = (string) ($meta['url'] ?? '');
        $demoUrl    = (string) ($meta['demo'] ?? $productUrl);
        if ($slug === 'wordpress' && !empty($meta['plugin'])) {
            $productUrl = (string) $meta['plugin'];
            $demoUrl    = (string) ($meta['ai_demo'] ?? $meta['demo'] ?? $productUrl);
        }

        $names = [];
        $descs = [];
        $longDescs = [];
        $highlights = [];
        $seoTitles = [];
        $seoDescs = [];

        foreach ($langs as $lang) {
            $labels = bh_ecosystem_product_labels($lang);
            $row    = $labels[$slug] ?? ['name' => ucfirst($slug) . ' CMS', 'desc' => ''];
            $names[$lang]      = $row['name'] . ' — ' . match ($lang) {
                'no' => 'månedlig lisens (demo)',
                'uk' => 'місячна ліцензія (демо)',
                'ru' => 'месячная лицензия (демо)',
                'sv' => 'månadslicens (demo)',
                'lt' => 'mėnesio licencija (demo)',
                default => 'monthly license (demo)',
            };
            $descs[$lang]      = sh_ecosystem_cms_demo_phrase($lang, $row);
            $longDescs[$lang]  = sh_ecosystem_cms_long_desc($lang, $row, $productUrl, $demoUrl);
            $highlights[$lang] = sh_ecosystem_cms_highlights($lang, $row);
            $seoTitles[$lang]  = $row['name'] . ' — demo 49 kr/mo | BILOHASH';
            $seoDescs[$lang]   = sh_ecosystem_cms_demo_phrase($lang, $row);
        }

        $products[] = [
            'id'         => $id,
            'category'   => 'php-scripts',
            'featured'   => in_array($slug, $featured, true),
            'active'     => true,
            'price'      => $price,
            'sale_price' => 0,
            'sku'        => $skus[$slug] ?? ('BH-' . strtoupper($slug) . '-049'),
            'stock'      => 999,
            'name'       => $names,
            'desc'       => $descs,
            'long_desc'  => $longDescs,
            'highlights' => $highlights,
            'image'      => $images[$slug] ?? 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            'seo'        => [
                'meta_title'       => $seoTitles,
                'meta_description' => $seoDescs,
            ],
        ];
    }

    return $products;
}