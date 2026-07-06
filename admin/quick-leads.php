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
    $action = trim($_POST['action'] ?? 'update');

    if ($id !== '' && $action === 'delete') {
        if (sh_lead_delete($id)) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $lp['deleted'] ?? 'Lead deleted.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $lp['delete_error'] ?? 'Could not delete lead.'];
        }
        header('Location: ' . sh_admin_url('quick-leads.php'));
        exit;
    }

    if ($id !== '') {
        $status = trim($_POST['status'] ?? 'new');
        if (sh_lead_update_status($id, $status, trim($_POST['note'] ?? ''))) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $lp['updated'] ?? 'Lead updated.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $lp['update_error'] ?? 'Could not update lead.'];
        }
        header('Location: ' . sh_admin_url('quick-leads.php'));
        exit;
    }
}

$leads = sh_leads_load();
$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

$countNew = (int) sh_leads_count_by_status('new');
$countContacted = (int) sh_leads_count_by_status('contacted');
$countClosed = (int) sh_leads_count_by_status('closed');
$countAll = count($leads);

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-leads-page">
    <div class="adm-leads-hero">
        <div class="adm-leads-hero-text">
            <h2 class="adm-leads-title"><i class="fas fa-bolt"></i> <?= htmlspecialchars($page_title) ?></h2>
            <p><?= htmlspecialchars($lp['help'] ?? 'Hot clients from quick buy — phone captured on product pages.') ?></p>
        </div>
        <a href="<?= sh_url('search.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-store"></i> <?= htmlspecialchars($lp['view_store'] ?? 'View storefront') ?>
        </a>
    </div>

    <div class="adm-leads-stats">
        <div class="adm-leads-stat adm-leads-stat--new">
            <span class="adm-leads-stat-val"><?= $countNew ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($lp['st_new'] ?? 'New') ?></span>
        </div>
        <div class="adm-leads-stat adm-leads-stat--contacted">
            <span class="adm-leads-stat-val"><?= $countContacted ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($lp['st_contacted'] ?? 'Contacted') ?></span>
        </div>
        <div class="adm-leads-stat adm-leads-stat--closed">
            <span class="adm-leads-stat-val"><?= $countClosed ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($lp['st_closed'] ?? 'Closed') ?></span>
        </div>
        <div class="adm-leads-stat adm-leads-stat--total">
            <span class="adm-leads-stat-val"><?= $countAll ?></span>
            <span class="adm-leads-stat-label"><?= htmlspecialchars($lp['total'] ?? 'Total') ?></span>
        </div>
    </div>

    <?php if ($leads === []): ?>
    <div class="adm-card adm-leads-empty">
        <div class="adm-card-body padded">
            <div class="adm-leads-empty-icon"><i class="fas fa-inbox"></i></div>
            <h3><?= htmlspecialchars($lp['empty_title'] ?? 'No leads yet') ?></h3>
            <p class="adm-muted"><?= htmlspecialchars($lp['empty'] ?? 'When customers use quick purchase on a product page, their phone numbers appear here.') ?></p>
        </div>
    </div>
    <?php else: ?>
    <div class="adm-leads-list">
        <?php foreach ($leads as $lead):
            $status = (string) ($lead['status'] ?? 'new');
            $statusClass = in_array($status, ['new', 'contacted', 'closed'], true) ? $status : 'new';
            $leadId = (string) ($lead['id'] ?? '');
            $phone = (string) ($lead['phone'] ?? '');
            $waUrl = sh_lead_whatsapp_url($phone);
        ?>
        <article class="adm-lead-card adm-lead-card--<?= htmlspecialchars($statusClass) ?>">
            <div class="adm-lead-card-top">
                <a href="tel:<?= htmlspecialchars($phone) ?>" class="adm-lead-phone">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                    <?= htmlspecialchars($phone) ?>
                </a>
                <span class="adm-lead-badge adm-lead-badge--<?= htmlspecialchars($statusClass) ?>">
                    <?= htmlspecialchars($lp['st_' . $statusClass] ?? $status) ?>
                </span>
            </div>
            <div class="adm-lead-card-meta">
                <div class="adm-lead-meta-item">
                    <span class="adm-lead-meta-label"><?= htmlspecialchars($lp['product'] ?? 'Product') ?></span>
                    <span class="adm-lead-meta-value">
                        <?php if (!empty($lead['product_id'])): ?>
                        <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($lead['product_id'])) ?>">
                            <?= htmlspecialchars($lead['product_name'] ?: $lead['product_id']) ?>
                        </a>
                        <?php else: ?>
                        <span class="adm-muted">—</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="adm-lead-meta-item">
                    <span class="adm-lead-meta-label"><?= htmlspecialchars($lp['date'] ?? 'Date') ?></span>
                    <span class="adm-lead-meta-value"><?= htmlspecialchars(substr($lead['created_at'] ?? '', 0, 16)) ?></span>
                </div>
            </div>

            <div class="adm-lead-actions">
                <a href="tel:<?= htmlspecialchars($phone) ?>" class="adm-lead-btn adm-lead-btn--call">
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($lp['btn_call'] ?? 'Call') ?>
                </a>
                <?php if ($waUrl !== ''): ?>
                <a href="<?= htmlspecialchars($waUrl) ?>" class="adm-lead-btn adm-lead-btn--whatsapp" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($lp['btn_whatsapp'] ?? 'WhatsApp') ?>
                </a>
                <?php endif; ?>
                <?php if ($status !== 'contacted'): ?>
                <form method="post" class="adm-lead-inline-action">
                    <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="status" value="contacted">
                    <button type="submit" class="adm-lead-btn adm-lead-btn--contacted">
                        <i class="fas fa-check"></i> <?= htmlspecialchars($lp['btn_contacted'] ?? 'Contacted') ?>
                    </button>
                </form>
                <?php endif; ?>
                <?php if ($status !== 'closed'): ?>
                <form method="post" class="adm-lead-inline-action">
                    <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="status" value="closed">
                    <button type="submit" class="adm-lead-btn adm-lead-btn--closed">
                        <i class="fas fa-archive"></i> <?= htmlspecialchars($lp['btn_closed'] ?? 'Close') ?>
                    </button>
                </form>
                <?php endif; ?>
                <form method="post" class="adm-lead-inline-action" onsubmit="return confirm('<?= htmlspecialchars($lp['delete_confirm'] ?? 'Delete this lead?', ENT_QUOTES) ?>');">
                    <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="adm-lead-btn adm-lead-btn--delete">
                        <i class="fas fa-trash"></i> <?= htmlspecialchars($lp['btn_delete'] ?? 'Delete') ?>
                    </button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <div class="adm-table-wrap adm-leads-table-wrap">
        <table class="adm-table adm-leads-table">
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
                <?php foreach ($leads as $lead):
                    $status = (string) ($lead['status'] ?? 'new');
                    $statusClass = in_array($status, ['new', 'contacted', 'closed'], true) ? $status : 'new';
                    $leadId = (string) ($lead['id'] ?? '');
                    $phone = (string) ($lead['phone'] ?? '');
                    $waUrl = sh_lead_whatsapp_url($phone);
                ?>
                <tr>
                    <td data-label="<?= htmlspecialchars($lp['phone'] ?? 'Phone') ?>">
                        <a href="tel:<?= htmlspecialchars($phone) ?>" class="adm-lead-phone-inline"><?= htmlspecialchars($phone) ?></a>
                    </td>
                    <td data-label="<?= htmlspecialchars($lp['product'] ?? 'Product') ?>">
                        <?php if (!empty($lead['product_id'])): ?>
                        <a href="<?= sh_admin_url('product-edit.php?id=' . urlencode($lead['product_id'])) ?>"><?= htmlspecialchars($lead['product_name'] ?: $lead['product_id']) ?></a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td data-label="<?= htmlspecialchars($lp['status'] ?? 'Status') ?>">
                        <span class="adm-lead-badge adm-lead-badge--<?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($lp['st_' . $statusClass] ?? $status) ?></span>
                    </td>
                    <td data-label="<?= htmlspecialchars($lp['date'] ?? 'Date') ?>"><?= htmlspecialchars(substr($lead['created_at'] ?? '', 0, 16)) ?></td>
                    <td data-label="<?= htmlspecialchars($lp['actions'] ?? 'Actions') ?>">
                        <div class="adm-lead-actions adm-lead-actions--table">
                            <a href="tel:<?= htmlspecialchars($phone) ?>" class="adm-lead-btn adm-lead-btn--call adm-lead-btn--icon" title="<?= htmlspecialchars($lp['btn_call'] ?? 'Call') ?>"><i class="fas fa-phone"></i></a>
                            <?php if ($waUrl !== ''): ?>
                            <a href="<?= htmlspecialchars($waUrl) ?>" class="adm-lead-btn adm-lead-btn--whatsapp adm-lead-btn--icon" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($lp['btn_whatsapp'] ?? 'WhatsApp') ?>"><i class="fab fa-whatsapp"></i></a>
                            <?php endif; ?>
                            <?php if ($status !== 'contacted'): ?>
                            <form method="post" class="adm-lead-inline-action">
                                <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="status" value="contacted">
                                <button type="submit" class="adm-lead-btn adm-lead-btn--contacted adm-lead-btn--icon" title="<?= htmlspecialchars($lp['btn_contacted'] ?? 'Contacted') ?>"><i class="fas fa-check"></i></button>
                            </form>
                            <?php endif; ?>
                            <?php if ($status !== 'closed'): ?>
                            <form method="post" class="adm-lead-inline-action">
                                <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="adm-lead-btn adm-lead-btn--closed adm-lead-btn--icon" title="<?= htmlspecialchars($lp['btn_closed'] ?? 'Close') ?>"><i class="fas fa-archive"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="post" class="adm-lead-inline-action" onsubmit="return confirm('<?= htmlspecialchars($lp['delete_confirm'] ?? 'Delete this lead?', ENT_QUOTES) ?>');">
                                <input type="hidden" name="lead_id" value="<?= htmlspecialchars($leadId) ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="adm-lead-btn adm-lead-btn--delete adm-lead-btn--icon" title="<?= htmlspecialchars($lp['btn_delete'] ?? 'Delete') ?>"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>