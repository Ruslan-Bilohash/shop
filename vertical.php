<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/vertical-lib.php';

$slug = trim($_GET['slug'] ?? '');
$vertical = sh_vertical_by_slug($slug);
if (!$vertical) {
    header('Location: ' . sh_url('solutions.php'), true, 302);
    exit;
}

$GLOBALS['vertical_slug'] = $slug;
$v = sh_vertical_lang($vertical, $lang);
$page_title = $v['title'] ?? SH_SITE_NAME;
$page_desc  = $v['description'] ?? '';
$canonical  = sh_vertical_canonical($slug);
$canon_abs  = sh_absolute_url($canonical);

$seo_schemas = [
    sh_seo_organization(),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => sh_vertical_hub_label($lang), 'url' => sh_absolute_url(sh_url('solutions.php'))],
        ['name' => $v['h1'] ?? $slug, 'url' => $canon_abs],
    ]),
    sh_seo_vertical_service($v['h1'] ?? $slug, $page_desc, $canon_abs),
    sh_seo_software_app($canon_abs, $page_desc),
];
if (!empty($v['faq'])) {
    $seo_schemas[] = sh_seo_faq_page($v['faq']);
}

require __DIR__ . '/includes/vertical-template.php';