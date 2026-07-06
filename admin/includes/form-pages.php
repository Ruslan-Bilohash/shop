<?php
/** @var array $settings @var array $ta */
require_once dirname(__DIR__, 2) . '/includes/service-pages.php';
require_once __DIR__ . '/rich-editor.php';
$settings = sh_merge_service_settings($settings);
$page_slug = strtolower(trim($_GET['page'] ?? 'delivery'));
$defs = sh_service_page_defs($settings);
$allSlugs = sh_service_page_slugs($settings);
if (!sh_service_page_slug_valid($page_slug) || !isset($defs[$page_slug])) {
    $page_slug = $allSlugs[0] ?? 'delivery';
}
$page = $settings['service_pages'][$page_slug] ?? [];
$isCustom = !sh_service_page_is_builtin($page_slug);
$isNew = !empty($_GET['new']) && $isCustom && trim((string) ($page['title']['en'] ?? '')) === '';
?>
<form method="post" class="adm-settings-form" id="shServicePagesForm">
    <input type="hidden" name="page_slug" value="<?= htmlspecialchars($page_slug) ?>">

    <div class="adm-card adm-settings-section" id="pages-list">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-file-lines"></i> <?= htmlspecialchars(sh_settings_admin_label('service_pages_list', $ta)) ?></h2>
            <div class="adm-inline-actions">
                <input type="text" id="shServicePageNewSlug" class="adm-input-sm" placeholder="<?= htmlspecialchars(sh_settings_admin_label('service_page_new_slug_ph', $ta)) ?>"
                       data-invalid="<?= htmlspecialchars(sh_settings_admin_label('service_page_slug_invalid', $ta)) ?>">
                <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shServicePageCreateBtn"
                        data-base-url="<?= htmlspecialchars(sh_admin_url('settings-pages.php')) ?>">
                    <i class="fas fa-plus"></i> <?= htmlspecialchars(sh_settings_admin_label('service_page_add', $ta)) ?>
                </button>
            </div>
        </div>
        <div class="adm-card-body padded">
            <nav class="adm-edit-tabs adm-edit-tabs--sub adm-edit-tabs--wrap" aria-label="Service pages">
                <?php foreach ($defs as $slug => $def): ?>
                <a href="<?= htmlspecialchars(sh_admin_url('settings-pages.php?page=' . urlencode($slug))) ?>"
                   class="adm-edit-tab <?= $page_slug === $slug ? 'active' : '' ?>">
                    <i class="fas fa-<?= htmlspecialchars($def['icon']) ?>"></i>
                    <?= htmlspecialchars(sh_settings_admin_label('service_page_' . $slug, $ta) ?: ($def['admin_label'] ?? $slug)) ?>
                    <?php if (!empty($def['custom'])): ?><span class="adm-tab-badge">+</span><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head adm-card-head--stack">
            <h2><i class="fas fa-<?= htmlspecialchars($defs[$page_slug]['icon'] ?? 'file') ?>"></i>
                <?= htmlspecialchars(sh_settings_admin_label('service_page_edit', $ta)) ?>:
                <?= htmlspecialchars(sh_settings_admin_label('service_page_' . $page_slug, $ta) ?: ($defs[$page_slug]['admin_label'] ?? $page_slug)) ?>
            </h2>
            <div class="adm-inline-actions">
                <a href="<?= htmlspecialchars(sh_url('page.php?slug=' . urlencode($page_slug))) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(sh_settings_admin_label('view_page', $ta)) ?>
                </a>
                <?php if ($isCustom): ?>
                <button type="submit" name="delete_service_page" value="1" class="adm-btn adm-btn-danger adm-btn-sm" formnovalidate
                        onclick="return confirm('<?= htmlspecialchars(sh_settings_admin_label('service_page_delete_confirm', $ta)) ?>')">
                    <i class="fas fa-trash"></i> <?= htmlspecialchars(sh_settings_admin_label('service_page_delete', $ta)) ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars(sh_settings_admin_label('service_pages_help', $ta)) ?></p>
            <?php if ($isNew): ?>
            <div class="adm-alert adm-alert-info adm-alert-compact"><i class="fas fa-info-circle"></i> <?= htmlspecialchars(sh_settings_admin_label('service_page_new_hint', $ta)) ?></div>
            <?php endif; ?>

            <div class="adm-toggle-grid adm-toggle-grid--dense">
                <?php require_once __DIR__ . '/toggle-field.php'; sh_admin_toggle('page_active', 'page_active', ($page['active'] ?? true) !== false, $ta); ?>
            </div>

            <?php if ($isCustom): ?>
            <div class="adm-form-grid" style="margin-top:12px">
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('service_page_admin_label', $ta)) ?></label>
                    <input type="text" name="page_admin_label" value="<?= htmlspecialchars($page['admin_label'] ?? ($defs[$page_slug]['admin_label'] ?? '')) ?>">
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars(sh_settings_admin_label('service_page_icon', $ta)) ?></label>
                    <input type="text" name="page_icon" value="<?= htmlspecialchars($page['icon'] ?? 'file-lines') ?>" placeholder="file-lines">
                    <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('service_page_icon_hint', $ta)) ?></small>
                </div>
            </div>
            <?php endif; ?>

            <?php foreach (sh_langs() as $code => $info): ?>
            <details class="adm-spoiler adm-spoiler-nested" <?= $code === 'en' ? 'open' : '' ?>>
                <summary><?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label']) ?>)</summary>
                <div class="adm-spoiler-body">
                    <div class="adm-form-grid">
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars(sh_settings_admin_label('page_title', $ta)) ?></label>
                            <input type="text" name="page_title_<?= htmlspecialchars($code) ?>"
                                   value="<?= htmlspecialchars($page['title'][$code] ?? '') ?>">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars(sh_settings_admin_label('page_content', $ta)) ?></label>
                            <?php sh_admin_rich_editor(
                                'page_content_' . $code,
                                (string) ($page['content'][$code] ?? ''),
                                'page-editor-' . $page_slug . '-' . $code,
                                $ta
                            ); ?>
                            <small class="adm-field-hint"><?= htmlspecialchars(sh_settings_admin_label('page_content_editor_hint', $ta)) ?></small>
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars(sh_settings_admin_label('page_meta_title', $ta)) ?></label>
                            <input type="text" name="page_meta_title_<?= htmlspecialchars($code) ?>"
                                   value="<?= htmlspecialchars($page['meta_title'][$code] ?? '') ?>" maxlength="70">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label><?= htmlspecialchars(sh_settings_admin_label('page_meta_description', $ta)) ?></label>
                            <textarea name="page_meta_description_<?= htmlspecialchars($code) ?>" rows="2" maxlength="320"><?= htmlspecialchars($page['meta_description'][$code] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
    </div>
</form>