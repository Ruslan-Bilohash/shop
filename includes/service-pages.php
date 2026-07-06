<?php

/** @return list<string> */
function sh_service_builtin_slugs(): array
{
    return ['delivery', 'payment', 'privacy', 'cookies'];
}

function sh_service_page_slug_valid(string $slug): bool
{
    $slug = strtolower(trim($slug));
    return $slug !== '' && preg_match('/^[a-z][a-z0-9_-]{1,31}$/', $slug) === 1;
}

function sh_service_page_is_builtin(string $slug): bool
{
    return in_array($slug, sh_service_builtin_slugs(), true);
}

/** @return array<string, array{icon:string,admin_label:string,custom?:bool}> */
function sh_service_builtin_page_defs(): array
{
    return [
        'delivery' => ['icon' => 'truck', 'admin_label' => 'Delivery'],
        'payment'  => ['icon' => 'credit-card', 'admin_label' => 'Payment'],
        'privacy'  => ['icon' => 'user-shield', 'admin_label' => 'Privacy policy'],
        'cookies'  => ['icon' => 'cookie-bite', 'admin_label' => 'Cookies'],
    ];
}

/** @return array<string, array{icon:string,admin_label:string,custom?:bool}> */
function sh_service_page_defs(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $settings = sh_merge_service_settings(is_array($settings) ? $settings : []);
    $defs = sh_service_builtin_page_defs();
    foreach ($settings['service_pages'] ?? [] as $slug => $page) {
        if (!is_array($page) || isset($defs[$slug])) {
            continue;
        }
        $defs[$slug] = [
            'icon'         => trim((string) ($page['icon'] ?? 'file-lines')) ?: 'file-lines',
            'admin_label'  => trim((string) ($page['admin_label'] ?? '')) ?: ucfirst(str_replace(['-', '_'], ' ', $slug)),
            'custom'       => true,
        ];
    }
    return $defs;
}

/** @return list<string> */
function sh_service_page_slugs(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $settings = sh_merge_service_settings(is_array($settings) ? $settings : []);
    $slugs = array_keys($settings['service_pages'] ?? []);
    sort($slugs);
    return $slugs !== [] ? $slugs : sh_service_builtin_slugs();
}

function sh_lang_codes_for_defaults(): array
{
    if (function_exists('sh_langs')) {
        return array_keys(sh_langs());
    }
    if (function_exists('sh_builtin_langs')) {
        return array_keys(sh_builtin_langs());
    }
    return ['no', 'en', 'uk', 'ru', 'sv'];
}

