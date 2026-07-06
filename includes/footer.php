<?php
if (!function_exists('sh_vertical_hub_label')) {
    require_once __DIR__ . '/vertical-lib.php';
}
if (!function_exists('cms_contact_texts')) {
    $cms_contact_path = dirname(__DIR__, 2) . '/includes/cms-contact.php';
    if (is_file($cms_contact_path)) {
        require_once $cms_contact_path;
    }
}
require_once __DIR__ . '/service-pages.php';
if (!function_exists('bh_cms_news_url')) {
    require_once dirname(__DIR__, 2) . '/includes/bh-cms-links.php';
}

$ft = $t['footer'] ?? [];
$sh_news_url = bh_cms_news_url('shop') ?? 'https://bilohash.com/news/shop-cms.html';
$sh_discuss = function_exists('cms_contact_texts')
    ? (cms_contact_texts('shop', $lang)['nav_discuss'] ?? 'Contact')
    : 'Contact';
$sh_copyright = sprintf($ft['copyright'] ?? '© %s Shop CMS Demo.', date('Y'));
$sh_footer_cols = sh_footer_links(sh_site_settings());

if (empty($sh_skip_ecosystem) && is_file(__DIR__ . '/ecosystem-strip.php')) {
    require __DIR__ . '/ecosystem-strip.php';
}
?>
<footer class="sh-footer" itemscope itemtype="https://schema.org/WPFooter">
    <div class="sh-footer-inner">
        <div class="sh-footer-grid">
            <div class="sh-footer-brand">
                <a href="<?= sh_url('index.php') ?>" class="sh-footer-logo">
                    <span class="sh-logo-icon"><i class="fas fa-store" aria-hidden="true"></i></span>
                    <span><?= htmlspecialchars($t['meta']['site_name'] ?? 'Shop CMS') ?></span>
                </a>
                <p class="sh-footer-text"><?= htmlspecialchars($ft['demo'] ?? '') ?></p>
            </div>

            <div>
                <h4><?= htmlspecialchars($ft['shop'] ?? 'Shop') ?></h4>
                <ul>
                    <?php foreach ($sh_footer_cols['shop'] ?? [] as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars(sh_footer_link_href($link)) ?>"
                           <?= !empty($link['external']) ? 'rel="noopener noreferrer" target="_blank"' : '' ?>>
                            <?= htmlspecialchars(sh_footer_link_label($link, $lang)) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h4><?= htmlspecialchars($ft['crosslinks'] ?? $ft['links'] ?? 'Links') ?></h4>
                <ul>
                    <li><a href="<?= sh_url('site/') ?>" rel="related"><?= htmlspecialchars($ft['product_page'] ?? 'Product page') ?></a></li>
                    <li><a href="<?= sh_url('solutions.php') ?>"><?= htmlspecialchars($ft['solutions'] ?? 'Solutions') ?></a></li>
                    <li><a href="<?= sh_url('contact.php') ?>"><?= htmlspecialchars($sh_discuss) ?></a></li>
                    <li><a href="https://bilohash.com/" rel="author"><?= htmlspecialchars($ft['portfolio'] ?? 'Portfolio') ?></a></li>
                    <li><a href="<?= htmlspecialchars($sh_news_url) ?>" rel="related"><?= htmlspecialchars($ft['news'] ?? 'News') ?></a></li>
                    <li><a href="https://bilohash.com/news/" rel="related"><?= htmlspecialchars($ft['news_releases'] ?? 'News & releases') ?></a></li>
                    <li><a href="<?= sh_url('admin/login.php') ?>"><?= htmlspecialchars($ft['admin_demo'] ?? 'Admin demo') ?></a></li>
                </ul>
            </div>

            <div>
                <h4><?= htmlspecialchars($ft['legal'] ?? 'Legal') ?></h4>
                <ul>
                    <?php foreach ($sh_footer_cols['legal'] ?? [] as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars(sh_footer_link_href($link)) ?>"
                           <?= !empty($link['external']) ? 'rel="noopener noreferrer" target="_blank"' : '' ?>>
                            <?= htmlspecialchars(sh_footer_link_label($link, $lang)) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <li><a href="https://bilohash.com/website/privacy-policy.php"><?= htmlspecialchars($ft['privacy'] ?? 'Privacy') ?></a></li>
                    <li><a href="https://bilohash.com/website/cookies.php"><?= htmlspecialchars($ft['terms'] ?? 'Cookies') ?></a></li>
                </ul>
            </div>
        </div>

        <div class="sh-footer-bottom">
            <span><?= htmlspecialchars($sh_copyright) ?></span>
            <div class="sh-footer-bottom-links">
                <a href="<?= sh_url('sitemap.php') ?>"><?= htmlspecialchars($ft['sitemap'] ?? 'Sitemap') ?></a>
                <a href="<?= sh_url('contact.php') ?>"><?= htmlspecialchars($sh_discuss) ?></a>
            </div>
        </div>
    </div>
</footer>
<?php
require_once __DIR__ . '/store-settings.php';
sh_render_tracking_snippets(sh_site_settings());
sh_render_custom_footer_js(sh_site_settings());
bh_cms_render_chat_widget('Shop CMS', sh_site_settings(), $lang ?? 'no');
require __DIR__ . '/cookie-consent.php';
?>
</body>
</html>