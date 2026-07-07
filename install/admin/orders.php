<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/orders-storage.php';
require_once dirname(__DIR__) . '/includes/store-settings.php';

$admin_page = 'orders';
$ta = $t['admin'] ?? [];
$op = $ta['orders_page'] ?? [];
$page_title = $op['title'] ?? 'Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['order_id'] ?? '');
    $action = trim($_POST['action'] ?? 'update');

    if ($id !== '' && $action === 'delete') {
        if (sh_order_delete($id)) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $op['deleted'] ?? 'Order deleted.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $op['delete_error'] ?? 'Could not delete order.'];
        }
        header('Location: ' . sh_admin_url('orders.php'));
        exit;
    }

    if ($id !== '' && $action === 'send_invoice') {
        require_once dirname(__DIR__) . '/includes/invoice-mail.php';
        $email = trim($_POST['send_email'] ?? '');
        $result = sh_send_order_invoice($id, $email !== '' ? $email : null);
        if ($result['ok']) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $op['sent'] ?? 'Invoice sent.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $result['error'] ?: ($op['send_error'] ?? 'Could not send invoice.')];
        }
        header('Location: ' . sh_admin_url('order-view.php?id=' . rawurlencode($id)));
        exit;
    }

    if ($id !== '') {
        $status = trim($_POST['status'] ?? 'pending');
        if (sh_order_update_status($id, $status)) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $op['updated'] ?? 'Order updated.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $op['update_error'] ?? 'Could not update order.'];
        }
        header('Location: ' . sh_admin_url('orders.php'));
        exit;
    }
}

$orders = sh_orders_load();
$countPending = sh_orders_count_by_status('pending');
$countPaid = sh_orders_count_by_status('paid');
$countAll = count($orders);

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-orders-page">
    <div class="adm-leads-hero">
        <div class="adm-leads-hero-text">
            <h2 class="adm-leads-title"><i class="fas fa-receipt"></i> <?= htmlspecialchars($page_title) ?></h2>
            <p><?= htmlspecialchars($op['help'] ?? 'Demo orders with printable invoices and email delivery.') ?></p>
        </div>
        <a href="<?= sh_admin_url('settings-invoice.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
            <i class="fas fa-cog"></i> <?= htmlspecialchars($op['invoice_settings'] ?? 'Invoice settings') ?>
        </a>
    </div>

    <div class="adm-leads-stats">
        <div class="adm-leads-stat adm-leads-stat--new">
            <span class="adm-leads-stat-val"><?= $countPending ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($op['st_pending'] ?? 'Pending') ?></span>
        </div>
        <div class="adm-leads-stat adm-leads-stat--contacted">
            <span class="adm-leads-stat-val"><?= $countPaid ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($op['st_paid'] ?? 'Paid') ?></span>
        </div>
        <div class="adm-leads-stat adm-leads-stat--total">
            <span class="adm-leads-stat-val"><?= $countAll ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($op['total'] ?? 'Total') ?></span>
        </div>
    </div>

    <?php if ($orders === []): ?>
    <div class="adm-card adm-leads-empty">
        <div class="adm-card-body padded">
            <div class="adm-leads-empty-icon"><i class="fas fa-inbox"></i></div>
            <p><?= htmlspecialchars($op['empty'] ?? 'No orders yet. Place a demo order on checkout.') ?></p>
        </div>
    </div>
    <?php else: ?>
    <div class="adm-card">
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($op['col_invoice'] ?? 'Invoice') ?></th>
                        <th><?= htmlspecialchars($op['col_customer'] ?? 'Customer') ?></th>
                        <th><?= htmlspecialchars($op['col_total'] ?? 'Total') ?></th>
                        <th><?= htmlspecialchars($op['col_status'] ?? 'Status') ?></th>
                        <th><?= htmlspecialchars($op['col_date'] ?? 'Date') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order):
                        $cust = $order['customer'] ?? [];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($order['invoice_no'] ?? '') ?></strong></td>
                        <td>
                            <?= htmlspecialchars($cust['name'] ?? '—') ?>
                            <?php if (!empty($cust['email'])): ?><br><small class="adm-muted"><?= htmlspecialchars($cust['email']) ?></small><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(sh_format_price((int) ($order['totals']['total'] ?? 0))) ?></td>
                        <td><span class="adm-badge"><?= htmlspecialchars($order['status'] ?? 'pending') ?></span></td>
                        <td><small><?= htmlspecialchars(substr($order['created_at'] ?? '', 0, 10)) ?></small></td>
                        <td class="adm-table-actions">
                            <a href="<?= htmlspecialchars(sh_admin_url('order-view.php?id=' . rawurlencode($order['id'] ?? ''))) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>