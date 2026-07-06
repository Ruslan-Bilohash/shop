<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/storage.php';
require_once dirname(__DIR__) . '/includes/product-io.php';
require_once dirname(__DIR__) . '/includes/store-settings.php';
sh_admin_require();

$admin_page = 'products-io';
$tp = $ta['products_io_page'] ?? [];
$page_title = $ta['products_io'] ?? ($tp['title'] ?? 'Import / Export');

$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

$formats = sh_product_io_formats();
$settings = sh_load_settings();
$sourceLang = trim((string) ($settings['ai_source_lang'] ?? ''));
if ($sourceLang === '' || !array_key_exists($sourceLang, sh_langs())) {
    $sourceLang = sh_site_default_lang($settings);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'import') {
    $importFormat = trim($_POST['import_format'] ?? 'shop_csv');
    $importMode = trim($_POST['import_mode'] ?? 'merge');
    if (!in_array($importMode, ['merge', 'append', 'replace'], true)) {
        $importMode = 'merge';
    }
    $importLang = trim($_POST['import_lang'] ?? $sourceLang);
    if (!array_key_exists($importLang, sh_langs())) {
        $importLang = $sourceLang;
    }
    $fillAllLangs = !empty($_POST['fill_all_langs']);

    if (!isset($formats[$importFormat])) {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['import_format_invalid'] ?? 'Unknown import format.'];
        header('Location: ' . sh_admin_url('products-io.php'));
        exit;
    }

    if (empty($_FILES['import_file']['tmp_name']) || !is_upload_file($_FILES['import_file']['tmp_name'])) {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['import_no_file'] ?? 'Choose a file to import.'];
        header('Location: ' . sh_admin_url('products-io.php'));
        exit;
    }

    $content = file_get_contents($_FILES['import_file']['tmp_name']) ?: '';
    if ($content === '') {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['import_empty'] ?? 'File is empty.'];
        header('Location: ' . sh_admin_url('products-io.php'));
        exit;
    }

    $parsed = sh_product_io_parse_import($importFormat, $content, $importLang, $fillAllLangs);
    if ($parsed['products'] === []) {
        $msg = $tp['import_parse_fail'] ?? 'No products found in file.';
        if ($parsed['errors'] !== []) {
            $msg .= ' ' . implode(' ', array_slice($parsed['errors'], 0, 3));
        }
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $msg];
        header('Location: ' . sh_admin_url('products-io.php'));
        exit;
    }

    if ($importMode === 'replace') {
        $confirm = trim($_POST['replace_confirm'] ?? '');
        if ($confirm !== 'REPLACE') {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['import_replace_confirm'] ?? 'Type REPLACE to replace the entire catalog.'];
            header('Location: ' . sh_admin_url('products-io.php'));
            exit;
        }
    }

    $stats = sh_product_io_import_apply($parsed['products'], $importMode);
    $summary = sprintf(
        $tp['import_done'] ?? 'Import complete: %d created, %d updated, %d skipped.',
        $stats['created'],
        $stats['updated'],
        $stats['skipped']
    );
    if ($parsed['errors'] !== []) {
        $summary .= ' ' . ($tp['import_warnings'] ?? 'Warnings:') . ' ' . implode('; ', array_slice($parsed['errors'], 0, 5));
    }
    if ($stats['errors'] !== []) {
        $summary .= ' ' . implode('; ', array_slice($stats['errors'], 0, 3));
    }

    $_SESSION['sh_admin_flash'] = [
        'type' => $stats['errors'] !== [] && $stats['created'] + $stats['updated'] === 0 ? 'error' : 'success',
        'msg'  => $summary,
    ];
    header('Location: ' . sh_admin_url('products-io.php'));
    exit;
}

$productCount = count(sh_load_products_raw());
$activeCount = count(array_filter(sh_load_products_raw(), static fn(array $p): bool => ($p['active'] ?? true) !== false));

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg'] ?? '') ?>
</div>
<?php endif; ?>

<div class="adm-alert adm-alert-info">
    <i class="fas fa-info-circle"></i>
    <?= htmlspecialchars($tp['intro'] ?? 'Export catalog to Rozetka, WooCommerce, OpenCart or import back into Shop CMS.') ?>
</div>

