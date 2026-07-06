<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/category-storage.php';
sh_admin_require();

$admin_page = 'categories';
$page_title = $ta['categories'] ?? 'Categories';
$tp = $ta['categories_page'] ?? [];

$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_slug'])) {
    $slug = trim($_POST['delete_slug'] ?? '');
    if ($slug === '') {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['delete_error'] ?? 'Cannot delete category.'];
    } elseif (sh_category_by_slug($slug, true) === null) {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['not_found'] ?? 'Category not found.'];
    } else {
        $hadProducts = sh_category_product_count($slug) > 0;
        if (sh_category_delete($slug)) {
        $_SESSION['sh_admin_flash'] = [
            'type' => 'success',
            'msg'  => $hadProducts
                ? ($tp['deleted_with_products'] ?? 'Category deleted. Linked products are now uncategorized.')
                : ($tp['deleted'] ?? 'Category deleted.'),
        ];
        } else {
        $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $tp['delete_error'] ?? 'Cannot delete category.'];
        }
    }
    header('Location: ' . sh_admin_url('categories.php'));
    exit;
}

$categories = sh_category_records(false);
$chart = sh_admin_category_chart();
$admin_extra_js = [sh_asset('js/admin-categories.js') . '?v=2'];

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg'] ?? '') ?>
</div>
<?php endif; ?>

<div class="adm-alert adm-alert-info">
    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($tp['note'] ?? 'Manage category slugs, icons and multilingual names. Products keep their category slug.') ?>
</div>

