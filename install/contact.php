<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/cms-contact.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$current_page = 'contact';
$product = 'shop';
$cms_t = cms_contact_texts($product, $lang);
$cms_alert = '';
$cms_alert_type = '';
$cms_values = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = cms_contact_handle_post($product, $lang, 'https://bilohash.com/shop/contact.php');
    $cms_alert = $result['alert'];
    $cms_alert_type = $result['alert_type'];
    $cms_values = $result['values'];
}

$cms_csrf = cms_contact_ensure_csrf();
$page_title = $cms_t['page_title'];
$page_desc  = $cms_t['meta_description'];
$canonical  = $site_url . '/contact.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = sh_absolute_url($canonical);
$seo_schemas = [
    sh_seo_organization(),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $cms_t['h1'], 'url' => $canon_abs],
    ]),
];

$cms_prefix = 'sh';
$body_class = 'sh-page-contact';
$cms_action = sh_url('contact.php') . ($lang !== 'no' ? '?lang=' . urlencode($lang) : '');

require __DIR__ . '/includes/header.php';
require dirname(__DIR__) . '/includes/cms-contact-form.php';
require __DIR__ . '/includes/footer.php';