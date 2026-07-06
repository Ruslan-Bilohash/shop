<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/leads-storage.php';

$admin_page = 'quick-leads';
$ta = $t['admin'] ?? [];
$lp = $ta['quick_leads_page'] ?? [];
$page_title = $lp['title'] ?? 'Quick purchase';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['lead_id'] ?? '');
    $status = trim($_POST['status'] ?? 'new');
    $note = trim($_POST['note'] ?? '');
    if ($id !== '') {
        sh_lead_update_status($id, $status, $note);
        $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $lp['updated'] ?? 'Lead updated.'];
        header('Location: ' . sh_admin_url('quick-leads.php'));
        exit;
    }
}

$leads = sh_leads_load();
$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-head adm-card-head--stack">
        <h2><i class="fas fa-bolt"></i> <?= htmlspecialchars($page_title) ?></h2>
        <span class="adm-badge adm-badge--gold"><?= (int) sh_leads_count_by_status('new') ?> <?= htmlspecialchars($lp['new_badge'] ?? 'new') ?></span>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help"><?= htmlspecialchars($lp['help'] ?? 'Hot clients from quick buy — phone captured on product pages.') ?></p>
        <?php if ($leads === []): ?>
        <p class="adm-muted"><?= htmlspecialchars($lp['empty'] ?? 'No quick purchase leads yet.') ?></p>
        <?php else: ?>
        <div class="adm-table-wrap">
            <table class="adm-table adm-table--cards">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($lp['phone'] ?? 'Phone') ?></th>
                        <th><?= htmlspecialchars($lp['product'] ?? 'Product') ?></th>
                        <th><?= htmlspecialchars($lp['status'] ?? 'Status') ?></th>
                        <th><?= htmlspecialchars($lp['date'] ?? 'Date') ?></th>
                        <th><?= htmlspecialchars($lp['actions'] ?? 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td data-label="<?= htmlspecialchars($lp['phone'] ?? 'Phone') ?>">
                            <a href="tel:<?= htmlspecialchars($lead['phone'] ?? '') ?>"><?= htmlspecialchars($lead['phone'] ?? '') ?></a>
                        </td>
                        <td data-label="<?= htmlspecialchars($lp['product'] ?? 'Product') ?>">
                            <?php if (!empty($lead['product_id'])): ?>
                            <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($lead['product_id'])) ?>"><?= htmlspecialchars($lead['product_name'] ?: $lead['product_id']) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td data-label="<?= htmlspecialchars($lp['status'] ?? 'Status') ?>">
                            <span class="adm-badge"><?= htmlspecialchars($lead['status'] ?? 'new') ?></span>
                        </td>
                        <td data-label="<?= htmlspecialchars($lp['date'] ?? 'Date') ?>"><?= htmlspecialchars(substr($lead['created_at'] ?? '', 0, 16)) ?></td>
                        <td data-label="<?= htmlspecialchars($lp['actions'] ?? 'Actions') ?>">
                            <form method="post" class="adm-inline-form">
                                <input type="hidden" name="lead_id" value="<?= htmlspecialchars($lead['id'] ?? '') ?>">
                                <select name="status">
                                    <option value="new" <?= ($lead['status'] ?? '') === 'new' ? 'selected' : '' ?>><?= htmlspecialchars($lp['st_new'] ?? 'New') ?></option>
                                    <option value="contacted" <?= ($lead['status'] ?? '') === 'contacted' ? 'selected' : '' ?>><?= htmlspecialchars($lp['st_contacted'] ?? 'Contacted') ?></option>
                                    <option value="closed" <?= ($lead['status'] ?? '') === 'closed' ? 'selected' : '' ?>><?= htmlspecialchars($lp['st_closed'] ?? 'Closed') ?></option>
                                </select>
                                <button type="submit" class="adm-btn adm-btn-sm adm-btn-outline"><?= htmlspecialchars($lp['save'] ?? 'Save') ?></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>