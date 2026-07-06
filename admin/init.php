<?php
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/ecosystem-load.php';
sh_require_ecosystem('bh-cms-site-settings.php');

$ta = $t['admin'] ?? [];
$admin_page = $admin_page ?? 'dashboard';

$guides_all = require dirname(__DIR__) . '/lang/admin-guides.php';
$ta['payments_page']['guides'] = $guides_all[$lang] ?? $guides_all['en'] ?? [];

$settings_guides_all = require dirname(__DIR__) . '/lang/admin-settings-guides.php';
$ta['settings_guides'] = $settings_guides_all[$lang] ?? $settings_guides_all['en'] ?? [];

function sh_admin_lang_url(string $code): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: sh_admin_url('index.php');
    parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? '', $q);
    if ($code === 'no') {
        unset($q['lang']);
    } else {
        $q['lang'] = $code;
    }
    $qs = http_build_query($q);
    return $path . ($qs !== '' ? '?' . $qs : '');
}