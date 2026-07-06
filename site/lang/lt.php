<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title'       => 'Shop CMS — Užsakyti e. parduotuvę {for_country} | PHP e-commerce',
        'description' => 'Užsakyti e. parduotuvės kūrimą {in_country}. PHP skriptas: katalogas, krepšelis, Stripe PayPal Vipps COD, kategorijos, admin, Schema.org SEO. Demo {currency}.',
    ],
    'nav' => ['order' => 'Užsakyti kūrimą', 'features' => 'Funkcijos', 'tech' => 'Technologijos', 'demo' => 'Live demo', 'seo' => 'SEO', 'version' => 'Versija', 'menu' => 'Meniu', 'main_nav' => 'Pagrindinė navigacija'],
    'a11y' => ['menu' => 'Meniu'],
    'hero' => [
        'badge' => 'PHP e. prekyba · Shop CMS · %s',
        'title' => 'Užsakyti e. parduotuvės kūrimą',
        'subtitle' => 'Universali PHP e. prekyba iš {origin} {for_market} — mada, elektronika, maistas, B2B. Stripe · PayPal · Vipps · COD. Demo kainos {currency}.',
        'cta_order' => 'Užsakyti parduotuvę',
        'cta_demo' => 'Peržiūrėti demo', 'cta_admin' => 'Admin demo', 'cta_contact' => 'Susisiekti su kūrėju',
    ],
    'pitch' => [
        'title' => 'Kuriame jūsų parduotuvę su Shop CMS',
        'text' => 'Shop CMS — patikima PHP e. prekybos platforma. Pritaikome demo, checkout, kategorijas, dizainą ir SEO jūsų nišai {in_country}.',
        'cta_order' => 'Užsakyti kūrimą', 'cta_demo' => 'Išbandyti demo',
        'items' => [
            ['icon' => 'paint-brush', 'title' => 'Prekės ženklas ir UX', 'desc' => 'Jūsų logotipas, spalvos ir kategorijos — ne šablonas.'],
            ['icon' => 'credit-card', 'title' => 'Mokėjimai ir pristatymas', 'desc' => 'Stripe, PayPal, Vipps, COD — Lietuvai ir Europai.'],
            ['icon' => 'language', 'title' => 'Daugiakalbis SEO', 'desc' => 'hreflang, Schema.org, sitemap, PageSpeed 99+ ir AI vertimas.'],
            ['icon' => 'server', 'title' => 'Diegimas ir palaikymas', 'desc' => 'JSON demo arba MySQL produkcijoje — white-label pagal poreikį.'],
        ],
    ],
    'intro' => ['title' => 'Vienas skriptas — daug parduotuvių modelių', 'text' => 'Peržiūrėkite live demo, pasirinkite nišą ir užsakykite PHP parduotuvę.', 'use_label' => 'Idealu:'],
    'faq' => [
        'title' => 'DUK — Shop CMS užsakymas',
        'items' => [
            ['q' => 'Kaip užsakyti parduotuvę {in_country}?', 'a' => 'Atidarykite užsakymo puslapį arba kontaktų formą su niša, kalbomis ir mokėjimais.'],
            ['q' => 'Ar Shop CMS tik Norvegijai?', 'a' => 'Skriptas sukurtas Norvegijoje, bet diegiame Lietuvai, Ukrainai ir Europai.'],
            ['q' => 'Ar galiu pradėti nuo live demo?', 'a' => 'Taip. Demo /shop/ rodo katalogą, krepšelį, admin ir checkout.'],
            ['q' => 'Kokia versija produkcijoje?', 'a' => 'Dabartinė v1.3.0 — ta pati admin, produkto svetainėje ir demo pakete.'],
        ],
    ],
    'cta' => ['title' => 'Pasiruošę užsakyti parduotuvę?', 'text' => 'Aprašykite nišą {in_country}. Pritaikysime Shop CMS ir paleisime jūsų domene.', 'btn' => 'Užsakyti kūrimą'],
    'tech' => ['items' => [
        'PHP 8+ be framework — shared hosting arba VPS Norvegijoje/EU',
        'Produkcijos skripto demo — JSON be MySQL. Klientų projektai — MySQL/PostgreSQL pagal pageidavimą',
        'Modulinis i18n su kalbų failais ir cookie perjungimu',
        'SEO: canonical, hreflang, sitemap, robots.txt, Schema.org Product',
        'Mokėjimai: Stripe, PayPal, Vipps, COD — admin panelėje',
        'Lengvas adaptyvus UI su Font Awesome 6 — be build',
    ]],
    'version' => ['older_versions' => 'Ankstesnės versijos (%d)', 'script_note' => 'Ta pati versija admin ir produkto svetainėje. Demo veikia su JSON be MySQL. Klientų projektai — MySQL/PostgreSQL pagal poreikį.'],
    'cookies_banner' => [
        'title' => 'Naudojame slapukus',
        'text' => 'Shop CMS naudoja sesijos, kalbos ir pasirenkamos analitikos slapukus.',
        'privacy' => 'Privatumo politika', 'more' => 'Slapukų politika',
        'accept' => 'Priimti visus', 'reject' => 'Atmesti', 'settings' => 'Nustatymai',
        'warning' => 'Be slapukų kai kurios funkcijos gali neveikti.',
        'accept_again' => 'Priimti slapukus',
        'modal_title' => 'Slapukų nustatymai', 'modal_text' => 'Būtini slapukai (krepšelis, kalba) reikalingi demo.',
        'modal_cancel' => 'Atšaukti', 'modal_save' => 'Išsaugoti',
    ],
    'footer' => [
        'demo_link' => 'Live parduotuvės demo', 'order' => 'Užsakyti kūrimą', 'order_page' => 'Užsakyti parduotuvę', 'news' => 'Paleidimo naujienos',
        'tagline' => 'White-label PHP e. prekybos platforma iš Norvegijos. Paleiskite mados, elektronikos, maisto ir B2B parduotuves Lietuvai ir Europai.',
        'copyright' => '© %s Shop CMS · %s',
    ],
]);