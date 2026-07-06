<?php
if (!function_exists('bh_cms_news_url')) {
    require_once dirname(__DIR__, 3) . '/includes/bh-cms-links.php';
}
$sh_news_url = shs_demo_url('news.php');
?>
<footer class="shs-footer">
    <div class="shs-container shs-footer-inner">
        <div class="shs-footer-grid">
            <div class="shs-footer-brand">
                <strong>Shop CMS</strong>
                <p><?= htmlspecialchars($t['footer']['tagline'] ?? 'PHP e-commerce script — Norway & Europe') ?></p>
            </div>
            <div>
                <h4><?= htmlspecialchars($t['footer']['product']) ?></h4>
                <ul>
                    <li><a href="<?= shs_url('index.php') ?>">Shop CMS</a></li>
                    <li><a href="<?= shs_demo_url() ?>" rel="related"><?= htmlspecialchars($t['footer']['shop_demo'] ?? $t['footer']['demo_link']) ?></a></li>
                    <li><a href="<?= shs_solutions_url() ?>" rel="related"><?= htmlspecialchars($t['footer']['solutions'] ?? 'Solutions') ?></a></li>
                    <li><a href="<?= shs_url('order.php') ?>"><?= htmlspecialchars($t['footer']['order_page'] ?? $t['footer']['order']) ?></a></li>
                    <li><a href="<?= shs_demo_url('admin/login.php') ?>"><?= htmlspecialchars($t['footer']['admin_demo'] ?? 'Admin demo') ?></a></li>
                </ul>
            </div>
            <div>
                <h4><?= htmlspecialchars($t['footer']['links']) ?></h4>
                <ul>
                    <li><a href="<?= shs_demo_url('contact.php') ?>"><?= htmlspecialchars($t['nav']['contact'] ?? 'Contact') ?></a></li>
                    <li><a href="<?= shs_demo_url('llms.txt') ?>"><?= htmlspecialchars($t['footer']['llms']) ?></a></li>
                    <li><a href="https://bilohash.com/" rel="author"><?= htmlspecialchars($t['footer']['portfolio'] ?? 'bilohash.com') ?></a></li>
                    <li><a href="<?= htmlspecialchars($sh_news_url) ?>" rel="related"><?= htmlspecialchars($t['footer']['news'] ?? 'News') ?></a></li>
                    <li><a href="https://bilohash.com/news/" rel="related"><?= htmlspecialchars($t['footer']['news_releases'] ?? 'News & releases') ?></a></li>
                    <li><a href="<?= shs_demo_url('solutions.php') ?>"><?= htmlspecialchars($t['footer']['solutions'] ?? 'Solutions') ?></a></li>
                    <li><a href="<?= shs_demo_url('admin/login.php') ?>"><?= htmlspecialchars($t['footer']['admin_demo'] ?? 'Admin demo') ?></a></li>
                </ul>
            </div>
            <div>
                <h4><?= htmlspecialchars($t['footer']['legal'] ?? 'Legal') ?></h4>
                <ul>
                    <li><a href="https://bilohash.com/website/privacy-policy.php"><?= htmlspecialchars($t['footer']['privacy'] ?? 'Privacy') ?></a></li>
                    <li><a href="https://bilohash.com/website/cookies.php"><?= htmlspecialchars($t['footer']['terms'] ?? 'Cookies') ?></a></li>
                </ul>
            </div>
        </div>
        <div class="shs-footer-bottom">
            <?= sprintf(htmlspecialchars($t['footer']['copyright']), date('Y'), sh_version_label()) ?>
        </div>
    </div>
</footer>
<?php
require_once dirname(__DIR__, 2) . '/includes/shop-mode.php';
if (sh_cookie_consent_enabled()) {
    $sh_cookie_privacy_url = shs_demo_url('page.php?slug=privacy');
    $sh_cookie_cookies_url = shs_demo_url('page.php?slug=cookies');
    require dirname(__DIR__, 2) . '/includes/cookie-consent.php';
}
?>
</body>
</html>