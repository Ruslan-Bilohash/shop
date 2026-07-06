<?php
require_once __DIR__ . '/init.php';

$flash = '';
$flash_type = '';

if (isset($_GET['action']) && $_GET['action'] === 'add') {
    $id = trim($_GET['id'] ?? '');
    $qty = max(1, min(99, (int)($_GET['qty'] ?? 1)));
    if ($id !== '' && sh_cart_add($id, $qty)) {
        $flash = $t['product']['added'];
        $flash_type = 'success';
    }
    header('Location: ' . sh_url('cart.php') . ($lang !== 'no' ? '?lang=' . urlencode($lang) : ''), true, 302);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        foreach ($_POST['qty'] ?? [] as $pid => $qty) {
            sh_cart_update((string)$pid, (int)$qty);
        }
        $flash = $t['cart']['updated'];
        $flash_type = 'success';
    } elseif ($action === 'remove') {
        sh_cart_remove((string)($_POST['id'] ?? ''));
        $flash = $t['cart']['updated'];
        $flash_type = 'success';
    } elseif ($action === 'clear') {
        sh_cart_clear();
        $flash = $t['cart']['cleared'];
        $flash_type = 'success';
    }
}

$current_page = 'cart';
require_once __DIR__ . '/includes/tax-settings.php';
$lines = sh_cart_lines($lang);
$settings = sh_load_settings();
$total = sh_cart_total_gross($settings);
$page_title = $t['cart']['title'] . ' — ' . ($t['meta']['site_name'] ?? 'Shop CMS');
$page_desc  = $t['meta']['description'];
$canonical  = $site_url . '/cart.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = sh_absolute_url($canonical);
$seo_noindex = true;
$seo_schemas = [
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $t['cart']['title'], 'url' => $canon_abs],
    ]),
];
require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-cart-page">
    <h1><?= htmlspecialchars($t['cart']['title']) ?></h1>

    <?php if ($flash): ?>
    <div class="sh-alert sh-alert-<?= htmlspecialchars($flash_type) ?>"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if (empty($lines)): ?>
    <div class="sh-form-card sh-empty-state">
        <i class="fas fa-shopping-cart"></i>
        <p><?= htmlspecialchars($t['cart']['empty']) ?></p>
        <span class="sh-empty-sub"><?= htmlspecialchars($t['cart']['empty_sub']) ?></span>
        <a href="<?= sh_url('search.php') ?>" class="sh-btn-primary"><?= htmlspecialchars($t['cart']['continue']) ?></a>
    </div>
    <?php else: ?>
    <div class="sh-cart-table-wrap">
        <table class="sh-cart-table">
            <thead>
                <tr>
                    <th><?= htmlspecialchars($t['cart']['product']) ?></th>
                    <th><?= htmlspecialchars($t['cart']['price']) ?></th>
                    <th><?= htmlspecialchars($t['cart']['qty']) ?></th>
                    <th><?= htmlspecialchars($t['cart']['subtotal']) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                <tr>
                    <td class="sh-cart-product-cell" data-label="<?= htmlspecialchars($t['cart']['product']) ?>">
                        <a href="<?= sh_url('product.php?id=' . urlencode($line['id'])) ?>" class="sh-cart-thumb-link">
                            <img src="<?= htmlspecialchars($line['image']) ?>" alt="" width="64" height="64" loading="lazy">
                        </a>
                        <a href="<?= sh_url('product.php?id=' . urlencode($line['id'])) ?>"><?= htmlspecialchars($line['name']) ?></a>
                    </td>
                    <td data-label="<?= htmlspecialchars($t['cart']['price']) ?>"><?= sh_price($line['price']) ?></td>
                    <td data-label="<?= htmlspecialchars($t['cart']['qty']) ?>">
                        <form method="post" action="<?= sh_url('cart.php') ?>" class="sh-cart-qty-form">
                            <input type="hidden" name="action" value="update">
                            <input type="number" name="qty[<?= htmlspecialchars($line['id']) ?>]" value="<?= (int)$line['qty'] ?>" min="0" max="99" class="sh-qty-input" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td data-label="<?= htmlspecialchars($t['cart']['subtotal']) ?>"><strong><?= sh_price($line['subtotal']) ?></strong></td>
                    <td>
                        <form method="post" action="<?= sh_url('cart.php') ?>" class="sh-cart-remove-form">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($line['id']) ?>">
                            <button type="submit" class="sh-btn-ghost sh-btn-sm"><i class="fas fa-trash"></i> <?= htmlspecialchars($t['cart']['remove']) ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sh-cart-footer">
        <div class="sh-cart-actions">
            <a href="<?= sh_url('search.php') ?>" class="sh-btn-outline"><?= htmlspecialchars($t['cart']['continue']) ?></a>
            <form method="post" action="<?= sh_url('cart.php') ?>" style="display:inline">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="sh-btn-ghost"><?= htmlspecialchars($t['cart']['remove_all'] ?? $t['cart']['remove']) ?></button>
            </form>
        </div>
        <div class="sh-cart-summary">
            <?php require __DIR__ . '/includes/cart-order-totals.php'; ?>
            <a href="<?= sh_url('checkout.php') ?>" class="sh-btn-primary sh-btn-lg sh-btn-block">
                <i class="fas fa-credit-card"></i> <?= htmlspecialchars($t['cart']['checkout']) ?>
            </a>
            <p class="sh-checkout-note"><?= htmlspecialchars($t['cart']['checkout_note']) ?></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>