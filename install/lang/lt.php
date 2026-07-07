<?php
$en = require __DIR__ . '/en.php';
return array_replace_recursive($en, [
    'meta' => [
        'title'       => 'Shop CMS — PHP e. parduotuvė | Užsakyti e. prekybą',
        'description' => 'Universali PHP e. prekybos sistema. Užsakyti internetinės parduotuvės kūrimą — mada, elektronika, maistas, B2B katalogas.',
        'keywords'    => 'e. prekybos skriptas, internetinė parduotuvė, PHP shop CMS, produktų katalogas, krepšelis, bilohash',
        'site_name'   => 'Shop CMS',
    ],
    'nav' => [
        'shop' => 'Parduotuvė', 'categories' => 'Kategorijos', 'sale' => 'Išpardavimas', 'cart' => 'Krepšelis',
        'search' => 'Paieška', 'signin' => 'Prisijungti', 'help' => 'Pagalba',
        'menu' => 'Meniu', 'menu_close' => 'Uždaryti meniu', 'admin' => 'Admin', 'skip' => 'Pereiti prie turinio',
        'main_nav' => 'Pagrindinė navigacija',
    ],
    'demo_strip' => [
        'text' => 'Tai gyva Shop CMS demonstracija — ne gamybinė parduotuvė.',
        'cms'  => 'Shop CMS produkto svetainė',
    ],
    'theme_preview' => [
        'banner' => 'Dizaino peržiūra — pakeitimai neišsaugomi, kol nepritaikysite.',
        'exit'   => 'Išeiti iš peržiūros',
    ],
    'admin' => [
        'design_demos_page' => [
            'title' => 'Dizaino demo',
            'intro_storefront' => 'Šeši parduotuvės dizaino demo. Peržiūrėkite parduotuvę arba pritaikykite temą gamyboje.',
            'active_theme' => 'Aktyvi tema: {theme}',
            'apply_theme' => 'Pritaikyti parduotuvei',
            'live_preview' => 'Tiesioginė parduotuvės peržiūra',
            'open_live' => 'Atidaryti tiesioginę peržiūrą',
            'apply' => 'Išvaizdos nustatymai',
        ],
    ],
]);