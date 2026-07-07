<?php
/**
 * BILOHASH ecosystem release promo cards + news hub newsletter (homepage block).
 * Expects global $t, $lang and sh_url().
 */
function sh_ecosystem_release_cards(): array
{
    return [
        [
            'id'    => 'auction',
            'badge' => 'release',
            'icon'  => 'gavel',
            'color' => 'amber',
            'url'   => 'https://bilohash.com/news/auction-cms.html',
            'title' => 'Auction CMS',
        ],
        [
            'id'    => 'booking',
            'badge' => 'release',
            'icon'  => 'calendar-check',
            'color' => 'sky',
            'url'   => 'https://bilohash.com/news/booking-cms.html',
            'title' => 'Booking CMS',
        ],
        [
            'id'    => 'wordpress',
            'badge' => 'official',
            'icon'  => 'wordpress',
            'color' => 'emerald',
            'fab'   => true,
            'url'   => 'https://bilohash.com/news/wordpress.html',
            'title' => 'AI Chat Consultant for WordPress',
        ],
    ];
}

function sh_ecosystem_release_label(array $map, string $lang, string $fallback = ''): string
{
    if (!is_array($map)) {
        return $fallback;
    }
    $val = trim((string) ($map[$lang] ?? ''));
    if ($val !== '') {
        return $val;
    }
    foreach (['en', 'uk', 'no'] as $code) {
        $val = trim((string) ($map[$code] ?? ''));
        if ($val !== '') {
            return $val;
        }
    }
    return $fallback;
}

function sh_render_ecosystem_release_section(): void
{
    global $t, $lang;
    $er = $t['ecosystem_releases'] ?? [];
    if ($er === []) {
        return;
    }
    if (!function_exists('sh_subscription_url')) {
        require_once __DIR__ . '/subscription-links.php';
    }
    if (!function_exists('sh_billing_subscription_tagline')) {
        require_once __DIR__ . '/billing-pricing.php';
    }
    $subUrl = sh_subscription_url();
    $subTagline = sh_billing_subscription_tagline($lang);
    $cards = sh_ecosystem_release_cards();
    $badges = is_array($er['badges'] ?? null) ? $er['badges'] : [];
    $descs  = is_array($er['descriptions'] ?? null) ? $er['descriptions'] : [];
    $sub    = $t['subscribe'] ?? [];
    ?>
<section class="sh-eco-releases" aria-labelledby="shEcoReleasesTitle">
    <div class="sh-container">
        <?php if (!empty($er['subscription_title'])): ?>
        <div class="sh-eco-subscription-banner">
            <div class="sh-eco-subscription-copy">
                <h2 class="sh-eco-subscription-title"><?= htmlspecialchars((string) $er['subscription_title']) ?></h2>
                <p class="sh-eco-subscription-sub"><?= htmlspecialchars((string) ($er['subscription_sub'] ?? $subTagline)) ?></p>
            </div>
            <a href="<?= htmlspecialchars($subUrl) ?>" class="sh-btn sh-btn-primary" <?= sh_subscription_external_attrs() ?>>
                <?= htmlspecialchars((string) ($er['subscription_cta'] ?? 'Subscribe')) ?>
            </a>
        </div>
        <?php endif; ?>
        <div class="sh-eco-releases-grid">
            <?php foreach ($cards as $card):
                $cid = (string) ($card['id'] ?? '');
                $desc = sh_ecosystem_release_label($descs[$cid] ?? [], $lang, '');
                $badgeKey = (string) ($card['badge'] ?? 'release');
                $badgeLabel = sh_ecosystem_release_label($badges[$badgeKey] ?? [], $lang, ucfirst($badgeKey));
                $color = htmlspecialchars((string) ($card['color'] ?? 'blue'));
                $isFab = !empty($card['fab']);
                ?>
            <a href="<?= htmlspecialchars((string) ($card['url'] ?? '#')) ?>" class="sh-eco-release-card sh-eco-release-card--<?= $color ?>" target="_blank" rel="noopener">
                <div class="sh-eco-release-badge"><?= htmlspecialchars($badgeLabel) ?></div>
                <div class="sh-eco-release-icon" aria-hidden="true">
                    <?php if ($isFab): ?>
                    <i class="fab fa-<?= htmlspecialchars((string) ($card['icon'] ?? 'wordpress')) ?>"></i>
                    <?php else: ?>
                    <i class="fas fa-<?= htmlspecialchars((string) ($card['icon'] ?? 'cube')) ?>"></i>
                    <?php endif; ?>
                </div>
                <h3 class="sh-eco-release-title"><?= htmlspecialchars((string) ($card['title'] ?? '')) ?></h3>
                <?php if ($desc !== ''): ?>
                <p class="sh-eco-release-desc"><?= htmlspecialchars($desc) ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="sh-eco-news-hub" id="newsletter">
            <div class="sh-eco-news-hub-head">
                <div class="sh-eco-news-hub-kicker"><?= htmlspecialchars((string) ($er['news_kicker'] ?? 'News')) ?></div>
                <h2 class="sh-section-title" id="shEcoReleasesTitle"><?= htmlspecialchars((string) ($er['news_title'] ?? '')) ?></h2>
                <p class="sh-section-sub"><?= htmlspecialchars((string) ($er['news_subtitle'] ?? '')) ?></p>
            </div>
            <div class="sh-eco-news-hub-body">
                <div class="sh-eco-newsletter-card">
                    <div class="sh-eco-newsletter-badge"><?= htmlspecialchars((string) ($er['newsletter_badge'] ?? 'Newsletter')) ?></div>
                    <h3 class="sh-eco-newsletter-title"><?= htmlspecialchars((string) ($er['newsletter_title'] ?? '')) ?></h3>
                    <p class="sh-eco-newsletter-sub"><?= htmlspecialchars((string) ($er['newsletter_subtitle'] ?? '')) ?></p>
                    <form class="sh-newsletter-form sh-eco-newsletter-form" data-sh-subscribe action="<?= sh_url('api/subscribe.php') ?>" method="post" novalidate
                          data-success="<?= htmlspecialchars($sub['success'] ?? 'Subscribed! Thank you.') ?>"
                          data-already="<?= htmlspecialchars($sub['already'] ?? 'Already subscribed.') ?>"
                          data-invalid="<?= htmlspecialchars($sub['invalid'] ?? 'Invalid email.') ?>"
                          data-failed="<?= htmlspecialchars($sub['failed'] ?? 'Could not subscribe.') ?>">
                        <div class="sh-newsletter-row">
                            <input type="email" name="email" required autocomplete="email"
                                   placeholder="<?= htmlspecialchars((string) ($er['newsletter_email'] ?? ($sub['placeholder'] ?? 'your@email.com'))) ?>"
                                   aria-label="<?= htmlspecialchars((string) ($er['newsletter_email'] ?? 'Email')) ?>">
                            <button type="submit" class="sh-btn sh-btn-primary"><?= htmlspecialchars((string) ($er['newsletter_submit'] ?? ($sub['submit'] ?? 'Subscribe'))) ?></button>
                        </div>
                        <p class="sh-newsletter-msg" hidden role="status"></p>
                    </form>
                </div>
                <div class="sh-eco-news-links">
                    <a href="https://bilohash.com/news/" class="sh-eco-news-link-card" target="_blank" rel="noopener">
                        <i class="fas fa-folder-open" aria-hidden="true"></i>
                        <span><?= htmlspecialchars((string) ($er['all_news'] ?? 'All news & updates')) ?></span>
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
    <?php
}