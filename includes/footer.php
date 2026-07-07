<?php
if (!function_exists('sh_vertical_hub_label')) {
    require_once __DIR__ . '/vertical-lib.php';
}
if (!function_exists('cms_contact_texts')) {
    require_once __DIR__ . '/ecosystem-load.php';
    try {
        sh_require_ecosystem('cms-contact.php');
    } catch (Throwable $e) {
        // optional on storefront
    }
}
require_once __DIR__ . '/service-pages.php';
if (!function_exists('bh_cms_news_url')) {
    require_once __DIR__ . '/ecosystem-load.php';
    sh_require_ecosystem('bh-cms-links.php');
}

$ft = $t['footer'] ?? [];
$sh_news_url = sh_url('news.php');
$sh_discuss = function_exists('cms_contact_texts')
    ? (cms_contact_texts('shop', $lang)['nav_discuss'] ?? 'Contact')
    : 'Contact';
$sh_copyright = sprintf($ft['copyright'] ?? '© %s Shop CMS Demo.', date('Y'));
$sh_footer_cols = sh_footer_links(sh_site_settings());
$sh_site_settings = sh_site_settings();
$sh_newsletter_on = true;
if (function_exists('sh_smtp_merge_settings')) {
    require_once __DIR__ . '/smtp-settings.php';
    $sh_newsletter_on = !empty(sh_smtp_merge_settings($sh_site_settings)['newsletter_enabled']);
}
$sh_subscribe = $t['subscribe'] ?? [];

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

        <?php if ($sh_newsletter_on): ?>
        <div class="sh-footer-newsletter">
            <form class="sh-newsletter-form" data-sh-subscribe action="<?= sh_url('api/subscribe.php') ?>" method="post" novalidate
                  data-success="<?= htmlspecialchars($sh_subscribe['success'] ?? 'Subscribed! Thank you.') ?>"
                  data-already="<?= htmlspecialchars($sh_subscribe['already'] ?? 'Already subscribed.') ?>"
                  data-invalid="<?= htmlspecialchars($sh_subscribe['invalid'] ?? 'Invalid email.') ?>"
                  data-failed="<?= htmlspecialchars($sh_subscribe['failed'] ?? 'Could not subscribe.') ?>">
                <label class="sh-newsletter-label" for="shFooterEmail"><?= htmlspecialchars($sh_subscribe['title'] ?? 'Newsletter') ?></label>
                <p class="sh-newsletter-sub"><?= htmlspecialchars($sh_subscribe['subtitle'] ?? '') ?></p>
                <div class="sh-newsletter-row">
                    <input type="email" id="shFooterEmail" name="email" required
                           placeholder="<?= htmlspecialchars($sh_subscribe['placeholder'] ?? 'your@email.com') ?>"
                           autocomplete="email">
                    <button type="submit" class="sh-btn sh-btn-primary"><?= htmlspecialchars($sh_subscribe['submit'] ?? 'Subscribe') ?></button>
                </div>
                <p class="sh-newsletter-msg" hidden role="status"></p>
            </form>
        </div>
        <?php endif; ?>

        <div class="sh-footer-bottom">
            <span><?= htmlspecialchars($sh_copyright) ?></span>
            <div class="sh-footer-bottom-links">
                <a href="<?= sh_url('sitemap.php') ?>"><?= htmlspecialchars($ft['sitemap'] ?? 'Sitemap') ?></a>
                <a href="<?= sh_url('contact.php') ?>"><?= htmlspecialchars($sh_discuss) ?></a>
                <?php
                require_once __DIR__ . '/google-marketing.php';
                $gmbFooter = sh_google_marketing_merge_settings($sh_site_settings);
                if (sh_gmb_active($gmbFooter) && !empty($gmbFooter['gmb_show_footer']) && trim((string) ($gmbFooter['gmb_profile_url'] ?? '')) !== ''):
                ?>
                <a href="<?= htmlspecialchars($gmbFooter['gmb_profile_url']) ?>" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-google" aria-hidden="true"></i> <?= htmlspecialchars(sh_gmb_footer_link_label($gmbFooter)) ?>
                </a>
                <?php endif; ?>
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
sh_render_product_3d_assets();
?>
</body>
</html>