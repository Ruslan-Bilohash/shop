<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/google-marketing.php';
require_once __DIR__ . '/admin-field-help.php';
$tab = 'analytics';
$sections = sh_admin_settings_sections($tab, $ta);
$settings = sh_google_marketing_merge_settings($settings);
?>
<form method="post" class="adm-settings-form">
    <?php sh_admin_section_open($tab, 'analytics-tracking', $sections['analytics-tracking'] ?? sh_settings_admin_label('analytics_section', $ta), 'chart-pie', $ta); ?>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('tracking_gtag_id', $ta)) ?></label>
                    <input type="text" name="tracking_gtag_id" value="<?= htmlspecialchars($settings['tracking_gtag_id'] ?? '') ?>" placeholder="G-XXXXXXXX">
                    <?php sh_admin_render_field_hint($tab, 'tracking_gtag_id', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('tracking_meta_pixel', $ta)) ?></label>
                    <input type="text" name="tracking_meta_pixel" value="<?= htmlspecialchars($settings['tracking_meta_pixel'] ?? '') ?>" placeholder="Meta Pixel ID">
                    <?php sh_admin_render_field_hint($tab, 'tracking_meta_pixel', $ta); ?>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'analytics-google-ads', $sections['analytics-google-ads'] ?? sh_settings_admin_label('google_ads_section', $ta), 'bullhorn', $ta); ?>
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('google_ads_help', $ta)) ?></p>
            <label class="adm-toggle">
                <input type="checkbox" name="google_ads_enabled" value="1" <?= !empty($settings['google_ads_enabled']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('google_ads_enabled', $ta)) ?></span>
            </label>
            <div class="adm-form-grid">
                <div class="adm-field">
                    <label for="shGoogleAdsId"><?= htmlspecialchars(sh_settings_admin_label('google_ads_id', $ta)) ?></label>
                    <input type="text" name="google_ads_id" id="shGoogleAdsId" value="<?= htmlspecialchars($settings['google_ads_id'] ?? '') ?>" placeholder="AW-XXXXXXXXX">
                    <?php sh_admin_render_field_hint($tab, 'google_ads_id', $ta); ?>
                </div>
                <div class="adm-field">
                    <label for="shGoogleAdsLabel"><?= htmlspecialchars(sh_settings_admin_label('google_ads_conversion_label', $ta)) ?></label>
                    <input type="text" name="google_ads_conversion_label" id="shGoogleAdsLabel" value="<?= htmlspecialchars($settings['google_ads_conversion_label'] ?? '') ?>" placeholder="AbCdEfGhIjKlMnOpQr">
                    <?php sh_admin_render_field_hint($tab, 'google_ads_conversion_label', $ta); ?>
                </div>
            </div>
            <label class="adm-toggle">
                <input type="checkbox" name="google_ads_remarketing" value="1" <?= !empty($settings['google_ads_remarketing']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('google_ads_remarketing', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="google_ads_track_purchase" value="1" <?= !empty($settings['google_ads_track_purchase']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('google_ads_track_purchase', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="google_ads_track_begin_checkout" value="1" <?= !empty($settings['google_ads_track_begin_checkout']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('google_ads_track_begin_checkout', $ta)) ?></span>
            </label>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'analytics-gmb', $sections['analytics-gmb'] ?? sh_settings_admin_label('gmb_section', $ta), 'map-location-dot', $ta); ?>
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('gmb_help', $ta)) ?></p>
            <label class="adm-toggle">
                <input type="checkbox" name="gmb_enabled" value="1" <?= !empty($settings['gmb_enabled']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('gmb_enabled', $ta)) ?></span>
            </label>
            <div class="adm-form-grid">
                <div class="adm-field adm-field--wide">
                    <label for="shGmbName"><?= htmlspecialchars(sh_settings_admin_label('gmb_business_name', $ta)) ?></label>
                    <input type="text" name="gmb_business_name" id="shGmbName" value="<?= htmlspecialchars($settings['gmb_business_name'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbPlaceId"><?= htmlspecialchars(sh_settings_admin_label('gmb_place_id', $ta)) ?></label>
                    <input type="text" name="gmb_place_id" id="shGmbPlaceId" value="<?= htmlspecialchars($settings['gmb_place_id'] ?? '') ?>" placeholder="ChIJ…">
                    <?php sh_admin_render_field_hint($tab, 'gmb_place_id', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shGmbAddress"><?= htmlspecialchars(sh_settings_admin_label('gmb_address', $ta)) ?></label>
                    <input type="text" name="gmb_address" id="shGmbAddress" value="<?= htmlspecialchars($settings['gmb_address'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbCity"><?= htmlspecialchars(sh_settings_admin_label('gmb_city', $ta)) ?></label>
                    <input type="text" name="gmb_city" id="shGmbCity" value="<?= htmlspecialchars($settings['gmb_city'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbPostal"><?= htmlspecialchars(sh_settings_admin_label('gmb_postal', $ta)) ?></label>
                    <input type="text" name="gmb_postal" id="shGmbPostal" value="<?= htmlspecialchars($settings['gmb_postal'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbCountry"><?= htmlspecialchars(sh_settings_admin_label('gmb_country', $ta)) ?></label>
                    <input type="text" name="gmb_country" id="shGmbCountry" value="<?= htmlspecialchars($settings['gmb_country'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbPhone"><?= htmlspecialchars(sh_settings_admin_label('gmb_phone', $ta)) ?></label>
                    <input type="text" name="gmb_phone" id="shGmbPhone" value="<?= htmlspecialchars($settings['gmb_phone'] ?? '') ?>">
                </div>
                <div class="adm-field">
                    <label for="shGmbLat"><?= htmlspecialchars(sh_settings_admin_label('gmb_latitude', $ta)) ?></label>
                    <input type="text" name="gmb_latitude" id="shGmbLat" value="<?= htmlspecialchars($settings['gmb_latitude'] ?? '') ?>" placeholder="59.7440">
                </div>
                <div class="adm-field">
                    <label for="shGmbLng"><?= htmlspecialchars(sh_settings_admin_label('gmb_longitude', $ta)) ?></label>
                    <input type="text" name="gmb_longitude" id="shGmbLng" value="<?= htmlspecialchars($settings['gmb_longitude'] ?? '') ?>" placeholder="10.2045">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shGmbProfile"><?= htmlspecialchars(sh_settings_admin_label('gmb_profile_url', $ta)) ?></label>
                    <input type="url" name="gmb_profile_url" id="shGmbProfile" value="<?= htmlspecialchars($settings['gmb_profile_url'] ?? '') ?>" placeholder="https://maps.google.com/…">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shGmbReviews"><?= htmlspecialchars(sh_settings_admin_label('gmb_reviews_url', $ta)) ?></label>
                    <input type="url" name="gmb_reviews_url" id="shGmbReviews" value="<?= htmlspecialchars($settings['gmb_reviews_url'] ?? '') ?>" placeholder="https://g.page/r/…/review">
                    <?php sh_admin_render_field_hint($tab, 'gmb_reviews_url', $ta); ?>
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shGmbHours"><?= htmlspecialchars(sh_settings_admin_label('gmb_opening_hours', $ta)) ?></label>
                    <input type="text" name="gmb_opening_hours" id="shGmbHours" value="<?= htmlspecialchars($settings['gmb_opening_hours'] ?? '') ?>">
                </div>
                <div class="adm-field adm-field--wide">
                    <label for="shGmbEmbed"><?= htmlspecialchars(sh_settings_admin_label('gmb_map_embed', $ta)) ?></label>
                    <textarea name="gmb_map_embed" id="shGmbEmbed" rows="2" class="adm-textarea" placeholder="<iframe …>"><?= htmlspecialchars($settings['gmb_map_embed'] ?? '') ?></textarea>
                    <?php sh_admin_render_field_hint($tab, 'gmb_map_embed', $ta); ?>
                </div>
            </div>
            <label class="adm-toggle">
                <input type="checkbox" name="gmb_schema" value="1" <?= !empty($settings['gmb_schema']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('gmb_schema', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="gmb_show_contact" value="1" <?= !empty($settings['gmb_show_contact']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('gmb_show_contact', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="gmb_show_footer" value="1" <?= !empty($settings['gmb_show_footer']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('gmb_show_footer', $ta)) ?></span>
            </label>
            <label class="adm-toggle">
                <input type="checkbox" name="gmb_show_map" value="1" <?= !empty($settings['gmb_show_map']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars(sh_settings_admin_label('gmb_show_map', $ta)) ?></span>
            </label>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>