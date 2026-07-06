<?php
/**
 * Font Awesome icon picker (AJAX grid). Icons without fa- prefix.
 * @var string $selectedIcon
 * @var string $inputName
 * @var string $pickerPrefix unique DOM id prefix
 * @var array $tp labels
 * @var bool $includeModal
 */
$selectedIcon = trim((string) ($selectedIcon ?? 'tag')) ?: 'tag';
$inputName = (string) ($inputName ?? 'icon');
$pickerPrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($pickerPrefix ?? 'shIcon')) ?: 'shIcon';
$includeModal = ($includeModal ?? true) !== false;
$iconLabel = $tp['icon'] ?? 'Icon';
$pickLabel = $tp['icon_pick'] ?? 'Choose icon';
$changeLabel = $tp['icon_change'] ?? 'Change icon';
$hintText = $tp['icon_hint_short'] ?? $tp['icon_hint'] ?? '';
$inputId = $pickerPrefix . 'Input';
$openId = $pickerPrefix . 'Open';
$previewId = $pickerPrefix . 'Preview';
$labelId = $pickerPrefix . 'Label';
$modalId = $pickerPrefix . 'Modal';
$gridId = $pickerPrefix . 'Grid';
$searchId = $pickerPrefix . 'Search';
$titleId = $pickerPrefix . 'ModalTitle';
?>
<div class="adm-icon-field adm-icon-picker-root" data-icon-picker>
    <label class="adm-icon-field-label" for="<?= htmlspecialchars($openId) ?>"><?= htmlspecialchars($iconLabel) ?></label>
    <input type="hidden" name="<?= htmlspecialchars($inputName) ?>" id="<?= htmlspecialchars($inputId) ?>" value="<?= htmlspecialchars($selectedIcon) ?>">
    <button type="button" class="adm-icon-trigger" id="<?= htmlspecialchars($openId) ?>"
            data-icon-picker-open
            data-url="<?= htmlspecialchars(sh_admin_url('api/icons.php')) ?>"
            data-change-label="<?= htmlspecialchars($changeLabel) ?>"
            aria-haspopup="dialog">
        <span class="adm-icon-trigger-preview" id="<?= htmlspecialchars($previewId) ?>" data-icon-picker-preview>
            <i class="fas fa-<?= htmlspecialchars($selectedIcon) ?>" aria-hidden="true"></i>
        </span>
        <span class="adm-icon-trigger-text" id="<?= htmlspecialchars($labelId) ?>"><?= htmlspecialchars($pickLabel) ?></span>
        <i class="fas fa-chevron-right adm-icon-trigger-chevron" aria-hidden="true"></i>
    </button>
    <?php if ($hintText !== ''): ?>
    <small class="adm-field-hint"><?= htmlspecialchars($hintText) ?></small>
    <?php endif; ?>

<?php if ($includeModal): ?>
    <div class="adm-modal" id="<?= htmlspecialchars($modalId) ?>" data-icon-picker-modal role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($titleId) ?>" hidden>
        <div class="adm-modal-backdrop" data-close="icon-modal"></div>
        <div class="adm-modal-panel adm-modal-panel--icons">
            <div class="adm-modal-head">
                <h3 id="<?= htmlspecialchars($titleId) ?>"><i class="fas fa-icons"></i> <?= htmlspecialchars($pickLabel) ?></h3>
                <button type="button" class="adm-modal-close" data-close="icon-modal" aria-label="<?= htmlspecialchars($tp['close'] ?? 'Close') ?>">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="adm-modal-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" id="<?= htmlspecialchars($searchId) ?>" data-icon-picker-search placeholder="<?= htmlspecialchars($tp['icon_search'] ?? 'Search icon…') ?>" autocomplete="off">
            </div>
            <div class="adm-modal-body">
                <div class="adm-icon-modal-grid" id="<?= htmlspecialchars($gridId) ?>" data-icon-picker-grid aria-live="polite">
                    <p class="adm-icon-modal-loading"><?= htmlspecialchars($tp['icon_loading'] ?? 'Loading icons…') ?></p>
                </div>
            </div>
            <div class="adm-modal-foot">
                <button type="button" class="adm-btn adm-btn-outline" data-close="icon-modal"><?= htmlspecialchars($tp['cancel'] ?? 'Cancel') ?></button>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>