<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/payment-settings.php';
require_once __DIR__ . '/includes/norwegian-carriers.php';

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
$checkout_disabled = sh_checkout_payments_disabled();

if (!empty($_GET['paid']) && !empty($_GET['order'])) {
    require_once __DIR__ . '/includes/orders-storage.php';
    $returnOrder = sh_order_by_id((string) $_GET['order']);
    if (is_array($returnOrder)) {
        $placed_order = $returnOrder;
        $flash = sprintf(
            $t['checkout']['order_success'] ?? 'Order placed. Invoice %s created.',
            $returnOrder['invoice_no'] ?? ''
        );
        $flash_type = 'success';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($checkout_disabled) {
        $flash = $t['checkout']['dev_disabled'] ?? 'Payments are disabled in development / demo mode.';
        $flash_type = 'error';
    } else {
    $method = trim($_POST['payment_method'] ?? '');
    $valid = ['stripe', 'paypal', 'vipps', 'paysera', 'revolut', 'cod'];
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
            'shipping_carrier' => trim($_POST['shipping_carrier'] ?? ''),
        ];

        $invSettings = sh_invoice_merge_settings($settings);
        if (!empty($invSettings['invoice_enabled'])) {
            $placed_order = sh_order_create($lines, $customer, $method, $settings, $lang);
            if ($placed_order && !empty($invSettings['invoice_auto_send']) && filter_var($customer['email'], FILTER_VALIDATE_EMAIL)) {
                sh_send_order_invoice($placed_order['id'], $customer['email'], $settings);
            }
            if ($placed_order) {
                require_once __DIR__ . '/includes/telegram-notify.php';
                sh_telegram_notify_order($placed_order, $settings);
            }
        }

        if ($placed_order && in_array($method, ['paysera', 'revolut'], true) && sh_payment_is_configured($method, $settings)) {
            try {
                if ($method === 'paysera') {
                    require_once __DIR__ . '/includes/paysera-gateway.php';
                    $payUrl = sh_paysera_build_payment_url($placed_order, $settings);
                    sh_cart_clear();
                    header('Location: ' . $payUrl, true, 302);
                    exit;
                }
                if ($method === 'revolut') {
                    require_once __DIR__ . '/includes/revolut-gateway.php';
                    $rev = sh_revolut_create_checkout($placed_order, $settings);
                    sh_cart_clear();
                    header('Location: ' . $rev['checkout_url'], true, 302);
                    exit;
                }
            } catch (Throwable $e) {
                $flash = ($t['checkout']['gateway_error'] ?? 'Payment gateway error: %s');
                $flash = is_string($flash) && str_contains($flash, '%s')
                    ? sprintf($flash, $e->getMessage())
                    : ($t['checkout']['gateway_error'] ?? 'Payment gateway error.');
                $flash_type = 'error';
            }
        } elseif ($placed_order) {
            $flash = sprintf(
                $t['checkout']['order_success'] ?? 'Order placed. Invoice %s created.',
                $placed_order['invoice_no'] ?? ''
            );
            $flash_type = 'success';
            sh_cart_clear();
        } else {
            $flash = $t['checkout']['demo_success'] ?? 'Demo order placed — no payment processed.';
            $flash_type = 'success';
            sh_cart_clear();
        }
    } else {
        $flash = $t['checkout']['method_invalid'] ?? 'Please select a valid payment method.';
        $flash_type = 'error';
    }
    }
}

$method_icons = [
    'stripe'  => 'fab fa-stripe-s',
    'paypal'  => 'fab fa-paypal',
    'vipps'   => 'fas fa-mobile-alt',
    'paysera' => 'fas fa-university',
    'revolut' => 'fas fa-credit-card',
    'cod'     => 'fas fa-truck',
];
$shippingOptions = sh_norwegian_shipping_checkout_options($settings, $t);

