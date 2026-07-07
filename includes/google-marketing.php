<?php
/**
 * Google Ads (paid) + Google Business Profile integrations.
 */
declare(strict_types=1);

/** @return array<string, mixed> */
function sh_google_marketing_defaults(): array
{
    return [
        'google_ads_enabled'             => false,
        'google_ads_id'                  => '',
        'google_ads_conversion_label'    => '',
        'google_ads_remarketing'         => true,
        'google_ads_track_purchase'      => true,
        'google_ads_track_begin_checkout'  => true,

        'gmb_enabled'          => false,
        'gmb_place_id'         => '',
        'gmb_business_name'    => '',
        'gmb_address'          => '',
        'gmb_city'             => '',
        'gmb_postal'           => '',
        'gmb_country'          => 'Norway',
        'gmb_phone'            => '',
        'gmb_latitude'         => '',
        'gmb_longitude'        => '',
        'gmb_profile_url'      => '',
        'gmb_reviews_url'      => '',
        'gmb_opening_hours'    => 'Mon–Fri 09:00–17:00',
        'gmb_map_embed'        => '',
        'gmb_show_contact'     => true,
        'gmb_show_footer'      => true,
        'gmb_show_map'         => true,
        'gmb_schema'           => true,
    ];
}

/** @return array<string, string> */
function sh_gmb_demo_defaults(): array
{
    return [
        'gmb_business_name' => 'BILOHASH Demo AS',
        'gmb_address'         => 'Storgata 12',
        'gmb_city'            => 'Drammen',
        'gmb_postal'          => '3044',
        'gmb_country'         => 'Norway',
        'gmb_phone'           => '+47 40 00 00 00',
        'gmb_latitude'        => '59.7440',
        'gmb_longitude'       => '10.2045',
        'gmb_profile_url'     => 'https://maps.google.com/?q=BILOHASH+Demo+AS+Drammen',
        'gmb_reviews_url'     => 'https://g.page/r/demo/review',
        'gmb_opening_hours'   => 'Mon–Fri 09:00–17:00',
    ];
}

/** @param array<string, mixed>|null $settings */
function sh_google_marketing_merge_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $base = is_array($settings) ? $settings : [];
    if (function_exists('sh_merge_store_settings')) {
        require_once __DIR__ . '/store-settings.php';
        $base = sh_merge_store_settings($base);
    }
    $merged = array_merge(sh_google_marketing_defaults(), $base);
    foreach (sh_gmb_demo_defaults() as $key => $demo) {
        if (trim((string) ($merged[$key] ?? '')) === '' && trim($demo) !== '') {
            $merged[$key] = $demo;
        }
    }
    return $merged;
}

function sh_normalize_google_ads_id(string $id): string
{
    $id = strtoupper(trim($id));
    if ($id === '') {
        return '';
    }
    if (!preg_match('/^AW-\d+$/', $id)) {
        return '';
    }
    return $id;
}

/** @param array<string, mixed> $settings */
function sh_google_ads_send_to(array $settings): string
{
    $adsId = sh_normalize_google_ads_id((string) ($settings['google_ads_id'] ?? ''));
    $label = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) ($settings['google_ads_conversion_label'] ?? ''));
    if ($adsId === '' || $label === '' || empty($settings['google_ads_enabled'])) {
        return '';
    }
    return $adsId . '/' . $label;
}

/** @param array<string, mixed>|null $settings */
function sh_google_ads_active(?array $settings = null): bool
{
    $s = sh_google_marketing_merge_settings($settings);
    return !empty($s['google_ads_enabled']) && sh_normalize_google_ads_id((string) ($s['google_ads_id'] ?? '')) !== '';
}

/** @param array<string, mixed>|null $settings */
function sh_gmb_active(?array $settings = null): bool
{
    $s = sh_google_marketing_merge_settings($settings);
    if (empty($s['gmb_enabled'])) {
        return false;
    }
    return trim((string) ($s['gmb_business_name'] ?? '')) !== ''
        || trim((string) ($s['gmb_place_id'] ?? '')) !== ''
        || trim((string) ($s['gmb_profile_url'] ?? '')) !== '';
}

