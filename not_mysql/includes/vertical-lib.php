<?php

if (!function_exists('bh_str_lower')) {
    function bh_str_lower(string $s): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
    }
}

function sh_vertical_defs(): array
{
    static $defs = null;
    if ($defs === null) {
        $defs = require __DIR__ . '/../data/vertical-defs.php';
    }
    return $defs;
}

function sh_use_case_slugs(): array
{
    return array_keys(sh_vertical_defs());
}

function sh_vertical_hub_label(string $lang): string
{
    $labels = [
        'no' => 'Nettbutikkløsninger',
        'en' => 'E-commerce solutions',
        'uk' => 'Рішення для інтернет-магазину',
        'ru' => 'Решения для интернет-магазина',
    ];
    return $labels[$lang] ?? $labels['en'];
}

function sh_verticals_build(): array
{
    $defs = sh_vertical_defs();
    $tpl = [
        'no' => [
            'title'       => 'Bestill %s nettbutikk Norge | Shop CMS',
            'description' => 'Shop CMS — PHP nettbutikk for %s i Norge og Europa. Produktkatalog, handlekurv, adminpanel, flerspråklig SEO og live demo. Bestill skreddersydd e-handel.',
            'keywords'    => 'bestill %s nettbutikk Norge, PHP e-handel %s, online butikk Norge, Shop CMS, nettbutikk Europa, adminpanel butikk, flerspråklig nettbutikk',
            'subtitle'    => 'Profesjonelt PHP nettbutikksystem for %s — bygget for norske og europeiske bedrifter',
            'intro'       => 'Shop CMS er en modulær PHP e-handelsplattform for %s. Live demo med produktkatalog, handlekurv, adminpanel og JSON-lagring — klar for Vipps, Stripe, flerspråklig SEO og tilpasset checkout.',
            'cta'         => 'Klar for en nettbutikk for %s? Få et tilbud i dag.',
            'h1'          => 'Shop CMS for %s',
        ],
        'en' => [
            'title'       => 'Order %s Online Store Norway | Shop CMS',
            'description' => 'Shop CMS — PHP e-commerce platform for %s in Norway & Europe. Product catalog, cart, admin panel, multilingual SEO and live demo. Order a custom online shop.',
            'keywords'    => 'order %s online store Norway, PHP e-commerce %s, web shop Europe, Shop CMS, ecommerce script Norway, multilingual shop admin',
            'subtitle'    => 'Professional PHP online store system for %s — built for Norwegian and European businesses',
            'intro'       => 'Shop CMS is a modular PHP e-commerce platform for %s. Live demo with product catalog, session cart, admin panel and JSON storage — ready for Vipps, Stripe, multilingual SEO and custom checkout.',
            'cta'         => 'Ready for an online store for %s? Get a quote today.',
            'h1'          => 'Shop CMS for %s',
        ],
        'uk' => [
            'title'       => 'Замовити інтернет-магазин %s | Shop CMS',
            'description' => 'Shop CMS — PHP e-commerce для %s у Норвегії та Європі. Каталог, кошик, адмін-панель, багатомовне SEO та live demo. Замовити індивідуальний магазин.',
            'keywords'    => 'замовити інтернет-магазин %s Норвегія, PHP e-commerce %s, онлайн-магазин Європа, Shop CMS, скрипт магазину, багатомовний магазин',
            'subtitle'    => 'Професійна PHP-система інтернет-магазину для %s — для бізнесу в Норвегії та Європі',
            'intro'       => 'Shop CMS — модульна PHP e-commerce платформа для %s. Live demo з каталогом, кошиком, адмін-панеллю та JSON-сховищем — готова до Vipps, Stripe, багатомовного SEO та кастомного checkout.',
            'cta'         => 'Потрібен інтернет-магазин для %s? Отримайте пропозицію сьогодні.',
            'h1'          => 'Shop CMS — %s',
        ],
        'ru' => [
            'title'       => 'Заказать интернет-магазин %s | Shop CMS',
            'description' => 'Shop CMS — PHP e-commerce для %s в Норвегии и Европе. Каталог, корзина, админ-панель, многоязычное SEO и live demo. Закажите индивидуальный магазин.',
            'keywords'    => 'заказать интернет-магазин %s Норвегия, PHP e-commerce %s, онлайн-магазин Европа, Shop CMS, скрипт магазина, многоязычный магазин',
            'subtitle'    => 'Профессиональная PHP-система интернет-магазина для %s — для бизнеса в Норвегии и Европе',
            'intro'       => 'Shop CMS — модульная PHP e-commerce платформа для %s. Live demo с каталогом, корзиной, админ-панелью и JSON-хранилищем — готова к Vipps, Stripe, многоязычному SEO и кастомному checkout.',
            'cta'         => 'Нужен интернет-магазин для %s? Получите предложение сегодня.',
            'h1'          => 'Shop CMS — %s',
        ],
    ];
    $benefits = [
        'no' => [
            ['title' => 'Høyere konvertering', 'text' => 'Rask, mobilvennlig butikkflyt med tydelige produktsider og handlekurv som øker fullførte kjøp.'],
            ['title' => 'Full kontroll i admin', 'text' => 'Administrer produkter, lager, priser, kategorier og kampanjer i et moderne adminpanel.'],
            ['title' => 'Flerspråklig & SEO', 'text' => 'Norsk, engelsk, ukrainsk og russisk med hreflang, Schema.org Product og dynamisk sitemap.'],
            ['title' => 'Skalerbar PHP-arkitektur', 'text' => 'Uten tungt rammeverk — enkel deploy på norsk hosting og migrering til MySQL.'],
        ],
        'en' => [
            ['title' => 'Higher conversion', 'text' => 'Fast, mobile-first storefront with clear product pages and session cart that increases completed purchases.'],
            ['title' => 'Full admin control', 'text' => 'Manage products, stock, prices, categories and campaigns in a modern admin panel.'],
            ['title' => 'Multilingual & SEO', 'text' => 'Norwegian, English, Ukrainian and Russian with hreflang, Schema.org Product and dynamic sitemap.'],
            ['title' => 'Scalable PHP architecture', 'text' => 'No heavy framework — easy deploy on Norwegian hosting and migration to MySQL.'],
        ],
        'uk' => [
            ['title' => 'Вища конверсія', 'text' => 'Швидкий mobile-first магазин з чіткими сторінками товарів і кошиком, що підвищує завершені покупки.'],
            ['title' => 'Повний контроль в адмінці', 'text' => 'Керуйте товарами, залишками, цінами, категоріями та акціями в сучасній адмін-панелі.'],
            ['title' => 'Багатомовність та SEO', 'text' => 'Норвезька, англійська, українська та російська з hreflang, Schema.org Product і динамічним sitemap.'],
            ['title' => 'Масштабована PHP-архітектура', 'text' => 'Без важких фреймворків — легкий деплой на норвезькому хостингу та міграція на MySQL.'],
        ],
        'ru' => [
            ['title' => 'Выше конверсия', 'text' => 'Быстрый mobile-first магазин с понятными карточками товаров и корзиной, повышающей завершённые покупки.'],
            ['title' => 'Полный контроль в админке', 'text' => 'Управляйте товарами, остатками, ценами, категориями и акциями в современной админ-панели.'],
            ['title' => 'Многоязычность и SEO', 'text' => 'Норвежский, английский, украинский и русский с hreflang, Schema.org Product и динамическим sitemap.'],
            ['title' => 'Масштабируемая PHP-архитектура', 'text' => 'Без тяжёлых фреймворков — лёгкий деплой на норвежском хостинге и миграция на MySQL.'],
        ],
    ];
    $features = [
        'no' => ['Produktkatalog med kategorier og søk', 'Sesjonshandlekurv (demo)', 'Adminpanel for produkter og lager', '4 språk med hreflang og cookie', 'Schema.org Product markup', 'Lyst, responsivt UI — mobil først'],
        'en' => ['Product catalog with categories and search', 'Session cart (demo)', 'Admin panel for products and stock', '4 languages with hreflang and cookie', 'Schema.org Product markup', 'Light responsive UI — mobile first'],
        'uk' => ['Каталог товарів з категоріями та пошуком', 'Сесійний кошик (демо)', 'Адмін-панель товарів і залишків', '4 мови з hreflang і cookie', 'Schema.org Product markup', 'Світлий адаптивний UI — mobile first'],
        'ru' => ['Каталог товаров с категориями и поиском', 'Сессионная корзина (демо)', 'Админ-панель товаров и остатков', '4 языка с hreflang и cookie', 'Schema.org Product markup', 'Светлый адаптивный UI — mobile first'],
    ];
    $faq_q = [
        'no' => ['Kan Shop CMS tilpasses %s?', 'Hvilke språk støttes?', 'Trenger jeg egen server?', 'Hvordan bestiller jeg utvikling?'],
        'en' => ['Can Shop CMS be customized for %s?', 'Which languages are supported?', 'Do I need a dedicated server?', 'How do I order development?'],
        'uk' => ['Чи можна адаптувати Shop CMS під %s?', 'Які мови підтримуються?', 'Чи потрібен окремий сервер?', 'Як замовити розробку?'],
        'ru' => ['Можно ли адаптировать Shop CMS под %s?', 'Какие языки поддерживаются?', 'Нужен ли отдельный сервер?', 'Как заказать разработку?'],
    ];
    $faq_a = [
        'no' => ['Ja. Vi tilpasser katalog, felt, betaling, frakt og varsler for din %s-virksomhet i Norge eller Europa.', 'Demoen har norsk (standard), engelsk, ukrainsk og russisk. Flere språk kan legges til raskt.', 'Nei. PHP 8+ på delt hosting eller VPS i Norge/EU er nok for start.', 'Kontakt via bilohash.com med kort beskrivelse av %s-behovet — vi leverer tilbud og tidsplan.'],
        'en' => ['Yes. We adapt catalog, fields, payments, shipping and notifications for your %s business in Norway or Europe.', 'The demo includes Norwegian (default), English, Ukrainian and Russian. More languages can be added quickly.', 'No. PHP 8+ on shared hosting or a VPS in Norway/EU is enough to start.', 'Contact via bilohash.com with a short brief about your %s needs — we provide a quote and timeline.'],
        'uk' => ['Так. Ми налаштовуємо каталог, поля, оплату, доставку та сповіщення для вашого %s-бізнесу в Норвегії чи Європі.', 'У демо — норвезька (за замовчуванням), англійська, українська та російська. Інші мови додаються швидко.', 'Ні. Достатньо PHP 8+ на shared hosting або VPS у Норвегії/ЄС.', 'Зв\'яжіться через bilohash.com з коротким описом потреб для %s — надішлемо пропозицію та терміни.'],
        'ru' => ['Да. Мы настраиваем каталог, поля, оплату, доставку и уведомления для вашего %s-бизнеса в Норвегии или Европе.', 'В демо — норвежский (по умолчанию), английский, украинский и русский. Другие языки добавляются быстро.', 'Нет. Достаточно PHP 8+ на shared hosting или VPS в Норвегии/ЕС.', 'Свяжитесь через bilohash.com с кратким описанием потребностей для %s — пришлём предложение и сроки.'],
    ];

    $out = [];
    foreach ($defs as $slug => $def) {
        $entry = ['icon' => $def['icon'], 'demo_param' => $def['demo_param']];
        foreach (['no', 'en', 'uk', 'ru'] as $lng) {
            $name  = $def[$lng];
            $lower = bh_str_lower($name);
            $t     = $tpl[$lng];
            $faqs  = [];
            foreach ($faq_q[$lng] as $i => $q) {
                $faqs[] = ['q' => sprintf($q, $lower), 'a' => sprintf($faq_a[$lng][$i], $lower)];
            }
            $entry[$lng] = [
                'title'       => sprintf($t['title'], $name),
                'description' => sprintf($t['description'], $lower),
                'keywords'    => sprintf($t['keywords'], $lower, $lower),
                'h1'          => sprintf($t['h1'], $name),
                'subtitle'    => sprintf($t['subtitle'], $lower),
                'intro'       => sprintf($t['intro'], $lower),
                'benefits'    => $benefits[$lng],
                'features'    => $features[$lng],
                'faq'         => $faqs,
                'cta'         => sprintf($t['cta'], $lower),
            ];
        }
        $out[$slug] = $entry;
    }
    return $out;
}

