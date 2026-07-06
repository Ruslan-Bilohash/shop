<?php
/**
 * Compact toggle field (same style as Product card settings).
 * @param string $name input name
 * @param string $labelKey translation key
 * @param bool $checked
 * @param array $ta admin labels
 */
function sh_admin_toggle(string $name, string $labelKey, bool $checked, array $ta): void
{
    sh_admin_toggle_label($name, sh_settings_admin_label($labelKey, $ta), $checked);
}

function sh_admin_toggle_label(string $name, string $label, bool $checked): void
{
    ?>
    <label class="adm-toggle adm-toggle--compact" title="<?= htmlspecialchars($label) ?>">
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="0">
        <input type="checkbox" name="<?= htmlspecialchars($name) ?>" value="1" <?= $checked ? 'checked' : '' ?>>
        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
        <span class="adm-toggle-label"><?= htmlspecialchars($label) ?></span>
    </label>
    <?php
}

/**
 * Compact toggle row — same layout as Settings → Store.
 * @param list<array{name:string,label:string,checked:bool}> $items
 */
function sh_admin_toggle_grid(array $items, string $extraClass = ''): void
{
    $class = 'adm-toggle-grid adm-toggle-grid--dense';
    if ($extraClass !== '') {
        $class .= ' ' . $extraClass;
    }
    echo '<div class="' . htmlspecialchars($class) . '">';
    foreach ($items as $item) {
        sh_admin_toggle_label(
            (string) ($item['name'] ?? ''),
            (string) ($item['label'] ?? ''),
            !empty($item['checked'])
        );
    }
    echo '</div>';
}

/** @param list<array{name:string,label:string,checked:bool}> $items */
function sh_admin_toggle_section(string $kicker, array $items, string $icon = 'sliders'): void
{
    ?>
    <div class="adm-toggle-section">
        <?php if ($kicker !== ''): ?>
        <p class="adm-compact-kicker"><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($kicker) ?></p>
        <?php endif; ?>
        <?php sh_admin_toggle_grid($items); ?>
    </div>
    <?php
}