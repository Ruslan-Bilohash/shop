<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/storage.php';
require_once dirname(__DIR__) . '/includes/product-io.php';
require_once dirname(__DIR__) . '/includes/store-settings.php';
sh_admin_require();

$admin_page = 'products-io';
$tp = $ta['products_io_page'] ?? [];
$page_title = $ta['products_io'] ?? ($tp['title'] ?? 'Import / Export');

$formats = sh_product_io_formats();
$formatGroups = sh_product_io_format_groups();
$groupLabels = $tp['format_groups'] ?? [
    'native'      => 'Shop CMS',
    'platform'    => 'E-commerce platforms',
    'marketplace' => 'Marketplaces',
    'feeds'       => 'Ad & feed exports',
];
$settings = sh_load_settings();
$sourceLang = trim((string) ($settings['ai_source_lang'] ?? ''));
if ($sourceLang === '' || !array_key_exists($sourceLang, sh_langs())) {
    $sourceLang = sh_site_default_lang($settings);
}

$migrationPlatforms = $tp['migration_platforms'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'import' || $action === 'preview') {
        $importFormat = trim($_POST['import_format'] ?? 'auto');
        $importMode = trim($_POST['import_mode'] ?? 'merge');
        if (!in_array($importMode, ['merge', 'append', 'replace'], true)) {
            $importMode = 'merge';
        }
        $importLang = trim($_POST['import_lang'] ?? $sourceLang);
        if (!array_key_exists($importLang, sh_langs())) {
            $importLang = $sourceLang;
        }
        $fillAllLangs = !empty($_POST['fill_all_langs']);
        $importOpts = [
            'update_prices_only' => !empty($_POST['update_prices_only']),
            'update_stock_only'  => !empty($_POST['update_stock_only']),
            'skip_images'        => !empty($_POST['skip_images']),
            'preserve_seo'       => !empty($_POST['preserve_seo']),
        ];

        if (!in_array($importFormat, sh_product_io_import_formats(), true)) {
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

        $detectedFormat = $importFormat === 'auto' ? sh_product_io_detect_format($content) : $importFormat;
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

        if ($action === 'preview') {
            $withSku = 0;
            foreach ($parsed['products'] as $p) {
                if (trim((string) ($p['sku'] ?? '')) !== '') {
                    $withSku++;
                }
            }
            $detectedLabel = $formats[$detectedFormat]['label'] ?? $detectedFormat;
            $summary = sprintf(
                $tp['preview_done'] ?? 'Preview: %d products found (%d with SKU). Detected format: %s.',
                count($parsed['products']),
                $withSku,
                $detectedLabel
            );
            if ($parsed['errors'] !== []) {
                $summary .= ' ' . ($tp['import_warnings'] ?? 'Warnings:') . ' ' . implode('; ', array_slice($parsed['errors'], 0, 5));
            }
            $_SESSION['sh_admin_flash'] = ['type' => 'info', 'msg' => $summary];
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

        $stats = sh_product_io_import_apply($parsed['products'], $importMode, $importOpts);
        $summary = sprintf(
            $tp['import_done'] ?? 'Import complete: %d created, %d updated, %d skipped.',
            $stats['created'],
            $stats['updated'],
            $stats['skipped']
        );
        if ($importFormat === 'auto') {
            $summary .= ' ' . sprintf($tp['detected_format'] ?? '(Detected: %s)', $formats[$detectedFormat]['label'] ?? $detectedFormat);
        }
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
}

$productCount = count(sh_load_products_raw());
$activeCount = count(array_filter(sh_load_products_raw(), static fn(array $p): bool => ($p['active'] ?? true) !== false));
$categories = sh_categories();

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-alert adm-alert-info">
    <i class="fas fa-route"></i>
    <?= htmlspecialchars($tp['intro'] ?? 'Migrate from WooCommerce, Shopify, Rozetka, OpenCart, PrestaShop, Prom.ua — or export to Google & Meta feeds.') ?>
</div>

<?php if ($migrationPlatforms !== []): ?>
<section class="adm-card adm-io-migrate">
    <div class="adm-card-head">
        <h2><i class="fas fa-store"></i> <?= htmlspecialchars($tp['migration_title'] ?? 'Migrate from another store') ?></h2>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help"><?= htmlspecialchars($tp['migration_help'] ?? 'Pick your current platform — we pre-select the import format and show export steps.') ?></p>
        <div class="adm-io-platforms">
            <?php foreach ($migrationPlatforms as $plat): ?>
            <button type="button" class="adm-io-platform" data-format="<?= htmlspecialchars((string) ($plat['format'] ?? '')) ?>" data-import-format="<?= htmlspecialchars((string) ($plat['import_format'] ?? 'auto')) ?>">
                <span class="adm-io-platform-icon"><i class="<?= htmlspecialchars((string) ($plat['icon'] ?? 'fas fa-shopping-cart')) ?>"></i></span>
                <span class="adm-io-platform-name"><?= htmlspecialchars((string) ($plat['name'] ?? '')) ?></span>
                <span class="adm-io-platform-hint"><?= htmlspecialchars((string) ($plat['hint'] ?? '')) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <details class="adm-spoiler adm-spoiler-nested" id="shMigrationGuide" hidden>
            <summary id="shMigrationGuideTitle"><?= htmlspecialchars($tp['migration_steps_title'] ?? 'Migration steps') ?></summary>
            <div class="adm-spoiler-body">
                <ol class="adm-guide-steps" id="shMigrationSteps"></ol>
            </div>
        </details>
    </div>
</section>
<?php endif; ?>

<div class="adm-io-grid">
    <div class="adm-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-file-export"></i> <?= htmlspecialchars($tp['export_title'] ?? 'Export products') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <p class="adm-help"><?= htmlspecialchars($tp['export_help'] ?? 'Download the current catalog. JSON keeps all languages and SEO fields.') ?></p>
            <form method="get" action="<?= sh_admin_url('api/products-export.php') ?>" class="adm-form-grid adm-form-grid--settings" id="shExportForm">
                <div class="adm-field adm-field--wide">
                    <label><?= htmlspecialchars($tp['format'] ?? 'Format') ?></label>
                    <select name="format" id="shExportFormat" required>
                        <?php foreach ($formatGroups as $groupKey => $groupFormats): ?>
                        <optgroup label="<?= htmlspecialchars($groupLabels[$groupKey] ?? $groupKey) ?>">
                            <?php foreach ($groupFormats as $key):
                                if (!in_array($key, sh_product_io_export_formats(), true)) {
                                    continue;
                                }
                                $meta = $formats[$key] ?? null;
                                if (!$meta) {
                                    continue;
                                }
                            ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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
                <div class="adm-field">
                    <label><?= htmlspecialchars($tp['export_category'] ?? 'Category filter') ?></label>
                    <select name="category">
                        <option value=""><?= htmlspecialchars($tp['export_category_all'] ?? 'All categories') ?></option>
                        <?php foreach ($categories as $catSlug): ?>
                        <option value="<?= htmlspecialchars($catSlug) ?>"><?= htmlspecialchars(sh_category_label($catSlug, $lang)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field adm-field--wide adm-io-toggles">
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="active_only" value="1">
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars($tp['active_only'] ?? 'Active products only') ?></span>
                    </label>
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="featured_only" value="1">
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars($tp['featured_only'] ?? 'Featured only') ?></span>
                    </label>
                    <label class="adm-toggle adm-toggle--compact">
                        <input type="checkbox" name="in_stock_only" value="1">
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars($tp['in_stock_only'] ?? 'In stock only') ?></span>
                    </label>
                    <label class="adm-toggle adm-toggle--compact" id="shIncludeSeoWrap">
                        <input type="checkbox" name="include_seo" value="1" checked>
                        <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                        <span class="adm-toggle-label"><?= htmlspecialchars($tp['include_seo'] ?? 'Include SEO fields (JSON)') ?></span>
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
                        <?php foreach ($tp['format_notes'] ?? [] as $note): ?>
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
            <p class="adm-help"><?= htmlspecialchars($tp['import_help'] ?? 'Upload CSV or JSON. Auto-detect reads WooCommerce, Shopify, Rozetka, Prom.ua and more.') ?></p>
            <form method="post" enctype="multipart/form-data" class="adm-settings-form" id="shImportForm">
                <input type="hidden" name="action" value="import" id="shImportAction">
                <div class="adm-form-grid adm-form-grid--settings">
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['format'] ?? 'Format') ?></label>
                        <select name="import_format" id="shImportFormat" required>
                            <?php foreach ($formatGroups as $groupKey => $groupFormats): ?>
                            <optgroup label="<?= htmlspecialchars($groupLabels[$groupKey] ?? $groupKey) ?>">
                                <?php foreach ($groupFormats as $key):
                                    if (!in_array($key, sh_product_io_import_formats(), true)) {
                                        continue;
                                    }
                                    $meta = $formats[$key] ?? null;
                                    if (!$meta) {
                                        continue;
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= $key === 'auto' ? 'selected' : '' ?>><?= htmlspecialchars($meta['label']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
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
                    <div class="adm-field adm-field--wide adm-io-toggles">
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="fill_all_langs" value="1" checked>
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['fill_all_langs'] ?? 'Copy source text to all languages') ?></span>
                        </label>
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="preserve_seo" value="1" checked>
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['preserve_seo'] ?? 'Keep existing SEO on update') ?></span>
                        </label>
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="skip_images" value="1">
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['skip_images'] ?? 'Do not overwrite images') ?></span>
                        </label>
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="update_prices_only" value="1" id="shUpdatePricesOnly">
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['update_prices_only'] ?? 'Update prices & stock only') ?></span>
                        </label>
                        <label class="adm-toggle adm-toggle--compact">
                            <input type="checkbox" name="update_stock_only" value="1" id="shUpdateStockOnly">
                            <span class="adm-toggle-track"><span class="adm-toggle-thumb"></span></span>
                            <span class="adm-toggle-label"><?= htmlspecialchars($tp['update_stock_only'] ?? 'Update stock quantity only') ?></span>
                        </label>
                    </div>
                    <div class="adm-field adm-field--wide">
                        <label><?= htmlspecialchars($tp['import_file'] ?? 'File') ?></label>
                        <input type="file" name="import_file" accept=".csv,.json,.txt,.xml" required>
                        <small class="adm-field-hint"><?= htmlspecialchars($tp['import_file_hint'] ?? 'CSV or JSON up to your server upload limit.') ?></small>
                    </div>
                    <div class="adm-field adm-field--wide" id="shReplaceConfirmWrap" hidden>
                        <label><?= htmlspecialchars($tp['replace_confirm_label'] ?? 'Type REPLACE to confirm') ?></label>
                        <input type="text" name="replace_confirm" placeholder="REPLACE" autocomplete="off">
                    </div>
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn adm-btn-primary" id="shImportBtn">
                        <i class="fas fa-upload"></i> <?= htmlspecialchars($tp['import_btn'] ?? 'Import products') ?>
                    </button>
                    <button type="button" class="adm-btn adm-btn-outline" id="shPreviewBtn">
                        <i class="fas fa-search"></i> <?= htmlspecialchars($tp['preview_btn'] ?? 'Preview file') ?>
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
    if (mode && wrap) {
        function toggleReplace() { wrap.hidden = mode.value !== 'replace'; }
        mode.addEventListener('change', toggleReplace);
        toggleReplace();
    }

    var pricesOnly = document.getElementById('shUpdatePricesOnly');
    var stockOnly = document.getElementById('shUpdateStockOnly');
    if (pricesOnly && stockOnly) {
        pricesOnly.addEventListener('change', function () { if (pricesOnly.checked) stockOnly.checked = false; });
        stockOnly.addEventListener('change', function () { if (stockOnly.checked) pricesOnly.checked = false; });
    }

    var exportFmt = document.getElementById('shExportFormat');
    var seoWrap = document.getElementById('shIncludeSeoWrap');
    if (exportFmt && seoWrap) {
        function toggleSeo() { seoWrap.hidden = exportFmt.value !== 'shop_json'; }
        exportFmt.addEventListener('change', toggleSeo);
        toggleSeo();
    }

    var previewBtn = document.getElementById('shPreviewBtn');
    var importAction = document.getElementById('shImportAction');
    var importForm = document.getElementById('shImportForm');
    if (previewBtn && importAction && importForm) {
        previewBtn.addEventListener('click', function () {
            importAction.value = 'preview';
            importForm.submit();
        });
        importForm.addEventListener('submit', function () {
            if (importAction.value !== 'preview') importAction.value = 'import';
        });
    }

    var platforms = <?= json_encode($migrationPlatforms, JSON_UNESCAPED_UNICODE) ?>;
    var importFmt = document.getElementById('shImportFormat');
    var exportFmtSel = document.getElementById('shExportFormat');
    var guide = document.getElementById('shMigrationGuide');
    var guideSteps = document.getElementById('shMigrationSteps');
    var guideTitle = document.getElementById('shMigrationGuideTitle');

    document.querySelectorAll('.adm-io-platform').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.adm-io-platform').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            var fmt = btn.getAttribute('data-format') || '';
            var imp = btn.getAttribute('data-import-format') || 'auto';
            if (importFmt) importFmt.value = imp;
            if (exportFmtSel && fmt) exportFmtSel.value = fmt;
            if (exportFmt) exportFmt.dispatchEvent(new Event('change'));

            var name = btn.querySelector('.adm-io-platform-name');
            var key = name ? name.textContent.trim() : '';
            var plat = platforms.find(function (p) { return p.name === key; });
            if (guide && guideSteps && plat && plat.steps) {
                guide.hidden = false;
                if (guideTitle) guideTitle.textContent = plat.name + ' — ' + (<?= json_encode($tp['migration_steps_title'] ?? 'Migration steps') ?>);
                guideSteps.innerHTML = '';
                plat.steps.forEach(function (step) {
                    var li = document.createElement('li');
                    li.textContent = step;
                    guideSteps.appendChild(li);
                });
                guide.open = true;
                document.getElementById('shImportForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
})();
</script>

<?php require __DIR__ . '/includes/layout-end.php'; ?>