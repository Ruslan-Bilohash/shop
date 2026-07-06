<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/storage.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/ai.php';
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
require_once __DIR__ . '/includes/seo-parse.php';
require_once __DIR__ . '/includes/toggle-field.php';
sh_admin_require();

$admin_page = 'products';
$tp = $ta['products_page'] ?? [];
$id = trim($_GET['id'] ?? '');
$is_new = $id === '';
$record = $is_new ? null : sh_product_by_id($id, true);
$scroll_section = trim($_GET['tab'] ?? '');
if (!in_array($scroll_section, ['general', 'names', 'seo'], true)) {
    $scroll_section = '';
}

if (!$is_new && $record === null) {
    header('Location: ' . sh_admin_url('products.php'));
    exit;
}

$page_title = $is_new
    ? ($tp['add'] ?? 'Add product')
    : ($tp['edit'] ?? 'Edit product');

$flash = '';
$errors = [];
$aiSettings = sh_ai_settings(sh_load_settings());
$aiSourceLang = (string) ($aiSettings['ai_source_lang'] ?? 'en');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = trim($_POST['id'] ?? '');
    $orig_id = trim($_POST['orig_id'] ?? '');

    if (!sh_product_id_valid($post_id)) {
        $errors[] = $tp['id_invalid'] ?? 'ID must be 2–49 lowercase letters, numbers, hyphen or underscore.';
    }

    if ($is_new && sh_product_by_id($post_id, true) !== null) {
        $errors[] = $tp['id_exists'] ?? 'This product ID already exists.';
    }

    $names = [];
    $descs = [];
    foreach (sh_langs() as $code => $_info) {
        $names[$code] = trim($_POST['name_' . $code] ?? '');
        $descs[$code] = trim($_POST['desc_' . $code] ?? '');
        if (!$is_new && $record !== null) {
            if ($names[$code] === '') {
                $names[$code] = trim((string) ($record['name'][$code] ?? ''));
            }
            if ($descs[$code] === '') {
                $descs[$code] = trim((string) ($record['desc'][$code] ?? ''));
            }
        }
    }
    foreach (sh_langs() as $code => $_info) {
        if ($names[$code] === '' || $descs[$code] === '') {
            $errors[] = $tp['names_required'] ?? 'Fill product name and description for all languages.';
            break;
        }
    }

    $category = trim($_POST['category'] ?? '');
    if ($category === '' || !in_array($category, sh_categories(), true)) {
        $errors[] = $tp['category_required'] ?? 'Select a valid category.';
    }

    $productImages = [];
    $imagesJson = trim($_POST['images_json'] ?? '');
    if ($imagesJson !== '') {
        $decoded = json_decode($imagesJson, true);
        if (is_array($decoded)) {
            $productImages = array_values(array_filter(array_map('trim', $decoded)));
        }
    }
    if ($productImages === [] && !empty($_POST['images']) && is_array($_POST['images'])) {
        $productImages = array_values(array_filter(array_map('trim', $_POST['images'])));
    }
    $primaryImage = $productImages[0] ?? trim($_POST['image'] ?? '');

    if ($errors === []) {
        $ok = sh_product_upsert([
            'id'         => $post_id,
            'category'   => $category,
            'featured'   => !empty($_POST['featured']),
            'active'     => !empty($_POST['active']),
            'price'      => (int)($_POST['price'] ?? 0),
            'sale_price' => (int)($_POST['sale_price'] ?? 0),
            'sku'        => trim($_POST['sku'] ?? ''),
            'stock'      => (int)($_POST['stock'] ?? 0),
            'image'      => $primaryImage,
            'images'     => $productImages,
            'name'       => $names,
            'desc'       => $descs,
            'seo'        => sh_admin_parse_seo_post($_POST, 'product'),
        ]);

        if ($ok) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $tp['saved'] ?? 'Product saved.'];
            header('Location: ' . sh_admin_url('product-edit.php?id=' . urlencode($post_id)));
            exit;
        }
        $errors[] = $tp['save_error'] ?? 'Could not save product.';
    }

    $record = [
        'id'         => $post_id,
        'category'   => $category,
        'featured'   => !empty($_POST['featured']),
        'active'     => !empty($_POST['active']),
        'price'      => (int)($_POST['price'] ?? 0),
        'sale_price' => (int)($_POST['sale_price'] ?? 0),
        'sku'        => trim($_POST['sku'] ?? ''),
        'stock'      => (int)($_POST['stock'] ?? 0),
        'image'      => $primaryImage,
        'images'     => $productImages,
        'name'       => $names,
        'desc'       => $descs,
        'seo'        => sh_admin_parse_seo_post($_POST, 'product'),
    ];
    $flash = 'error';
}

$productImagesList = $record !== null ? sh_product_images($record) : [];

