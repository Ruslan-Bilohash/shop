<?php
/**
 * Google PageSpeed Insights — live API with demo cache fallback.
 */
declare(strict_types=1);

function sh_psi_cache_path(): string
{
    return dirname(__DIR__) . '/data/pagespeed-cache.json';
}

/** @return array<string, mixed> */
function sh_psi_cache_load(): array
{
    $path = sh_psi_cache_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/** @param array<string, mixed> $cache */
function sh_psi_cache_save(array $cache): bool
{
    $dir = dirname(sh_psi_cache_path());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(sh_psi_cache_path(), $json . "\n", LOCK_EX) !== false;
}

function sh_psi_api_key(?array $settings = null): string
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    $settings = is_array($settings) ? $settings : [];
    return trim((string) ($settings['pagespeed_api_key'] ?? ''));
}

function sh_psi_http_get(string $url): string|false
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 50,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER     => ['Accept: application/json', 'User-Agent: ShopCMS-PSI/1.1'],
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $code < 200 || $code >= 300) {
            return false;
        }
        return (string) $raw;
    }
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 50,
            'header'  => "Accept: application/json\r\nUser-Agent: ShopCMS-PSI/1.1\r\n",
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    return ($raw === false || $raw === '') ? false : (string) $raw;
}

/**
 * @param list<array<string, mixed>> $fallback
 * @return list<array{label:string,value:int,note?:string,highlight?:bool}>
 */
function sh_psi_scores_from_lang(array $fallback): array
{
    $out = [];
    foreach ($fallback as $row) {
        if (!is_array($row)) {
            continue;
        }
        $out[] = [
            'label'     => (string) ($row['label'] ?? ''),
            'value'     => min(100, max(0, (int) ($row['value'] ?? 0))),
            'note'      => isset($row['note']) ? (string) $row['note'] : '',
            'highlight' => !empty($row['highlight']),
        ];
    }
    return $out;
}

/**
 * @return array{ok:bool,demo:bool,url:string,strategy:string,fetched_at:string,scores:list,vitals:list,error:string}
 */
