<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title'       => 'Shop CMS — Beställ e-handelswebbplats {for_country} | PHP webbutik',
        'description' => 'Beställ skräddarsydd e-handelswebbplats {in_country}. PHP webbutik: katalog, varukorg, Stripe PayPal Vipps COD, kategorier, admin, Schema.org SEO. Demo i {currency}.',
    ],
    'nav' => ['features' => 'Funktioner', 'tech' => 'Teknik', 'demo' => 'Live demo', 'admin' => 'Admin', 'contact' => 'Kontakt', 'order' => 'Beställ utveckling', 'seo' => 'SEO', 'version' => 'Version', 'menu' => 'Meny', 'main_nav' => 'Huvudnavigering'],
    'hero' => [
        'badge' => 'PHP e-handel · Shop CMS · %s',
        'title' => 'Beställ utveckling av e-handelswebbplats',
        'subtitle' => 'Universell PHP e-handel från {origin} {for_market} — mode, elektronik, mat, B2B. Stripe · PayPal · Vipps · postförskott. Demopriser i {currency}.',
        'cta_order' => 'Beställ webbutik',
        'cta_demo' => 'Se live demo', 'cta_admin' => 'Admin demo', 'cta_contact' => 'Kontakta utvecklare',
    ],
    'pitch' => [
        'title' => 'Vi utvecklar din webbutik på Shop CMS',
        'text' => 'Shop CMS är vår PHP e-handelsplattform. Vi anpassar demo, checkout, kategorier, design och SEO för din nisch {in_country}.',
        'cta_order' => 'Beställ utveckling', 'cta_demo' => 'Prova live demo',
        'items' => [
            ['icon' => 'paint-brush', 'title' => 'Varumärke & UX', 'desc' => 'Din logotyp, färger och kategorier — inte en generisk mall.'],
            ['icon' => 'credit-card', 'title' => 'Betalning & frakt', 'desc' => 'Stripe, PayPal, Vipps, postförskott — för Sverige och Norden.'],
            ['icon' => 'language', 'title' => 'Flerspråkig SEO', 'desc' => 'hreflang, Schema.org, sitemap, PageSpeed 99+ och AI-översättning.'],
            ['icon' => 'server', 'title' => 'Deploy & support', 'desc' => 'JSON-demo eller MySQL-produktion på shared hosting / VPS.'],
        ],
    ],
    'intro' => ['title' => 'Ett skript — många butiksmodeller', 'text' => 'Utforska live demo, välj nisch och beställ en skräddarsydd PHP-webbutik.', 'use_label' => 'Ideellt för:'],
    'faq' => [
        'title' => 'FAQ — beställning av Shop CMS',
        'items' => [
            ['q' => 'Hur beställer jag webbutik {in_country}?', 'a' => 'Öppna beställningssidan eller kontaktformuläret med nisch, språk och betalningslösningar.'],
            ['q' => 'Är Shop CMS bara för Norge?', 'a' => 'Skriptet byggdes i Norge men vi deployer för Sverige, Ukraina och Europa.'],
            ['q' => 'Kan jag börja från live demo?', 'a' => 'Ja. Demon på /shop/ visar katalog, varukorg, admin och checkout.'],
            ['q' => 'Vilken version är i produktion?', 'a' => 'Aktuell produktionsversion är v1.3.0 i admin, produktsida och demo-paket.'],
        ],
    ],
    'cta' => ['title' => 'Redo att beställa webbutik?', 'text' => 'Berätta om din nisch {in_country}. Vi anpassar Shop CMS och lanserar på din domän.', 'btn' => 'Beställ utveckling'],
    'tech' => ['items' => [
        'PHP 8+ utan ramverk — shared hosting eller VPS i Norge/EU',
        'Produktionsskript-demo — JSON utan MySQL. Kundprojekt använder MySQL/PostgreSQL på begäran',
        'Modulärt i18n med språkfiler och cookie-baserat byte',
        'SEO: canonical, hreflang, sitemap, robots.txt, Schema.org Product',
        'Betalningar: Stripe, PayPal, Vipps, postförskott — i admin',
        'Lätt responsiv UI med Font Awesome 6 — ingen build',
    ]],
    'version' => ['older_versions' => 'Äldre versioner (%d)', 'script_note' => 'Samma version i admin och produktsida. Demon körs på JSON utan MySQL. Kundprojekt deployas på MySQL/PostgreSQL vid behov.'],
    'cookies_banner' => [
        'title' => 'Vi använder cookies',
        'text' => 'Shop CMS använder sessions-, språk- och valfria analyscookies.',
        'privacy' => 'Integritetspolicy', 'more' => 'Cookiepolicy',
        'accept' => 'Godkänn alla', 'reject' => 'Avvisa', 'settings' => 'Inställningar',
        'warning' => 'Vissa funktioner kanske inte fungerar utan cookies.',
        'accept_again' => 'Godkänn cookies',
        'modal_title' => 'Cookieinställningar', 'modal_text' => 'Nödvändiga cookies (varukorg, språk) krävs för demon.',
        'modal_cancel' => 'Avbryt', 'modal_save' => 'Spara',
    ],
    'footer' => [
        'demo_link' => 'Live butiksdemo', 'order' => 'Beställ utveckling', 'order_page' => 'Beställ webbutik', 'news' => 'Lanseringsnyheter',
        'tagline' => 'White-label PHP e-handelsplattform från Norge. Lansera mode-, elektronik-, mat- och B2B-butiker för Sverige och Norden.',
    ],
]);