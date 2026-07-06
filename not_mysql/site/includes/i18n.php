<?php
require_once __DIR__ . '/../config.php';

define('SHS_LANG_COOKIE', 'shs_lang');

$SHS_LANGS = [
    'no' => ['label' => 'NO', 'name' => 'Norsk', 'flag' => '🇳🇴', 'locale' => 'nb-NO', 'html' => 'no'],
    'en' => ['label' => 'EN', 'name' => 'English', 'flag' => '🇬🇧', 'locale' => 'en-GB', 'html' => 'en'],
    'uk' => ['label' => 'UA', 'name' => 'Українська', 'flag' => '🇺🇦', 'locale' => 'uk-UA', 'html' => 'uk'],
    'ru' => ['label' => 'RU', 'name' => 'Русский', 'flag' => '🇷🇺', 'locale' => 'ru-RU', 'html' => 'ru'],
    'sv' => ['label' => 'SV', 'name' => 'Svenska', 'flag' => '🇸🇪', 'locale' => 'sv-SE', 'html' => 'sv'],
    'lt' => ['label' => 'LT', 'name' => 'Lietuvių', 'flag' => '🇱🇹', 'locale' => 'lt-LT', 'html' => 'lt'],
];

function shs_langs(): array { global $SHS_LANGS; return $SHS_LANGS; }

function shs_detect_lang(): string
{
    global $base_path, $SHS_LANGS;
    $codes = array_keys($SHS_LANGS);

    if (!empty($_GET['lang']) && in_array($_GET['lang'], $codes, true)) {
        $chosen = $_GET['lang'];
        setcookie(SHS_LANG_COOKIE, $chosen, [
            'expires' => time() + 365 * 86400,
            'path'    => rtrim($base_path, '/') . '/' ?: '/',
            'secure'  => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite'=> 'Lax',
        ]);
        if ($chosen === 'no') {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $parts = parse_url($uri);
            parse_str($parts['query'] ?? '', $q);
            unset($q['lang']);
            $clean = ($parts['path'] ?? '/') . ($q ? '?' . http_build_query($q) : '');
            if ($clean !== $uri) { header('Location: ' . $clean, true, 302); exit; }
        }
        return $chosen;
    }
    if (!empty($_COOKIE[SHS_LANG_COOKIE]) && in_array($_COOKIE[SHS_LANG_COOKIE], $codes, true)) {
        return $_COOKIE[SHS_LANG_COOKIE];
    }
    if (!empty($_COOKIE['sh_lang']) && in_array($_COOKIE['sh_lang'], $codes, true)) {
        return $_COOKIE['sh_lang'];
    }
    return 'no';
}

$lang      = shs_detect_lang();
$lang_meta = $SHS_LANGS[$lang] ?? $SHS_LANGS['no'];
$en_file = __DIR__ . '/../lang/en.php';
$en_t = is_file($en_file) ? require $en_file : [];
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
if (!is_file($lang_file)) {
    $lang_file = $en_file;
}
$t_local = require $lang_file;
$t = ($lang === 'en' || !is_array($en_t)) ? $t_local : array_replace_recursive($en_t, $t_local);

require_once dirname(__DIR__, 3) . '/includes/ecosystem-i18n.php';
$t = bh_apply_ecosystem_translations($t, $lang, 'shop');

require_once __DIR__ . '/market.php';
$t = shs_apply_market_translations($t, $lang);

require_once __DIR__ . '/seo.php';
if (!empty($t['meta']['description'])) {
    $t['meta']['description'] = shs_meta_description_fit((string) $t['meta']['description']);
}
if (!empty($t['order']['meta_description'])) {
    $t['order']['meta_description'] = shs_meta_description_fit((string) $t['order']['meta_description']);
}

require_once __DIR__ . '/helpers.php';