<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/homepage-blocks.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'homepage';
$sections = sh_admin_settings_sections($tab, $ta);
$blocks = sh_home_blocks_from_settings($settings);
$types = sh_home_block_types();
?>
<div class="adm-alert adm-alert-info adm-block-builder-promo">
    <i class="fas fa-wand-magic-sparkles"></i>
    <?= htmlspecialchars(sh_settings_admin_label('homepage_builder_promo', $ta)) ?>
    <a href="<?= htmlspecialchars(sh_admin_url('settings-block-builder.php')) ?>" class="adm-btn adm-btn-primary adm-btn-sm">
        <i class="fas fa-pen-ruler"></i> <?= htmlspecialchars(sh_settings_admin_label('settings_tab_block_builder', $ta)) ?>
    </a>
</div>
<form method="post" class="adm-settings-form" id="shHomepageForm">
    <div class="adm-card adm-settings-section" id="homepage-blocks">
        <div class="adm-card-head">
            <h2><i class="fas fa-house"></i> <?= htmlspecialchars($sections['homepage-blocks'] ?? sh_settings_admin_label('homepage_blocks_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><i class="fas fa-grip-vertical"></i> <?= htmlspecialchars(sh_settings_admin_label('homepage_drag_hint', $ta)) ?></p>
            <div id="shHomeBlocksList" class="adm-home-blocks-list">
                <?php foreach ($blocks as $i => $block):
                    require __DIR__ . '/homepage-block-row.php';
                endforeach; ?>
            </div>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
        <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('homepage_preview', $ta)) ?></a>
    </div>
</form>