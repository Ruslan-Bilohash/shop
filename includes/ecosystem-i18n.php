<?php
/**
 * Shared Bilohash ecosystem UI + product labels (NO / EN / UK / RU / LT).
 */
require_once __DIR__ . '/ecosystem-defs.php';

function bh_ecosystem_lang_code(string $lang): string
{
    if ($lang === 'ua') {
        return 'uk';
    }

    return match ($lang) {
        'no', 'en', 'uk', 'ru', 'lt' => $lang,
        default => 'en',
    };
}

function bh_ecosystem_ui(string $lang): array
{
    $lang = bh_ecosystem_lang_code($lang);
    $all = [
        'no' => [
            'title'       => 'Andre Bilohash CMS-produkter',
            'subtitle'    => 'PHP-skript for shop, auksjon, frilans, 3D, AI og mer — live demo fra Norge.',
            'strip_label' => 'Bilohash-økosystem',
            'product_btn' => 'Produktside',
            'demo_btn'    => 'Live demo',
            'footer_ecosystem' => 'Bilohash-økosystem',
            'footer_related'   => 'Relaterte produkter',
            'footer_eco_toggle' => 'Bilohash-økosystem — alle CMS-produkter',
            'footer_eco_show_more' => 'Vis flere (%d)',
            'footer_eco_show_less' => 'Vis færre',
        ],
        'en' => [
            'title'       => 'Other Bilohash CMS products',
            'subtitle'    => 'Universal PHP scripts — shop, auctions, freelance, 3D, AI and more. Live demos from Norway.',
            'strip_label' => 'Bilohash ecosystem',
            'product_btn' => 'Product page',
            'demo_btn'    => 'Live demo',
            'footer_ecosystem' => 'Bilohash ecosystem',
            'footer_related'   => 'Related products',
            'footer_eco_toggle' => 'Bilohash ecosystem — all CMS products',
            'footer_eco_show_more' => 'Show more (%d)',
            'footer_eco_show_less' => 'Show less',
        ],
        'uk' => [
            'title'       => 'Інші продукти Bilohash CMS',
            'subtitle'    => 'PHP-скрипти для магазину, аукціонів, фрілансу, 3D, AI та інших рішень — live demo.',
            'strip_label' => 'Екосистема Bilohash',
            'product_btn' => 'Сторінка продукту',
            'demo_btn'    => 'Live demo',
            'footer_ecosystem' => 'Екосистема Bilohash',
            'footer_related'   => 'Пов\'язані продукти',
            'footer_eco_toggle' => 'Екосистема Bilohash — усі CMS продукти',
            'footer_eco_show_more' => 'Показати ще (%d)',
            'footer_eco_show_less' => 'Згорнути',
        ],
        'ru' => [
            'title'       => 'Другие продукты Bilohash CMS',
            'subtitle'    => 'PHP-скрипты для магазина, аукционов, фриланса, 3D, AI и других решений — live demo.',
            'strip_label' => 'Экосистема Bilohash',
            'product_btn' => 'Страница продукта',
            'demo_btn'    => 'Live demo',
            'footer_ecosystem' => 'Экосистема Bilohash',
            'footer_related'   => 'Связанные продукты',
            'footer_eco_toggle' => 'Экосистема Bilohash — все CMS продукты',
            'footer_eco_show_more' => 'Показать ещё (%d)',
            'footer_eco_show_less' => 'Свернуть',
        ],
        'lt' => [
            'title'       => 'Kiti Bilohash CMS produktai',
            'subtitle'    => 'PHP skriptai parduotuvei, aukcionams, freelance, 3D, AI ir kt. — live demo.',
            'strip_label' => 'Bilohash ekosistema',
            'product_btn' => 'Produkto puslapis',
            'demo_btn'    => 'Live demo',
            'footer_ecosystem' => 'Bilohash ekosistema',
            'footer_related'   => 'Susiję produktai',
            'footer_eco_toggle' => 'Bilohash ekosistema — visi CMS produktai',
            'footer_eco_show_more' => 'Rodyti daugiau (%d)',
            'footer_eco_show_less' => 'Suskleisti',
        ],
    ];

    return $all[$lang] ?? $all['en'];
}

