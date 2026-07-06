<?php

function shs_lang_url(string $code, bool $for_hreflang = false): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: shs_url('index.php');
    parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? '', $q);
    if ($code === 'no' && $for_hreflang) {
        unset($q['lang']);
    } else {
        $q['lang'] = $code;
    }
    $qs = http_build_query($q);
    return $path . ($qs !== '' ? '?' . $qs : '');
}

function shs_vertical_url(string $slug, ?string $langCode = null): string
{
    global $lang;
    $lng = $langCode ?? $lang ?? 'no';
    $url = shs_demo_url($slug . '/');
    return $lng !== 'no' ? $url . '?lang=' . urlencode($lng) : $url;
}

function shs_solutions_url(?string $langCode = null): string
{
    global $lang;
    $lng = $langCode ?? $lang ?? 'no';
    $url = shs_demo_url('solutions.php');
    return $lng !== 'no' ? $url . '?lang=' . urlencode($lng) : $url;
}