function sh_verticals_all(): array
{
    static $cache = null;
    if ($cache === null) {
        $file = __DIR__ . '/../data/verticals.php';
        $cache = is_file($file) ? require $file : sh_verticals_build();
    }
    return $cache;
}

function sh_vertical_by_slug(string $slug): ?array
{
    $all = sh_verticals_all();
    return $all[$slug] ?? null;
}

function sh_vertical_lang(array $vertical, string $lang): array
{
    $lang = in_array($lang, ['no', 'en', 'uk', 'ru'], true) ? $lang : 'no';
    return $vertical[$lang] ?? $vertical['no'] ?? [];
}

function sh_vertical_url(string $slug, ?string $lang = null): string
{
    $path = sh_url($slug . '/');
    if ($lang && $lang !== 'no') {
        return $path . '?lang=' . urlencode($lang);
    }
    return $path;
}

function sh_vertical_canonical(string $slug): string
{
    global $site_url, $lang;
    $base = rtrim($site_url, '/') . '/' . $slug . '/';
    return $lang !== 'no' ? $base . '?lang=' . $lang : $base;
}

function sh_vertical_lang_url_for(string $slug, string $code): string
{
    return sh_vertical_url($slug, $code === 'no' ? null : $code);
}

function sh_seo_faq_page(array $items): array
{
    $entities = [];
    foreach ($items as $item) {
        $entities[] = [
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $item['a'],
            ],
        ];
    }
    return [
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ];
}

