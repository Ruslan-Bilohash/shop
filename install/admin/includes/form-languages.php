<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/lang-registry.php';
$settings = sh_merge_store_settings($settings);
$worldByRegion = sh_world_languages_by_region();
$rows = $settings['site_languages'] ?? [];
if ($rows === []) {
    foreach (sh_builtin_langs() as $code => $info) {
        $rows[] = array_merge(['code' => $code, 'active' => true], $info);
    }
}
?>
<form method="post" class="adm-settings-form" id="shLangForm">
    <div class="adm-card">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-language"></i> <?= htmlspecialchars(sh_settings_admin_label('languages_section', $ta)) ?></h2>
            <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" id="shLangAdd"><i class="fas fa-plus"></i> <?= htmlspecialchars(sh_settings_admin_label('lang_add', $ta)) ?></button>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('languages_help', $ta)) ?></p>
            <p class="adm-help adm-help--compact" id="shLangAddHint" hidden><i class="fas fa-lightbulb"></i> <?= htmlspecialchars(sh_settings_admin_label('lang_add_hint', $ta)) ?></p>
            <div class="adm-lang-ai-panel">
                <p class="adm-help adm-help--compact"><?= htmlspecialchars(sh_settings_admin_label('lang_ai_translate_help', $ta)) ?></p>
                <div class="adm-lang-ai-row">
                    <div class="adm-field adm-field--grow">
                        <label for="shAiTranslateTarget"><?= htmlspecialchars(sh_settings_admin_label('lang_ai_target', $ta)) ?></label>
                        <select id="shAiTranslateTarget">
                            <option value=""><?= htmlspecialchars(sh_settings_admin_label('lang_ai_target_pick', $ta)) ?></option>
                            <?php foreach ($worldByRegion as $region => $langs): ?>
                            <optgroup label="<?= htmlspecialchars($region) ?>">
                                <?php foreach ($langs as $langOpt):
                                    if ($langOpt['code'] === 'en') continue;
                                    ?>
                                <option value="<?= htmlspecialchars($langOpt['code']) ?>">
                                    <?= htmlspecialchars(($langOpt['flag'] ?? '🌐') . ' ' . $langOpt['name'] . ' (' . $langOpt['code'] . ')') ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <label class="adm-toggle adm-toggle--compact" title="<?= htmlspecialchars(sh_settings_admin_label('lang_ai_add_active', $ta)) ?>">
                        <input type="checkbox" id="shAiTranslateAddActive" value="1" checked>
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('lang_ai_add_active', $ta)) ?></span>
                    </label>
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shAiTranslateLangs"
                            data-url="<?= htmlspecialchars(sh_admin_url('api/ai-translate.php')) ?>"
                            data-from="<?= htmlspecialchars(sh_settings_admin_label('lang_ai_from_en', $ta)) ?>">
                        <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars(sh_settings_admin_label('lang_ai_translate', $ta)) ?>
                    </button>
                </div>
                <span id="shAiTranslateStatus" class="adm-ai-status" hidden></span>
            </div>
            <div id="shLangRows">
                <?php foreach ($rows as $i => $row): ?>
                <div class="adm-lang-row" data-row="<?= (int) $i ?>">
                    <input type="hidden" name="lang_idx[]" value="<?= (int) $i ?>">
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('lang_code', $ta)) ?></label>
                            <input type="text" name="lang_code_<?= (int) $i ?>" value="<?= htmlspecialchars($row['code'] ?? '') ?>" pattern="[a-z]{2,5}" required>
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('lang_label', $ta)) ?></label>
                            <input type="text" name="lang_label_<?= (int) $i ?>" value="<?= htmlspecialchars($row['label'] ?? '') ?>">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('lang_name', $ta)) ?></label>
                            <input type="text" name="lang_name_<?= (int) $i ?>" value="<?= htmlspecialchars($row['name'] ?? '') ?>">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('lang_flag', $ta)) ?></label>
                            <input type="text" name="lang_flag_<?= (int) $i ?>" value="<?= htmlspecialchars($row['flag'] ?? '') ?>">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('lang_locale', $ta)) ?></label>
                            <input type="text" name="lang_locale_<?= (int) $i ?>" value="<?= htmlspecialchars($row['locale'] ?? '') ?>">
                        </div>
                        <div class="adm-field" style="display:flex;align-items:flex-end">
                            <label class="adm-toggle adm-toggle--compact" title="<?= htmlspecialchars(sh_settings_admin_label('lang_active', $ta)) ?>">
                                <input type="checkbox" name="lang_active_<?= (int) $i ?>" value="1" <?= ($row['active'] ?? true) !== false ? 'checked' : '' ?>>
                                <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                                <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('lang_active', $ta)) ?></span>
                            </label>
                        </div>
                    </div>
                    <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-lang-remove"><i class="fas fa-trash"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>

<template id="shLangRowTpl">
    <div class="adm-lang-row" data-row="__IDX__">
        <input type="hidden" name="lang_idx[]" value="__IDX__">
        <div class="adm-form-grid">
            <div class="adm-field"><label><?= htmlspecialchars(sh_settings_admin_label('lang_code', $ta)) ?></label><input type="text" name="lang_code___IDX__" pattern="[a-z]{2,5}"></div>
            <div class="adm-field"><label><?= htmlspecialchars(sh_settings_admin_label('lang_label', $ta)) ?></label><input type="text" name="lang_label___IDX__"></div>
            <div class="adm-field"><label><?= htmlspecialchars(sh_settings_admin_label('lang_name', $ta)) ?></label><input type="text" name="lang_name___IDX__"></div>
            <div class="adm-field"><label><?= htmlspecialchars(sh_settings_admin_label('lang_flag', $ta)) ?></label><input type="text" name="lang_flag___IDX__" value="🌐"></div>
            <div class="adm-field"><label><?= htmlspecialchars(sh_settings_admin_label('lang_locale', $ta)) ?></label><input type="text" name="lang_locale___IDX__"></div>
            <div class="adm-field" style="display:flex;align-items:flex-end"><label class="adm-toggle adm-toggle--compact"><input type="checkbox" name="lang_active___IDX__" value="1" checked><span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span><span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('lang_active', $ta)) ?></span></label></div>
        </div>
        <button type="button" class="adm-btn adm-btn-danger adm-btn-sm sh-lang-remove"><i class="fas fa-trash"></i></button>
    </div>
</template>