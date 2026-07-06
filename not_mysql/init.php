<?php
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/i18n.php';
$shAuthFile = __DIR__ . '/includes/customer-auth.php';
if (is_file($shAuthFile)) {
    require_once $shAuthFile;
}
$shModeFile = __DIR__ . '/includes/shop-mode.php';
if (is_file($shModeFile)) {
    require_once $shModeFile;
}
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/site-settings.php';
require_once __DIR__ . '/includes/version.php';
if (function_exists('sh_boot_dev_errors')) {
    sh_boot_dev_errors();
}
sh_bootstrap_data();
if (function_exists('sh_shop_maybe_maintenance')) {
    sh_shop_maybe_maintenance();
}