function sh_seo_vertical_service(string $name, string $description, string $canonical): array
{
    return [
        '@type'       => 'Service',
        '@id'         => $canonical . '#service',
        'name'        => $name,
        'description' => $description,
        'url'         => $canonical,
        'provider'    => ['@id' => 'https://bilohash.com/shop/#organization'],
        'areaServed'  => [
            ['@type' => 'Country', 'name' => 'Norway'],
            ['@type' => 'Country', 'name' => 'Ukraine'],
            ['@type' => 'Place', 'name' => 'Europe'],
        ],
        'serviceType' => 'E-commerce and online store development',
    ];
}

function sh_render_vertical_seo_head(
    string $page_title,
    string $page_desc,
    string $canonical,
    array $schema_graphs = [],
    ?string $page_keywords = null,
    ?string $og_image = null
): void {
    global $lang_meta, $lang;
    $og_image = $og_image ?: sh_seo_og_image();
    $canonical_abs = sh_absolute_url($canonical);
    $robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $vertical_langs = ['no', 'en', 'uk', 'ru'];
    $slug = $GLOBALS['vertical_slug'] ?? '';
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
    <?php if ($page_keywords): ?>
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= $robots ?>">
    <meta name="author" content="Shop CMS">
    <meta name="geo.region" content="NO">
    <meta name="geo.placename" content="Norway">
    <link rel="canonical" href="<?= htmlspecialchars($canonical_abs) ?>">
    <?php foreach ($vertical_langs as $code):
        $alt = sh_absolute_url(sh_vertical_lang_url_for($slug, $code));
        $hreflang = $code === 'uk' ? 'uk' : $code;
    ?>
    <link rel="alternate" hreflang="<?= $hreflang ?>" href="<?= htmlspecialchars($alt) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars(sh_absolute_url(sh_vertical_lang_url_for($slug, 'no'))) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical_abs) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(SH_SITE_NAME) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:locale" content="<?= htmlspecialchars(str_replace('-', '_', $lang_meta['locale'])) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
    <?php if (!empty($schema_graphs)): ?>
    <script type="application/ld+json"><?= sh_seo_json(array_values(array_filter($schema_graphs))) ?></script>
    <?php endif;
}