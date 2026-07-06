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

$placed_order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = trim($_POST['payment_method'] ?? '');
    $valid = ['stripe', 'paypal', 'vipps', 'cod'];
    if (in_array($method, $valid, true) && !empty($settings[$method]['enabled'])) {
        require_once __DIR__ . '/includes/orders-storage.php';
        require_once __DIR__ . '/includes/invoice-mail.php';
        require_once __DIR__ . '/includes/invoice-settings.php';

        $customer = [
            'name'    => trim($_POST['customer_name'] ?? ''),
            'email'   => trim($_POST['customer_email'] ?? ''),
            'phone'   => trim($_POST['customer_phone'] ?? ''),
            'address' => trim($_POST['customer_address'] ?? ''),
            'city'    => trim($_POST['customer_city'] ?? ''),
            'postal'  => trim($_POST['customer_postal'] ?? ''),
            'country' => trim($_POST['customer_country'] ?? ''),
        ];

        $invSettings = sh_invoice_merge_settings($settings);
        if (!empty($invSettings['invoice_enabled'])) {
            $placed_order = sh_order_create($lines, $customer, $method, $settings, $lang);
            if ($placed_order && !empty($invSettings['invoice_auto_send']) && filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
                sh_send_order_invoice($placed_order['id'], $customer['email'], $settings);
            }
        }

        if ($placed_order) {
            $flash = sprintf(
                $t['checkout']['order_success'] ?? 'Order placed. Invoice %s created.',
                $placed_order['invoice_no'] ?? ''
            );
        } else {
            $flash = $t['checkout']['demo_success'] ?? 'Demo order placed — no payment processed.';
        }
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
    <p>
        <?php if ($placed_order): ?>
        <a href="<?= htmlspecialchars(sh_invoice_public_url($placed_order)) ?>" class="sh-btn-primary" target="_blank" rel="noopener">
            <i class="fas fa-file-invoice"></i> <?= htmlspecialchars($t['checkout']['view_invoice'] ?? 'View invoice') ?>
        </a>
        <?php endif; ?>
        <a href="<?= sh_url('search.php') ?>" class="sh-btn-outline"><?= htmlspecialchars($t['cart']['continue']) ?></a>
    </p>
    <?php endif; ?>
    <?php else: ?>

    <div class="sh-checkout-grid">
        <section class="sh-form-card">
            <h2><?= htmlspecialchars($t['checkout']['payment_title'] ?? 'Payment method') ?></h2>
            <?php if (empty($methods)): ?>
            <p class="sh-checkout-note"><?= htmlspecialchars($t['checkout']['no_methods'] ?? 'No payment methods enabled. Configure them in admin.') ?></p>
            <?php else: ?>
            <form method="post" class="sh-checkout-form">
                <h3><?= htmlspecialchars($t['checkout']['customer_title'] ?? 'Customer details') ?></h3>
                <div class="sh-form-grid sh-checkout-customer">
                    <label class="sh-field sh-field--wide">
                        <span><?= htmlspecialchars($t['checkout']['customer_name'] ?? 'Name') ?></span>
                        <input type="text" name="customer_name" autocomplete="name">
                    </label>
                    <label class="sh-field">
                        <span><?= htmlspecialchars($t['checkout']['customer_email'] ?? 'Email') ?></span>
                        <input type="email" name="customer_email" autocomplete="email">
                    </label>
                    <label class="sh-field">
                        <span><?= htmlspecialchars($t['checkout']['customer_phone'] ?? 'Phone') ?></span>
                        <input type="tel" name="customer_phone" autocomplete="tel">
                    </label>
                    <label class="sh-field sh-field--wide">
                        <span><?= htmlspecialchars($t['checkout']['customer_address'] ?? 'Address') ?></span>
                        <input type="text" name="customer_address" autocomplete="street-address">
                    </label>
                    <label class="sh-field">
                        <span><?= htmlspecialchars($t['checkout']['customer_city'] ?? 'City') ?></span>
                        <input type="text" name="customer_city" autocomplete="address-level2">
                    </label>
                    <label class="sh-field">
                        <span><?= htmlspecialchars($t['checkout']['customer_postal'] ?? 'Postal code') ?></span>
                        <input type="text" name="customer_postal" autocomplete="postal-code">
                    </label>
                    <label class="sh-field">
                        <span><?= htmlspecialchars($t['checkout']['customer_country'] ?? 'Country') ?></span>
                        <input type="text" name="customer_country" autocomplete="country-name">
                    </label>
                </div>
                <h3><?= htmlspecialchars($t['checkout']['payment_title'] ?? 'Payment method') ?></h3>
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