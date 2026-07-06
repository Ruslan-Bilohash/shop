<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$platform = strtolower(trim((string) ($_GET['platform'] ?? 'ios')));
if (!in_array($platform, ['ios', 'android'], true)) {
    $platform = 'ios';
}

$ma = $t['mobile_app'] ?? [];
$isIos = $platform === 'ios';
$otherPlatform = $isIos ? 'android' : 'ios';

$current_page = 'mobile-app';
$page_title = $ma[$isIos ? 'title_ios' : 'title_android'] ?? ('Mobile app — Shop CMS');
$page_desc  = $ma[$isIos ? 'meta_ios' : 'meta_android'] ?? 'Demo mobile app generator stub.';
$canonical  = sh_url('mobile-app.php?platform=' . $platform . ($lang !== 'no' ? '&lang=' . $lang : ''));
$canon_abs  = sh_absolute_url($canonical);
$body_class = 'sh-page-mobile-app sh-page-mobile-app--' . $platform;
$seo_schemas = [
    sh_seo_organization(),
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $ma[$isIos ? 'heading_ios' : 'heading_android'] ?? 'Mobile app', 'url' => $canon_abs],
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-mobile-app-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($ma[$isIos ? 'heading_ios' : 'heading_android'] ?? 'Mobile app') ?></span>
    </nav>

    <div class="sh-form-card sh-mobile-app-hero">
        <span class="sh-footer-demo-badge"><?= htmlspecialchars($t['footer']['demo_badge'] ?? 'Demo') ?></span>
        <p class="sh-section-kicker" style="margin-top:12px">
            <i class="fas fa-mobile-alt" aria-hidden="true"></i>
            <?= htmlspecialchars($ma['kicker'] ?? 'Mobile app') ?>
        </p>
        <?php if ($isIos): ?>
        <i class="fab fa-apple" aria-hidden="true"></i>
        <?php else: ?>
        <i class="fab fa-google-play" aria-hidden="true"></i>
        <?php endif; ?>
        <h1><?= htmlspecialchars($ma[$isIos ? 'heading_ios' : 'heading_android'] ?? 'Mobile app generator') ?></h1>
        <p><?= htmlspecialchars($ma['intro'] ?? '') ?></p>
        <p class="sh-muted" style="font-size:13px"><?= htmlspecialchars($ma['demo_note'] ?? '') ?></p>
        <div class="sh-mobile-app-actions">
            <a href="<?= sh_url('index.php') ?>" class="sh-btn-primary">
                <i class="fas fa-store" aria-hidden="true"></i>
                <?= htmlspecialchars($ma['back_shop'] ?? 'Back to shop') ?>
            </a>
            <a href="<?= sh_url('mobile-app.php?platform=' . $otherPlatform . ($lang !== 'no' ? '&lang=' . urlencode($lang) : '')) ?>" class="sh-btn-outline-dark">
                <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                <?= htmlspecialchars($ma['other_platform'] ?? 'Other platform') ?>
            </a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>