function sh_psi_fetch(string $url, string $strategy = 'mobile', ?array $settings = null): array
{
    $strategy = in_array($strategy, ['mobile', 'desktop'], true) ? $strategy : 'mobile';
    $url = trim($url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        return ['ok' => false, 'demo' => false, 'cached' => false, 'needs_key' => false, 'url' => $url, 'strategy' => $strategy, 'fetched_at' => '', 'scores' => [], 'vitals' => [], 'error' => 'Invalid URL'];
    }

    $key = sh_psi_api_key($settings);
    if ($key === '') {
        return [
            'ok'         => false,
            'demo'       => false,
            'cached'     => false,
            'needs_key'  => true,
            'url'        => $url,
            'strategy'   => $strategy,
            'fetched_at' => '',
            'scores'     => [],
            'vitals'     => [],
            'error'      => 'Add Google PageSpeed API key in Site health console (free quota at Google Cloud Console).',
        ];
    }

    $apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?'
        . http_build_query([
            'url'      => $url,
            'strategy' => $strategy,
            'category' => ['performance', 'accessibility', 'best-practices', 'seo'],
            'key'      => $key,
        ]);

    $raw = sh_psi_http_get($apiUrl);
    if ($raw === false) {
        return ['ok' => false, 'demo' => false, 'cached' => false, 'needs_key' => false, 'url' => $url, 'strategy' => $strategy, 'fetched_at' => '', 'scores' => [], 'vitals' => [], 'error' => 'PSI API unreachable'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'demo' => false, 'cached' => false, 'needs_key' => false, 'url' => $url, 'strategy' => $strategy, 'fetched_at' => '', 'scores' => [], 'vitals' => [], 'error' => 'Invalid PSI response'];
    }
    if (!empty($data['error']['message'])) {
        return ['ok' => false, 'demo' => false, 'cached' => false, 'needs_key' => false, 'url' => $url, 'strategy' => $strategy, 'fetched_at' => '', 'scores' => [], 'vitals' => [], 'error' => (string) $data['error']['message']];
    }

    $cats = $data['lighthouseResult']['categories'] ?? [];
    $labelMap = [
        'performance'    => 'Performance',
        'accessibility'  => 'Accessibility',
        'best-practices' => 'Best practices',
        'seo'            => 'SEO',
    ];
    $scores = [];
    foreach ($labelMap as $key => $label) {
        $score = $cats[$key]['score'] ?? null;
        if ($score === null) {
            continue;
        }
        $val = (int) round((float) $score * 100);
        $scores[] = [
            'label'     => $label,
            'value'     => $val,
            'highlight' => $key === 'seo',
            'note'      => $key === 'seo' ? 'Lighthouse SEO' : '',
        ];
    }

    $audits = $data['lighthouseResult']['audits'] ?? [];
    $vitals = [];
    $vitalMap = [
        'first-contentful-paint' => 'FCP',
        'largest-contentful-paint' => 'LCP',
        'total-blocking-time' => 'TBT',
        'cumulative-layout-shift' => 'CLS',
        'speed-index' => 'SI',
    ];
    foreach ($vitalMap as $auditKey => $short) {
        $audit = $audits[$auditKey] ?? null;
        if (!is_array($audit)) {
            continue;
        }
        $display = (string) ($audit['displayValue'] ?? '');
        if ($display === '') {
            continue;
        }
        $vitals[] = ['label' => $short, 'boost' => $display];
    }

    $fetchedAt = gmdate('c');
    $result = [
        'ok'         => $scores !== [],
        'demo'       => false,
        'cached'     => false,
        'needs_key'  => false,
        'url'        => $url,
        'strategy'   => $strategy,
        'fetched_at' => $fetchedAt,
        'scores'     => $scores,
        'vitals'     => $vitals,
        'error'      => '',
    ];

    if ($result['ok']) {
        $cache = sh_psi_cache_load();
        $cache[$strategy] = $result;
        $cache['url'] = $url;
        sh_psi_cache_save($cache);
    }

    return $result;
}

/**
 * @param list<array<string, mixed>> $langFallback
 * @return array{ok:bool,demo:bool,url:string,strategy:string,fetched_at:string,scores:list,vitals:list,error:string}
 */
function sh_psi_get(string $url, string $strategy = 'mobile', bool $refresh = false, array $langFallback = [], ?array $settings = null): array
{
    $strategy = in_array($strategy, ['mobile', 'desktop'], true) ? $strategy : 'mobile';
    if (!$refresh) {
        $cache = sh_psi_cache_load();
        $cached = $cache[$strategy] ?? null;
        if (is_array($cached) && !empty($cached['scores']) && ($cached['url'] ?? '') === $url) {
            $cached['demo'] = false;
            $cached['cached'] = true;
            $cached['needs_key'] = false;
            $cached['ok'] = true;
            $cached['error'] = '';
            return $cached;
        }
    }

    $live = sh_psi_fetch($url, $strategy, $settings);
    if ($live['ok']) {
        return $live;
    }

    if (!empty($live['needs_key']) || sh_psi_api_key($settings) === '') {
        return $live;
    }

    $fallbackScores = sh_psi_scores_from_lang($langFallback);
    if ($fallbackScores !== [] && $langFallback !== []) {
        return [
            'ok'         => true,
            'demo'       => true,
            'cached'     => false,
            'needs_key'  => false,
            'url'        => $url,
            'strategy'   => $strategy,
            'fetched_at' => gmdate('c'),
            'scores'     => $fallbackScores,
            'vitals'     => [],
            'error'      => $live['error'],
        ];
    }

    return $live;
}

function sh_psi_default_url(): string
{
    if (function_exists('sh_absolute_url') && function_exists('sh_url')) {
        return sh_absolute_url(sh_url('index.php'));
    }
    if (function_exists('shs_absolute_url')) {
        return shs_absolute_url('/');
    }
    return 'https://bilohash.com/shop/';
}