$seo_record = $record ?? ['seo' => []];
$seo_ctx = 'product';
$seo_tp = $ta['seo_editor'] ?? [];

$contentChecklistLabels = $tp['content_checklist'] ?? [];
$seoChecklistLabels = $tp['seo_checklist'] ?? [];
$contentChecklist = sh_product_content_checklist($record, $contentChecklistLabels);
$seoChecklist = sh_product_seo_checklist($record, $seoChecklistLabels);
$checklistLangs = [];
foreach (sh_langs() as $code => $info) {
    $checklistLangs[] = ['code' => $code, 'name' => $info['name'] ?? $code];
}

$admin_extra_js = [
    sh_asset('js/admin-product.js') . '?v=5',
    sh_asset('js/admin-product-images.js') . '?v=2',
    sh_asset('js/admin-seo-checklist.js') . '?v=2',
];

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash === 'error' && $errors !== []): ?>
<div class="adm-alert adm-alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <?= htmlspecialchars(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<div class="adm-product-edit-layout">
<div class="adm-product-edit-main">
<form method="post" class="adm-settings-form" id="shProductForm"
      data-ai-url="<?= htmlspecialchars(sh_admin_url('api/ai-product.php')) ?>"
      data-ai-source-lang="<?= htmlspecialchars($aiSourceLang) ?>">
    <input type="hidden" name="orig_id" value="<?= htmlspecialchars($record['id'] ?? $id) ?>">
    <section class="adm-edit-section" id="product-section-general" data-panel="general">
        <div class="adm-card">
            <div class="adm-card-head">
                <h2><?= htmlspecialchars($page_title) ?></h2>
                <a href="<?= sh_admin_url('products.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($tp['back'] ?? 'Back') ?>
                </a>
            </div>
            <div class="adm-card-body padded">
                <div class="adm-form-grid">
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['id'] ?? 'Product ID') ?> *</label>
                        <input type="text" name="id" value="<?= htmlspecialchars($record['id'] ?? '') ?>"
                               pattern="[a-z][a-z0-9_-]{1,48}" <?= $is_new ? '' : 'readonly' ?>
                               placeholder="wireless-headphones-pro" required>
                        <small class="adm-field-hint"><?= htmlspecialchars($tp['id_hint'] ?? 'Used in URLs. Lowercase only.') ?></small>
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['sku'] ?? 'SKU') ?></label>
                        <input type="text" name="sku" value="<?= htmlspecialchars($record['sku'] ?? '') ?>">
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['category'] ?? 'Category') ?> *</label>
                        <select name="category" required>
                            <?php foreach (sh_category_records(true) as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($record['category'] ?? '') === ($cat['slug'] ?? '') ? 'selected' : '' ?>>
                                <?= htmlspecialchars(sh_localized($cat, 'name', $lang)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['price'] ?? 'Price (NOK)') ?></label>
                        <input type="number" name="price" min="0" step="1" value="<?= (int)($record['price'] ?? 0) ?>">
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['sale_price'] ?? 'Sale price (0 = none)') ?></label>
                        <input type="number" name="sale_price" min="0" step="1" value="<?= (int)($record['sale_price'] ?? 0) ?>">
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['stock'] ?? 'Stock') ?></label>
                        <input type="number" name="stock" min="0" step="1" value="<?= (int)($record['stock'] ?? 0) ?>">
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['images'] ?? 'Product images') ?></label>
                        <div class="adm-img-gallery" id="shProductImages"
                             data-upload-url="<?= htmlspecialchars(sh_admin_url('api/upload-image.php')) ?>"
                             data-uploading="<?= htmlspecialchars($tp['images_uploading'] ?? 'Uploading…') ?>"
                             data-upload-ok="<?= htmlspecialchars($tp['images_upload_ok'] ?? 'Images added.') ?>">
                            <input type="hidden" name="images_json" id="shProductImagesJson" value="<?= htmlspecialchars(json_encode($productImagesList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
                            <input type="hidden" name="image" id="shProductImageUrl" value="<?= htmlspecialchars($productImagesList[0] ?? ($record['image'] ?? '')) ?>">
                            <ul class="adm-img-gallery-list">
                                <?php foreach ($productImagesList as $imgUrl): ?>
                                <li class="adm-img-gallery-item" data-url="<?= htmlspecialchars($imgUrl) ?>" draggable="true">
                                    <div class="adm-img-gallery-thumb">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" loading="lazy">
                                        <span class="adm-img-gallery-drag" title="<?= htmlspecialchars($tp['images_drag'] ?? 'Drag to reorder') ?>"><i class="fas fa-grip-vertical"></i></span>
                                    </div>
                                    <button type="button" class="adm-img-gallery-remove" aria-label="<?= htmlspecialchars($tp['images_remove'] ?? 'Remove') ?>"><i class="fas fa-trash"></i></button>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="adm-img-dropzone" tabindex="0" role="button">
                                <i class="fas fa-cloud-arrow-up"></i>
                                <span><?= htmlspecialchars($tp['images_drop'] ?? 'Drop images here or click to upload') ?></span>
                                <small><?= htmlspecialchars($tp['images_drop_hint'] ?? 'Auto-converted to WebP · max 8 MB · drag to reorder') ?></small>
                                <input type="file" class="adm-img-file-input" accept="image/jpeg,image/png,image/gif,image/webp" multiple hidden>
                            </div>
                            <p class="adm-img-status" hidden></p>
                        </div>
                    </div>
                </div>
                <?php
                require_once __DIR__ . '/includes/toggle-field.php';
                sh_admin_toggle_section(
                    $tp['visibility'] ?? 'Visibility',
                    [
                        ['name' => 'featured', 'label' => $tp['featured'] ?? 'Featured on homepage', 'checked' => !empty($record['featured'])],
                        ['name' => 'active', 'label' => $tp['active'] ?? 'Active', 'checked' => ($record['active'] ?? true) !== false],
                    ],
                    'eye'
                );
                ?>
            </div>
        </div>
    </section>

    <section class="adm-edit-section" id="product-section-names" data-panel="names">
        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><?= htmlspecialchars($tp['names_title'] ?? 'Names & descriptions') ?></h2>
                <div class="adm-ai-toolbar">
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shAiGenerateBtn"
                            data-generating="<?= htmlspecialchars($tp['ai_generating'] ?? 'Generating…') ?>"
                            data-ok="<?= htmlspecialchars($tp['ai_ok'] ?? 'Generated and translated for all languages.') ?>"
                            data-demo-ok="<?= htmlspecialchars($tp['ai_demo_ok'] ?? 'Demo templates applied — add API key in Settings → AI.') ?>"
                            data-failed="<?= htmlspecialchars($tp['ai_failed'] ?? 'AI generation failed.') ?>"
                            data-need-name="<?= htmlspecialchars($tp['ai_need_name'] ?? 'Enter a product name first (source language).') ?>">
                        <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($tp['ai_generate'] ?? 'Generate with AI') ?>
                    </button>
                    <span id="shAiStatus" class="adm-ai-status" hidden></span>
                </div>
            </div>
            <div class="adm-card-body padded">
                <div class="adm-ai-source-box adm-ai-source-box--product">
                    <div class="adm-ai-source-fields adm-form-grid">
                        <div class="adm-field adm-field--wide">
                            <label for="shAiProductName"><?= htmlspecialchars($tp['ai_product_name_label'] ?? 'Product name for AI') ?> *</label>
                            <input type="text" id="shAiProductName" class="adm-input-lg"
                                   value="<?= htmlspecialchars($record !== null ? ($record['name'][$aiSourceLang] ?? '') : '') ?>"
                                   placeholder="<?= htmlspecialchars($tp['ai_product_name_ph'] ?? 'e.g. Wireless Headphones Pro') ?>">
                        </div>
                        <div class="adm-field adm-field--wide">
                            <label for="shAiProductBrief"><?= htmlspecialchars($tp['ai_product_brief_label'] ?? 'Brief description for AI') ?></label>
                            <textarea id="shAiProductBrief" rows="3" class="adm-textarea"
                                      placeholder="<?= htmlspecialchars($tp['ai_product_brief_ph'] ?? 'e.g. Wireless ANC headphones, 40h battery, USB-C, for gym and travel') ?>"><?= htmlspecialchars($record !== null ? ($record['desc'][$aiSourceLang] ?? '') : '') ?></textarea>
                            <small class="adm-field-hint"><?= htmlspecialchars($tp['ai_product_name_hint'] ?? 'AI fills names, descriptions, meta title, meta description (OG) and keywords for all languages.') ?></small>
                        </div>
                    </div>
                </div>
                <p class="adm-help"><?= htmlspecialchars($tp['ai_names_help'] ?? 'Edit per-language texts below after generation.') ?></p>
                <?php foreach (sh_langs() as $code => $info): ?>
                <details class="adm-spoiler adm-spoiler-nested" <?= $code === $aiSourceLang ? 'open' : '' ?>>
                    <summary><?= htmlspecialchars($info['name']) ?> (<?= htmlspecialchars($info['label']) ?>)<?= $code === $aiSourceLang ? ' ★' : '' ?></summary>
                    <div class="adm-spoiler-body">
                        <div class="adm-form-grid">
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars($tp['name'] ?? 'Name') ?></label>
                                <input type="text" name="name_<?= htmlspecialchars($code) ?>"
                                       value="<?= htmlspecialchars($record['name'][$code] ?? '') ?>">
                            </div>
                            <div class="adm-field adm-field--wide">
                                <label><?= htmlspecialchars($tp['short_desc'] ?? 'Short description') ?></label>
                                <textarea name="desc_<?= htmlspecialchars($code) ?>" rows="3"><?= htmlspecialchars($record['desc'][$code] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="adm-edit-section" id="product-section-seo" data-panel="seo">
        <div class="adm-card">
            <div class="adm-card-head adm-card-head--stack">
                <h2><?= htmlspecialchars($seo_tp['spoiler_title'] ?? 'SEO & Schema.org') ?></h2>
                <div class="adm-ai-toolbar">
                    <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shAiSeoGenerateBtn"
                            data-generating="<?= htmlspecialchars($seo_tp['ai_generating'] ?? $tp['ai_generating'] ?? 'Generating…') ?>"
                            data-ok="<?= htmlspecialchars($seo_tp['ai_ok'] ?? 'SEO generated for all languages.') ?>"
                            data-demo-ok="<?= htmlspecialchars($tp['ai_demo_ok'] ?? 'Demo templates applied.') ?>"
                            data-failed="<?= htmlspecialchars($tp['ai_failed'] ?? 'AI generation failed.') ?>"
                            data-need-name="<?= htmlspecialchars($seo_tp['ai_need_name'] ?? $tp['ai_need_name'] ?? 'Enter product name first.') ?>">
                        <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($seo_tp['ai_generate'] ?? 'Generate SEO with AI') ?>
                    </button>
                    <span id="shAiSeoStatus" class="adm-ai-status" hidden></span>
                </div>
            </div>
            <?php $seo_panel_mode = true; require __DIR__ . '/includes/seo-spoiler.php'; ?>
        </div>
    </section>

    <div class="adm-form-actions adm-form-actions-sticky">
        <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars($tp['save'] ?? 'Save') ?></button>
        <a href="<?= sh_admin_url('products.php') ?>" class="adm-btn adm-btn-outline"><?= htmlspecialchars($tp['cancel'] ?? 'Cancel') ?></a>
    </div>
</form>
</div>

<aside class="adm-product-edit-aside" id="shProductChecklistAside" aria-label="<?= htmlspecialchars($tp['checklist_panel'] ?? 'Quality checklist') ?>">
    <button type="button" class="adm-checklist-aside-toggle" id="shChecklistAsideToggle"
            aria-expanded="true" aria-controls="shChecklistAsideBody"
            title="<?= htmlspecialchars($tp['checklist_hide'] ?? 'Hide checklist') ?>"
            data-show-label="<?= htmlspecialchars($tp['checklist_show'] ?? 'Show checklist') ?>">
        <i class="fas fa-clipboard-check" aria-hidden="true"></i>
        <span class="adm-checklist-aside-toggle-label"><?= htmlspecialchars($tp['checklist_content'] ?? 'Product information') ?></span>
        <i class="fas fa-chevron-right adm-checklist-aside-chevron" aria-hidden="true"></i>
    </button>
    <div class="adm-checklist-aside-body" id="shChecklistAsideBody">
        <?php
        sh_admin_render_checklist_panel(
            $contentChecklist,
            $contentChecklistLabels,
            'shContentChecklist',
            $tp['checklist_content'] ?? 'Product information',
            'box-open'
        );
        sh_admin_render_checklist_panel(
            $seoChecklist,
            $seoChecklistLabels,
            'shSeoChecklist',
            $tp['checklist_seo'] ?? 'SEO quality',
            'chart-line'
        );
        ?>
        <details class="adm-checklist-tip-spoiler">
            <summary><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($tp['checklist_tip_title'] ?? 'Tips') ?></summary>
            <p class="adm-checklist-tip">
                <?= htmlspecialchars($tp['checklist_tip'] ?? 'Scores update live as you edit. Green = great, yellow = improve, red = fix before publish.') ?>
            </p>
        </details>
    </div>
</aside>
</div>

<script>
window.SH_CHECKLIST_LANGS = <?= json_encode($checklistLangs, JSON_UNESCAPED_UNICODE) ?>;
window.SH_CONTENT_CHECKLIST_LABELS = <?= json_encode($contentChecklistLabels, JSON_UNESCAPED_UNICODE) ?>;
window.SH_SEO_CHECKLIST_LABELS = <?= json_encode($seoChecklistLabels, JSON_UNESCAPED_UNICODE) ?>;
(function () {
    var section = <?= json_encode($scroll_section, JSON_UNESCAPED_UNICODE) ?>;
    var hash = (window.location.hash || '').replace(/^#/, '');
    var id = hash || (section ? 'product-section-' + section : '');
    if (!id) return;
    var el = document.getElementById(id);
    if (el) {
        requestAnimationFrame(function () {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/layout-end.php'; ?>