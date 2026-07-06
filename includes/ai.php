<?php

/** @return array<string, array{label:string,api_base:string,models:list<string>}> */
function sh_ai_providers(): array
{
    return [
        'openai' => [
            'label'    => 'OpenAI',
            'api_base' => 'https://api.openai.com/v1',
            'models'   => ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini'],
        ],
        'grok' => [
            'label'    => 'xAI Grok',
            'api_base' => 'https://api.x.ai/v1',
            'models'   => ['grok-3-mini', 'grok-3', 'grok-2-latest'],
        ],
    ];
}

function sh_ai_defaults(): array
{
    return [
        'ai_enabled'              => false,
        'ai_provider'             => 'grok',
        'ai_api_key'              => '',
        'ai_api_base'             => '',
        'ai_model'                => 'grok-3-mini',
        'ai_prompt_product'       => 'You are an e-commerce copywriter for a Norway/EU online shop. Product: {product_name}. Category: {category}. Source language hint: {source_lang}. Return ONLY valid JSON (no markdown) with keys: names, desc, seo. names and desc are objects with every active language key (no, en, uk, ru, sv, lt). desc: 80-200 chars per language — benefits, specs, use cases. seo has meta_title, meta_description, meta_keywords — each an object with the same language keys. meta_description is also used as Open Graph og:description. Include seo.brand (single string, product brand). Meta title 30-60 chars. meta_description MUST be 120-160 characters (inclusive) in EVERY language — compelling Google snippet with keyword, benefit and call-to-action. Count characters carefully. Professional, SEO-friendly, demo-safe tone.',
        'ai_source_lang'          => 'en',
    ];
}

/** @param array<string, mixed> $ai */
function sh_ai_resolve_config(array $ai): array
{
    $providers = sh_ai_providers();
    $provider = (string) ($ai['ai_provider'] ?? $ai['provider'] ?? 'grok');
    if (!isset($providers[$provider])) {
        $provider = 'grok';
    }
    $preset = $providers[$provider];
    $apiBase = rtrim(trim((string) ($ai['ai_api_base'] ?? $ai['api_base'] ?? '')), '/');
    if ($apiBase === '') {
        $apiBase = rtrim($preset['api_base'], '/');
    }
    $model = trim((string) ($ai['ai_model'] ?? $ai['model'] ?? ''));
    if ($model === '') {
        $model = $preset['models'][0] ?? 'grok-3-mini';
    }
    return ['provider' => $provider, 'api_base' => $apiBase, 'model' => $model];
}

/** @return array<string, string> */
function sh_ai_lang_names(): array
{
    require_once __DIR__ . '/lang-registry.php';
    $out = [];
    foreach (sh_world_languages() as $code => $info) {
        $out[$code] = $info['name'] ?? $code;
    }
    foreach (sh_langs() as $code => $info) {
        $out[$code] = $info['name'] ?? $out[$code] ?? $code;
    }
    return $out;
}

function sh_ai_settings(?array $settings = null): array
{
    if ($settings === null) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    return array_merge(sh_ai_defaults(), array_intersect_key($settings, sh_ai_defaults()));
}

function sh_ai_enabled(?array $settings = null): bool
{
    $ai = sh_ai_settings($settings);
    return !empty($ai['ai_enabled']) && trim((string) ($ai['ai_api_key'] ?? '')) !== '';
}

/**
 * @return array{ok:bool,demo:bool,data:array,error:string}
 */
function sh_ai_generate_product(array $settings, string $productName, string $categorySlug = '', string $sourceLang = 'en', string $briefDescription = ''): array
{
    $productName = trim($productName);
    if ($productName === '') {
        return ['ok' => false, 'demo' => false, 'data' => [], 'error' => 'Product name required'];
    }

    $briefDescription = trim($briefDescription);

    $ai = sh_ai_settings($settings);
    $categoryLabel = $categorySlug;
    if ($categorySlug !== '') {
        require_once __DIR__ . '/category-storage.php';
        $cat = sh_category_by_slug($categorySlug, false);
        if ($cat) {
            $categoryLabel = sh_localized($cat, 'name', $sourceLang) ?: $categorySlug;
        }
    }

    if (!sh_ai_enabled($settings)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_product_fallback($productName, $categoryLabel, $sourceLang, $briefDescription),
            'error' => '',
        ];
    }

    $langList = implode(', ', array_keys(sh_langs()));
    $briefBlock = $briefDescription !== ''
        ? ' Merchant brief (expand into full copy): "' . mb_substr($briefDescription, 0, 800) . '".'
        : '';

    $prompt = str_replace(
        ['{product_name}', '{category}', '{source_lang}'],
        [$productName, $categoryLabel, sh_ai_lang_names()[$sourceLang] ?? $sourceLang],
        (string) ($ai['ai_prompt_product'] ?? sh_ai_defaults()['ai_prompt_product'])
    );
    $prompt .= $briefBlock . ' Languages required in JSON: ' . $langList . '.';

    $result = sh_ai_call_chat($ai, $prompt, 1800);
    if (!$result['ok']) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_product_fallback($productName, $categoryLabel, $sourceLang, $briefDescription),
            'error' => $result['error'],
        ];
    }

    $parsed = sh_ai_parse_product_json($result['text']);
    if ($parsed === null) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_product_fallback($productName, $categoryLabel, $sourceLang, $briefDescription),
            'error' => 'Invalid JSON from AI',
        ];
    }

    if (!empty($parsed['seo']['brand'])) {
        $parsed['brand'] = trim((string) $parsed['seo']['brand']);
    }

    return ['ok' => true, 'demo' => false, 'data' => $parsed, 'error' => ''];
}

