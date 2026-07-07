<?php
/** @var array $settings @var array $ta */
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'seo';
$sections = sh_admin_settings_sections($tab, $ta);
$sitemapUrl = sh_absolute_url(sh_url('sitemap.xml'));
?>
<form method="post" class="adm-settings-form" id="shSeoSettingsForm"
      data-ai-url="<?= htmlspecialchars(sh_admin_url('api/ai-seo.php')) ?>">
    <div class="adm-card adm-ai-source-card">
        <div class="adm-card-body padded">
            <div class="adm-ai-source-box">
                <div class="adm-ai-source-fields">
                    <div class="adm-field adm-field--wide">
                        <label for="shAiBrandName"><?= htmlspecialchars(sh_settings_admin_label('seo_ai_brand_label', $ta)) ?></label>
                        <input type="text" id="shAiBrandName" value="<?= htmlspecialchars($settings['seo_site_name'] ?? '') ?>"
                               placeholder="<?= htmlspecialchars(sh_settings_admin_label('seo_site_name', $ta)) ?>">
                        <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('seo_ai_brand_hint', $ta)) ?></small>
                    </div>
                </div>
                <div class="adm-ai-source-actions adm-seo-ai-actions">
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-ai-generate adm-btn--seo-ai" id="shAiSeoSiteBtn"
                            data-generating="<?= htmlspecialchars(sh_settings_admin_label('seo_ai_generating', $ta)) ?>"
                            data-ok="<?= htmlspecialchars(sh_settings_admin_label('seo_ai_ok', $ta)) ?>"
                            data-demo-ok="<?= htmlspecialchars(sh_settings_admin_label('seo_ai_demo_ok', $ta)) ?>"
                            data-failed="<?= htmlspecialchars(sh_settings_admin_label('seo_ai_failed', $ta)) ?>"
                            data-need-brand="<?= htmlspecialchars(sh_settings_admin_label('seo_ai_need_brand', $ta)) ?>">
                        <i class="fas fa-wand-magic-sparkles adm-ai-btn-icon" aria-hidden="true"></i>
                        <span class="adm-ai-btn-label"><?= htmlspecialchars(sh_settings_admin_label('seo_ai_generate', $ta)) ?></span>
                    </button>
                    <span id="shAiSeoSiteStatus" class="adm-ai-status adm-ai-status--block" hidden></span>
                </div>
            </div>
        </div>
    </div>

    <?php sh_admin_section_open($tab, 'seo-global', $sections['seo-global'] ?? sh_settings_admin_label('seo_section', $ta), 'globe', $ta); ?>
            <div class="adm-form-grid adm-form-grid--settings">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_site_name', $ta)) ?></label>
                    <input type="text" name="seo_site_name" id="seo_site_name" value="<?= htmlspecialchars($settings['seo_site_name'] ?? '') ?>" placeholder="Shop CMS">
                    <?php sh_admin_render_field_hint($tab, 'seo_site_name', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_org_name', $ta)) ?></label>
                    <input type="text" name="seo_org_name" id="seo_org_name" value="<?= htmlspecialchars($settings['seo_org_name'] ?? '') ?>" placeholder="Shop CMS">
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_default_og_image', $ta)) ?></label>
                    <input type="url" name="seo_default_og_image" value="<?= htmlspecialchars($settings['seo_default_og_image'] ?? '') ?>" placeholder="https://yourdomain.com/og-image.jpg" inputmode="url" autocomplete="url">
                    <?php sh_admin_render_field_hint($tab, 'seo_default_og_image', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_geo_region', $ta)) ?></label>
                    <input type="text" name="seo_geo_region" id="seo_geo_region" value="<?= htmlspecialchars($settings['seo_geo_region'] ?? 'NO') ?>" maxlength="8" autocapitalize="characters">
                    <?php sh_admin_render_field_hint($tab, 'seo_geo_region', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_geo_placename', $ta)) ?></label>
                    <input type="text" name="seo_geo_placename" id="seo_geo_placename" value="<?= htmlspecialchars($settings['seo_geo_placename'] ?? 'Norway') ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_twitter_site', $ta)) ?></label>
                    <input type="text" name="seo_twitter_site" id="seo_twitter_site" value="<?= htmlspecialchars($settings['seo_twitter_site'] ?? '') ?>" placeholder="@yourbrand">
                    <?php sh_admin_render_field_hint($tab, 'seo_twitter_site', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('seo_default_country_code', $ta)) ?></label>
                    <input type="text" name="seo_default_country_code" id="seo_default_country_code" value="<?= htmlspecialchars($settings['seo_default_country_code'] ?? 'NO') ?>" maxlength="2" class="adm-input-upper" autocapitalize="characters">
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'seo-schema', $sections['seo-schema'] ?? 'Structured data', 'code', $ta, sh_settings_admin_label('seo_product_note', $ta)); ?>
            <?php
            $schemaToggles = [];
            foreach ([
                'seo_schema_organization', 'seo_schema_website', 'seo_schema_product',
                'seo_schema_breadcrumbs', 'seo_schema_itemlist',
            ] as $labelKey) {
                $schemaToggles[] = [
                    'name' => $labelKey,
                    'label' => sh_settings_admin_label($labelKey, $ta),
                    'checked' => !empty($settings[$labelKey]),
                ];
            }
            sh_admin_toggle_section($sections['seo-schema'] ?? 'Structured data', $schemaToggles, 'code');
            ?>
    <?php sh_admin_section_close(); ?>

    <?php sh_admin_section_open($tab, 'seo-sitemap', $sections['seo-sitemap'] ?? sh_settings_admin_label('seo_sitemap_section', $ta), 'sitemap', $ta, sh_settings_admin_label('seo_sitemap_help', $ta)); ?>
            <?php
            $sitemapToggles = [];
            foreach ([
                'sitemap_enabled', 'sitemap_include_products', 'sitemap_include_categories', 'sitemap_include_verticals', 'sitemap_include_news',
            ] as $labelKey) {
                $sitemapToggles[] = [
                    'name' => $labelKey,
                    'label' => sh_settings_admin_label($labelKey, $ta),
                    'checked' => !empty($settings[$labelKey]),
                ];
            }
            sh_admin_toggle_section($sections['seo-sitemap'] ?? sh_settings_admin_label('seo_sitemap_section', $ta), $sitemapToggles, 'sitemap');
            ?>
            <div class="adm-form-grid">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('sitemap_priority_home', $ta)) ?></label>
                    <input type="text" name="sitemap_priority_home" value="<?= htmlspecialchars($settings['sitemap_priority_home'] ?? '1.0') ?>" inputmode="decimal">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('sitemap_priority_product', $ta)) ?></label>
                    <input type="text" name="sitemap_priority_product" value="<?= htmlspecialchars($settings['sitemap_priority_product'] ?? '0.8') ?>" inputmode="decimal">
                    <?php sh_admin_render_field_hint($tab, 'sitemap_priority_product', $ta); ?>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('sitemap_priority_category', $ta)) ?></label>
                    <input type="text" name="sitemap_priority_category" value="<?= htmlspecialchars($settings['sitemap_priority_category'] ?? '0.85') ?>" inputmode="decimal">
                </div>
            </div>
            <div class="adm-sitemap-bar">
                <div class="adm-sitemap-url">
                    <span class="adm-sitemap-url-label"><?= htmlspecialchars(sh_settings_admin_label('sitemap_index_url', $ta)) ?>:</span>
                    <code id="shSitemapUrl"><?= htmlspecialchars($sitemapUrl) ?></code>
                </div>
                <?php if (!empty($settings['sitemap_last_generated'])): ?>
                <p class="adm-sitemap-meta">
                    <?= htmlspecialchars(sh_settings_admin_label('sitemap_last_generated', $ta)) ?>:
                    <strong><?= htmlspecialchars($settings['sitemap_last_generated']) ?></strong>
                </p>
                <?php endif; ?>
                <div class="adm-sitemap-actions">
                    <button type="submit" name="regenerate_sitemap" value="1" class="adm-btn adm-btn--sitemap-regen" formnovalidate>
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>
                        <?= htmlspecialchars(sh_settings_admin_label('sitemap_regenerate', $ta)) ?>
                    </button>
                    <a href="<?= htmlspecialchars($sitemapUrl) ?>" class="adm-btn adm-btn--sitemap-open" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                        <?= htmlspecialchars(sh_settings_admin_label('sitemap_open', $ta)) ?>
                    </a>
                    <button type="button" class="adm-btn adm-btn--sitemap-copy" id="shSitemapCopyBtn"
                            data-url="<?= htmlspecialchars($sitemapUrl) ?>"
                            data-copied="<?= htmlspecialchars(sh_settings_admin_label('sitemap_copied', $ta)) ?>">
                        <i class="fas fa-copy" aria-hidden="true"></i>
                        <?= htmlspecialchars(sh_settings_admin_label('sitemap_copy_url', $ta)) ?>
                    </button>
                </div>
            </div>
    <?php sh_admin_section_close(); ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>