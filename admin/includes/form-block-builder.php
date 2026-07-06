<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/block-templates.php';
require_once dirname(__DIR__, 2) . '/includes/block-presets.php';
require_once dirname(__DIR__, 2) . '/includes/service-pages.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once __DIR__ . '/admin-field-help.php';
require_once __DIR__ . '/toggle-field.php';
$tab = 'block_builder';
$sections = sh_admin_settings_sections($tab, $ta);
$templates = sh_block_templates_from_settings($settings);
$pageSlugs = sh_service_page_slugs($settings);
$pageDefs = sh_service_page_defs($settings);
$adminLang = $GLOBALS['lang'] ?? 'en';
$primaryLang = (string) (sh_ai_settings($settings)['ai_source_lang'] ?? $adminLang);
if (!array_key_exists($primaryLang, sh_langs())) {
    $primaryLang = array_key_first(sh_langs()) ?: 'en';
}
$blockPresets = sh_block_presets();
$presetColors = ['#2563eb', '#059669', '#7c3aed', '#ea580c', '#0d9488', '#dc2626', '#db2777', '#0891b2'];
?>
<form method="post" class="adm-settings-form" id="shBlockBuilderForm"
      data-presets="<?= htmlspecialchars(json_encode($blockPresets, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>"
      data-ai-url="<?= htmlspecialchars(sh_admin_url('api/ai-block-template.php')) ?>"
      data-preview-lang="<?= htmlspecialchars($primaryLang) ?>"
      data-err-prompt="<?= htmlspecialchars(sh_settings_admin_label('block_builder_err_prompt', $ta)) ?>"
      data-err-server="<?= htmlspecialchars(sh_settings_admin_label('block_builder_err_server', $ta)) ?>"
      data-status-generating="<?= htmlspecialchars(sh_settings_admin_label('block_builder_generating', $ta)) ?>"
      data-status-demo="<?= htmlspecialchars(sh_settings_admin_label('block_builder_demo_ok', $ta)) ?>"
      data-status-ok="<?= htmlspecialchars(sh_settings_admin_label('block_builder_gen_ok', $ta)) ?>"
      data-status-preset="<?= htmlspecialchars(sh_settings_admin_label('block_builder_status_preset', $ta)) ?>">
    <div class="adm-card adm-settings-section" id="block-builder-generate">
        <div class="adm-card-head">
            <h2><i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($sections['block-builder-generate'] ?? sh_settings_admin_label('block_builder_generate_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('block_builder_generate_help', $ta)) ?></p>
            <div class="adm-field adm-field--wide">
                <label for="shTplPrompt"><?= htmlspecialchars(sh_settings_admin_label('block_builder_prompt', $ta)) ?></label>
                <textarea id="shTplPrompt" rows="3" class="adm-textarea" placeholder="<?= htmlspecialchars(sh_settings_admin_label('block_builder_prompt_ph', $ta)) ?>"></textarea>
            </div>
            <div class="adm-inline-actions">
                <button type="button" class="adm-btn adm-btn-primary" id="shTplGenerateBtn">
                    <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars(sh_settings_admin_label('block_builder_generate_btn', $ta)) ?>
                </button>
                <span id="shTplGenerateStatus" class="adm-ai-status" hidden></span>
            </div>

            <div class="adm-block-presets" id="shBlockPresets">
                <p class="adm-compact-kicker"><i class="fas fa-layer-group"></i> <?= htmlspecialchars(sh_settings_admin_label('block_builder_presets_title', $ta)) ?></p>
                <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('block_builder_presets_help', $ta)) ?></p>
                <div class="adm-block-preset-colors" role="group" aria-label="<?= htmlspecialchars(sh_settings_admin_label('block_builder_color_label', $ta)) ?>">
                    <label class="adm-block-color-label" for="shBlockColorPicker"><?= htmlspecialchars(sh_settings_admin_label('block_builder_color_label', $ta)) ?></label>
                    <input type="color" id="shBlockColorPicker" value="#2563eb" class="adm-block-color-input">
                    <?php foreach ($presetColors as $hex): ?>
                    <button type="button" class="adm-block-color-swatch" data-color="<?= htmlspecialchars($hex) ?>" style="--swatch:<?= htmlspecialchars($hex) ?>" title="<?= htmlspecialchars($hex) ?>" aria-label="<?= htmlspecialchars($hex) ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="adm-block-preset-grid">
                    <?php foreach ($blockPresets as $preset): ?>
                    <button type="button" class="adm-block-preset-card" data-preset-id="<?= htmlspecialchars($preset['id']) ?>" style="--preset-color:<?= htmlspecialchars($preset['color']) ?>">
                        <i class="fas fa-<?= htmlspecialchars($preset['icon']) ?>" aria-hidden="true"></i>
                        <span><?= htmlspecialchars(sh_settings_admin_label('block_builder_preset_' . $preset['id'], $ta) ?: ($preset['name'] ?? '')) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-settings-section adm-block-builder-editor" id="block-builder-new">
        <div class="adm-card-head">
            <h2><i class="fas fa-pen-ruler"></i> <?= htmlspecialchars($sections['block-builder-new'] ?? sh_settings_admin_label('block_builder_new_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <div class="adm-block-builder-layout">
                <div class="adm-block-builder-preview-wrap">
                    <p class="adm-compact-kicker"><i class="fas fa-eye"></i> <?= htmlspecialchars(sh_settings_admin_label('block_builder_preview', $ta)) ?></p>
                    <iframe id="shTplPreview" class="adm-block-builder-preview" title="<?= htmlspecialchars(sh_settings_admin_label('block_builder_preview', $ta)) ?>" sandbox="allow-same-origin"></iframe>
                </div>
                <div class="adm-block-builder-fields">
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_name', $ta)) ?></label>
                            <input type="text" name="new_tpl_name" id="shNewTplName" placeholder="<?= htmlspecialchars(sh_settings_admin_label('block_builder_name_ph', $ta)) ?>">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement', $ta)) ?></label>
                            <select name="new_tpl_placement" id="shNewTplPlacement">
                                <option value="none"><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_none', $ta)) ?></option>
                                <option value="homepage"><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_home', $ta)) ?></option>
                                <option value="page"><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_page', $ta)) ?></option>
                            </select>
                        </div>
                        <div class="adm-field adm-field--wide sh-new-tpl-page" id="shNewTplPageWrap" hidden>
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_page', $ta)) ?></label>
                            <select name="new_tpl_page_slug" id="shNewTplPageSlug">
                                <option value="">—</option>
                                <?php foreach ($pageSlugs as $slug): ?>
                                <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($pageDefs[$slug]['admin_label'] ?? $slug) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="new_tpl_prompt" id="shNewTplPrompt">
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="new_tpl_enabled" value="1" checked id="shNewTplEnabled">
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('block_builder_enabled', $ta)) ?></span>
                    </label>
                    <input type="hidden" name="new_tpl_body_mirror" id="shNewTplBodyMirror" value="">
                    <?php foreach (sh_langs() as $code => $info):
                        $isPrimary = $code === $primaryLang;
                        $langFields = function () use ($code, $ta): void { ?>
                        <div class="adm-form-grid">
                            <div class="adm-field">
                                <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_title', $ta)) ?></label>
                                <input type="text" name="new_tpl_title_<?= htmlspecialchars($code) ?>" class="sh-tpl-text-input" data-field="title" data-lang="<?= htmlspecialchars($code) ?>">
                            </div>
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars(sh_settings_admin_label('homepage_block_subtitle', $ta)) ?></label>
                                <input type="text" name="new_tpl_subtitle_<?= htmlspecialchars($code) ?>" class="sh-tpl-text-input" data-field="subtitle" data-lang="<?= htmlspecialchars($code) ?>">
                            </div>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_html', $ta)) ?></label>
                            <div class="adm-cm-wrap">
                                <textarea name="new_tpl_body_<?= htmlspecialchars($code) ?>" rows="8" class="adm-code-input adm-code-mirror sh-tpl-body-input" data-mode="htmlmixed" data-lang="<?= htmlspecialchars($code) ?>"></textarea>
                            </div>
                        </div>
                        <?php };
                    ?>
                    <?php if ($isPrimary): ?>
                    <div class="adm-block-builder-lang adm-block-builder-lang--primary" data-lang="<?= htmlspecialchars($code) ?>">
                        <p class="adm-compact-kicker"><i class="fas fa-star"></i> <?= htmlspecialchars($info['name']) ?> <span class="adm-muted">(<?= htmlspecialchars(sh_settings_admin_label('block_builder_primary_lang', $ta)) ?>)</span></p>
                        <?php $langFields(); ?>
                    </div>
                    <?php else: ?>
                    <details class="adm-spoiler adm-spoiler-nested adm-block-builder-lang-spoiler" data-lang="<?= htmlspecialchars($code) ?>">
                        <summary><i class="fas fa-language"></i> <?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars(strtoupper($code)) ?>)</summary>
                        <div class="adm-spoiler-body"><?php $langFields(); ?></div>
                    </details>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($templates !== []): ?>
    <div class="adm-card adm-settings-section" id="block-builder-saved">
        <div class="adm-card-head">
            <h2><i class="fas fa-layer-group"></i> <?= htmlspecialchars($sections['block-builder-saved'] ?? sh_settings_admin_label('block_builder_saved_section', $ta)) ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars(sh_settings_admin_label('block_builder_saved_help', $ta)) ?></p>
            <div id="shTplDeleteIds" hidden aria-hidden="true"></div>
            <?php foreach ($templates as $i => $tpl): ?>
            <details class="adm-spoiler adm-block-builder-saved-row" data-tpl-id="<?= htmlspecialchars($tpl['id']) ?>">
                <summary>
                    <strong><?= htmlspecialchars($tpl['name'] ?? $tpl['id']) ?></strong>
                    <span class="adm-muted">— <?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_' . ($tpl['placement'] ?? 'none'), $ta)) ?></span>
                    <button type="button"
                            class="adm-btn adm-btn-danger adm-btn-sm sh-tpl-delete-btn"
                            data-tpl-id="<?= htmlspecialchars($tpl['id']) ?>"
                            data-confirm="<?= htmlspecialchars(sh_settings_admin_label('block_builder_delete_confirm', $ta)) ?>"
                            title="<?= htmlspecialchars(sh_settings_admin_label('block_builder_delete', $ta)) ?>">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </summary>
                <div class="adm-spoiler-body">
                    <input type="hidden" name="tpl_id[]" value="<?= (int) $i ?>">
                    <input type="hidden" name="tpl_id_val_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['id']) ?>">
                    <div class="adm-form-grid">
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_name', $ta)) ?></label>
                            <input type="text" name="tpl_name_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['name'] ?? '') ?>">
                        </div>
                        <div class="adm-field">
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement', $ta)) ?></label>
                            <select name="tpl_placement_<?= (int) $i ?>" class="sh-tpl-placement-select" data-idx="<?= (int) $i ?>">
                                <option value="none" <?= ($tpl['placement'] ?? '') === 'none' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_none', $ta)) ?></option>
                                <option value="homepage" <?= ($tpl['placement'] ?? '') === 'homepage' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_home', $ta)) ?></option>
                                <option value="page" <?= ($tpl['placement'] ?? '') === 'page' ? 'selected' : '' ?>><?= htmlspecialchars(sh_settings_admin_label('block_builder_placement_page', $ta)) ?></option>
                            </select>
                        </div>
                        <div class="adm-field adm-field--wide sh-tpl-page-wrap" data-idx="<?= (int) $i ?>" <?= ($tpl['placement'] ?? '') === 'page' ? '' : 'hidden' ?>>
                            <label><?= htmlspecialchars(sh_settings_admin_label('block_builder_page', $ta)) ?></label>
                            <select name="tpl_page_slug_<?= (int) $i ?>">
                                <?php foreach ($pageSlugs as $slug): ?>
                                <option value="<?= htmlspecialchars($slug) ?>" <?= ($tpl['page_slug'] ?? '') === $slug ? 'selected' : '' ?>><?= htmlspecialchars($pageDefs[$slug]['admin_label'] ?? $slug) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="tpl_enabled_<?= (int) $i ?>" value="1" <?= !empty($tpl['enabled']) ? 'checked' : '' ?>>
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars(sh_settings_admin_label('block_builder_enabled', $ta)) ?></span>
                    </label>
                    <input type="hidden" name="tpl_prompt_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['prompt'] ?? '') ?>">
                    <?php foreach (sh_langs() as $code => $info):
                        $isPrimary = $code === $primaryLang;
                    ?>
                    <?php if ($isPrimary): ?>
                    <div class="adm-home-block-lang adm-home-block-lang--primary">
                        <p class="adm-compact-kicker"><i class="fas fa-star"></i> <?= htmlspecialchars($info['name']) ?></p>
                        <div class="adm-form-grid">
                            <div class="adm-field">
                                <input type="text" name="tpl_title_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['title'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_title', $ta)) ?>">
                            </div>
                            <div class="adm-field adm-field--wide">
                                <input type="text" name="tpl_subtitle_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['subtitle'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_subtitle', $ta)) ?>">
                            </div>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <textarea name="tpl_body_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" rows="5" class="adm-code-input adm-code-mirror" data-mode="htmlmixed"><?= htmlspecialchars($tpl['body'][$code] ?? '') ?></textarea>
                        </div>
                    </div>
                    <?php else: ?>
                    <details class="adm-spoiler adm-spoiler-nested adm-home-block-lang-spoiler">
                        <summary><?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars(strtoupper($code)) ?>)</summary>
                        <div class="adm-spoiler-body">
                            <div class="adm-form-grid">
                                <div class="adm-field">
                                    <input type="text" name="tpl_title_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['title'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_title', $ta)) ?>">
                                </div>
                                <div class="adm-field adm-field--wide">
                                    <input type="text" name="tpl_subtitle_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" value="<?= htmlspecialchars($tpl['subtitle'][$code] ?? '') ?>" placeholder="<?= htmlspecialchars(sh_settings_admin_label('homepage_block_subtitle', $ta)) ?>">
                                </div>
                            </div>
                            <div class="adm-field adm-field--wide">
                                <textarea name="tpl_body_<?= htmlspecialchars($code) ?>_<?= (int) $i ?>" rows="5" class="adm-code-input adm-code-mirror" data-mode="htmlmixed"><?= htmlspecialchars($tpl['body'][$code] ?? '') ?></textarea>
                            </div>
                        </div>
                    </details>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('block_builder_save', $ta)) ?></button>
        <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline" target="_blank"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('homepage_preview', $ta)) ?></a>
    </div>
</form>