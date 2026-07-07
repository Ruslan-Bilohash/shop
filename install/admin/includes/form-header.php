<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/menu-settings.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'header';
$sections = sh_admin_settings_sections($tab, $ta);
if (function_exists('sh_menu_settings')) {
    $menu = sh_menu_settings($settings);
    $navLinks = $menu['header_nav_links'] ?? [];
} else {
    $navLinks = function_exists('sh_header_nav_links_defaults') ? sh_header_nav_links_defaults() : [];
}
if ($navLinks === [] && function_exists('sh_header_nav_links_defaults')) {
    $navLinks = sh_header_nav_links_defaults();
}

function sh_render_header_nav_row(int $i, array $link, array $ta): void
{
    ?>
    <div class="adm-footer-link-row adm-header-nav-row" data-row="<?= (int) $i ?>">
        <input type="hidden" name="header_nav_idx[]" value="<?= (int) $i ?>">
        <div class="adm-footer-link-row-head">
            <strong>#<?= (int) $i + 1 ?></strong>
            <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-header-nav-remove" aria-label="Remove">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="adm-form-grid">
            <div class="adm-field">
                <label><?= htmlspecialchars(sh_settings_admin_label('header_link_id', $ta)) ?></label>
                <input type="text" name="header_nav_id_<?= (int) $i ?>"
                       value="<?= htmlspecialchars($link['id'] ?? '') ?>" placeholder="sale">
            </div>
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('header_link_url', $ta)) ?></label>
                <input type="text" name="header_nav_url_<?= (int) $i ?>"
                       value="<?= htmlspecialchars($link['url'] ?? '') ?>" placeholder="search.php?sale=1">
            </div>
            <?php
            sh_admin_toggle_grid([
                ['name' => 'header_nav_active_' . $i, 'label' => sh_settings_admin_label('header_link_active', $ta), 'checked' => ($link['active'] ?? true) !== false],
                ['name' => 'header_nav_external_' . $i, 'label' => sh_settings_admin_label('header_link_external', $ta), 'checked' => !empty($link['external'])],
            ]);
            ?>
        </div>
        <details class="adm-spoiler adm-spoiler-nested">
            <summary><?= htmlspecialchars(sh_settings_admin_label('header_link_labels', $ta)) ?></summary>
            <div class="adm-spoiler-body">
                <div class="adm-form-grid">
                    <?php foreach (sh_langs() as $code => $info): ?>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($info['label']) ?></label>
                        <input type="text" name="header_nav_label_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>"
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
<form method="post" class="adm-settings-form" id="shHeaderNavForm">
    <div class="adm-card adm-settings-section" id="header-nav">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-bars"></i> <?= htmlspecialchars($sections['header-nav'] ?? sh_settings_admin_label('header_nav_section', $ta)) ?></h2>
            <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" id="shHeaderNavAdd">
                <i class="fas fa-plus"></i> <?= htmlspecialchars(sh_settings_admin_label('header_add_link', $ta)) ?>
            </button>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('header_nav_help', $ta)) ?></p>
            <div class="sh-header-nav-rows" id="shHeaderNavRows">
                <?php foreach ($navLinks as $i => $link): ?>
                <?php sh_render_header_nav_row((int) $i, $link, $ta); ?>
                <?php endforeach; ?>
            </div>
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('header_nav_note', $ta)) ?></p>
            <p><a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('header_preview', $ta)) ?></a></p>
        </div>
    </div>

    <div class="adm-card adm-settings-section" id="header-actions">
        <div class="adm-card-head">
            <h2><i class="fas fa-user"></i> <?= htmlspecialchars(sh_settings_admin_label('header_actions_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded-compact">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('header_actions_help', $ta)) ?></p>
            <?php sh_admin_toggle_section(
                '',
                [
                    ['name' => 'menu_show_signin', 'label' => sh_settings_admin_label('menu_show_signin', $ta), 'checked' => !empty($menu['menu_show_signin'])],
                    ['name' => 'menu_show_admin', 'label' => sh_settings_admin_label('menu_show_admin', $ta), 'checked' => !empty($menu['menu_show_admin'])],
                ],
                'user'
            ); ?>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>

<template id="shHeaderNavRowTemplate">
    <div class="adm-footer-link-row adm-header-nav-row" data-row="__IDX__">
        <input type="hidden" name="header_nav_idx[]" value="__IDX__">
        <div class="adm-footer-link-row-head">
            <strong>#__NUM__</strong>
            <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-header-nav-remove"><i class="fas fa-trash"></i></button>
        </div>
        <div class="adm-form-grid">
            <div class="adm-field">
                <label><?= htmlspecialchars(sh_settings_admin_label('header_link_id', $ta)) ?></label>
                <input type="text" name="header_nav_id___IDX__" placeholder="custom-link">
            </div>
            <div class="adm-field adm-field--wide">
                <label><?= htmlspecialchars(sh_settings_admin_label('header_link_url', $ta)) ?></label>
                <input type="text" name="header_nav_url___IDX__" placeholder="page.php?slug=delivery">
            </div>
            <div class="adm-toggle-grid adm-toggle-grid--dense">
                <label class="adm-toggle adm-toggle--compact">
                    <input type="checkbox" name="header_nav_active___IDX__" value="1" checked>
                    <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                    <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('header_link_active', $ta)) ?></span>
                </label>
                <label class="adm-toggle adm-toggle--compact">
                    <input type="checkbox" name="header_nav_external___IDX__" value="1">
                    <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                    <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('header_link_external', $ta)) ?></span>
                </label>
            </div>
        </div>
        <details class="adm-spoiler adm-spoiler-nested">
            <summary><?= htmlspecialchars(sh_settings_admin_label('header_link_labels', $ta)) ?></summary>
            <div class="adm-spoiler-body">
                <div class="adm-form-grid">
                    <?php foreach (sh_langs() as $code => $info): ?>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($info['label']) ?></label>
                        <input type="text" name="header_nav_label_<?= htmlspecialchars($code) ?>___IDX__" value="">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </details>
    </div>
</template>