<?php
/**
 * Bootstrap diagnostics — upload to /shop/_health.php, open in browser, then delete.
 */
header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

$steps = [];

try {
    require_once __DIR__ . '/config.php';
    $steps[] = 'config.php OK';
} catch (Throwable $e) {
    $steps[] = 'config.php FAIL: ' . $e->getMessage();
    echo implode("\n", $steps);
    exit;
}

try {
    require_once __DIR__ . '/includes/store-settings.php';
    $steps[] = 'store-settings.php OK';
    $SH_LANGS = sh_builtin_langs();
    function sh_langs(): array { global $SH_LANGS; return $SH_LANGS; }
    $steps[] = 'sh_langs() stub OK';
} catch (Throwable $e) {
    $steps[] = 'store-settings FAIL: ' . $e->getMessage();
    echo implode("\n", $steps);
    exit;
}

try {
    require_once __DIR__ . '/includes/payment-settings.php';
    $settings = sh_load_settings();
    $steps[] = 'sh_load_settings() OK (' . count($settings) . ' keys)';
} catch (Throwable $e) {
    $steps[] = 'sh_load_settings FAIL: ' . $e->getMessage();
    echo implode("\n", $steps);
    exit;
}

try {
    require_once __DIR__ . '/init.php';
    $steps[] = 'init.php OK';
    $steps[] = 'lang=' . ($lang ?? '?') . ' products=' . count(sh_products());
} catch (Throwable $e) {
    $steps[] = 'init.php FAIL: ' . $e->getMessage();
}

try {
    chdir(__DIR__ . '/site');
    $_SERVER['SCRIPT_NAME'] = '/shop/site/index.php';
    $_SERVER['REQUEST_URI'] = '/shop/site/';
    require __DIR__ . '/site/init.php';
    $steps[] = 'site/init.php OK';
} catch (Throwable $e) {
    $steps[] = 'site/init.php FAIL: ' . $e->getMessage();
}

echo implode("\n", $steps) . "\n";
echo "PHP " . PHP_VERSION . "\n";
echo "Delete this file after checking.\n";