/** @param array<string, mixed> $settings */
function sh_gmb_map_embed_url(array $settings): string
{
    $custom = trim((string) ($settings['gmb_map_embed'] ?? ''));
    if ($custom !== '') {
        if (preg_match('/src=["\']([^"\']+)["\']/i', $custom, $m)) {
            return $m[1];
        }
        if (str_starts_with($custom, 'http')) {
            return $custom;
        }
    }
    $placeId = trim((string) ($settings['gmb_place_id'] ?? ''));
    if ($placeId !== '') {
        return 'https://maps.google.com/maps?q=place_id:' . rawurlencode($placeId) . '&z=15&output=embed';
    }
    $lat = trim((string) ($settings['gmb_latitude'] ?? ''));
    $lng = trim((string) ($settings['gmb_longitude'] ?? ''));
    if ($lat !== '' && $lng !== '') {
        return 'https://maps.google.com/maps?q=' . rawurlencode($lat . ',' . $lng) . '&z=15&output=embed';
    }
    $parts = array_filter([
        trim((string) ($settings['gmb_address'] ?? '')),
        trim((string) ($settings['gmb_postal'] ?? '')),
        trim((string) ($settings['gmb_city'] ?? '')),
        trim((string) ($settings['gmb_country'] ?? '')),
    ]);
    if ($parts !== []) {
        return 'https://maps.google.com/maps?q=' . rawurlencode(implode(', ', $parts)) . '&z=15&output=embed';
    }
    return '';
}