/**
 * @return array{ok:bool,demo:bool,data:array,error:string}
 */
function sh_ai_generate_site_seo(array $settings, string $brandName, string $countryCode = 'NO'): array
{
    $brandName = trim($brandName);
    if ($brandName === '') {
        return ['ok' => false, 'demo' => false, 'data' => [], 'error' => 'Brand name required'];
    }

    $countryCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $countryCode), 0, 2)) ?: 'NO';
    $geoNames = [
        'NO' => ['region' => 'NO', 'place' => 'Norway'],
        'UA' => ['region' => 'UA', 'place' => 'Ukraine'],
        'SE' => ['region' => 'SE', 'place' => 'Sweden'],
        'GB' => ['region' => 'GB', 'place' => 'United Kingdom'],
        'DE' => ['region' => 'DE', 'place' => 'Germany'],
    ];
    $geo = $geoNames[$countryCode] ?? ['region' => $countryCode, 'place' => $countryCode];

    if (!sh_ai_enabled($settings)) {
        $handle = strtolower(preg_replace('/[^a-z0-9]/', '', $brandName));
        $handle = substr($handle, 0, 15) ?: 'shop';
        return [
            'ok'   => true,
            'demo' => true,
            'data' => [
                'seo_site_name'             => $brandName,
                'seo_org_name'              => $brandName,
                'seo_geo_region'            => $geo['region'],
                'seo_geo_placename'         => $geo['place'],
                'seo_default_country_code'  => $countryCode,
                'seo_twitter_site'          => '@' . $handle,
            ],
            'error' => '',
        ];
    }

    $prompt = 'You are an SEO specialist for an e-commerce store in Europe. Brand/shop name: "' . $brandName . '". '
        . 'Target country ISO code: ' . $countryCode . '. Return ONLY valid JSON with keys: '
        . 'seo_site_name, seo_org_name, seo_geo_region (2-8 chars), seo_geo_placename, '
        . 'seo_default_country_code (2 letters), seo_twitter_site (optional @handle). '
        . 'Professional, concise, suitable for Schema.org Organization and Open Graph.';

    $ai = sh_ai_settings($settings);
    $result = sh_ai_call_chat($ai, $prompt, 800);
    if (!$result['ok']) {
        return sh_ai_generate_site_seo(array_merge($settings, ['ai_enabled' => false]), $brandName, $countryCode);
    }

    $raw = $result['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return sh_ai_generate_site_seo(array_merge($settings, ['ai_enabled' => false]), $brandName, $countryCode);
    }

    return [
        'ok'   => true,
        'demo' => false,
        'data' => [
            'seo_site_name'            => trim((string) ($data['seo_site_name'] ?? $brandName)),
            'seo_org_name'             => trim((string) ($data['seo_org_name'] ?? $brandName)),
            'seo_geo_region'           => strtoupper(substr(trim((string) ($data['seo_geo_region'] ?? $geo['region'])), 0, 8)),
            'seo_geo_placename'        => trim((string) ($data['seo_geo_placename'] ?? $geo['place'])),
            'seo_default_country_code' => strtoupper(substr(trim((string) ($data['seo_default_country_code'] ?? $countryCode)), 0, 2)),
            'seo_twitter_site'         => trim((string) ($data['seo_twitter_site'] ?? '')),
        ],
        'error' => '',
    ];
}