function sh_service_pages_defaults(): array
{
    $langs = sh_lang_codes_for_defaults();
    $mk = static function (array $byLang) use ($langs): array {
        $out = [];
        foreach ($langs as $code) {
            $out[$code] = $byLang[$code] ?? $byLang['en'] ?? '';
        }
        return $out;
    };

    return [
        'service_pages' => [
            'delivery' => [
                'active'  => true,
                'title'   => $mk([
                    'en' => 'Delivery & shipping',
                    'no' => 'Levering og frakt',
                    'uk' => 'Доставка та відправлення',
                    'ru' => 'Доставка и отправка',
                    'sv' => 'Leverans och frakt',
                ]),
                'content' => $mk([
                    'en' => "Demo Shop CMS — shipping information.\n\n• Standard delivery 3–5 business days within Norway\n• EU delivery 5–10 business days (demo rates)\n• Free pickup option in Oslo area (demo)\n• Tracking email sent when order ships (production feature)\n\nThis is a showcase store — no real shipments are processed.",
                    'no' => "Demo Shop CMS — fraktinformasjon.\n\n• Standardlevering 3–5 virkedager i Norge\n• EU-levering 5–10 virkedager (demopriser)\n• Gratis henting i Oslo-området (demo)\n\nDette er en demobutikk — ingen reell forsendelse.",
                    'uk' => "Демо Shop CMS — інформація про доставку.\n\n• Стандартна доставка 3–5 робочих днів по Норвегії\n• Доставка по ЄС 5–10 днів (демо)\n\nЦе демо-магазин — реальні відправлення не здійснюються.",
                    'ru' => "Демо Shop CMS — информация о доставке.\n\n• Стандартная доставка 3–5 рабочих дней по Норвегии\n• Доставка по ЕС 5–10 дней (демо)\n\nЭто демо-магазин — реальные отправления не выполняются.",
                    'sv' => "Demo Shop CMS — leveransinformation.\n\n• Standardleverans 3–5 arbetsdagar i Norge\n• EU-leverans 5–10 dagar (demo)\n\nDetta är en demobutik — inga riktiga leveranser.",
                ]),
                'meta_title' => $mk([
                    'en' => 'Delivery — Shop CMS',
                    'no' => 'Levering — Shop CMS',
                    'uk' => 'Доставка — Shop CMS',
                    'ru' => 'Доставка — Shop CMS',
                    'sv' => 'Leverans — Shop CMS',
                ]),
                'meta_description' => $mk([
                    'en' => 'Shipping zones, delivery times and pickup options for the Shop CMS demo store.',
                    'no' => 'Fraktsoner, leveringstider og henting for Shop CMS-demobutikken.',
                    'uk' => 'Зони доставки та терміни для демо-магазину Shop CMS.',
                    'ru' => 'Зоны доставки и сроки для демо-магазина Shop CMS.',
                    'sv' => 'Leveranszoner och tider för Shop CMS-demobutiken.',
                ]),
            ],
            'payment' => [
                'active'  => true,
                'title'   => $mk([
                    'en' => 'Payment methods',
                    'no' => 'Betalingsmetoder',
                    'uk' => 'Способи оплати',
                    'ru' => 'Способы оплаты',
                    'sv' => 'Betalningsmetoder',
                ]),
                'content' => $mk([
                    'en' => "Accepted payment methods in production builds:\n\n• Stripe (cards, Apple Pay, Google Pay)\n• PayPal\n• Vipps (Norway)\n• Cash on delivery (optional)\n\nDemo checkout simulates payment — configure providers in Admin → Payments.",
                    'no' => "Betalingsmetoder i produksjon:\n\n• Stripe, PayPal, Vipps, kontant ved levering\n\nDemokassen simulerer betaling — konfigurer i Admin → Betalinger.",
                    'uk' => "Способи оплати у продакшн:\n\n• Stripe, PayPal, Vipps, накладений платіж\n\nДемо-каса симулює оплату.",
                    'ru' => "Способы оплаты в продакшене:\n\n• Stripe, PayPal, Vipps, наложенный платёж\n\nДемо-касса симулирует оплату.",
                    'sv' => "Betalningsmetoder i produktion:\n\n• Stripe, PayPal, Vipps, postförskott\n\nDemokassan simulerar betalning.",
                ]),
                'meta_title' => $mk([
                    'en' => 'Payment — Shop CMS', 'no' => 'Betaling — Shop CMS',
                    'uk' => 'Оплата — Shop CMS', 'ru' => 'Оплата — Shop CMS', 'sv' => 'Betalning — Shop CMS',
                ]),
                'meta_description' => $mk([
                    'en' => 'Stripe, PayPal, Vipps and COD options for Shop CMS storefronts.',
                    'no' => 'Stripe, PayPal, Vipps og COD for Shop CMS.',
                    'uk' => 'Stripe, PayPal, Vipps для Shop CMS.',
                    'ru' => 'Stripe, PayPal, Vipps для Shop CMS.',
                    'sv' => 'Stripe, PayPal, Vipps för Shop CMS.',
                ]),
            ],
            'privacy' => [
                'active'  => true,
                'title'   => $mk([
                    'en' => 'Privacy policy', 'no' => 'Personvern',
                    'uk' => 'Політика конфіденційності', 'ru' => 'Политика конфиденциальности', 'sv' => 'Integritetspolicy',
                ]),
                'content' => $mk([
                    'en' => "Shop CMS demo — privacy overview.\n\nWe process contact form data and session cart items locally in this demo. No real orders or payment data are stored.\n\nProduction shops should publish a full GDPR-compliant policy with data controller details, legal basis, retention and user rights.",
                    'no' => "Shop CMS demo — personvern.\n\nKontaktskjema og handlekurv lagres lokalt i demo. Ingen reelle bestillinger.\n\nProduksjonsbutikker bør publisere full GDPR-policy.",
                    'uk' => "Демо Shop CMS — конфіденційність.\n\nДані форми та кошика — лише в демо-сесії. Реальні замовлення не зберігаються.",
                    'ru' => "Демо Shop CMS — конфиденциальность.\n\nДанные формы и корзины — только в демо-сессии.",
                    'sv' => "Demo Shop CMS — integritet.\n\nKontaktformulär och varukorg lagras lokalt i demon.",
                ]),
                'meta_title' => $mk([
                    'en' => 'Privacy policy — Shop CMS', 'no' => 'Personvern — Shop CMS',
                    'uk' => 'Конфіденційність — Shop CMS', 'ru' => 'Конфиденциальность — Shop CMS', 'sv' => 'Integritet — Shop CMS',
                ]),
                'meta_description' => $mk([
                    'en' => 'Privacy and data processing information for the Shop CMS demo storefront.',
                    'no' => 'Personvern for Shop CMS-demobutikken.',
                    'uk' => 'Політика конфіденційності демо-магазину Shop CMS.',
                    'ru' => 'Политика конфиденциальности демо-магазина Shop CMS.',
                    'sv' => 'Integritetspolicy för Shop CMS-demobutiken.',
                ]),
            ],
            'cookies' => [
                'active'  => true,
                'title'   => $mk([
                    'en' => 'Cookie policy', 'no' => 'Informasjonskapsler',
                    'uk' => 'Політика cookies', 'ru' => 'Политика cookies', 'sv' => 'Cookiepolicy',
                ]),
                'content' => $mk([
                    'en' => "This demo uses:\n\n• Session cookie (cart)\n• Language preference cookie (sh_lang)\n• Optional reCAPTCHA cookies when contact form is enabled\n\nNo advertising or third-party tracking cookies in the default demo.",
                    'no' => "Denne demoen bruker sesjonskapsel for handlekurv, språkvalg og eventuelt reCAPTCHA.",
                    'uk' => "Демо використовує cookies сесії, мови та reCAPTCHA (за потреби).",
                    'ru' => "Демо использует cookies сессии, языка и reCAPTCHA (при необходимости).",
                    'sv' => "Denna demo använder sessionscookies för varukorg och språk.",
                ]),
                'meta_title' => $mk([
                    'en' => 'Cookies — Shop CMS', 'no' => 'Cookies — Shop CMS',
                    'uk' => 'Cookies — Shop CMS', 'ru' => 'Cookies — Shop CMS', 'sv' => 'Cookies — Shop CMS',
                ]),
                'meta_description' => $mk([
                    'en' => 'Cookie usage on the Shop CMS demo site.',
                    'no' => 'Bruk av informasjonskapsler på Shop CMS-demo.',
                    'uk' => 'Використання cookies на демо Shop CMS.',
                    'ru' => 'Использование cookies на демо Shop CMS.',
                    'sv' => 'Cookie-användning på Shop CMS-demo.',
                ]),
            ],
        ],
        'footer_links' => sh_footer_links_defaults(),
    ];
}

