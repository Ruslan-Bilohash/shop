<?php

/** @param array<string, mixed> $ta */
function sh_admin_settings_tab_meta(string $tab, array $ta): array
{
    $guides = is_array($ta['settings_guides'] ?? null) ? $ta['settings_guides'] : [];
    return is_array($guides[$tab] ?? null) ? $guides[$tab] : [];
}

/** @param array<string, mixed> $ta */
function sh_admin_settings_intro(string $tab, array $ta): string
{
    $meta = sh_admin_settings_tab_meta($tab, $ta);
    if (!empty($meta['intro'])) {
        return (string) $meta['intro'];
    }
    $key = $tab . '_page_intro';
    if (!empty($ta[$key])) {
        return (string) $ta[$key];
    }
    $legacy = sh_settings_admin_label($tab . '_help', $ta);
    if ($legacy !== $tab . '_help' && $legacy !== '') {
        return $legacy;
    }
    return '';
}

/** @param array<string, mixed> $ta */
function sh_admin_settings_sections(string $tab, array $ta): array
{
    $meta = sh_admin_settings_tab_meta($tab, $ta);
    return is_array($meta['sections'] ?? null) ? $meta['sections'] : [];
}

/** @param array<string, mixed> $ta */
function sh_admin_field_hint(string $tab, string $key, array $ta): string
{
    $meta = sh_admin_settings_tab_meta($tab, $ta);
    $hints = is_array($meta['hints'] ?? null) ? $meta['hints'] : [];
    if (!empty($hints[$key])) {
        return (string) $hints[$key];
    }
    $legacy = $ta[$key . '_hint'] ?? '';
    return is_string($legacy) ? $legacy : '';
}

/** @param array<string, mixed> $ta */
function sh_admin_render_field_hint(string $tab, string $key, array $ta): void
{
    $hint = sh_admin_field_hint($tab, $key, $ta);
    if ($hint === '') {
        return;
    }
    echo '<small class="adm-field-hint">' . htmlspecialchars($hint) . '</small>';
}

/** @param array<string, mixed> $guide */
function sh_admin_render_guide_panel(array $guide, array $labels = []): void
{
    if ($guide === []) {
        return;
    }
    ?>
    <aside class="adm-settings-guide">
        <div class="adm-card adm-card--guide">
            <div class="adm-card-head">
                <h3><i class="fas fa-book-open"></i> <?= htmlspecialchars($guide['title'] ?? ($labels['guide_title'] ?? 'Setup guide')) ?></h3>
            </div>
            <div class="adm-card-body padded adm-guide-body">
                <?php if (!empty($guide['intro'])): ?>
                <p class="adm-guide-intro"><?= htmlspecialchars($guide['intro']) ?></p>
                <?php endif; ?>
                <?php if (!empty($guide['steps']) && is_array($guide['steps'])): ?>
                <ol class="adm-guide-steps">
                    <?php foreach ($guide['steps'] as $step): ?>
                    <li><?= htmlspecialchars($step) ?></li>
                    <?php endforeach; ?>
                </ol>
                <?php endif; ?>
                <?php if (!empty($guide['links']) && is_array($guide['links'])): ?>
                <div class="adm-guide-links">
                    <strong><?= htmlspecialchars($labels['useful_links'] ?? 'Useful links') ?></strong>
                    <ul>
                        <?php foreach ($guide['links'] as $link): ?>
                        <li>
                            <a href="<?= htmlspecialchars($link['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                                <?= htmlspecialchars($link['label'] ?? '') ?> <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($guide['note'])): ?>
                <p class="adm-guide-note"><i class="fas fa-lightbulb" aria-hidden="true"></i> <?= htmlspecialchars($guide['note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </aside>
    <?php
}

/** @param array<string, mixed> $ta */
function sh_admin_render_settings_toc(string $tab, array $ta): void
{
    $sections = sh_admin_settings_sections($tab, $ta);
    if ($sections === []) {
        return;
    }
    $label = sh_settings_admin_label('settings_toc_jump', $ta);
    ?>
    <nav class="adm-settings-toc" aria-label="<?= htmlspecialchars($label) ?>">
        <span class="adm-settings-toc-label"><i class="fas fa-list-ul"></i> <?= htmlspecialchars($label) ?>:</span>
        <div class="adm-settings-toc-links">
            <?php foreach ($sections as $id => $title): ?>
            <a href="#<?= htmlspecialchars($id) ?>" class="adm-settings-toc-link"><?= htmlspecialchars($title) ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
    <?php
}

/** @param array<string, mixed> $ta */
function sh_admin_render_settings_intro(string $tab, array $ta): void
{
    $intro = sh_admin_settings_intro($tab, $ta);
    if ($intro === '') {
        return;
    }
    ?>
    <div class="adm-settings-intro adm-card">
        <div class="adm-card-body padded">
            <p class="adm-settings-intro-text"><i class="fas fa-circle-info"></i> <?= htmlspecialchars($intro) ?></p>
            <?php sh_admin_render_settings_toc($tab, $ta); ?>
        </div>
    </div>
    <?php
}

/** @param array<string, mixed> $ta */
function sh_admin_render_settings_guide(string $tab, array $ta): void
{
    $meta = sh_admin_settings_tab_meta($tab, $ta);
    $guide = is_array($meta['guide'] ?? null) ? $meta['guide'] : [];
    sh_admin_render_guide_panel($guide, [
        'guide_title'  => sh_settings_admin_label('settings_guide_title', $ta),
        'useful_links' => sh_settings_admin_label('settings_useful_links', $ta),
    ]);
}

/** @param array<string, mixed> $ta */
function sh_admin_section_open(string $tab, string $id, string $title, string $icon = '', array $ta = [], string $desc = ''): void
{
    $sections = sh_admin_settings_sections($tab, $ta);
    if ($desc === '' && isset($sections[$id])) {
        $desc = '';
    }
    ?>
    <div class="adm-card adm-settings-section" id="<?= htmlspecialchars($id) ?>">
        <div class="adm-card-head">
            <h2><?php if ($icon !== ''): ?><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i> <?php endif; ?><?= htmlspecialchars($title) ?></h2>
        </div>
        <div class="adm-card-body padded">
    <?php if ($desc !== ''): ?>
            <p class="adm-help"><?= htmlspecialchars($desc) ?></p>
    <?php endif;
}

function sh_admin_section_close(): void
{
    echo '</div></div>';
}