<div class="adm-io-grid">
    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-export"></i> <?= htmlspecialchars($tp['export_title'] ?? 'Export products') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($tp['export_help'] ?? 'Download the current catalog. JSON keeps all languages and SEO fields.') ?></p>
            <form method="get" action="<?= sh_admin_url('api/products-export.php') ?>" class="adm-form-grid adm-form-grid--settings">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars($tp['format'] ?? 'Format') ?></label>
                    <select name="format" required>
                        <?php foreach ($formats as $key => $meta): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field">
                    <label><?= htmlspecialchars($tp['export_lang'] ?? 'Export language') ?></label>
                    <select name="lang">
                        <?php foreach (sh_langs() as $code => $info): ?>
                        <option value="<?= htmlspecialchars($code) ?>" <?= $code === $lang ? 'selected' : '' ?>><?= htmlspecialchars($info['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="adm-field-hint"><?= htmlspecialchars($tp['export_lang_hint'] ?? 'For CSV marketplaces — single language column.') ?></small>
                </div>
                <div class="adm-field adm-field--wide">
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="active_only" value="1">
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars($tp['active_only'] ?? 'Active products only') ?></span>
                    </label>
                </div>
                <div class="adm-field adm-field--wide">
                    <p class="adm-muted adm-help-compact">
                        <?= htmlspecialchars(sprintf($tp['catalog_stats'] ?? 'Catalog: %d products (%d active).', $productCount, $activeCount)) ?>
                    </p>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-download"></i> <?= htmlspecialchars($tp['export_btn'] ?? 'Download file') ?>
                    </button>
                </div>
            </form>
            <details class="adm-spoiler adm-spoiler-nested" style="margin-top:16px">
                <summary><?= htmlspecialchars($tp['formats_help_title'] ?? 'Format notes') ?></summary>
                <div class="adm-spoiler-body">
                    <ul class="adm-guide-steps">
                        <?php foreach ($tp['format_notes'] ?? [
                            'Shop JSON — full backup with all languages, images, SEO.',
                            'Rozetka CSV — semicolon delimiter, vendor code, prices, stock, images (|).',
                            'WooCommerce CSV — compatible with WordPress product import plugins.',
                            'OpenCart CSV — model, quantity, status, category.',
                            'Generic CSV — id, sku, multilingual name_*/desc_* columns.',
                        ] as $note): ?>
                        <li><?= htmlspecialchars($note) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </details>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-import"></i> <?= htmlspecialchars($tp['import_title'] ?? 'Import products') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($tp['import_help'] ?? 'Upload CSV or JSON. Existing products match by SKU or ID.') ?></p>
            <form method="post" enctype="multipart/form-data" class="adm-settings-form">
                <input type="hidden" name="action" value="import">
                <div class="adm-form-grid adm-form-grid--settings">
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['format'] ?? 'Format') ?></label>
                        <select name="import_format" required>
                            <?php foreach ($formats as $key => $meta): ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['import_mode'] ?? 'Import mode') ?></label>
                        <select name="import_mode" id="shImportMode">
                            <option value="merge"><?= htmlspecialchars($tp['mode_merge'] ?? 'Merge — update by SKU/ID, add new') ?></option>
                            <option value="append"><?= htmlspecialchars($tp['mode_append'] ?? 'Append only — skip existing SKU/ID') ?></option>
                            <option value="replace"><?= htmlspecialchars($tp['mode_replace'] ?? 'Replace all — clear catalog first') ?></option>
                        </select>
                    </div>
                    <div class="adm-field">
                        <label><?= htmlspecialchars($tp['import_lang'] ?? 'Source language') ?></label>
                        <select name="import_lang">
                            <?php foreach (sh_langs() as $code => $info): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === $sourceLang ? 'selected' : '' ?>><?= htmlspecialchars($info['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="fill_all_langs" value="1" checked>
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['fill_all_langs'] ?? 'Copy source text to all languages') ?></span>
                        </label>
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['import_file'] ?? 'File') ?></label>
                        <input type="file" name="import_file" accept=".csv,.json,.txt" required>
                    </div>
                    <div class="adm-field adm-field--wide" id="shReplaceConfirmWrap" hidden>
                        <label><?= htmlspecialchars($tp['replace_confirm_label'] ?? 'Type REPLACE to confirm') ?></label>
                        <input type="text" name="replace_confirm" placeholder="REPLACE" autocomplete="off">
                    </div>
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="fas fa-upload"></i> <?= htmlspecialchars($tp['import_btn'] ?? 'Import products') ?>
                    </button>
                    <a href="<?= sh_admin_url('products.php') ?>" class="adm-btn adm-btn-outline"><?= htmlspecialchars($tp['back'] ?? 'Back to products') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var mode = document.getElementById('shImportMode');
    var wrap = document.getElementById('shReplaceConfirmWrap');
    if (!mode || !wrap) return;
    function toggle() {
        wrap.hidden = mode.value !== 'replace';
    }
    mode.addEventListener('change', toggle);
    toggle();
})();
</script>

<?php require __DIR__ . '/includes/layout-end.php'; ?>