/** @param array<string, mixed>|null $settings */
function sh_render_google_tracking_tags(?array $settings = null): void
{
    $s = sh_google_marketing_merge_settings($settings);
    $gtag = trim((string) ($s['tracking_gtag_id'] ?? ''));
    $adsId = sh_google_ads_active($s) ? sh_normalize_google_ads_id((string) ($s['google_ads_id'] ?? '')) : '';
    if ($gtag === '' && $adsId === '') {
        return;
    }
    $primary = $gtag !== '' ? $gtag : $adsId;
    $sendTo = sh_google_ads_send_to($s);
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($primary, ENT_QUOTES) ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    <?php if ($gtag !== ''): ?>gtag('config', <?= json_encode($gtag, JSON_UNESCAPED_UNICODE) ?>);<?php endif; ?>
    <?php if ($adsId !== ''):
        $adsConfig = ['send_page_view' => true];
        if (!empty($s['google_ads_remarketing'])) {
            $adsConfig['allow_ad_personalization_signals'] = true;
        }
    ?>
    gtag('config', <?= json_encode($adsId, JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($adsConfig, JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
    window.SH_GOOGLE_ADS = <?= json_encode([
        'enabled'    => $adsId !== '',
        'send_to'    => $sendTo,
        'currency'   => function_exists('sh_site_currency') ? sh_site_currency($s) : 'NOK',
    ], JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <?php
}

/** @param array<string, mixed>|null $settings */
function sh_render_meta_pixel_tag(?array $settings = null): void
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    if (function_exists('sh_merge_store_settings')) {
        require_once __DIR__ . '/store-settings.php';
        $settings = sh_merge_store_settings(is_array($settings) ? $settings : []);
    }
    $pixel = trim((string) ($settings['tracking_meta_pixel'] ?? ''));
    if ($pixel === '') {
        return;
    }
    ?>
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',<?= json_encode($pixel, JSON_UNESCAPED_UNICODE) ?>);fbq('track','PageView');</script>
    <?php
}

/** @param array<string, mixed>|null $settings */
function sh_render_google_ads_begin_checkout(int $value, ?array $settings = null): void
{
    $s = sh_google_marketing_merge_settings($settings);
    if (empty($s['google_ads_track_begin_checkout']) || sh_google_ads_send_to($s) === '') {
        return;
    }
    $currency = function_exists('sh_site_currency') ? sh_site_currency($s) : 'NOK';
    ?>
    <script>
    if (typeof gtag === 'function') {
        gtag('event', 'begin_checkout', {
            currency: <?= json_encode($currency, JSON_UNESCAPED_UNICODE) ?>,
            value: <?= max(0, $value) ?>
        });
    }
    </script>
    <?php
}

/**
 * @param array<string, mixed> $order
 * @param array<string, mixed>|null $settings
 */
function sh_render_google_ads_purchase_conversion(array $order, ?array $settings = null): void
{
    $s = sh_google_marketing_merge_settings($settings);
    $sendTo = sh_google_ads_send_to($s);
    if ($sendTo === '' || empty($s['google_ads_track_purchase'])) {
        return;
    }
    $totals = is_array($order['totals'] ?? null) ? $order['totals'] : [];
    $value = (int) ($totals['total'] ?? 0);
    $currency = (string) ($totals['currency'] ?? (function_exists('sh_site_currency') ? sh_site_currency($s) : 'NOK'));
    $txId = (string) ($order['invoice_no'] ?? $order['id'] ?? '');
    ?>
    <script>
    if (typeof gtag === 'function') {
        gtag('event', 'conversion', {
            send_to: <?= json_encode($sendTo, JSON_UNESCAPED_UNICODE) ?>,
            value: <?= max(0, $value) ?>,
            currency: <?= json_encode($currency, JSON_UNESCAPED_UNICODE) ?>,
            transaction_id: <?= json_encode($txId, JSON_UNESCAPED_UNICODE) ?>
        });
        gtag('event', 'purchase', {
            transaction_id: <?= json_encode($txId, JSON_UNESCAPED_UNICODE) ?>,
            value: <?= max(0, $value) ?>,
            currency: <?= json_encode($currency, JSON_UNESCAPED_UNICODE) ?>
        });
    }
    </script>
    <?php
}

/** @return array<string, mixed>|null */
function sh_seo_local_business(?array $settings = null): ?array
{
    $s = sh_google_marketing_merge_settings($settings);
    if (empty($s['gmb_schema']) || !sh_gmb_active($s)) {
        return null;
    }
    $name = trim((string) ($s['gmb_business_name'] ?? ''));
    if ($name === '' && function_exists('sh_seo_org_name')) {
        $name = sh_seo_org_name($s);
    }
    $base = function_exists('sh_absolute_url') ? sh_absolute_url(sh_url('contact.php')) : '';
    $schema = [
        '@type' => 'LocalBusiness',
        '@id'   => rtrim($base, '/') . '#localbusiness',
        'name'  => $name,
        'url'   => function_exists('sh_absolute_url') ? sh_absolute_url(sh_url('index.php')) : '',
        'image' => function_exists('sh_seo_og_image') ? sh_seo_og_image() : '',
    ];
    $phone = trim((string) ($s['gmb_phone'] ?? ''));
    if ($phone !== '') {
        $schema['telephone'] = $phone;
    }
    $addr = array_filter([
        'streetAddress'   => trim((string) ($s['gmb_address'] ?? '')),
        'postalCode'      => trim((string) ($s['gmb_postal'] ?? '')),
        'addressLocality' => trim((string) ($s['gmb_city'] ?? '')),
        'addressCountry'  => trim((string) ($s['gmb_country'] ?? '')),
    ]);
    if ($addr !== []) {
        $schema['address'] = array_merge(['@type' => 'PostalAddress'], $addr);
    }
    $lat = trim((string) ($s['gmb_latitude'] ?? ''));
    $lng = trim((string) ($s['gmb_longitude'] ?? ''));
    if ($lat !== '' && $lng !== '') {
        $schema['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $lat,
            'longitude' => (float) $lng,
        ];
    }
    $hours = trim((string) ($s['gmb_opening_hours'] ?? ''));
    if ($hours !== '') {
        $schema['openingHours'] = $hours;
    }
    $profile = trim((string) ($s['gmb_profile_url'] ?? ''));
    if ($profile !== '') {
        $schema['hasMap'] = $profile;
        $schema['sameAs'] = [$profile];
    }
    return $schema;
}

/**
 * @param array<string, mixed> $labels
 * @param array<string, mixed>|null $settings
 */
function sh_render_gmb_contact_block(array $labels = [], ?array $settings = null): void
{
    $s = sh_google_marketing_merge_settings($settings);
    if (!sh_gmb_active($s) || empty($s['gmb_show_contact'])) {
        return;
    }
    $mapUrl = !empty($s['gmb_show_map']) ? sh_gmb_map_embed_url($s) : '';
    $name = trim((string) ($s['gmb_business_name'] ?? ''));
    $profile = trim((string) ($s['gmb_profile_url'] ?? ''));
    $reviews = trim((string) ($s['gmb_reviews_url'] ?? ''));
    $phone = trim((string) ($s['gmb_phone'] ?? ''));
    $hours = trim((string) ($s['gmb_opening_hours'] ?? ''));
    $addrParts = array_filter([
        trim((string) ($s['gmb_address'] ?? '')),
        trim((string) ($s['gmb_postal'] ?? '')) . ' ' . trim((string) ($s['gmb_city'] ?? '')),
        trim((string) ($s['gmb_country'] ?? '')),
    ]);
    ?>
    <aside class="sh-gmb-card" id="shGmbCard">
        <h2 class="sh-gmb-title"><i class="fas fa-store" aria-hidden="true"></i> <?= htmlspecialchars($labels['title'] ?? 'Google Business Profile') ?></h2>
        <?php if ($name !== ''): ?><p class="sh-gmb-name"><strong><?= htmlspecialchars($name) ?></strong></p><?php endif; ?>
        <?php if ($addrParts !== []): ?>
        <p class="sh-gmb-address"><i class="fas fa-location-dot" aria-hidden="true"></i> <?= htmlspecialchars(implode(', ', $addrParts)) ?></p>
        <?php endif; ?>
        <?php if ($phone !== ''): ?>
        <p class="sh-gmb-phone"><a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $phone)) ?>"><i class="fas fa-phone" aria-hidden="true"></i> <?= htmlspecialchars($phone) ?></a></p>
        <?php endif; ?>
        <?php if ($hours !== ''): ?>
        <p class="sh-gmb-hours"><i class="fas fa-clock" aria-hidden="true"></i> <?= htmlspecialchars($hours) ?></p>
        <?php endif; ?>
        <div class="sh-gmb-actions">
            <?php if ($profile !== ''): ?>
            <a href="<?= htmlspecialchars($profile) ?>" class="sh-btn sh-btn-outline sh-btn-sm" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-google" aria-hidden="true"></i> <?= htmlspecialchars($labels['view_profile'] ?? 'View on Google Maps') ?>
            </a>
            <?php endif; ?>
            <?php if ($reviews !== ''): ?>
            <a href="<?= htmlspecialchars($reviews) ?>" class="sh-btn sh-btn-primary sh-btn-sm" target="_blank" rel="noopener noreferrer">
                <i class="fas fa-star" aria-hidden="true"></i> <?= htmlspecialchars($labels['leave_review'] ?? 'Leave a review') ?>
            </a>
            <?php endif; ?>
        </div>
        <?php if ($mapUrl !== ''): ?>
        <div class="sh-gmb-map">
            <iframe src="<?= htmlspecialchars($mapUrl, ENT_QUOTES) ?>" title="<?= htmlspecialchars($name !== '' ? $name : 'Map') ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
        <?php endif; ?>
    </aside>
    <?php
}

/** @param array<string, mixed>|null $settings */
function sh_gmb_footer_link_label(?array $settings = null): string
{
    $s = sh_google_marketing_merge_settings($settings);
    $name = trim((string) ($s['gmb_business_name'] ?? ''));
    return $name !== '' ? $name : 'Google Maps';
}