/** @return array{ok:bool,text:string,error:string} */
function sh_ai_call_chat(array $ai, string $prompt, int $maxTokens = 1200): array
{
    $resolved = sh_ai_resolve_config($ai);
    $apiKey = trim((string) ($ai['ai_api_key'] ?? ''));
    if ($apiKey === '') {
        return ['ok' => false, 'text' => '', 'error' => 'No API key'];
    }

    $endpoint = $resolved['api_base'] . '/chat/completions';
    $payload = [
        'model'       => $resolved['model'],
        'messages'    => [
            ['role' => 'system', 'content' => 'Reply with valid JSON only. No markdown fences, no commentary.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.55,
        'max_tokens'  => $maxTokens,
    ];

    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        return ['ok' => false, 'text' => '', 'error' => 'JSON encode failed'];
    }

    $raw = false;
    $http = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
        ]);
        $raw = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['ok' => false, 'text' => '', 'error' => 'API request failed'];
        }
        curl_close($ch);
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
                'content' => $body,
                'timeout' => 45,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($endpoint, false, $ctx);
    }

    if ($raw === false) {
        return ['ok' => false, 'text' => '', 'error' => 'API request failed'];
    }
    if ($http >= 400 && $http > 0) {
        return ['ok' => false, 'text' => '', 'error' => 'API HTTP ' . $http];
    }

    $data = json_decode($raw ?: '', true);
    $text = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    if ($text === '') {
        return ['ok' => false, 'text' => '', 'error' => 'Empty AI response'];
    }

    return ['ok' => true, 'text' => $text, 'error' => ''];
}

/** @return list<string> */
function sh_ai_meta_desc_padding(string $lang): array
{
    $map = [
        'en' => [' Free delivery available.', ' Order securely online today.', ' Trusted quality — buy now.'],
        'no' => [' Gratis frakt tilgjengelig.', ' Bestill trygt på nett i dag.', ' Kvalitet du kan stole på.'],
        'uk' => [' Безкоштовна доставка.', ' Замовляйте онлайн сьогодні.', ' Якість та надійність.'],
        'ru' => [' Бесплатная доставка.', ' Закажите онлайн сегодня.', ' Качество и надёжность.'],
        'sv' => [' Fri frakt tillgänglig.', ' Beställ säkert online idag.', ' Kvalitet du kan lita på.'],
        'lt' => [' Nemokamas pristatymas.', ' Užsisakykite internetu šiandien.', ' Patikima kokybė.'],
    ];
    return $map[$lang] ?? $map['en'];
}

function sh_ai_fit_meta_description(string $text, string $lang = 'en', int $min = 120, int $max = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) > $max) {
        $text = mb_substr($text, 0, $max);
        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false && $lastSpace > (int) ($max * 0.65)) {
            $text = mb_substr($text, 0, $lastSpace);
        }
        $text = rtrim($text, ".,;:!?—-–");
    }
    foreach (sh_ai_meta_desc_padding($lang) as $pad) {
        if (mb_strlen($text) >= $min) {
            break;
        }
        if (mb_strlen($text . $pad) <= $max) {
            $text .= $pad;
        }
    }
    if (mb_strlen($text) > $max) {
        $text = mb_substr($text, 0, $max);
        $text = rtrim($text, ".,;:!?—-–");
    }
    return $text;
}

/** @return ?array */
function sh_ai_parse_product_json(string $raw): ?array
{
    $raw = trim($raw);
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return null;
    }

    $langs = array_keys(sh_langs());
    $out = ['names' => [], 'desc' => [], 'seo' => ['meta_title' => [], 'meta_description' => [], 'meta_keywords' => []]];

    foreach ($langs as $code) {
        $out['names'][$code] = trim((string) ($data['names'][$code] ?? ''));
        $out['desc'][$code] = trim((string) ($data['desc'][$code] ?? ''));
        $seo = is_array($data['seo'] ?? null) ? $data['seo'] : [];
        $out['seo']['meta_title'][$code] = trim((string) ($seo['meta_title'][$code] ?? ''));
        $rawDesc = trim((string) ($seo['meta_description'][$code] ?? ''));
        $out['seo']['meta_description'][$code] = $rawDesc !== '' ? sh_ai_fit_meta_description($rawDesc, $code) : '';
        $out['seo']['meta_keywords'][$code] = trim((string) ($seo['meta_keywords'][$code] ?? ''));
    }

    if ($out['names']['en'] === '' && $out['names']['no'] === '') {
        return null;
    }

    if (!empty($data['seo']['brand'])) {
        $out['brand'] = trim((string) $data['seo']['brand']);
    }

    return $out;
}