function sh_footer_links_defaults(): array
{
    $langs = array_keys(sh_langs());
    $label = static function (array $map) use ($langs): array {
        $out = [];
        foreach ($langs as $code) {
            $out[$code] = $map[$code] ?? $map['en'] ?? '';
        }
        return $out;
    };

    return [
        'shop' => [
            ['id' => 'solutions', 'url' => 'solutions.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Store solutions', 'no' => 'Butikkløsninger', 'uk' => 'Рішення для магазину', 'ru' => 'Решения для магазина', 'sv' => 'Butikslösningar'])],
            ['id' => 'products', 'url' => 'search.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'All products', 'no' => 'Alle produkter', 'uk' => 'Усі товари', 'ru' => 'Все товары', 'sv' => 'Alla produkter'])],
            ['id' => 'sale', 'url' => 'search.php?sale=1', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Sale', 'no' => 'Salg', 'uk' => 'Розпродаж', 'ru' => 'Распродажа', 'sv' => 'Rea'])],
            ['id' => 'cart', 'url' => 'cart.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Cart', 'no' => 'Handlekurv', 'uk' => 'Кошик', 'ru' => 'Корзина', 'sv' => 'Varukorg'])],
            ['id' => 'contact', 'url' => 'contact.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Contact', 'no' => 'Kontakt', 'uk' => 'Контакт', 'ru' => 'Контакты', 'sv' => 'Kontakt'])],
        ],
        'legal' => [
            ['id' => 'home', 'url' => 'index.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Home', 'no' => 'Hjem', 'uk' => 'Головна', 'ru' => 'Главная', 'sv' => 'Hem'])],
            ['id' => 'delivery', 'url' => 'page.php?slug=delivery', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Delivery', 'no' => 'Levering', 'uk' => 'Доставка', 'ru' => 'Доставка', 'sv' => 'Leverans'])],
            ['id' => 'payment', 'url' => 'page.php?slug=payment', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Payment', 'no' => 'Betaling', 'uk' => 'Оплата', 'ru' => 'Оплата', 'sv' => 'Betalning'])],
            ['id' => 'privacy', 'url' => 'page.php?slug=privacy', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Privacy', 'no' => 'Personvern', 'uk' => 'Конфіденційність', 'ru' => 'Конфиденциальность', 'sv' => 'Integritet'])],
            ['id' => 'cookies', 'url' => 'page.php?slug=cookies', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Cookies', 'no' => 'Cookies', 'uk' => 'Cookies', 'ru' => 'Cookies', 'sv' => 'Cookies'])],
            ['id' => 'sitemap', 'url' => 'sitemap.xml', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Sitemap', 'no' => 'Sitemap', 'uk' => 'Карта сайту', 'ru' => 'Карта сайта', 'sv' => 'Webbplatskarta'])],
            ['id' => 'admin', 'url' => 'admin/login.php', 'external' => false, 'active' => true,
             'label' => $label(['en' => 'Admin demo', 'no' => 'Admin demo', 'uk' => 'Адмін-демо', 'ru' => 'Демо админки', 'sv' => 'Admin demo'])],
        ],
    ];
}

function sh_merge_service_settings(array $settings): array
{
    $defaults = sh_service_pages_defaults();
    $merged = array_merge($defaults, $settings);

    if (!is_array($merged['service_pages'] ?? null)) {
        $merged['service_pages'] = $defaults['service_pages'];
    } else {
        foreach ($defaults['service_pages'] as $slug => $pageDefault) {
            $merged['service_pages'][$slug] = array_merge(
                $pageDefault,
                is_array($merged['service_pages'][$slug] ?? null) ? $merged['service_pages'][$slug] : []
            );
        }
        foreach ($merged['service_pages'] as $slug => $page) {
            if (!is_array($page) || sh_service_page_is_builtin($slug)) {
                continue;
            }
            $merged['service_pages'][$slug] = array_merge([
                'active' => true,
                'icon' => 'file-lines',
                'admin_label' => ucfirst(str_replace(['-', '_'], ' ', $slug)),
                'title' => [],
                'content' => [],
                'meta_title' => [],
                'meta_description' => [],
            ], $page);
        }
    }

    if (!is_array($merged['footer_links'] ?? null)) {
        $merged['footer_links'] = $defaults['footer_links'];
    } else {
        foreach (['shop', 'legal'] as $col) {
            if (!is_array($merged['footer_links'][$col] ?? null)) {
                $merged['footer_links'][$col] = $defaults['footer_links'][$col];
            }
        }
    }

    return $merged;
}

function sh_service_page(string $slug, ?array $settings = null): ?array
{
    $slug = strtolower(trim($slug));
    if (!sh_service_page_slug_valid($slug)) {
        return null;
    }
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $settings = sh_merge_service_settings($settings);
    $page = $settings['service_pages'][$slug] ?? null;
    if (!is_array($page) || ($page['active'] ?? true) === false) {
        return null;
    }
    return $page;
}

function sh_sanitize_service_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    $allowed = '<p><br><strong><b><em><i><u><s><h2><h3><h4><ul><ol><li><a><blockquote><hr><span><div>';
    $clean = strip_tags($html, $allowed);
    $clean = preg_replace('/\s(on\w+|style|class)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
    $clean = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $clean) ?? $clean;
    return $clean;
}

function sh_service_page_content_html(string $content): string
{
    $content = trim($content);
    if ($content === '') {
        return '';
    }
    if (str_contains($content, '<') && str_contains($content, '>')) {
        return sh_sanitize_service_html($content);
    }
    $out = '';
    foreach (preg_split('/\r\n|\r|\n/', $content) as $para) {
        $para = trim($para);
        if ($para === '') {
            continue;
        }
        if (str_starts_with($para, '•') || str_starts_with($para, '-')) {
            $out .= '<p class="sh-service-bullet">' . htmlspecialchars($para) . '</p>';
        } else {
            $out .= '<p>' . nl2br(htmlspecialchars($para)) . '</p>';
        }
    }
    return $out;
}

function sh_service_page_empty_lang_fields(): array
{
    $out = [];
    foreach (sh_lang_codes_for_defaults() as $code) {
        $out[$code] = '';
    }
    return $out;
}

function sh_service_page_create(string $slug, array $settings, string $adminLabel = '', string $icon = 'file-lines'): array
{
    $slug = strtolower(trim($slug));
    if (!sh_service_page_slug_valid($slug)) {
        return $settings;
    }
    $settings = sh_merge_service_settings($settings);
    if (isset($settings['service_pages'][$slug])) {
        return $settings;
    }
    $empty = sh_service_page_empty_lang_fields();
    $settings['service_pages'][$slug] = [
        'active'           => true,
        'icon'             => preg_match('/^[a-z0-9-]+$/', $icon) ? $icon : 'file-lines',
        'admin_label'      => $adminLabel !== '' ? $adminLabel : ucfirst(str_replace(['-', '_'], ' ', $slug)),
        'title'            => $empty,
        'content'          => $empty,
        'meta_title'       => $empty,
        'meta_description' => $empty,
    ];
    return $settings;
}

function sh_service_page_delete(string $slug, array $settings): array
{
    $slug = strtolower(trim($slug));
    if (!sh_service_page_slug_valid($slug) || sh_service_page_is_builtin($slug)) {
        return $settings;
    }
    $settings = sh_merge_service_settings($settings);
    unset($settings['service_pages'][$slug]);
    return $settings;
}

function sh_footer_links(?array $settings = null): array
{
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $settings = sh_merge_service_settings($settings);
    $out = [];
    foreach (['shop', 'legal'] as $col) {
        $out[$col] = [];
        foreach ($settings['footer_links'][$col] ?? [] as $link) {
            if (!is_array($link) || empty($link['active'])) {
                continue;
            }
            $out[$col][] = $link;
        }
    }
    return $out;
}

function sh_footer_link_href(array $link): string
{
    $url = trim($link['url'] ?? '');
    if ($url === '') {
        return sh_url('index.php');
    }
    if (!empty($link['external']) || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    return sh_url(ltrim($url, '/'));
}

function sh_footer_link_label(array $link, string $lang): string
{
    $labels = $link['label'] ?? [];
    if (is_array($labels)) {
        $val = trim($labels[$lang] ?? $labels['en'] ?? $labels['no'] ?? '');
        if ($val !== '') {
            return $val;
        }
    }
    return trim($link['id'] ?? 'Link');
}

function sh_service_pages_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_service_settings($settings);
    $pageSlug = strtolower(trim($post['page_slug'] ?? ''));
    if ($pageSlug !== '' && sh_service_page_slug_valid($pageSlug)) {
        $existing = $settings['service_pages'][$pageSlug] ?? [];
        $title = [];
        $content = [];
        $metaTitle = [];
        $metaDesc = [];
        foreach (sh_langs() as $code => $_info) {
            $title[$code] = trim($post['page_title_' . $code] ?? '');
            $rawContent = (string) ($post['page_content_' . $code] ?? '');
            $content[$code] = str_contains($rawContent, '<') ? sh_sanitize_service_html($rawContent) : trim($rawContent);
            $metaTitle[$code] = trim($post['page_meta_title_' . $code] ?? '');
            $metaDesc[$code] = trim($post['page_meta_description_' . $code] ?? '');
        }
        $page = [
            'active'           => !empty($post['page_active']),
            'title'            => $title,
            'content'          => $content,
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDesc,
        ];
        if (!sh_service_page_is_builtin($pageSlug)) {
            $page['icon'] = trim($post['page_icon'] ?? ($existing['icon'] ?? 'file-lines')) ?: 'file-lines';
            $page['admin_label'] = trim($post['page_admin_label'] ?? ($existing['admin_label'] ?? '')) ?: ucfirst(str_replace(['-', '_'], ' ', $pageSlug));
        }
        $settings['service_pages'][$pageSlug] = array_merge(is_array($existing) ? $existing : [], $page);
    }
    return $settings;
}

function sh_footer_links_apply_post(array $post, array $settings): array
{
    $settings = sh_merge_service_settings($settings);
    foreach (['shop', 'legal'] as $col) {
        $rows = [];
        $indices = $post['footer_' . $col . '_idx'] ?? [];
        if (!is_array($indices)) {
            continue;
        }
        foreach ($indices as $i) {
            $i = (int) $i;
            $labels = [];
            foreach (sh_langs() as $code => $_info) {
                $labels[$code] = trim($post['footer_' . $col . '_label_' . $code . '_' . $i] ?? '');
            }
            $url = trim($post['footer_' . $col . '_url_' . $i] ?? '');
            if ($url === '' && $labels['en'] === '' && $labels['no'] === '') {
                continue;
            }
            $rows[] = [
                'id'       => trim($post['footer_' . $col . '_id_' . $i] ?? '') ?: ('link-' . $i),
                'url'      => $url,
                'external' => !empty($post['footer_' . $col . '_external_' . $i]),
                'active'   => !empty($post['footer_' . $col . '_active_' . $i]),
                'label'    => $labels,
            ];
        }
        if ($rows !== []) {
            $settings['footer_links'][$col] = $rows;
        }
    }
    return $settings;
}