<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/service-pages.php';
require_once __DIR__ . '/admin-field-help.php';
$tab = 'footer';
$footerSections = sh_admin_settings_sections($tab, $ta);
$settings = sh_merge_service_settings($settings);
$footer = $settings['footer_links'] ?? sh_footer_links_defaults();

function sh_render_footer_link_rows(string $col, array $rows, array $ta, array $sectionTitles = []): void
{
    $colLabel = sh_settings_admin_label('footer_col_' . $col, $ta);
    ?>
    <div class="adm-card adm-footer-col-card adm-settings-section" id="footer-<?= htmlspecialchars($col) ?>" data-footer-col="<?= htmlspecialchars($col) ?>">
        <div class="adm-card-head adm-card-head--stack">
            <h2><?= htmlspecialchars($sectionTitles['footer-' . $col] ?? $colLabel) ?></h2>
            <button type="button" class="adm-btn adm-btn-outline adm-btn-sm sh-footer-add" data-col="<?= htmlspecialchars($col) ?>">
                <i class="fas fa-plus"></i> <?= htmlspecialchars(sh_settings_admin_label('footer_add_link', $ta)) ?>
            </button>
        </div>
        <div class="adm-card-body padded sh-footer-rows" id="footer-rows-<?= htmlspecialchars($col) ?>">
            <?php foreach ($rows as $i => $link): ?>
            <?php sh_render_footer_link_row($col, (int) $i, $link, $ta); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function sh_render_footer_link_row(string $col, int $i, array $link, array $ta): void
{
    ?>
    <div class="adm-footer-link-row" data-row="<?= (int) $i ?>">
        <input type="hidden" name="footer_<?= htmlspecialchars($col) ?>_idx[]" value="<?= (int) $i ?>">
        <div class="adm-footer-link-row-head">
            <strong>#<?= (int) $i + 1 ?></strong>
            <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-footer-remove" aria-label="Remove">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="adm-form-grid">
            <div class="adm-field">
                <label><?= htmlspecialchars(sh_settings_admin_label('footer_link_id', $ta)) ?></label>
                <input type="text" name="footer_<?= htmlspecialchars($col) ?>_id_<?= (int) $i ?>"
                       value="<?= htmlspecialchars($link['id'] ?? '') ?>" placeholder="delivery">
            </div>
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('footer_link_url', $ta)) ?></label>
                <input type="text" name="footer_<?= htmlspecialchars($col) ?>_url_<?= (int) $i ?>"
                       value="<?= htmlspecialchars($link['url'] ?? '') ?>" placeholder="page.php?slug=delivery">
                <?php if ($i === 0): sh_admin_render_field_hint('footer', 'footer_link_url', $ta); endif; ?>
            </div>
            <?php
            require_once __DIR__ . '/toggle-field.php';
            sh_admin_toggle_grid([
                ['name' => 'footer_' . $col . '_active_' . $i, 'label' => sh_settings_admin_label('footer_link_active', $ta), 'checked' => ($link['active'] ?? true) !== false],
                ['name' => 'footer_' . $col . '_external_' . $i, 'label' => sh_settings_admin_label('footer_link_external', $ta), 'checked' => !empty($link['external'])],
            ]);
            ?>
        </div>
        <details class="adm-spoiler adm-spoiler-nested">
            <summary><?= htmlspecialchars(sh_settings_admin_label('footer_link_labels', $ta)) ?></summary>
            <div class="adm-spoiler-body">
                <div class="adm-form-grid">
                    <?php foreach (sh_langs() as $code => $info): ?>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($info['label']) ?></label>
                        <input type="text" name="footer_<?= htmlspecialchars($col) ?>_label_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>"
                               value="<?= htmlspecialchars($link['label'][$code] ?? '') ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>
    </div>
    <?php
}
?>
<form method="post" class="adm-settings-form" id="shFooterForm">
    <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('footer_help', $ta)) ?></p>

    <div class="adm-footer-links-layout">
        <?php sh_render_footer_link_rows('shop', $footer['shop'] ?? [], $ta, $footerSections); ?>
        <?php sh_render_footer_link_rows('legal', $footer['legal'] ?? [], $ta, $footerSections); ?>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>

<template id="shFooterRowTemplate">
    <div class="adm-footer-link-row" data-row="__IDX__">
        <input type="hidden" name="footer___COL___idx[]" value="__IDX__">
        <div class="adm-footer-link-row-head">
            <strong>#__NUM__</strong>
            <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-footer-remove"><i class="fas fa-trash"></i></button>
        </div>
        <div class="adm-form-grid">
            <div class="adm-field">
                <label><?= htmlspecialchars(sh_settings_admin_label('footer_link_id', $ta)) ?></label>
                <input type="text" name="footer___COL___id___IDX__" placeholder="link-id">
            </div>
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('footer_link_url', $ta)) ?></label>
                <input type="text" name="footer___COL___url___IDX__" placeholder="search.php">
            </div>
            <div class="adm-toggle-grid adm-toggle-grid--dense">
                <label class="adm-toggle adm-toggle--compact" title="<?= htmlspecialchars(sh_settings_admin_label('footer_link_active', $ta)) ?>">
                    <input type="checkbox" name="footer___COL___active___IDX__" value="1" checked>
                    <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                    <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('footer_link_active', $ta)) ?></span>
                </label>
                <label class="adm-toggle adm-toggle--compact" title="<?= htmlspecialchars(sh_settings_admin_label('footer_link_external', $ta)) ?>">
                    <input type="checkbox" name="footer___COL___external___IDX__" value="1">
                    <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                    <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('footer_link_external', $ta)) ?></span>
                </label>
            </div>
        </div>
        <details class="adm-spoiler adm-spoiler-nested">
            <summary><?= htmlspecialchars(sh_settings_admin_label('footer_link_labels', $ta)) ?></summary>
            <div class="adm-spoiler-body">
                <div class="adm-form-grid">
                    <?php foreach (sh_langs() as $code => $info): ?>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($info['label']) ?></label>
                        <input type="text" name="footer___COL___label_<?= htmlspecialchars($code) ?>___IDX__">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>
    </div>
</template>