/** @return array */
function sh_ai_product_fallback(string $productName, string $category, string $sourceLang, string $briefDescription = ''): array
{
    $langs = array_keys(sh_langs());
    $names = [];
    $desc = [];
    $metaTitle = [];
    $metaDesc = [];
    $metaKw = [];
    $briefSuffix = $briefDescription !== '' ? ' ' . $briefDescription : '';

    $templates = [
        'en' => [
            'desc' => '{name} — quality product for everyday use.{brief} Category: {category}. Fast delivery across Norway & EU.',
            'meta_desc' => 'Buy {name} online — premium {category} with fast delivery across Norway & EU.{brief} Secure checkout, Schema.org SEO, multilingual storefront. Order today.',
        ],
        'no' => [
            'desc' => '{name} — kvalitetsprodukt for daglig bruk.{brief} Kategori: {category}. Rask levering i Norge og EU.',
            'meta_desc' => 'Kjøp {name} på nett — {category} med rask levering i Norge og EU.{brief} Trygg kasse, Schema.org SEO og flerspråklig butikk. Bestill i dag.',
        ],
        'uk' => [
            'desc' => '{name} — якісний товар для щоденного використання.{brief} Категорія: {category}. Швидка доставка по Норвегії та ЄС.',
            'meta_desc' => 'Купити {name} онлайн — {category} з швидкою доставкою по Норвегії та ЄС.{brief} Безпечне оформлення, Schema.org SEO, багатомовна вітрина. Замовте сьогодні.',
        ],
        'ru' => [
            'desc' => '{name} — качественный товар для повседневного использования.{brief} Категория: {category}. Быстрая доставка по Норвегии и ЕС.',
            'meta_desc' => 'Купить {name} онлайн — {category} с быстрой доставкой по Норвегии и ЕС.{brief} Безопасный checkout, Schema.org SEO, мультиязычная витрина. Закажите сегодня.',
        ],
        'sv' => [
            'desc' => '{name} — kvalitetsprodukt för vardagsbruk.{brief} Kategori: {category}. Snabb leverans i Norge och EU.',
            'meta_desc' => 'Köp {name} online — {category} med snabb leverans i Norge och EU.{brief} Säker kassa, Schema.org SEO och flerspråkig butik. Beställ idag.',
        ],
        'lt' => [
            'desc' => '{name} — kokybiškas produktas kasdieniam naudojimui.{brief} Kategorija: {category}. Greitas pristatymas Norvegijoje ir ES.',
            'meta_desc' => 'Pirkite {name} internetu — {category} su greitu pristatymu Norvegijoje ir ES.{brief} Saugus atsiskaitymas, Schema.org SEO. Užsisakykite šiandien.',
        ],
    ];

    $briefText = $briefSuffix;
    foreach ($langs as $code) {
        $tpl = $templates[$code] ?? $templates['en'];
        $names[$code] = $productName;
        $repl = ['{name}' => $productName, '{category}' => $category ?: 'shop', '{brief}' => $briefText];
        $desc[$code] = strtr($tpl['desc'], $repl);
        if (mb_strlen($desc[$code]) < 80 && $briefDescription !== '') {
            $desc[$code] .= ' ' . $briefDescription;
        }
        $metaTitle[$code] = mb_substr($productName . ' | ' . ($category ?: 'Shop'), 0, 60);
        $metaDesc[$code] = sh_ai_fit_meta_description(strtr($tpl['meta_desc'], $repl), $code);
        $metaKw[$code] = strtolower(str_replace(' ', ', ', $productName)) . ', ' . strtolower($category ?: 'ecommerce');
    }

    if ($sourceLang !== 'en' && isset($names[$sourceLang])) {
        $names[$sourceLang] = $productName;
    }

    return [
        'names' => $names,
        'desc'  => $desc,
        'seo'   => [
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDesc,
            'meta_keywords'    => $metaKw,
            'brand'            => $productName,
        ],
        'brand' => $productName,
    ];
}

/**
 * @return array{ok:bool,demo:bool,data:array,error:string}
 */
function sh_ai_generate_category(array $settings, string $categoryName, string $slug = '', string $sourceLang = 'en'): array
{
    $categoryName = trim($categoryName);
    if ($categoryName === '') {
        return ['ok' => false, 'demo' => false, 'data' => [], 'error' => 'Category name required'];
    }

    $langs = array_keys(sh_langs());
    $langList = implode(', ', $langs);
    $ai = sh_ai_settings($settings);

    if (!sh_ai_enabled($settings)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_category_fallback($categoryName, $slug, $sourceLang),
            'error' => '',
        ];
    }

    $prompt = 'You are an e-commerce SEO specialist for Norway/EU online shops. '
        . 'Category: "' . $categoryName . '"' . ($slug !== '' ? ' (slug: ' . $slug . ')' : '') . '. '
        . 'Source language: ' . (sh_ai_lang_names()[$sourceLang] ?? $sourceLang) . '. '
        . 'Return ONLY valid JSON (no markdown) with key "seo" containing: '
        . 'meta_title, meta_description, meta_keywords, intro — each an object with keys: ' . $langList . '. '
        . 'meta_title max 60 chars, meta_description max 155 chars, intro 2-3 sentences for category landing page. '
        . 'Keywords comma-separated. Professional, SEO-friendly tone.';

    $result = sh_ai_call_chat($ai, $prompt, 2000);
    if (!$result['ok']) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_category_fallback($categoryName, $slug, $sourceLang),
            'error' => $result['error'],
        ];
    }

    $parsed = sh_ai_parse_category_seo_json($result['text']);
    if ($parsed === null) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_category_fallback($categoryName, $slug, $sourceLang),
            'error' => 'Invalid JSON from AI',
        ];
    }

    return ['ok' => true, 'demo' => false, 'data' => $parsed, 'error' => ''];
}