function bh_ecosystem_product_labels(string $lang): array
{
    $lang = bh_ecosystem_lang_code($lang);
    $labels = [
        'auction' => [
            'no' => ['name' => 'Auction CMS', 'desc' => 'Tidsbegrensede auksjoner, bud, selger-dashbord og CSV-eksport.'],
            'en' => ['name' => 'Auction CMS', 'desc' => 'Timed auctions, bids, seller dashboard and CSV export.'],
            'uk' => ['name' => 'Auction CMS', 'desc' => 'Аукціони з таймером, ставки, адмін-дашборд і CSV-експорт.'],
            'ru' => ['name' => 'Auction CMS', 'desc' => 'Аукционы с таймером, ставки, админ-дашборд и CSV-экспорт.'],
            'lt' => ['name' => 'Auction CMS', 'desc' => 'Laiku riboti aukcionai, statymai ir admin panelė.'],
        ],
        'shop' => [
            'no' => ['name' => 'Shop CMS', 'desc' => 'E-handel — katalog, handlekurv, kategorier og adminpanel.'],
            'en' => ['name' => 'Shop CMS', 'desc' => 'E-commerce — catalog, cart, categories and admin panel.'],
            'uk' => ['name' => 'Shop CMS', 'desc' => 'E-commerce — каталог, кошик, категорії та адмін-панель.'],
            'ru' => ['name' => 'Shop CMS', 'desc' => 'E-commerce — каталог, корзина, категории и админ-панель.'],
            'lt' => ['name' => 'Shop CMS', 'desc' => 'E. prekyba — katalogas, krepšelis ir admin.'],
        ],
        'pizza' => [
            'no' => ['name' => 'Pizza CMS', 'desc' => 'Restaurant & pizzeria — meny, reservasjon, solterrasse og adminpanel.'],
            'en' => ['name' => 'Pizza CMS', 'desc' => 'Restaurant & pizzeria — menu, reservations, terrace section and admin panel.'],
            'uk' => ['name' => 'Pizza CMS', 'desc' => 'Ресторан і піцерія — меню, бронювання, тераса та адмін-панель.'],
            'ru' => ['name' => 'Pizza CMS', 'desc' => 'Ресторан и пиццерия — меню, бронирование, терраса и админ-панель.'],
            'lt' => ['name' => 'Pizza CMS', 'desc' => 'Restoranas ir picerija — meniu, rezervacijos ir admin.'],
        ],
        'freelance' => [
            'no' => ['name' => 'Freelance CMS', 'desc' => 'Jobbportal, tilbud, frilanserprofiler og SEO-regioner.'],
            'en' => ['name' => 'Freelance CMS', 'desc' => 'Job board, proposals, freelancer profiles and SEO regions.'],
            'uk' => ['name' => 'Freelance CMS', 'desc' => 'Дошка проєктів, пропозиції, профілі фрілансерів і SEO-регіони.'],
            'ru' => ['name' => 'Freelance CMS', 'desc' => 'Доска проектов, предложения, профили фрилансеров и SEO-регионы.'],
            'lt' => ['name' => 'Freelance CMS', 'desc' => 'Darbo skelbimai, pasiūlymai ir SEO regionai.'],
        ],
        'gamehub' => [
            'no' => ['name' => 'GameHub CMS', 'desc' => 'Spillserver-monitor — CS2, Minecraft, Rust, admin og JSON.'],
            'en' => ['name' => 'GameHub CMS', 'desc' => 'Game server monitor — CS2, Minecraft, Rust, admin and JSON.'],
            'uk' => ['name' => 'GameHub CMS', 'desc' => 'Моніторинг серверів — CS2, Minecraft, Rust, адмін і JSON.'],
            'ru' => ['name' => 'GameHub CMS', 'desc' => 'Мониторинг серверов — CS2, Minecraft, Rust, админ и JSON.'],
            'lt' => ['name' => 'GameHub CMS', 'desc' => 'Žaidimų serverių monitorius — CS2, Minecraft, Rust.'],
        ],
        'tavle' => [
            'no' => ['name' => 'Bilen CMS', 'desc' => 'Bilannonser — klebrige filtre, 18 demo, Schema.org Car og SQLite-admin.'],
            'en' => ['name' => 'Bilen CMS', 'desc' => 'Car classifieds — sticky filters, 18 demo listings, Schema.org Car and SQLite admin.'],
            'uk' => ['name' => 'Bilen CMS', 'desc' => 'Оголошення авто — липкі фільтри, 18 демо, Schema.org Car і SQLite-адмін.'],
            'ru' => ['name' => 'Bilen CMS', 'desc' => 'Объявления авто — липкие фильтры, 18 демо, Schema.org Car и SQLite-админ.'],
            'lt' => ['name' => 'Bilen CMS', 'desc' => 'Automobilių skelbimai — filtrai, Schema.org Car ir SQLite admin.'],
        ],
        'faktura' => [
            'no' => ['name' => 'Faktura CMS', 'desc' => 'Mobil faktura — 2 klikk, kunder, SMTP, AI-notater, QR-betaling, 12 design, 7 språk.'],
            'en' => ['name' => 'Faktura CMS', 'desc' => 'Mobile invoices — 2-click send, clients, SMTP, AI notes, QR pay links, 12 designs, 7 languages.'],
            'uk' => ['name' => 'Faktura CMS', 'desc' => 'Рахунки з телефону — 2 кліки, клієнти, SMTP, AI, QR оплата, 12 дизайнів, 7 мов.'],
            'ru' => ['name' => 'Faktura CMS', 'desc' => 'Счета с телефона — 2 клика, клиенты, SMTP, AI, QR оплата, 12 дизайнов, 7 языков.'],
            'lt' => ['name' => 'Faktura CMS', 'desc' => 'Mobilios sąskaitos — 2 paspaudimai, klientai, SMTP, AI, QR mokėjimas, 12 dizainų, 7 kalbos.'],
        ],
        'today' => [
            'no' => ['name' => 'Today CMS', 'desc' => 'Nyhets-CMS — artikler, forfattere, kategorier og JSON-admin.'],
            'en' => ['name' => 'Today CMS', 'desc' => 'News CMS — articles, authors, categories and JSON admin.'],
            'uk' => ['name' => 'Today CMS', 'desc' => 'News CMS — статті, автори, категорії та JSON-адмін.'],
            'ru' => ['name' => 'Today CMS', 'desc' => 'News CMS — статьи, авторы, категории и JSON-админ.'],
            'lt' => ['name' => 'Today CMS', 'desc' => 'Naujienų CMS — straipsniai, kategorijos ir admin.'],
        ],
        '3d' => [
            'no' => ['name' => '3D Gallery CMS', 'desc' => '3D-print bestillinger, PayPal/Vipps og ordreadmin.'],
            'en' => ['name' => '3D Gallery CMS', 'desc' => '3D print orders, PayPal/Vipps and order admin.'],
            'uk' => ['name' => '3D Gallery CMS', 'desc' => '3D-друк, PayPal/Vipps та адмін замовлень.'],
            'ru' => ['name' => '3D Gallery CMS', 'desc' => '3D-печать, PayPal/Vipps и админ заказов.'],
            'lt' => ['name' => '3D Gallery CMS', 'desc' => '3D spausdinimo užsakymai ir admin.'],
        ],
        'ai' => [
            'no' => ['name' => 'AI Platform', 'desc' => 'Chat-widgets, CRM-demoer og Grok/OpenAI-automatisering.'],
            'en' => ['name' => 'AI Platform', 'desc' => 'Chat widgets, CRM demos and Grok/OpenAI automation.'],
            'uk' => ['name' => 'AI Платформа', 'desc' => 'Чат-віджети, CRM-демо та автоматизація Grok/OpenAI.'],
            'ru' => ['name' => 'AI Платформа', 'desc' => 'Чат-виджеты, CRM-демо и автоматизация Grok/OpenAI.'],
            'lt' => ['name' => 'AI Platforma', 'desc' => 'Pokalbių valdikliai ir Grok/OpenAI automatizacija.'],
        ],
        'wordpress' => [
            'no' => ['name' => 'WordPress AI Plugin', 'desc' => 'Offisiell bilohash-ai-chat-consultant på WordPress.org.'],
            'en' => ['name' => 'WordPress AI Plugin', 'desc' => 'Official bilohash-ai-chat-consultant on WordPress.org.'],
            'uk' => ['name' => 'WordPress AI плагін', 'desc' => 'Офіційний bilohash-ai-chat-consultant на WordPress.org.'],
            'ru' => ['name' => 'WordPress AI плагин', 'desc' => 'Официальный bilohash-ai-chat-consultant на WordPress.org.'],
            'lt' => ['name' => 'WordPress AI įskiepis', 'desc' => 'Oficialus bilohash-ai-chat-consultant WordPress.org.'],
        ],
        'news' => [
            'no' => ['name' => 'Nyheter & utgivelser', 'desc' => 'Utgivelseshub — demoer, admin-tilgang og produktsider.'],
            'en' => ['name' => 'News & Releases', 'desc' => 'Release hub — demos, admin access and product pages.'],
            'uk' => ['name' => 'Новини та релізи', 'desc' => 'Хаб релізів — демо, адмінка та продуктові сторінки.'],
            'ru' => ['name' => 'Новости и релизы', 'desc' => 'Хаб релизов — демо, админка и продуктовые страницы.'],
            'lt' => ['name' => 'Naujienos ir leidiniai', 'desc' => 'Leidinių centras — demo ir produktų puslapiai.'],
        ],
        'booking' => [
            'no' => ['name' => 'Booking CMS', 'desc' => 'Hotell, klinikker, salonger — online reservasjon med adminpanel.'],
            'en' => ['name' => 'Booking CMS', 'desc' => 'Hotels, clinics, salons — online reservation with admin panel.'],
            'uk' => ['name' => 'Booking CMS', 'desc' => 'Готелі, клініки, салони — онлайн-бронювання з адмін-панеллю.'],
            'ru' => ['name' => 'Booking CMS', 'desc' => 'Отели, клиники, салоны — онлайн-бронирование с админ-панелью.'],
            'lt' => ['name' => 'Booking CMS', 'desc' => 'Viešbučiai, klinikos, salonai — rezervacijos su admin.'],
        ],
    ];

    $out = [];
    foreach ($labels as $slug => $byLang) {
        $out[$slug] = $byLang[$lang] ?? $byLang['en'];
    }

    return $out;
}

