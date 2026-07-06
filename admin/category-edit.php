<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/category-storage.php';
require_once __DIR__ . '/includes/seo-parse.php';
sh_admin_require();

$admin_page = 'categories';
$tp = $ta['categories_page'] ?? [];
$slug = trim($_GET['slug'] ?? '');
$is_new = $slug === '';
$record = $is_new ? null : sh_category_by_slug($slug, false);
$edit_tab = trim($_GET['tab'] ?? 'general');
if (!in_array($edit_tab, ['general', 'seo'], true)) {
    $edit_tab = 'general';
}
if ($is_new && $edit_tab === 'seo') {
    $edit_tab = 'general';
}

if (!$is_new && $record === null) {
    header('Location: ' . sh_admin_url('categories.php'));
    exit;
}

$page_title = $is_new
    ? ($tp['add'] ?? 'Add category')
    : ($tp['edit'] ?? 'Edit category');

$flash = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_slug = trim($_POST['slug'] ?? '');
    $orig_slug = trim($_POST['orig_slug'] ?? '');

    if (!sh_category_slug_valid($post_slug)) {
        $errors[] = $tp['slug_invalid'] ?? 'Slug must be 2–32 lowercase letters, numbers, hyphen or underscore.';
    }

    if (!$is_new && $orig_slug !== '' && $orig_slug !== $post_slug && sh_category_product_count($orig_slug) > 0) {
        $errors[] = $tp['slug_locked'] ?? 'Cannot rename slug while products use this category.';
    }

    if ($is_new && sh_category_by_slug($post_slug, false) !== null) {
        $errors[] = $tp['slug_exists'] ?? 'This slug already exists.';
    }

    $names = [];
    foreach (sh_langs() as $code => $_info) {
        $names[$code] = trim($_POST['name_' . $code] ?? '');
    }
    $names = sh_category_normalize_names($names);
    $defaultLang = sh_site_default_lang();
    $hasLabel = trim($names[$defaultLang] ?? '') !== ''
        || trim($names['en'] ?? '') !== ''
        || trim($names['no'] ?? '') !== '';
    if (!$hasLabel) {
        $errors[] = $tp['names_required'] ?? 'Enter at least one category name (default or English).';
    }

    if ($errors === []) {
        if (!$is_new && $orig_slug !== '' && $orig_slug !== $post_slug) {
            sh_category_delete($orig_slug);
        }

        $ok = sh_category_upsert([
            'slug'   => $post_slug,
            'icon'   => trim($_POST['icon'] ?? 'tag') ?: 'tag',
            'active' => !empty($_POST['active']),
            'sort'   => max(1, (int)($_POST['sort'] ?? 99)),
            'name'   => $names,
            'seo'    => sh_admin_parse_seo_post($_POST, 'category'),
        ]);

        if ($ok) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $tp['saved'] ?? 'Category saved.'];
            $redirectTab = trim($_POST['return_tab'] ?? 'general');
            $url = sh_admin_url('category-edit.php?slug=' . urlencode($post_slug));
            if ($redirectTab === 'seo') {
                $url .= '&tab=seo';
            }
            header('Location: ' . $url);
            exit;
        }
        $errors[] = $tp['save_error'] ?? 'Could not save category.';
    }

    $record = [
        'slug'   => $post_slug,
        'icon'   => trim($_POST['icon'] ?? 'tag'),
        'active' => !empty($_POST['active']),
        'sort'   => (int)($_POST['sort'] ?? 99),
        'name'   => $names,
        'seo'    => sh_admin_parse_seo_post($_POST, 'category'),
    ];
    $flash = 'error';
}

if (is_array($record) && isset($record['name']) && is_array($record['name'])) {
    $record['name'] = sh_category_normalize_names($record['name']);
}

$seo_record = $record ?? ['seo' => []];
$seo_ctx = 'category';
$seo_tp = $ta['seo_editor'] ?? [];
$seo_panel_mode = $edit_tab === 'seo';

$edit_tabs = [
    'general' => $tp['tab_general'] ?? 'General',
    'seo'     => $tp['tab_seo'] ?? 'SEO & Schema',
];
$edit_tab_base_url = sh_admin_url('category-edit.php' . ($is_new ? '' : '?slug=' . urlencode($slug)));

require_once dirname(__DIR__) . '/includes/payment-settings.php';
$sh_ai_settings = sh_ai_settings(sh_load_settings());
$admin_extra_js = [
    sh_asset('js/admin-icon-picker.js') . '?v=2',
    sh_asset('js/admin-category.js') . '?v=1',
];

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash === 'error' && $errors !== []): ?>
<div class="adm-alert adm-alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<?php if (!$is_new): ?>
<?php require __DIR__ . '/includes/edit-tabs.php'; ?>
<?php endif; ?>

