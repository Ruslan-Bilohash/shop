<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/service-pages.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$slug = trim($_GET['slug'] ?? '');
$page = sh_service_page($slug, sh_site_settings());

if ($page === null) {
    http_response_code(404);
    $page_title = '404';
    $page_desc  = $t['meta']['description'];
    $canonical  = sh_url('page.php');
    require __DIR__ . '/includes/header.php';
    echo '<div class="sh-container"><div class="sh-form-card sh-empty-state"><i class="fas fa-file-alt"></i><p>Page not found.</p><a href="' . htmlspecialchars(sh_url('index.php')) . '" class="sh-btn-primary">' . htmlspecialchars($t['breadcrumb_home']) . '</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$title   = sh_localized($page, 'title', $lang);
$content = sh_localized($page, 'content', $lang);
$metaTitle = trim(sh_localized($page, 'meta_title', $lang));
$metaDesc  = trim(sh_localized($page, 'meta_description', $lang));

$current_page = 'page-' . $slug;
$page_title = $metaTitle !== '' ? $metaTitle : ($title . ' — ' . sh_seo_site_name());
$page_desc  = $metaDesc !== '' ? $metaDesc : bh_str_sub($content, 0, 160);
$canonical  = sh_url('page.php?slug=' . urlencode($slug) . ($lang !== 'no' ? '&lang=' . $lang : ''));
$canon_abs  = sh_absolute_url($canonical);
$body_class = 'sh-page-service sh-page-' . $slug;
$seo_schemas = [
    sh_seo_organization(),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $title, 'url' => $canon_abs],
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-service-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($title) ?></span>
    </nav>

    <article class="sh-service-article">
        <header class="sh-service-header">
            <h1><?= htmlspecialchars($title) ?></h1>
        </header>
        <div class="sh-service-content sh-service-content--rich">
            <?= sh_service_page_content_html($content) ?>
        </div>
    </article>
    <?php
    require_once __DIR__ . '/includes/block-templates.php';
    sh_render_page_block_templates($slug, sh_site_settings());
    ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>