<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
$settings_tab = 'seo_analysis';
$admin_extra_js = [sh_asset('js/admin-seo-analysis.js') . '?v=2'];
$bh_cms_load_settings = 'sh_load_settings';
$bh_cms_save_settings = 'sh_save_settings';
$bh_cms_admin_url = 'sh_admin_url';
$bh_cms_layout = __DIR__ . '/includes/layout.php';
$bh_cms_layout_end = __DIR__ . '/includes/layout-end.php';
require __DIR__ . '/includes/complete-settings-page.php';