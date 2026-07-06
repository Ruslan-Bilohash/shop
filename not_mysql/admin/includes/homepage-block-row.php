<?php
/** @var array $block @var int $i @var array $types @var array $ta */
$type = (string) ($block['type'] ?? '');
$typeInfo = $types[$type] ?? ['label' => $type, 'icon' => 'cube', 'has_limit' => false];
$isCustom = $type === 'custom';
$removable = $isCustom;
?>
<div class="adm-home-block-row<?= $isCustom ? ' adm-home-block-row--custom' : '' ?>" data-idx="<?= (int) $i ?>" data-type="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="home_block_idx[]" value="<?= (int) $i ?>">
    <input type="hidden" name="home_block_id_<?= (int) $i ?>" value="<?= htmlspecialchars($block['id'] ?? $type) ?>">
    <input type="hidden" name="home_block_type_<?= (int) $i ?>" value="<?= htmlspecialchars($type) ?>">
    <?php if (!empty($block['template_id'])): ?>
    <input type="hidden" name="home_block_template_id_<?= (int) $i ?>" value="<?= htmlspecialchars($block['template_id']) ?>">
    <?php endif; ?>
    <input type="hidden" name="home_block_sort_<?= (int) $i ?>" class="sh-home-block-sort" value="<?= (int) ($block['sort'] ?? ($i + 1)) ?>">
    <div class="adm-home-block-head">
        <span class="adm-cat-drag-handle sh-home-drag" title="<?= htmlspecialchars(sh_settings_admin_label('homepage_drag_handle', $ta)) ?>"><i class="fas fa-grip-vertical"></i></span>
        <span class="adm-home-block-type"><i class="fas fa-<?= htmlspecialchars($typeInfo['icon']) ?>"></i> <?= htmlspecialchars(sh_settings_admin_label('homepage_block_' . $type, $ta) ?: $typeInfo['label']) ?></span>

        <label class="adm-toggle adm-toggle--compact adm-home-block-toggle">
            <input type="checkbox" name="home_block_enabled_<?= (int) $i ?>" value="1" <?= !empty($block['enabled']) ? 'checked' : '' ?>>
            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
            <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('homepage_block_enabled', $ta)) ?></span>
        </label>
        <?php if ($removable): ?>
        <button type="button" class="adm-btn adm-btn-outline adm-btn-sm sh-home-block-remove" title="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_remove', $ta)) ?>">
            <i class="fas fa-trash-alt"></i>
        </button>
        <?php endif; ?>
    </div>
    <details class="adm-spoiler adm-spoiler-nested"<?= $isCustom ? ' open' : '' ?>>
        <summary><?= htmlspecialchars(sh_settings_admin_label('homepage_block_edit', $ta)) ?></summary>
        <div class="adm-spoiler-body">
            <?php if (!empty($typeInfo['has_limit'])): ?>
            <div class="adm-field adm-field--inline">
                <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_limit', $ta)) ?></label>
                <input type="number" name="home_block_limit_<?= (int) $i ?>" min="1" max="24" value="<?= (int) ($block['limit'] ?? 6) ?>">
            </div>
            <?php endif; ?>
            <?php foreach (sh_langs() as $code => $info): ?>
            <div class="adm-home-block-lang">
                <p class="adm-compact-kicker"><i class="fas fa-language"></i> <?= htmlspecialchars($info['name']) ?></p>
                <?php if ($isCustom): ?>
                <div class="adm-form-grid">
                    <div class="adm-field">
                        <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_title', $ta)) ?></label>
                        <input type="text" name="home_block_title_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($block['title'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_title_ph', $ta)) ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_subtitle', $ta)) ?></label>
                        <input type="text" name="home_block_subtitle_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($block['subtitle'][$code] ?? '') ?>">
                    </div>
                </div>
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_body_html', $ta)) ?></label>
                    <textarea name="home_block_body_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" rows="6" class="adm-code-input adm-code-mirror" data-mode="htmlmixed"><?= htmlspecialchars($block['body'][$code] ?? '') ?></textarea>
                </div>
                <?php else: ?>
                <div class="adm-form-grid">
                    <div class="adm-field">
                        <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_title', $ta)) ?></label>
                        <input type="text" name="home_block_title_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($block['title'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_title_ph', $ta)) ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_subtitle', $ta)) ?></label>
                        <input type="text" name="home_block_subtitle_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($block['subtitle'][$code] ?? '') ?>">
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </details>
</div>