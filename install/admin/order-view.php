<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/orders-storage.php';
require_once dirname(__DIR__) . '/includes/invoice-render.php';
require_once dirname(__DIR__) . '/includes/invoice-mail.php';
require_once dirname(__DIR__) . '/includes/payment-settings.php';

$admin_page = 'orders';
$ta = $t['admin'] ?? [];
$op = $ta['orders_page'] ?? [];
$id = trim($_GET['id'] ?? '');
$order = sh_order_by_id($id);

if ($order === null) {
    header('Location: ' . sh_admin_url('orders.php'));
    exit;
}

$settings = sh_load_settings();
$doc = sh_order_to_invoice_doc($order);
$labels = sh_invoice_labels($order['lang'] ?? 'en');
$invoiceUrl = sh_invoice_public_url($order);
$page_title = ($order['invoice_no'] ?? 'Order') . ' — ' . ($op['title'] ?? 'Orders');

$extra_css = [sh_asset('css/invoice-print.css') . '?v=1'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-order-view">
    <div class="adm-leads-hero">
        <div class="adm-leads-hero-text">
            <h2><i class="fas fa-file-invoice"></i> <?= htmlspecialchars($order['invoice_no'] ?? '') ?></h2>
            <p><?= htmlspecialchars($op['view_help'] ?? 'Print as PDF (A4) or send by email.') ?></p>
        </div>
        <div class="adm-btn-group">
            <a href="<?= htmlspecialchars(sh_admin_url('orders.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($op['back'] ?? 'Back') ?></a>
            <a href="<?= htmlspecialchars($invoiceUrl) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($op['open_invoice'] ?? 'Open invoice') ?></a>
            <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="sh-admin-inv-print"><i class="fas fa-print"></i> <?= htmlspecialchars($labels['print'] ?? 'Print PDF') ?></button>
        </div>
    </div>

    <div class="adm-form-grid" style="margin-bottom:1rem">
        <div class="adm-card">
            <div class="adm-card-body padded">
                <form method="post" action="<?= htmlspecialchars(sh_admin_url('orders.php')) ?>" class="adm-inline-form">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($id) ?>">
                    <label><?= htmlspecialchars($op['status'] ?? 'Status') ?>
                        <select name="status">
                            <?php foreach (['pending', 'paid', 'shipped', 'cancelled'] as $st): ?>
                            <option value="<?= htmlspecialchars($st) ?>" <?= ($order['status'] ?? '') === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="adm-btn adm-btn-outline adm-btn-sm"><?= htmlspecialchars($op['save_status'] ?? 'Save') ?></button>
                </form>
            </div>
        </div>
        <div class="adm-card">
            <div class="adm-card-body padded">
                <form method="post" action="<?= htmlspecialchars(sh_admin_url('orders.php')) ?>">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($id) ?>">
                    <input type="hidden" name="action" value="send_invoice">
                    <label><?= htmlspecialchars($op['send_to'] ?? 'Send invoice to') ?>
                        <input type="email" name="send_email" value="<?= htmlspecialchars($order['customer']['email'] ?? '') ?>" placeholder="email@example.com" required>
                    </label>
                    <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><i class="fas fa-paper-plane"></i> <?= htmlspecialchars($op['send_invoice'] ?? 'Send invoice') ?></button>
                    <?php if (!empty($order['invoice_sent_at'])): ?>
                    <p class="adm-help adm-help-compact"><?= htmlspecialchars($op['sent_at'] ?? 'Sent') ?>: <?= htmlspecialchars($order['invoice_sent_at']) ?></p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="adm-card adm-order-invoice-preview">
        <div class="adm-card-body padded">
            <?php
            $design = $order['print_design'] ?? 'classic-blue';
            $format = $order['print_format'] ?? 'a4';
            sh_render_invoice_article($doc, $labels, $design, $format, true, $settings);
            ?>
        </div>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('sh-admin-inv-print');
    if (!btn) return;
    var fmt = <?= json_encode($order['print_format'] ?? 'a4') ?>;
    var margin = <?= json_encode($order['print_margin'] ?? ($settings['invoice_print_margin'] ?? '8mm')) ?>;
    var formats = <?= json_encode(sh_inv_print_formats()) ?>;
    btn.addEventListener('click', function () {
        var pageCss = (formats[fmt] && formats[fmt].css) ? formats[fmt].css : 'A4';
        var s = document.getElementById('sh-inv-dynamic-page');
        if (!s) { s = document.createElement('style'); s.id = 'sh-inv-dynamic-page'; document.head.appendChild(s); }
        s.textContent = '@page { size: ' + pageCss + '; margin: ' + margin + '; } @media print { .adm-sidebar, .adm-topbar, .adm-order-view > *:not(.adm-order-invoice-preview), .adm-leads-hero, .adm-form-grid { display:none !important; } .adm-order-invoice-preview { border:none; box-shadow:none; } }';
        window.print();
    });
})();
</script>

<?php require __DIR__ . '/includes/layout-end.php'; ?>