<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/storage.php';
require_once dirname(__DIR__) . '/includes/seo-checklist.php';
sh_admin_require();

$seoLabels = $ta['products_page']['seo_checklist'] ?? [];

$admin_page = 'products';
$tp = $ta['products_page'] ?? [];
$page_title = $ta['products'] ?? 'Products';

$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = trim($_POST['delete_id'] ?? '');
    if ($id !== '' && sh_product_delete($id)) {
        $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $tp['deleted'] ?? 'Product deleted.'];
    } else {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['delete_error'] ?? 'Could not delete product.'];
    }
    header('Location: ' . sh_admin_url('products.php'));
    exit;
}

$products = sh_load_products_raw();
usort($products, fn($a, $b) => strcmp(sh_localized($a, 'name', $lang), sh_localized($b, 'name', $lang)));

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg'] ?? '') ?>
</div>
<?php endif; ?>

<div class="adm-alert adm-alert-info">
    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($tp['note'] ?? 'Manage demo products, prices, stock and per-product SEO in the editor.') ?>
</div>

<div class="adm-card">
    <div class="adm-card-head">
        <h2><?= htmlspecialchars($tp['list_title'] ?? 'All products') ?></h2>
        <div class="adm-card-head-actions">
            <a href="<?= sh_admin_url('products-io.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-file-import"></i> <?= htmlspecialchars($ta['products_io'] ?? 'Import / Export') ?>
            </a>
            <a href="<?= sh_admin_url('product-edit.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm">
                <i class="fas fa-plus"></i> <?= htmlspecialchars($tp['add'] ?? 'Add product') ?>
            </a>
        </div>
    </div>
    <div class="adm-card-body">
        <div class="adm-table-wrap">
        <table class="adm-table adm-table--cards">
            <thead>
                <tr>
                    <th><?= htmlspecialchars($tp['product'] ?? 'Product') ?></th>
                    <th><?= htmlspecialchars($tp['category'] ?? 'Category') ?></th>
                    <th><?= htmlspecialchars($tp['price'] ?? 'Price') ?></th>
                    <th><?= htmlspecialchars($tp['stock'] ?? 'Stock') ?></th>
                    <th><?= htmlspecialchars($tp['status'] ?? 'Status') ?></th>
                    <th><?= htmlspecialchars($tp['col_seo'] ?? 'SEO') ?></th>
                    <th><?= htmlspecialchars($tp['actions'] ?? 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product):
                    $id = $product['id'] ?? '';
                    $active = ($product['active'] ?? true) !== false;
                    $seoReport = sh_product_seo_checklist($product, $seoLabels);
                ?>
                <tr>
                    <td data-label="<?= htmlspecialchars($tp['product'] ?? 'Product') ?>">
                        <div class="adm-product-cell">
                            <img src="<?= htmlspecialchars(sh_product_image($product)) ?>" alt="" loading="lazy" width="40" height="40"
                                 onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
                            <div>
                                <a href="<?= htmlspecialchars(sh_url('product.php?id=' . urlencode($id))) ?>" class="adm-product-name-link" target="_blank" rel="noopener">
                                    <strong><?= htmlspecialchars(sh_localized($product, 'name', $lang)) ?></strong>
                                </a><br>
                                <code class="adm-muted-inline"><?= htmlspecialchars($id) ?></code>
                                <?php if (!empty($product['featured'])): ?>
                                <span class="adm-badge adm-badge--gold"><i class="fas fa-star"></i></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['category'] ?? 'Category') ?>"><?= htmlspecialchars(sh_category_label($product['category'] ?? '', $lang)) ?></td>
                    <td data-label="<?= htmlspecialchars($tp['price'] ?? 'Price') ?>"><?= sh_price(sh_product_price($product)) ?></td>
                    <td data-label="<?= htmlspecialchars($tp['stock'] ?? 'Stock') ?>"><?= (int)($product['stock'] ?? 0) ?></td>
                    <td data-label="<?= htmlspecialchars($tp['status'] ?? 'Status') ?>">
                        <span class="adm-badge <?= $active ? 'adm-badge--green' : 'adm-badge--muted' ?>">
                            <?= htmlspecialchars($active ? ($tp['active'] ?? 'Active') : ($tp['inactive'] ?? 'Inactive')) ?>
                        </span>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['col_seo'] ?? 'SEO') ?>">
                        <?php sh_admin_render_seo_score_pill((int) $seoReport['score'], $seoReport['grade']); ?>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['actions'] ?? 'Actions') ?>">
                        <div class="adm-actions-row">
                            <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($id)) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                                <i class="fas fa-pen"></i> <?= htmlspecialchars($tp['edit'] ?? 'Edit') ?>
                            </a>
                            <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($id) . '#product-section-seo') ?>" class="adm-btn adm-btn-outline adm-btn-sm" title="<?= htmlspecialchars($tp['tab_seo'] ?? 'SEO & Schema') ?>">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <a href="<?= sh_url('product.php?id=' . urlencode($id)) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form method="post" class="adm-inline-form" onsubmit="return confirm('<?= htmlspecialchars($tp['delete_confirm'] ?? 'Delete this product?') ?>')">
                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($id) ?>">
                                <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>