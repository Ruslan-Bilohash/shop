<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/payment-settings.php';
$settings_tab = 'appearance';
$bh_cms_load_settings = 'sh_load_settings';
$bh_cms_save_settings = 'sh_save_settings';
$bh_cms_admin_url = 'sh_admin_url';
$bh_cms_layout = __DIR__ . '/includes/layout.php';
$bh_cms_layout_end = __DIR__ . '/includes/layout-end.php';
require_once dirname(__DIR__, 2) . '/includes/bh-cms-site-settings.php';
require __DIR__ . '/includes/complete-settings-page.php';