/** @return ?array */
function sh_ai_parse_category_seo_json(string $raw): ?array
{
    $raw = trim($raw);
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return null;
    }
    $seo = is_array($data['seo'] ?? null) ? $data['seo'] : $data;
    $langs = array_keys(sh_langs());
    $out = ['seo' => ['meta_title' => [], 'meta_description' => [], 'meta_keywords' => [], 'intro' => []]];
    $has = false;
    foreach ($langs as $code) {
        $out['seo']['meta_title'][$code] = trim((string) ($seo['meta_title'][$code] ?? ''));
        $out['seo']['meta_description'][$code] = trim((string) ($seo['meta_description'][$code] ?? ''));
        $out['seo']['meta_keywords'][$code] = trim((string) ($seo['meta_keywords'][$code] ?? ''));
        $out['seo']['intro'][$code] = trim((string) ($seo['intro'][$code] ?? ''));
        if ($out['seo']['meta_title'][$code] !== '' || $out['seo']['meta_description'][$code] !== '') {
            $has = true;
        }
    }
    return $has ? $out : null;
}

/** @return array */
function sh_ai_category_fallback(string $categoryName, string $slug, string $sourceLang): array
{
    $langs = array_keys(sh_langs());
    $metaTitle = [];
    $metaDesc = [];
    $metaKw = [];
    $intro = [];
    $slugKw = $slug !== '' ? str_replace('-', ', ', $slug) : 'shop, catalog';

    $templates = [
        'en' => [
            'meta_desc' => 'Shop {name} online — curated products, fast delivery in Norway & EU. Demo Shop CMS category.',
            'intro'     => 'Browse our {name} selection. Quality products with multilingual SEO and session cart demo.',
        ],
        'no' => [
            'meta_desc' => 'Kjøp {name} på nett — utvalgte produkter, rask levering i Norge og EU. Demo Shop CMS.',
            'intro'     => 'Utforsk {name}-kategorien vår. Kvalitetsprodukter med flerspråklig SEO.',
        ],
        'uk' => [
            'meta_desc' => 'Купуйте {name} онлайн — добірка товарів, доставка по Норвегії та ЄС. Демо Shop CMS.',
            'intro'     => 'Перегляньте категорію {name}. Якісні товари з багатомовним SEO.',
        ],
        'ru' => [
            'meta_desc' => 'Купите {name} онлайн — подборка товаров, доставка по Норвегии и ЕС. Демо Shop CMS.',
            'intro'     => 'Смотрите категорию {name}. Качественные товары с многоязычным SEO.',
        ],
        'sv' => [
            'meta_desc' => 'Handla {name} online — utvalda produkter, snabb leverans i Norge och EU.',
            'intro'     => 'Utforska vår {name}-kategori. Kvalitetsprodukter med flerspråkig SEO.',
        ],
    ];

    foreach ($langs as $code) {
        $tpl = $templates[$code] ?? $templates['en'];
        $metaTitle[$code] = $categoryName . ' — Shop CMS';
        $metaDesc[$code] = str_replace('{name}', $categoryName, $tpl['meta_desc']);
        $intro[$code] = str_replace('{name}', $categoryName, $tpl['intro']);
        $metaKw[$code] = strtolower($categoryName) . ', ' . $slugKw;
    }

    if ($sourceLang !== 'en' && isset($metaTitle[$sourceLang])) {
        $metaTitle[$sourceLang] = $categoryName . ' — Shop CMS';
    }

    return ['seo' => [
        'meta_title'       => $metaTitle,
        'meta_description' => $metaDesc,
        'meta_keywords'    => $metaKw,
        'intro'            => $intro,
    ]];
}

/**
 * @return array{ok:bool,data:array,error:string}
 */
function sh_ai_translate_lang_file(array $source, string $sourceLang, string $targetLang, array $ai): array
{
    $langNames = sh_ai_lang_names();
    $srcName = $langNames[$sourceLang] ?? $sourceLang;
    $tgtName = $langNames[$targetLang] ?? $targetLang;
    $sample = json_encode(array_slice($source, 0, 3), JSON_UNESCAPED_UNICODE);
    $prompt = "Translate this PHP language array from {$srcName} ({$sourceLang}) to {$tgtName} ({$targetLang}). "
        . "Return ONLY valid JSON with the same structure and keys. Keep placeholders like %s, %d, URLs unchanged. "
        . "Sample structure start: " . mb_substr($sample, 0, 1200);

    $fullPrompt = "System: You are a professional UI translator for e-commerce. Output JSON only.\n\n"
        . $prompt . "\n\nFull source:\n" . json_encode($source, JSON_UNESCAPED_UNICODE);
    $resp = sh_ai_call_chat($ai, $fullPrompt, 8000);
    if (!$resp['ok']) {
        return ['ok' => false, 'data' => [], 'error' => $resp['error']];
    }
    $raw = $resp['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'data' => [], 'error' => 'Invalid translation JSON'];
    }
    return ['ok' => true, 'data' => array_replace_recursive($source, $data), 'error' => ''];
}

