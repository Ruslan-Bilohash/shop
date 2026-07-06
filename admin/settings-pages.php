<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/service-pages.php';

$settings_tab = 'pages';
$settings = sh_load_settings();
$settings = sh_merge_service_settings($settings);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_service_page'])) {
    $slug = strtolower(trim($_POST['page_slug'] ?? ''));
    $settings = sh_service_page_delete($slug, $settings);
    if (sh_save_settings($settings)) {
        $_SESSION['sh_admin_flash'] = 'success';
    }
    header('Location: ' . sh_admin_url('settings-pages.php?page=delivery'));
    exit;
}

$page_slug = strtolower(trim($_GET['page'] ?? 'delivery'));
if (!sh_service_page_slug_valid($page_slug)) {
    $page_slug = 'delivery';
}

if (!empty($_GET['new']) && sh_service_page_slug_valid($page_slug) && !isset($settings['service_pages'][$page_slug])) {
    $settings = sh_service_page_create($page_slug, $settings);
    sh_save_settings($settings);
    $settings = sh_load_settings();
}

$bh_cms_load_settings = 'sh_load_settings';
$bh_cms_save_settings = 'sh_save_settings';
$bh_cms_admin_url = 'sh_admin_url';
$bh_cms_layout = __DIR__ . '/includes/layout.php';
$bh_cms_layout_end = __DIR__ . '/includes/layout-end.php';
require __DIR__ . '/includes/complete-settings-page.php';