/**
 * Merge ecosystem products into CMS translation array.
 */
function bh_apply_ecosystem_translations(array $t, string $lang, string $excludeSlug = ''): array
{
    $ui = bh_ecosystem_ui($lang);
    $t['ecosystem'] = array_merge($ui, $t['ecosystem'] ?? []);
    $t['ecosystem']['items'] = bh_ecosystem_merge_labels(bh_ecosystem_product_labels($lang), $excludeSlug);

    if (!isset($t['footer']) || !is_array($t['footer'])) {
        $t['footer'] = [];
    }
    if (empty($t['footer']['ecosystem'])) {
        $t['footer']['ecosystem'] = $ui['footer_ecosystem'];
    }
    if (empty($t['footer']['related_products'])) {
        $t['footer']['related_products'] = $ui['footer_related'];
    }
    if (empty($t['footer']['eco_toggle'])) {
        $t['footer']['eco_toggle'] = $ui['footer_eco_toggle'] ?? $ui['footer_ecosystem'];
    }
    if (empty($t['footer']['eco_show_more'])) {
        $t['footer']['eco_show_more'] = $ui['footer_eco_show_more'] ?? 'Show more (%d)';
    }
    if (empty($t['footer']['eco_show_less'])) {
        $t['footer']['eco_show_less'] = $ui['footer_eco_show_less'] ?? 'Show less';
    }

    return $t;
}