<div class="adm-stats adm-stats--compact">
    <div class="adm-stat adm-stat--mini">
        <div class="adm-stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div>
            <div class="adm-stat-val"><?= count($categories) ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($ta['stats_cats'] ?? 'Categories') ?></div>
        </div>
    </div>
    <div class="adm-stat adm-stat--mini">
        <div class="adm-stat-icon green"><i class="fas fa-check"></i></div>
        <div>
            <div class="adm-stat-val"><?= count(array_filter($categories, fn($c) => ($c['active'] ?? true) !== false)) ?></div>
            <div class="adm-stat-label"><?= htmlspecialchars($tp['active'] ?? 'Active') ?></div>
        </div>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-head adm-card-head--stack">
        <h2><i class="fas fa-layer-group"></i> <?= htmlspecialchars($tp['list_title'] ?? 'All categories') ?></h2>
        <div class="adm-card-head-actions adm-card-head-actions--wrap">
            <span id="shCatSortStatus" class="adm-cat-sort-status" hidden></span>
            <span class="adm-badge adm-badge--blue"><?= count($categories) ?> <?= htmlspecialchars($ta['stats_cats'] ?? 'Categories') ?></span>
            <a href="<?= sh_admin_url('category-edit.php') ?>" class="adm-btn adm-btn-primary adm-btn-sm">
                <i class="fas fa-plus"></i> <?= htmlspecialchars($tp['add'] ?? 'Add category') ?>
            </a>
        </div>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help"><i class="fas fa-grip-vertical"></i> <?= htmlspecialchars($tp['drag_hint'] ?? 'Drag rows to change display priority on the storefront.') ?></p>
        <div class="adm-table-wrap">
        <table class="adm-table adm-table--cards adm-cat-table">
            <thead>
                <tr>
                    <th class="adm-cat-col-drag" aria-label="<?= htmlspecialchars($tp['sort'] ?? 'Sort') ?>"></th>
                    <th class="adm-cat-col-icon"><?= htmlspecialchars($tp['icon'] ?? 'Icon') ?></th>
                    <th><?= htmlspecialchars($tp['name'] ?? 'Name') ?></th>
                    <th><?= htmlspecialchars($tp['products'] ?? 'Products') ?></th>
                    <th><?= htmlspecialchars($tp['status'] ?? 'Status') ?></th>
                    <th><?= htmlspecialchars($tp['actions'] ?? 'Actions') ?></th>
                </tr>
            </thead>
            <tbody id="shCatSortable"
                   data-sort-url="<?= htmlspecialchars(sh_admin_url('api/category-sort.php')) ?>"
                   data-saving="<?= htmlspecialchars($tp['sort_saving'] ?? 'Saving order…') ?>"
                   data-saved="<?= htmlspecialchars($tp['sort_saved'] ?? 'Display order saved.') ?>"
                   data-error="<?= htmlspecialchars($tp['sort_error'] ?? 'Could not save order.') ?>">
                <?php foreach ($categories as $cat):
                    $slug = $cat['slug'] ?? '';
                    $count = sh_category_product_count($slug);
                    $active = ($cat['active'] ?? true) !== false;
                    $catUrl = sh_url('search.php?category=' . urlencode($slug));
                    $catName = sh_localized($cat, 'name', $lang);
                ?>
                <tr data-slug="<?= htmlspecialchars($slug) ?>">
                    <td class="adm-cat-col-drag" data-label="">
                        <span class="adm-cat-drag-handle" title="<?= htmlspecialchars($tp['drag_handle'] ?? 'Drag to reorder') ?>" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>
                    </td>
                    <td class="adm-cat-col-icon" data-label="<?= htmlspecialchars($tp['icon'] ?? 'Icon') ?>">
                        <span class="adm-cat-icon" title="<?= htmlspecialchars($cat['icon'] ?? 'tag') ?>" aria-hidden="true">
                            <i class="fas fa-<?= htmlspecialchars($cat['icon'] ?? 'tag') ?>"></i>
                        </span>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['name'] ?? 'Name') ?>">
                        <a href="<?= htmlspecialchars($catUrl) ?>" class="adm-cat-name-link" target="_blank" rel="noopener">
                            <strong><?= htmlspecialchars($catName) ?></strong>
                        </a><br>
                        <small class="adm-muted-inline">
                            <?= htmlspecialchars(sh_localized($cat, 'name', 'no')) ?> ·
                            <?= htmlspecialchars(sh_localized($cat, 'name', 'en')) ?> ·
                            <?= htmlspecialchars(sh_localized($cat, 'name', 'uk')) ?> ·
                            <?= htmlspecialchars(sh_localized($cat, 'name', 'ru')) ?>
                        </small>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['products'] ?? 'Products') ?>"><?= (int)$count ?></td>
                    <td data-label="<?= htmlspecialchars($tp['status'] ?? 'Status') ?>">
                        <span class="adm-badge <?= $active ? 'adm-badge--green' : 'adm-badge--muted' ?>">
                            <?= htmlspecialchars($active ? ($tp['active'] ?? 'Active') : ($tp['inactive'] ?? 'Inactive')) ?>
                        </span>
                    </td>
                    <td data-label="<?= htmlspecialchars($tp['actions'] ?? 'Actions') ?>">
                        <div class="adm-actions-row">
                            <a href="<?= sh_admin_url('category-edit.php?slug=' . urlencode($slug)) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                                <i class="fas fa-pen"></i> <?= htmlspecialchars($tp['edit'] ?? 'Edit') ?>
                            </a>
                            <a href="<?= sh_admin_url('category-edit.php?slug=' . urlencode($slug) . '&tab=seo') ?>" class="adm-btn adm-btn-outline adm-btn-sm" title="<?= htmlspecialchars($tp['tab_seo'] ?? 'SEO & Schema') ?>">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <a href="<?= sh_url('search.php?category=' . urlencode($slug)) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php if ($count === 0): ?>
                            <form method="post" class="adm-inline-form" onsubmit="return confirm('<?= htmlspecialchars($tp['delete_confirm'] ?? 'Delete this category?') ?>')">
                                <input type="hidden" name="delete_slug" value="<?= htmlspecialchars($slug) ?>">
                                <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-head"><h2><?= htmlspecialchars($ta['by_category'] ?? 'By category') ?></h2></div>
    <div class="adm-card-body padded">
        <?php require __DIR__ . '/includes/category-stats-grid.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>