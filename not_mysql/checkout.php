<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/payment-settings.php';

require_once __DIR__ . '/includes/tax-settings.php';
$lines = sh_cart_lines($lang);
$settings = sh_load_settings();
$total = sh_cart_total_gross($settings);

if (empty($lines)) {
    header('Location: ' . sh_url('cart.php') . ($lang !== 'no' ? '?lang=' . urlencode($lang) : ''), true, 302);
    exit;
}

$flash = '';
$flash_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = trim($_POST['payment_method'] ?? '');
    $valid = ['stripe', 'paypal', 'vipps', 'cod'];
    if (in_array($method, $valid, true) && !empty($settings[$method]['enabled'])) {
        $flash = $t['checkout']['demo_success'] ?? 'Demo order placed — no payment processed.';
        $flash_type = 'success';
        sh_cart_clear();
    } else {
        $flash = $t['checkout']['method_invalid'] ?? 'Please select a valid payment method.';
        $flash_type = 'error';
    }
}

$methods = [];
foreach (['stripe' => 'fab fa-stripe-s', 'paypal' => 'fab fa-paypal', 'vipps' => 'fas fa-mobile-alt', 'cod' => 'fas fa-truck'] as $key => $icon) {
    if (empty($settings[$key]['enabled'])) {
        continue;
    }
    $label = ($key === 'cod' && trim($settings['cod']['title'] ?? '') !== '')
        ? $settings['cod']['title']
        : ($t['checkout']['methods'][$key] ?? ucfirst($key));
    $methods[] = ['id' => $key, 'icon' => $icon, 'label' => $label];
}

$current_page = 'checkout';
$page_title = ($t['checkout']['title'] ?? 'Checkout') . ' — ' . ($t['meta']['site_name'] ?? 'Shop CMS');
$page_desc  = $t['checkout']['meta_desc'] ?? $t['meta']['description'];
$canonical  = $site_url . '/checkout.php' . ($lang !== 'no' ? '?lang=' . $lang : '');
$canon_abs  = sh_absolute_url($canonical);
$seo_noindex = true;
$seo_schemas = [
    sh_seo_webpage($canon_abs, $page_title, $page_desc),
    sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $t['cart']['title'], 'url' => sh_absolute_url(sh_url('cart.php'))],
        ['name' => $t['checkout']['title'] ?? 'Checkout', 'url' => $canon_abs],
    ]),
];

require __DIR__ . '/includes/header.php';
?>

<div class="sh-container sh-checkout-page">
    <h1><?= htmlspecialchars($t['checkout']['title'] ?? 'Checkout') ?></h1>

    <?php if ($flash): ?>
    <div class="sh-alert sh-alert-<?= htmlspecialchars($flash_type) ?>"><i class="fas fa-<?= $flash_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($flash) ?></div>
    <?php if ($flash_type === 'success'): ?>
    <p><a href="<?= sh_url('search.php') ?>" class="sh-btn-primary"><?= htmlspecialchars($t['cart']['continue']) ?></a></p>
    <?php endif; ?>
    <?php else: ?>

    <div class="sh-checkout-grid">
        <section class="sh-form-card">
            <h2><?= htmlspecialchars($t['checkout']['payment_title'] ?? 'Payment method') ?></h2>
            <?php if (empty($methods)): ?>
            <p class="sh-checkout-note"><?= htmlspecialchars($t['checkout']['no_methods'] ?? 'No payment methods enabled. Configure them in admin.') ?></p>
            <?php else: ?>
            <form method="post" class="sh-checkout-form">
                <div class="sh-payment-methods">
                    <?php foreach ($methods as $i => $m): ?>
                    <label class="sh-payment-method">
                        <input type="radio" name="payment_method" value="<?= htmlspecialchars($m['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                        <span class="sh-payment-method-box">
                            <i class="<?= htmlspecialchars($m['icon']) ?>" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($m['label']) ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($settings['cod']['enabled']) && trim($settings['cod']['instructions'] ?? '') !== ''): ?>
                <p class="sh-checkout-cod-note"><?= htmlspecialchars($settings['cod']['instructions']) ?></p>
                <?php endif; ?>
                <button type="submit" class="sh-btn-primary sh-btn-lg sh-btn-block">
                    <i class="fas fa-lock"></i> <?= htmlspecialchars($t['checkout']['place_order'] ?? 'Place demo order') ?>
                </button>
                <p class="sh-checkout-note"><?= htmlspecialchars($t['checkout']['demo_note'] ?? $t['cart']['checkout_note']) ?></p>
            </form>
            <?php endif; ?>
        </section>

        <aside class="sh-form-card sh-checkout-summary">
            <h2><?= htmlspecialchars($t['checkout']['summary'] ?? 'Order summary') ?></h2>
            <ul class="sh-checkout-lines">
                <?php foreach ($lines as $line): ?>
                <li>
                    <span><?= htmlspecialchars($line['name']) ?> × <?= (int)$line['qty'] ?></span>
                    <strong><?= sh_price($line['subtotal']) ?></strong>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php require __DIR__ . '/includes/cart-order-totals.php'; ?>
        </aside>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>