<form method="post" class="adm-settings-form adm-cat-edit-form" id="shCategoryForm"
      data-ai-url="<?= htmlspecialchars(sh_admin_url('api/ai-category.php')) ?>"
      data-ai-source-lang="<?= htmlspecialchars($sh_ai_settings['ai_source_lang'] ?? 'en') ?>">
    <input type="hidden" name="orig_slug" value="<?= htmlspecialchars($record['slug'] ?? $slug) ?>">
    <input type="hidden" name="return_tab" value="<?= htmlspecialchars($edit_tab) ?>">

    <div class="adm-edit-panel <?= $edit_tab === 'general' ? 'is-active' : '' ?>" data-panel="general">
        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><?= htmlspecialchars($page_title) ?></h2>
                <a href="<?= sh_admin_url('categories.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($tp['back'] ?? 'Back') ?>
                </a>
            </div>
            <div class="adm-card-body padded">
                <div class="adm-cat-edit-layout">
                    <div class="adm-cat-edit-main">
                        <div class="adm-field">
                            <label><?= htmlspecialchars($tp['slug'] ?? 'Slug') ?> *</label>
                            <input type="text" name="slug" value="<?= htmlspecialchars($record['slug'] ?? '') ?>"
                                   pattern="[a-z][a-z0-9_-]{1,31}" <?= $is_new ? '' : 'readonly' ?>
                                   placeholder="electronics" required>
                            <small class="adm-field-hint"><?= htmlspecialchars($tp['slug_hint'] ?? 'Used in URLs and product JSON. Lowercase only.') ?></small>
                        </div>
                    </div>
                    <div class="adm-cat-edit-side">
                        <?php
                        $selectedIcon = $record['icon'] ?? 'tag';
                        $pickerPrefix = 'shCatIcon';
                        $inputName = 'icon';
                        require __DIR__ . '/includes/icon-picker-field.php';
                        ?>
                        <div class="adm-cat-edit-meta">
                            <div class="adm-field">
                                <label><?= htmlspecialchars($tp['sort'] ?? 'Sort order') ?></label>
                                <input type="number" name="sort" min="1" max="999" value="<?= (int)($record['sort'] ?? 99) ?>">
                            </div>
                            <?php
                            require_once __DIR__ . '/includes/toggle-field.php';
                            sh_admin_toggle_section(
                                $tp['status_section'] ?? 'Status',
                                [
                                    ['name' => 'active', 'label' => $tp['active'] ?? 'Active', 'checked' => ($record['active'] ?? true) !== false],
                                ],
                                'eye'
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-card-head"><h2><?= htmlspecialchars($tp['names_title'] ?? 'Names') ?> (<?= count(sh_langs()) ?>)</h2></div>
            <div class="adm-card-body padded">
                <div class="adm-form-grid adm-cat-names-grid">
                    <?php foreach (sh_langs() as $code => $info): ?>
                    <div class="adm-field">
                        <label><span class="adm-lang-flag"><?= $info['flag'] ?? '🌐' ?></span> <?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label']) ?>)</label>
                        <input type="text" name="name_<?= htmlspecialchars($code) ?>"
                               value="<?= htmlspecialchars($record['name'][$code] ?? '') ?>" required>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$is_new): ?>
    <div class="adm-edit-panel <?= $edit_tab === 'seo' ? 'is-active' : '' ?>" data-panel="seo">
        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><i class="fas fa-chart-line"></i> <?= htmlspecialchars($tp['tab_seo'] ?? 'SEO & Schema') ?></h2>
                <div class="adm-ai-toolbar">
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shAiCategorySeo"
                            data-need-name="<?= htmlspecialchars($tp['ai_need_name'] ?? 'Enter category name on General tab first.') ?>"
                            data-loading="<?= htmlspecialchars($tp['ai_loading'] ?? 'Generating SEO…') ?>"
                            data-ok="<?= htmlspecialchars($tp['ai_seo_ok'] ?? 'Category SEO generated.') ?>"
                            data-demo-ok="<?= htmlspecialchars($tp['ai_seo_demo'] ?? 'Demo SEO templates applied.') ?>">
                        <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($tp['ai_seo_btn'] ?? 'AI: Generate SEO') ?>
                    </button>
                    <span id="shAiCategorySeoStatus" class="adm-ai-status" hidden></span>
                </div>
            </div>
            <?php $seo_panel_mode = true; require __DIR__ . '/includes/seo-spoiler.php'; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="adm-alert adm-alert-info">
        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($tp['seo_after_save'] ?? 'Save the category first, then open the SEO & Schema tab.') ?>
    </div>
    <?php endif; ?>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars($tp['save'] ?? 'Save') ?></button>
        <a href="<?= sh_admin_url('categories.php') ?>" class="adm-btn adm-btn-outline"><?= htmlspecialchars($tp['cancel'] ?? 'Cancel') ?></a>
    </div>
</form>

<?php require __DIR__ . '/includes/layout-end.php'; ?>