function sh_ai_homepage_fallback(string $sourceLang = 'en'): array
{
    $titles = [
        'featured'   => ['en' => 'Featured products', 'uk' => 'Рекомендовані товари', 'no' => 'Utvalgte produkter', 'ru' => 'Рекомендуемые товары'],
        'new'        => ['en' => 'New arrivals', 'uk' => 'Новинки', 'no' => 'Nyheter', 'ru' => 'Новинки'],
        'categories' => ['en' => 'Shop by category', 'uk' => 'Категорії', 'no' => 'Kategorier', 'ru' => 'Категории'],
        'about'      => ['en' => 'About Shop CMS', 'uk' => 'Про Shop CMS', 'no' => 'Om Shop CMS', 'ru' => 'О Shop CMS'],
        'steps'      => ['en' => 'How it works', 'uk' => 'Як це працює', 'no' => 'Slik fungerer det', 'ru' => 'Как это работает'],
        'why'        => ['en' => 'Why Shop CMS', 'uk' => 'Чому Shop CMS', 'no' => 'Hvorfor Shop CMS', 'ru' => 'Почему Shop CMS'],
        'faq'        => ['en' => 'FAQ', 'uk' => 'Питання та відповіді', 'no' => 'FAQ', 'ru' => 'Вопросы и ответы'],
    ];
    $subs = [
        'featured' => ['en' => 'Hand-picked demo products for your storefront.', 'uk' => 'Демо-товари для вітрини.', 'no' => 'Utvalgte demoprodukter.', 'ru' => 'Демо-товары для витрины.'],
        'new'      => ['en' => 'Latest items in the catalog.', 'uk' => 'Останні надходження в каталозі.', 'no' => 'Siste i katalogen.', 'ru' => 'Последние поступления в каталоге.'],
        'about'    => ['en' => 'PHP e-commerce demo for Norway and Europe.', 'uk' => 'PHP e-commerce демо для Норвегії та Європи.', 'no' => 'PHP e-handel demo for Norge og Europa.', 'ru' => 'PHP e-commerce демо для Норвегии и Европы.'],
        'steps'    => ['en' => 'From catalog to checkout in a few steps.', 'uk' => 'Від каталогу до оформлення за кілька кроків.', 'no' => 'Fra katalog til checkout.', 'ru' => 'От каталога до оформления за несколько шагов.'],
    ];
    $out = [];
    foreach (sh_home_blocks_defaults() as $block) {
        $type = $block['type'];
        $row = ['id' => $block['id'], 'type' => $type, 'title' => [], 'subtitle' => []];
        foreach (sh_langs() as $code => $_info) {
            $row['title'][$code] = $titles[$type][$code] ?? $titles[$type]['en'] ?? $titles[$type][$sourceLang] ?? '';
            $row['subtitle'][$code] = $subs[$type][$code] ?? $subs[$type]['en'] ?? $subs[$type][$sourceLang] ?? '';
        }
        $out[] = $row;
    }
    return $out;
}

function sh_ai_homepage_custom_fallback(string $sourceLang = 'en'): array
{
    $titles = [
        'en' => 'Why customers choose us',
        'uk' => 'Чому обирають нас',
        'no' => 'Hvorfor kunder velger oss',
        'ru' => 'Почему выбирают нас',
    ];
    $subs = [
        'en' => 'A flexible block you can edit as plain text and HTML.',
        'uk' => 'Гнучкий блок — редагуйте як текст і HTML.',
        'no' => 'En fleksibel blokk du kan redigere som tekst og HTML.',
        'ru' => 'Гибкий блок — редактируйте как текст и HTML.',
    ];
    $bodies = [
        'en' => '<div class="sh-why-grid"><div class="sh-why-item"><i class="fas fa-truck-fast"></i><h4>Fast delivery</h4><p>Demo copy for shipping and logistics.</p></div><div class="sh-why-item"><i class="fas fa-headset"></i><h4>Support</h4><p>Friendly customer service in your language.</p></div><div class="sh-why-item"><i class="fas fa-rotate-left"></i><h4>Easy returns</h4><p>Clear return policy for your storefront.</p></div></div>',
        'uk' => '<div class="sh-why-grid"><div class="sh-why-item"><i class="fas fa-truck-fast"></i><h4>Швидка доставка</h4><p>Демо-текст про логістику.</p></div><div class="sh-why-item"><i class="fas fa-headset"></i><h4>Підтримка</h4><p>Сервіс вашою мовою.</p></div><div class="sh-why-item"><i class="fas fa-rotate-left"></i><h4>Повернення</h4><p>Зрозуміла політика повернень.</p></div></div>',
        'no' => '<div class="sh-why-grid"><div class="sh-why-item"><i class="fas fa-truck-fast"></i><h4>Rask levering</h4><p>Demotekst om frakt.</p></div><div class="sh-why-item"><i class="fas fa-headset"></i><h4>Support</h4><p>Kundeservice på ditt språk.</p></div><div class="sh-why-item"><i class="fas fa-rotate-left"></i><h4>Enkel retur</h4><p>Tydelig returpolicy.</p></div></div>',
        'ru' => '<div class="sh-why-grid"><div class="sh-why-item"><i class="fas fa-truck-fast"></i><h4>Быстрая доставка</h4><p>Демо-текст о логистике.</p></div><div class="sh-why-item"><i class="fas fa-headset"></i><h4>Поддержка</h4><p>Сервис на вашем языке.</p></div><div class="sh-why-item"><i class="fas fa-rotate-left"></i><h4>Возврат</h4><p>Понятная политика возврата.</p></div></div>',
    ];
    $row = ['type' => 'custom', 'title' => [], 'subtitle' => [], 'body' => []];
    foreach (sh_langs() as $code => $_info) {
        $row['title'][$code] = $titles[$code] ?? $titles[$sourceLang] ?? $titles['en'];
        $row['subtitle'][$code] = $subs[$code] ?? $subs[$sourceLang] ?? $subs['en'];
        $row['body'][$code] = $bodies[$code] ?? $bodies[$sourceLang] ?? $bodies['en'];
    }
    return $row;
}

