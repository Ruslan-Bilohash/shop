<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/store-settings.php';

define('SH_LANG_COOKIE', 'sh_lang');

/** Must exist before sh_load_settings() — service-pages defaults call sh_langs(). */
$SH_LANGS = sh_builtin_langs();

if (!function_exists('sh_langs')) {
    function sh_langs(): array
    {
        global $SH_LANGS;
        return $SH_LANGS;
    }
}

require_once __DIR__ . '/payment-settings.php';
$SH_LANGS = sh_active_langs(sh_load_settings());

function sh_detect_lang(): string
{
    global $base_path, $SH_LANGS;
    $codes = array_keys($SH_LANGS);

    if (!empty($_GET['lang']) && in_array($_GET['lang'], $codes, true)) {
        $chosen = $_GET['lang'];
        setcookie(SH_LANG_COOKIE, $chosen, [
            'expires' => time() + 365 * 86400,
            'path'    => rtrim($base_path, '/') . '/' ?: '/',
            'secure'  => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite'=> 'Lax',
        ]);
        $isAdmin = str_contains((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/admin/');
        if ($chosen === 'no' && !$isAdmin) {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $parts = parse_url($uri);
            parse_str($parts['query'] ?? '', $q);
            unset($q['lang']);
            $clean = ($parts['path'] ?? '/') . ($q ? '?' . http_build_query($q) : '');
            if ($clean !== $uri) { header('Location: ' . $clean, true, 302); exit; }
        }
        return $chosen;
    }
    if (!empty($_COOKIE[SH_LANG_COOKIE]) && in_array($_COOKIE[SH_LANG_COOKIE], $codes, true)) {
        return $_COOKIE[SH_LANG_COOKIE];
    }
    if (function_exists('sh_site_default_lang')) {
        $default = sh_site_default_lang(sh_load_settings());
        return in_array($default, $codes, true) ? $default : 'no';
    }
    return 'no';
}

$lang      = sh_detect_lang();
$lang_meta = $SH_LANGS[$lang] ?? $SH_LANGS['no'];
$en_file = __DIR__ . '/../lang/en.php';
$en_t = is_file($en_file) ? require $en_file : [];
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
if (!is_file($lang_file)) {
    $lang_file = $en_file;
}
$t_local = require $lang_file;
$t = ($lang === 'en' || !is_array($en_t)) ? $t_local : array_replace_recursive($en_t, $t_local);

// Full admin panel overlays (100% key coverage for no / ru / sv)
if ($lang !== 'en') {
    $adminOverlayFile = __DIR__ . '/../lang/admin/' . $lang . '.php';
    if (is_file($adminOverlayFile)) {
        $adminOverlay = require $adminOverlayFile;
        if (is_array($adminOverlay)) {
            $t['admin'] = array_replace_recursive($t['admin'] ?? [], $adminOverlay);
        }
    } elseif (in_array($lang, ['no', 'ru', 'sv'], true) && is_array($t_local['admin'] ?? null)) {
        $t['admin'] = array_replace_recursive($t['admin'] ?? [], $t_local['admin']);
    }
    if (in_array($lang, ['no', 'ru', 'sv'], true) && !is_file($adminOverlayFile)) {
        $ukFile = __DIR__ . '/../lang/uk.php';
        if (is_file($ukFile)) {
            $ukData = require $ukFile;
            if (is_array($ukData['admin'] ?? null)) {
                $t['admin'] = array_replace_recursive($ukData['admin'], $t['admin'] ?? []);
            }
        }
        if (is_array($t_local['admin'] ?? null)) {
            $t['admin'] = array_replace_recursive($t['admin'] ?? [], $t_local['admin']);
        }
    }
}

require_once __DIR__ . '/ecosystem-load.php';
sh_require_ecosystem('ecosystem-i18n.php');
$t = bh_apply_ecosystem_translations($t, $lang, 'shop');

require_once __DIR__ . '/helpers.php';