$methods = [];
foreach ($method_icons as $key => $icon) {
    if (empty($settings[$key]['enabled'])) {
        continue;
    }
    if ($key !== 'cod' && !sh_payment_is_configured($key, $settings)) {
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

require_once __DIR__ . '/includes/google-marketing.php';
require __DIR__ . '/includes/header.php';
if (empty($flash)) {
    sh_render_google_ads_begin_checkout($total, $settings);
}
?>

<div class="sh-container sh-checkout-page">
    <h1><?= htmlspecialchars($t['checkout']['title'] ?? 'Checkout') ?></h1>

    <?php if ($flash): ?>
    <div class="sh-alert sh-alert-<?= htmlspecialchars($flash_type) ?>"><i class="fas fa-<?= $flash_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($flash) ?></div>
    <?php if ($flash_type === 'success'): ?>
    <?php
    if ($placed_order) {
        sh_render_google_ads_purchase_conversion($placed_order, $settings);
    }
    ?>
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
                <?php if (!empty($settings['revolut']['enabled'])): ?>
                <p class="sh-checkout-revolut-note"><i class="fas fa-credit-card"></i> <?= htmlspecialchars($t['checkout']['revolut_promo'] ?? '') ?></p>
                <?php endif; ?>
            <?php if ($checkout_disabled): ?>
            <div class="sh-alert sh-alert-info sh-checkout-dev-banner">
                <i class="fas fa-code"></i>
                <?= htmlspecialchars($t['checkout']['dev_banner'] ?? 'Development mode — payment buttons are disabled. Configure Paysera / Revolut in admin and set SH_DEMO_MODE to false for live checkout.') ?>
            </div>
            <?php endif; ?>
            <?php if (empty($methods)): ?>
            <p class="sh-checkout-note"><?= htmlspecialchars($t['checkout']['no_methods'] ?? 'No payment methods enabled. Configure them in admin.') ?></p>
            <?php else: ?>
            <form method="post" class="sh-checkout-form<?= $checkout_disabled ? ' is-dev-disabled' : '' ?>">
                <?php if ($shippingOptions !== []): ?>
                <h3><?= htmlspecialchars($t['checkout']['shipping_title'] ?? 'Shipping (Norway)') ?></h3>
                <p class="sh-checkout-note sh-checkout-shipping-help"><?= htmlspecialchars($t['checkout']['shipping_help'] ?? '') ?></p>
                <div class="sh-checkout-shipping-options">
                    <?php foreach ($shippingOptions as $i => $ship): ?>
                    <label class="sh-checkout-method">
                        <input type="radio" name="shipping_carrier" value="<?= htmlspecialchars($ship['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                        <span class="sh-checkout-method-box">
                            <i class="<?= htmlspecialchars($ship['icon']) ?>" aria-hidden="true"></i>
                            <span><?= htmlspecialchars($ship['label']) ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
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
                    <label class="sh-payment-method<?= $checkout_disabled ? ' is-disabled' : '' ?>">
                        <input type="radio" name="payment_method" value="<?= htmlspecialchars($m['id']) ?>" <?= $i === 0 ? 'checked' : '' ?> <?= $checkout_disabled ? 'disabled' : '' ?>>
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
                <button type="submit" class="sh-btn-primary sh-btn-lg sh-btn-block" <?= $checkout_disabled ? 'disabled' : '' ?>>
                    <i class="fas fa-<?= $checkout_disabled ? 'ban' : 'lock' ?>"></i>
                    <?= htmlspecialchars($checkout_disabled
                        ? ($t['checkout']['place_order_disabled'] ?? 'Payments disabled (dev)')
                        : ($t['checkout']['place_order'] ?? 'Place order')) ?>
                </button>
                <p class="sh-checkout-note"><?= htmlspecialchars($checkout_disabled
                    ? ($t['checkout']['dev_note'] ?? $t['checkout']['demo_note'] ?? '')
                    : ($t['checkout']['live_note'] ?? $t['checkout']['demo_note'] ?? $t['cart']['checkout_note'] ?? '')) ?></p>
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