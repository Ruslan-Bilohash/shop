<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/vertical-lib.php';
require_once dirname(__DIR__) . '/includes/cms-contact.php';
require_once __DIR__ . '/includes/render-homepage.php';
$current_page = 'home';
$search_params = sh_search_params();
$canonical     = $site_url . '/';
$page_title    = $t['meta']['title'];
$page_desc     = $t['meta']['description'];
$canon_abs     = sh_absolute_url($canonical);
$seo_schemas   = [
    sh_seo_organization(),
    sh_seo_website($canon_abs),
    sh_seo_software_app($canon_abs, $page_desc),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_professional_service(),
];
require __DIR__ . '/includes/header.php';
?>

<section class="sh-hero">
    <div class="sh-hero-bg"></div>
    <div class="sh-hero-inner">
        <span class="sh-demo-badge"><i class="fas fa-store"></i> <?= htmlspecialchars($t['hero']['badge']) ?></span>
        <h1><?= htmlspecialchars($t['hero']['title']) ?></h1>
        <p><?= htmlspecialchars($t['hero']['subtitle']) ?></p>
        <?php require __DIR__ . '/includes/search-form.php'; ?>
    </div>
</section>

<?php sh_render_homepage_blocks(sh_site_settings()); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>