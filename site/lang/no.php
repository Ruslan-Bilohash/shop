<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title'       => 'Shop CMS — Bestill nettbutikk {for_country} | PHP e-handelsskript',
        'description' => 'Bestill utvikling av nettbutikk {in_country}. PHP e-handel: katalog, handlekurv, Stripe PayPal Vipps postoppkrav, kategorier, admin, Schema.org SEO. Demo i {currency}.',
    ],
    'nav' => ['order' => 'Bestill utvikling', 'features' => 'Funksjoner', 'tech' => 'Teknologi', 'demo' => 'Live demo'],
    'hero' => [
        'badge' => 'PHP e-handel · Shop CMS · %s',
        'title' => 'Bestill utvikling av nettbutikk',
        'subtitle' => 'Universell PHP e-handel fra {origin} {for_market} — mote, elektronikk, mat, B2B. Stripe · PayPal · Vipps · postoppkrav. Demopriser i {currency}.',
        'cta_order' => 'Bestill nettbutikk',
        'cta_demo' => 'Se live demo',
        'cta_admin' => 'Admin demo',
        'cta_contact' => 'Kontakt utvikler',
    ],
    'pitch' => [
        'title' => 'Vi utvikler nettbutikken din på Shop CMS',
        'text' => 'Shop CMS er vårt PHP e-handelsgrunnlag. Vi tilpasser demo-butikken, checkout, kategorier, design og SEO for din nisje {in_country} — og deployer på ditt domene og hosting.',
        'cta_order' => 'Bestill utvikling',
        'cta_demo' => 'Prøv live demo',
        'items' => [
            ['icon' => 'paint-brush', 'title' => 'Merkevare & UX', 'desc' => 'Din logo, farger, produktfelt og kategorier — ikke en generisk mal.'],
            ['icon' => 'credit-card', 'title' => 'Betaling & frakt', 'desc' => 'Stripe, PayPal, Vipps, postoppkrav — konfigurert for Norge og Skandinavia.'],
            ['icon' => 'language', 'title' => 'Flerspråklig SEO', 'desc' => 'hreflang, Schema.org Product, sitemap, PageSpeed 99+ på demo og AI-oversettelse i admin.'],
            ['icon' => 'server', 'title' => 'Deploy & support', 'desc' => 'JSON-demo eller MySQL-produksjon på delt hosting / VPS — white-label valgfritt.'],
        ],
    ],
    'intro' => [
        'title' => 'Ett skript — mange butikkmodeller',
        'text' => 'Utforsk live demo, velg nisje under og bestill en skreddersydd PHP-nettbutikk. Hver vertikalside forklarer SEO, funksjoner og demo filtrert for det norske markedet.',
        'use_label' => 'Ideelt for:',
    ],
    'faq' => [
        'title' => 'FAQ — bestilling av Shop CMS',
        'items' => [
            ['q' => 'Hvordan bestiller jeg nettbutikk {in_country}?', 'a' => 'Åpne bestillingssiden eller kontaktskjema med nisje, språk og betalingsløsninger. Vi svarer med tilbud, tidsplan og valgfritt MySQL / fraktomfang.'],
            ['q' => 'Er Shop CMS bare for Norge?', 'a' => 'Skriptet er bygget i Norge med NOK-demo, Vipps og Posten-sporing — men vi deployer også for Ukraina og Europa med lokal valuta og SEO.'],
            ['q' => 'Kan jeg starte fra live demo?', 'a' => 'Ja. Demoen på /shop/ viser katalog, handlekurv, admin og checkout. Vi tilpasser denne kodebasen — ikke SaaS-lås.'],
            ['q' => 'Hvilken versjon er i produksjon?', 'a' => 'Gjeldende produksjonsversjon er v1.3.0 — samme versjon i admin, produktside og demo-pakke.'],
        ],
    ],
    'seo' => [
        'lighthouse_title' => 'Google PageSpeed Insights (demo)',
        'scores' => [
            ['label' => 'Ytelse', 'value' => '99'],
            ['label' => 'Tilgjengelighet', 'value' => '100'],
            ['label' => 'Beste praksis', 'value' => '100'],
            ['label' => 'SEO', 'value' => '100', 'note' => 'canonical, OG, Schema', 'highlight' => true],
            ['label' => 'Agentvisning', 'value' => '99'],
        ],
    ],
    'cta' => [
        'title' => 'Klar for å bestille nettbutikk?',
        'text' => 'Fortell oss om nisjen din {in_country} — mote, elektronikk, mat, B2B eller markedsplass. Vi tilpasser Shop CMS v1.3 og lanserer på ditt domene.',
        'btn' => 'Bestill utvikling',
    ],
    'order' => [
        'page_title' => 'Bestill nettbutikk utvikling | Shop CMS',
        'h1' => 'Bestill utvikling av nettbutikk',
        'subtitle' => 'Skreddersydd PHP-nettbutikk for mote, elektronikk, mat og B2B — fra {origin}, {for_country}. Betaling i {currency}.',
        'benefits_title' => 'Dette får du', 'steps_title' => 'Slik bestiller du',
        'cta_contact' => 'Diskuter prosjektet', 'cta_demo' => 'Se live demo', 'cta_product' => 'Produktfunksjoner',
        'crosslinks_title' => 'Utforsk før du bestiller',
    ],
    'version' => [
        'older_versions' => 'Eldre versjoner (%d)',
        'script_note' => 'Samme versjon i admin og produktside. Produksjonsskript-demo kjører på JSON uten MySQL. Ekte kundeprosjekter bruker MySQL/PostgreSQL ved behov.',
    ],
    'cookies_banner' => [
        'title' => 'Vi bruker informasjonskapsler',
        'text' => 'Shop CMS bruker session-, språk- og valgfrie analyse-cookies. Godta for å fortsette på demo og produktside.',
        'privacy' => 'Personvern',
        'more' => 'Cookies',
        'accept' => 'Godta alle',
        'reject' => 'Avslå',
        'settings' => 'Innstillinger',
        'warning' => 'Noen funksjoner virker kanskje ikke uten cookies.',
        'accept_again' => 'Godta cookies',
        'modal_title' => 'Cookie-innstillinger',
        'modal_text' => 'Nødvendige cookies (handlekurv, språk) kreves for demo.',
        'modal_cancel' => 'Avbryt',
        'modal_save' => 'Lagre',
    ],
    'footer' => [
        'demo_link' => 'Live butikkdemo', 'order' => 'Bestill utvikling', 'order_page' => 'Bestill nettbutikk',
        'tagline' => 'White-label PHP e-handelsplattform fra Norge. Lanser nettbutikker for mote, elektronikk, mat og B2B i Norge og Skandinavia — flerspråklig SEO, adminpanel og Stripe · PayPal · Vipps · postoppkrav.',
        'copyright' => '© %s Shop CMS · %s',
    ],
]);