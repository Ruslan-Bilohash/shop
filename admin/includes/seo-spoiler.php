<?php
/**
 * Reusable SEO & Schema spoiler for product/category edit forms.
 * Vars: $seo_ctx ('product'|'category'|'news'), $seo_record (array), $seo_tp (labels), $langs from sh_langs()
 */
$seo = is_array($seo_record['seo'] ?? null) ? $seo_record['seo'] : [];
$schema = is_array($seo['schema'] ?? null) ? $seo['schema'] : [];
$spoiler_title = $seo_tp['spoiler_title'] ?? 'SEO & Schema.org';
$spoiler_help = $seo_tp['spoiler_help'] ?? '';
$seo_panel_mode = !empty($seo_panel_mode);
?>
<?php if (!$seo_panel_mode): ?><details class="adm-spoiler"><?php endif; ?>
<?php if (!$seo_panel_mode): ?>
    <summary><i class="fas fa-chart-line"></i> <?= htmlspecialchars($spoiler_title) ?></summary>
<?php endif; ?>
    <div class="<?= $seo_panel_mode ? 'adm-card-body padded' : 'adm-spoiler-body' ?>">
        <?php if ($spoiler_help !== ''): ?>
        <p class="adm-help"><?= htmlspecialchars($spoiler_help) ?></p>
        <?php endif; ?>

        <h3 class="adm-spoiler-sub"><?= htmlspecialchars($seo_tp['meta_per_lang'] ?? 'Meta per language') ?></h3>
        <?php foreach (sh_langs() as $code => $info): ?>
        <details class="adm-spoiler adm-spoiler-nested">
            <summary><?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label']) ?>)</summary>
            <div class="adm-spoiler-body">
                <div class="adm-form-grid">
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($seo_tp['meta_title'] ?? 'Meta title') ?></label>
                        <input type="text" name="seo_meta_title_<?= htmlspecialchars($code) ?>"
                               value="<?= htmlspecialchars($seo['meta_title'][$code] ?? '') ?>"
                               maxlength="70" placeholder="<?= htmlspecialchars($seo_tp['meta_title_ph'] ?? '') ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($seo_tp['meta_description'] ?? 'Meta description') ?></label>
                        <textarea name="seo_meta_description_<?= htmlspecialchars($code) ?>" rows="3" maxlength="320"
                                  placeholder="<?= htmlspecialchars($seo_tp['meta_description_ph'] ?? '') ?>"><?= htmlspecialchars($seo['meta_description'][$code] ?? '') ?></textarea>
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($seo_tp['meta_keywords'] ?? 'Meta keywords (optional)') ?></label>
                        <input type="text" name="seo_meta_keywords_<?= htmlspecialchars($code) ?>"
                               value="<?= htmlspecialchars($seo['meta_keywords'][$code] ?? '') ?>"
                               placeholder="<?= htmlspecialchars($seo_tp['meta_keywords_ph'] ?? '') ?>">
                    </div>
                    <?php if ($seo_ctx === 'category'): ?>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($seo_tp['intro'] ?? 'Category intro (landing text)') ?></label>
                        <textarea name="seo_intro_<?= htmlspecialchars($code) ?>" rows="3"
                                  placeholder="<?= htmlspecialchars($seo_tp['intro_ph'] ?? '') ?>"><?= htmlspecialchars($seo['intro'][$code] ?? '') ?></textarea>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </details>
        <?php endforeach; ?>

        <h3 class="adm-spoiler-sub"><?= htmlspecialchars($seo_tp['global_fields'] ?? 'Global SEO fields') ?></h3>
        <div class="adm-form-grid">
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars($seo_tp['og_image'] ?? 'Open Graph image URL') ?></label>
                <input type="url" name="seo_og_image" value="<?= htmlspecialchars($seo['og_image'] ?? '') ?>"
                       placeholder="https://…" inputmode="url">
            </div>
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars($seo_tp['canonical_override'] ?? 'Canonical URL override (optional)') ?></label>
                <input type="url" name="seo_canonical_override" value="<?= htmlspecialchars($seo['canonical_override'] ?? '') ?>"
                       placeholder="https://…" inputmode="url">
            </div>
            <?php if ($seo_ctx === 'product'): ?>
            <div class="adm-field">
                <label><?= htmlspecialchars($seo_tp['brand'] ?? 'Brand (Schema.org)') ?></label>
                <input type="text" name="seo_brand" value="<?= htmlspecialchars($seo['brand'] ?? '') ?>">
            </div>
            <div class="adm-field">
                <label><?= htmlspecialchars($seo_tp['gtin'] ?? 'GTIN / EAN') ?></label>
                <input type="text" name="seo_gtin" value="<?= htmlspecialchars($seo['gtin'] ?? '') ?>">
            </div>
            <div class="adm-field">
                <label><?= htmlspecialchars($seo_tp['mpn'] ?? 'MPN') ?></label>
                <input type="text" name="seo_mpn" value="<?= htmlspecialchars($seo['mpn'] ?? '') ?>">
            </div>
            <?php endif; ?>
        </div>

        <?php
        require_once __DIR__ . '/toggle-field.php';
        $schemaToggles = match ($seo_ctx) {
            'product' => [
                ['name' => 'seo_schema_product', 'label' => $seo_tp['schema_product'] ?? 'Product schema', 'checked' => ($schema['product'] ?? true)],
                ['name' => 'seo_schema_offer', 'label' => $seo_tp['schema_offer'] ?? 'Offer schema', 'checked' => ($schema['offer'] ?? true)],
                ['name' => 'seo_schema_breadcrumb', 'label' => $seo_tp['schema_breadcrumb'] ?? 'BreadcrumbList', 'checked' => ($schema['breadcrumb'] ?? true)],
                ['name' => 'seo_schema_aggregate_rating', 'label' => $seo_tp['schema_aggregate_rating'] ?? 'AggregateRating (optional)', 'checked' => !empty($schema['aggregate_rating'])],
            ],
            'news' => [
                ['name' => 'seo_schema_news_article', 'label' => $seo_tp['schema_news_article'] ?? 'NewsArticle schema', 'checked' => ($schema['news_article'] ?? true)],
                ['name' => 'seo_schema_breadcrumb', 'label' => $seo_tp['schema_breadcrumb'] ?? 'BreadcrumbList', 'checked' => ($schema['breadcrumb'] ?? true)],
            ],
            default => [
                ['name' => 'seo_schema_collection', 'label' => $seo_tp['schema_collection'] ?? 'CollectionPage schema', 'checked' => ($schema['collection'] ?? true)],
                ['name' => 'seo_schema_itemlist', 'label' => $seo_tp['schema_itemlist'] ?? 'ItemList schema', 'checked' => ($schema['itemlist'] ?? true)],
                ['name' => 'seo_schema_breadcrumb', 'label' => $seo_tp['schema_breadcrumb'] ?? 'BreadcrumbList', 'checked' => ($schema['breadcrumb'] ?? true)],
            ],
        };
        sh_admin_toggle_section($seo_tp['schema_toggles'] ?? 'Structured data toggles', $schemaToggles, 'code');
        ?>

        <?php if ($seo_ctx === 'product'): ?>
        <div class="adm-form-grid">
            <div class="adm-field">
                <label><?= htmlspecialchars($seo_tp['rating_value'] ?? 'Rating value (1–5)') ?></label>
                <input type="text" name="seo_rating_value" value="<?= htmlspecialchars($seo['rating_value'] ?? '') ?>" inputmode="decimal" placeholder="4.8">
            </div>
            <div class="adm-field">
                <label><?= htmlspecialchars($seo_tp['rating_count'] ?? 'Review count') ?></label>
                <input type="number" name="seo_rating_count" value="<?= htmlspecialchars((string)($seo['rating_count'] ?? '')) ?>" min="0" step="1" placeholder="12">
            </div>
        </div>
        <?php endif; ?>
    </div>
<?php if (!$seo_panel_mode): ?>
</details>
<?php endif; ?>