function sh_ai_block_template_fallback(string $prompt, string $sourceLang = 'en'): array
{
    $prompt = trim($prompt) ?: 'Contact form';
    $titles = [
        'en' => 'Get in touch',
        'uk' => 'Звʼязатися з нами',
        'no' => 'Kontakt oss',
        'ru' => 'Связаться с нами',
    ];
    $subs = [
        'en' => 'Demo block generated locally — add your AI API key for custom designs.',
        'uk' => 'Демо-блок без API — додайте ключ AI для унікального дизайну.',
        'no' => 'Demoblokk uten API — legg til AI-nøkkel for egendefinert design.',
        'ru' => 'Демо-блок без API — добавьте ключ AI для своего дизайна.',
    ];
    $bodyEn = '<div class="sh-tpl-card" style="max-width:520px;margin:0 auto;padding:24px;border:1px solid #dbeafe;border-radius:12px;background:linear-gradient(180deg,#eff6ff,#fff);">'
        . '<p style="margin:0 0 16px;color:#1e40af;font-weight:600;"><i class="fas fa-envelope"></i> ' . htmlspecialchars($prompt) . '</p>'
        . '<form class="sh-tpl-form" onsubmit="return false;"><label style="display:block;font-weight:600;margin-bottom:6px;">Name</label>'
        . '<input type="text" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:12px;">'
        . '<label style="display:block;font-weight:600;margin-bottom:6px;">Email</label>'
        . '<input type="email" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:12px;">'
        . '<label style="display:block;font-weight:600;margin-bottom:6px;">Message</label>'
        . '<textarea rows="4" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:14px;"></textarea>'
        . '<button type="button" style="width:100%;padding:12px 16px;border:none;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer;"><i class="fas fa-paper-plane"></i> Send</button></form></div>';
    $row = ['title' => [], 'subtitle' => [], 'body' => []];
    foreach (sh_langs() as $code => $_info) {
        $row['title'][$code] = $titles[$code] ?? $titles[$sourceLang] ?? $titles['en'];
        $row['subtitle'][$code] = $subs[$code] ?? $subs[$sourceLang] ?? $subs['en'];
        $row['body'][$code] = $bodyEn;
    }
    return $row;
}

function sh_ai_generate_block_template(array $settings, string $prompt = ''): array
{
    $prompt = trim($prompt);
    if ($prompt === '') {
        return ['ok' => false, 'demo' => false, 'data' => [], 'error' => 'Prompt required'];
    }

    $ai = sh_ai_settings($settings);
    $sourceLang = (string) ($ai['ai_source_lang'] ?? 'en');

    if (!sh_ai_enabled($settings)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['template' => sh_ai_block_template_fallback($prompt, $sourceLang)],
            'error' => '',
        ];
    }

    $langList = implode(', ', array_keys(sh_langs()));
    $promptEsc = mb_substr($prompt, 0, 1200);
    $aiPrompt = "Design a small storefront HTML block for an e-commerce site (Shop CMS). User request: \"{$promptEsc}\". "
        . "Return JSON: {\"template\":{\"title\":{\"en\":\"...\"},\"subtitle\":{...},\"body\":{\"en\":\"<div class=\\\"sh-tpl-card\\\">...</div>\"}}} "
        . "Languages: {$langList}. Rules: body is safe HTML only (no script/onclick). Use inline styles or classes sh-tpl-*, primary blue #2563eb, Font Awesome <i class=\\\"fas fa-...\\\"></i>. "
        . "Keep compact — contact forms, feature strips, CTAs, icon grids. Title/subtitle are plain text.";

    $resp = sh_ai_call_chat($ai, "System: Output valid JSON only.\n\n" . $aiPrompt, 5000);
    if (!$resp['ok']) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['template' => sh_ai_block_template_fallback($prompt, $sourceLang)],
            'error' => $resp['error'],
        ];
    }
    $raw = $resp['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || !is_array($parsed['template'] ?? null)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['template' => sh_ai_block_template_fallback($prompt, $sourceLang)],
            'error' => 'Invalid AI JSON',
        ];
    }
    return ['ok' => true, 'demo' => false, 'data' => $parsed, 'error' => ''];
}

