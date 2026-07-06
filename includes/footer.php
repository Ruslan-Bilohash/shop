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
    <div class="sh-footer-cta sh-footer-cta--animated">
        <div class="sh-footer-cta-glow" aria-hidden="true"></div>
        <div class="sh-footer-cta-inner">
            <div class="sh-footer-cta-text">
                <span class="sh-footer-cta-kicker"><i class="fas fa-rocket" aria-hidden="true"></i> <?= htmlspecialchars($ft['cta_kicker'] ?? 'E-commerce for Norway & Europe') ?></span>
                <strong><?= htmlspecialchars($ft['cta_title'] ?? 'Order a custom e-commerce store') ?></strong>
                <span><?= htmlspecialchars($ft['cta_sub'] ?? 'PHP · multilingual SEO · session cart · Norway & Europe') ?></span>
                <ul class="sh-footer-cta-features" aria-label="<?= htmlspecialchars($ft['cta_features_label'] ?? 'Features') ?>">
                    <li><i class="fas fa-code" aria-hidden="true"></i> PHP</li>
                    <li><i class="fas fa-globe" aria-hidden="true"></i> <?= htmlspecialchars($ft['cta_feat_seo'] ?? 'Multilingual SEO') ?></li>
                    <li><i class="fas fa-cart-shopping" aria-hidden="true"></i> <?= htmlspecialchars($ft['cta_feat_cart'] ?? 'Session cart') ?></li>
                    <li><i class="fas fa-gauge-high" aria-hidden="true"></i> <?= htmlspecialchars($ft['cta_feat_admin'] ?? 'Admin panel') ?></li>
                </ul>
            </div>
            <div class="sh-footer-cta-actions">
                <a href="<?= sh_url('search.php') ?>" class="sh-btn-primary sh-footer-cta-btn"><i class="fas fa-store" aria-hidden="true"></i> <?= htmlspecialchars($ft['products'] ?? 'All products') ?></a>
                <a href="<?= sh_url('site/') ?>" class="sh-btn-outline-dark sh-footer-cta-btn"><i class="fas fa-book" aria-hidden="true"></i> <?= htmlspecialchars($ft['product_page'] ?? 'Product page') ?></a>
            </div>
        </div>
    </div>

    <div class="sh-footer-inner">
        <div class="sh-footer-grid">
            <div class="sh-footer-brand">
                <a href="<?= sh_url('index.php') ?>" class="sh-footer-logo">
                    <span class="sh-logo-icon"><i class="fas fa-store" aria-hidden="true"></i></span>
                    <span><?= htmlspecialchars($t['meta']['site_name'] ?? 'Shop CMS') ?></span>
                </a>
                <p class="sh-footer-text"><?= htmlspecialchars($ft['demo'] ?? '') ?></p>
                <div class="sh-footer-features">
                    <div class="sh-footer-feature"><i class="fas fa-truck-fast" aria-hidden="true"></i><span><?= htmlspecialchars($ft['feat_shipping'] ?? 'Posten tracking') ?></span></div>
                    <div class="sh-footer-feature"><i class="fas fa-shield-halved" aria-hidden="true"></i><span><?= htmlspecialchars($ft['feat_secure'] ?? 'Secure checkout') ?></span></div>
                    <div class="sh-footer-feature"><i class="fas fa-credit-card" aria-hidden="true"></i><span><?= htmlspecialchars($ft['feat_payments'] ?? 'Stripe · PayPal · Vipps') ?></span></div>
                    <div class="sh-footer-feature"><i class="fas fa-headset" aria-hidden="true"></i><span><?= htmlspecialchars($ft['feat_support'] ?? 'AI chat support') ?></span></div>
                </div>
                <div class="sh-footer-social">
                    <a href="https://bilohash.com/" rel="author" title="<?= htmlspecialchars($ft['portfolio'] ?? 'Portfolio') ?>"><i class="fas fa-globe" aria-hidden="true"></i><span class="sr-only"><?= htmlspecialchars($ft['portfolio'] ?? 'Portfolio') ?></span></a>
                    <a href="<?= htmlspecialchars($sh_news_url) ?>" rel="related" title="<?= htmlspecialchars($ft['news'] ?? 'News') ?>"><i class="fas fa-newspaper" aria-hidden="true"></i><span class="sr-only"><?= htmlspecialchars($ft['news'] ?? 'News') ?></span></a>
                    <a href="<?= sh_url('contact.php') ?>" title="<?= htmlspecialchars($sh_discuss) ?>"><i class="fas fa-envelope" aria-hidden="true"></i><span class="sr-only"><?= htmlspecialchars($sh_discuss) ?></span></a>
                    <a href="<?= sh_url('track.php') ?>" title="<?= htmlspecialchars($t['nav']['track'] ?? 'Track parcel') ?>"><i class="fas fa-box" aria-hidden="true"></i><span class="sr-only"><?= htmlspecialchars($t['nav']['track'] ?? 'Track parcel') ?></span></a>
                </div>
                <div class="sh-footer-trust">
                    <span class="sh-footer-badge"><i class="fas fa-flask" aria-hidden="true"></i> <?= htmlspecialchars($ft['trust_demo'] ?? 'Demo only') ?></span>
                    <span class="sh-footer-badge"><i class="fas fa-mobile-alt" aria-hidden="true"></i> <?= htmlspecialchars($ft['trust_responsive'] ?? 'Mobile · tablet · desktop') ?></span>
                    <span class="sh-footer-badge"><i class="fas fa-search" aria-hidden="true"></i> <?= htmlspecialchars($ft['trust_seo'] ?? 'Schema.org SEO') ?></span>
                    <span class="sh-footer-badge"><i class="fas fa-language" aria-hidden="true"></i> <?= htmlspecialchars($ft['trust_langs'] ?? '5 languages') ?></span>
                </div>
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
                    <li><a href="https://bilohash.com/" rel="author"><?= htmlspecialchars($ft['portfolio'] ?? 'Portfolio') ?></a></li>
                    <li><a href="<?= htmlspecialchars($sh_news_url) ?>" rel="related"><?= htmlspecialchars($ft['news'] ?? 'News') ?></a></li>
                    <li><a href="https://bilohash.com/news/" rel="related"><?= htmlspecialchars($ft['news_releases'] ?? 'News & releases') ?></a></li>
                    <li><a href="<?= sh_url('site/') ?>" rel="related"><?= htmlspecialchars($ft['product_page'] ?? 'Product page') ?></a></li>
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
                </ul>
            </div>
        </div>

        <?php
        $eco_class_prefix = 'sh-footer-eco';
        require dirname(__DIR__, 2) . '/includes/ecosystem-footer-block.php';
        ?>

        <div class="sh-footer-bottom"><?= htmlspecialchars($sh_copyright) ?></div>
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