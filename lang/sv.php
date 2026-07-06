<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title' => 'Shop CMS — PHP e-handel Norge | Beställ webbutik',
        'description' => 'Universellt PHP e-handelsskript från Norge. Beställ skräddarsydd webbutik — mode, elektronik, mat, B2B. Live demo, Schema.org SEO, varukorg, admin.',
    ],
    'nav' => ['lang_menu' => 'Språk', 'shop' => 'Butik', 'categories' => 'Kategorier', 'sale' => 'Rea', 'cart' => 'Varukorg'],
    'demo_strip' => ['text' => 'Demobutik under utveckling — inte en riktig butik.', 'cms' => 'Shop CMS'],
    'platform_features' => [
        'items' => [
            ['icon' => 'globe', 'title' => '5 språk', 'desc' => 'Norska, engelska, svenska, ukrainska och ryska med cookie och ?lang=.'],
        ],
    ],
    'cart' => [
        'checkout' => 'Kassa',
        'checkout_note' => 'Endast demo — konfigurera Stripe, PayPal, Vipps eller postförskott i admin.',
    ],
    'checkout' => [
        'title' => 'Kassa',
        'payment_title' => 'Betalningsmetod',
        'place_order' => 'Lägg demoorder',
        'methods' => [
            'stripe' => 'Kort (Stripe)', 'paypal' => 'PayPal', 'vipps' => 'Vipps', 'cod' => 'Postförskott',
        ],
    ],
    'footer' => ['trust_langs' => '5 språk'],
    'admin' => [
        'settings_tab_pages' => 'Tjänstesidor',
        'settings_tab_footer' => 'Sidfotslänkar',
        'service_page_delivery' => 'Leverans',
        'service_page_payment' => 'Betalning',
        'service_page_privacy' => 'Integritetspolicy',
        'service_page_cookies' => 'Cookies',
        'service_page_edit' => 'Redigera sida',
        'service_pages_help' => 'Flerspråkigt innehåll för leverans, betalning, integritet och cookies. URL: page.php?slug=…',
        'view_page' => 'Visa sida',
        'page_active' => 'Sida synlig på webbplatsen',
        'page_title' => 'Sidtitel',
        'page_content' => 'Innehåll',
        'page_content_hint' => 'Vanlig text. Rader med • eller - visas som lista.',
        'page_meta_title' => 'Meta title (SEO)',
        'page_meta_description' => 'Meta description (SEO)',
        'footer_help' => 'Redigera sidfotskolumner (Butik och Juridiskt). Relativa sökvägar: search.php eller page.php?slug=privacy.',
        'footer_col_shop' => 'Butikskolumn',
        'footer_col_legal' => 'Juridisk kolumn',
        'footer_add_link' => 'Lägg till länk',
        'footer_link_id' => 'Länk-ID',
        'footer_link_url' => 'URL',
        'footer_link_active' => 'Synlig',
        'footer_link_external' => 'Öppna i ny flik (extern URL)',
        'footer_link_labels' => 'Etiketter per språk',
    ],
]);