function sh_ai_generate_homepage_block(array $settings, string $type = 'custom', string $hint = ''): array
{
    require_once __DIR__ . '/homepage-blocks.php';
    $types = sh_home_block_types();
    if (!isset($types[$type])) {
        return ['ok' => false, 'demo' => false, 'data' => [], 'error' => 'Unknown block type'];
    }

    $ai = sh_ai_settings($settings);
    $sourceLang = (string) ($ai['ai_source_lang'] ?? 'en');
    $hint = trim($hint);

    if (!sh_ai_enabled($settings)) {
        $fallback = $type === 'custom' ? sh_ai_homepage_custom_fallback($sourceLang) : [];
        return ['ok' => true, 'demo' => true, 'data' => ['block' => $fallback], 'error' => ''];
    }

    $langList = implode(', ', array_keys(sh_langs()));
    $hintPart = $hint !== '' ? " User hint: {$hint}." : '';
    if ($type === 'custom') {
        $prompt = "Generate ONE custom homepage HTML block for an e-commerce demo shop (Shop CMS).{$hintPart} "
            . "Return JSON: {\"block\":{\"type\":\"custom\",\"title\":{\"en\":\"...\"},\"subtitle\":{...},\"body\":{\"en\":\"<div>...</div>\"}}} "
            . "Languages: {$langList}. body must be safe storefront HTML (no script). Use sh-why-grid / sh-why-item classes if helpful.";
    } else {
        $prompt = "Generate homepage section title and subtitle for type \"{$type}\" on an e-commerce demo shop.{$hintPart} "
            . "Return JSON: {\"block\":{\"type\":\"{$type}\",\"title\":{\"en\":\"...\"},\"subtitle\":{...}}} Languages: {$langList}. Keep text short.";
    }

    $resp = sh_ai_call_chat($ai, "System: Output valid JSON only.\n\n" . $prompt, 3000);
    if (!$resp['ok']) {
        $fallback = $type === 'custom' ? sh_ai_homepage_custom_fallback($sourceLang) : [];
        return ['ok' => true, 'demo' => true, 'data' => ['block' => $fallback], 'error' => $resp['error']];
    }
    $raw = $resp['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || !is_array($parsed['block'] ?? null)) {
        $fallback = $type === 'custom' ? sh_ai_homepage_custom_fallback($sourceLang) : [];
        return ['ok' => true, 'demo' => true, 'data' => ['block' => $fallback], 'error' => 'Invalid AI JSON'];
    }
    $parsed['block']['type'] = $type;
    return ['ok' => true, 'demo' => false, 'data' => $parsed, 'error' => ''];
}

function sh_ai_generate_homepage_blocks(array $settings): array
{
    require_once __DIR__ . '/homepage-blocks.php';
    $ai = sh_ai_settings($settings);
    $sourceLang = (string) ($ai['ai_source_lang'] ?? 'en');

    if (!sh_ai_enabled($settings)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['blocks' => sh_ai_homepage_fallback($sourceLang)],
            'error' => '',
        ];
    }

    $langList = implode(', ', array_keys(sh_langs()));
    $prompt = "Generate homepage section titles and subtitles for an e-commerce demo shop (Shop CMS). "
        . "Return JSON: {\"blocks\":[{\"type\":\"featured\",\"title\":{\"en\":\"...\",\"uk\":\"...\"},\"subtitle\":{...}}, ...]} "
        . "Types: about, featured, categories, new, steps, why, faq. Languages: {$langList}. Keep text short and sales-friendly.";

    $resp = sh_ai_call_chat($ai, "System: Output valid JSON only.\n\n" . $prompt, 4000);
    if (!$resp['ok']) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['blocks' => sh_ai_homepage_fallback($sourceLang)],
            'error' => $resp['error'],
        ];
    }
    $raw = $resp['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
        $raw = trim($m[1]);
    }
    $parsed = json_decode($raw, true);
    if (!is_array($parsed) || !is_array($parsed['blocks'] ?? null)) {
        return [
            'ok'    => true,
            'demo'  => true,
            'data'  => ['blocks' => sh_ai_homepage_fallback($sourceLang)],
            'error' => 'Invalid AI JSON',
        ];
    }
    return ['ok' => true, 'demo' => false, 'data' => $parsed, 'error' => ''];
}

function sh_json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}