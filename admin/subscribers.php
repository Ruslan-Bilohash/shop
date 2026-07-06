<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/subscribers-storage.php';

$admin_page = 'subscribers';
$ta = $t['admin'] ?? [];
$sp = $ta['subscribers_page'] ?? [];
$page_title = $sp['title'] ?? 'Newsletter subscribers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['subscriber_id'] ?? '');
    if ($id !== '' && trim($_POST['action'] ?? '') === 'delete') {
        if (sh_subscriber_delete($id)) {
            $_SESSION['sh_admin_flash'] = ['type' => 'success', 'msg' => $sp['deleted'] ?? 'Subscriber removed.'];
        } else {
            $_SESSION['sh_admin_flash'] = ['type' => 'error', 'msg' => $sp['delete_error'] ?? 'Could not remove subscriber.'];
        }
        header('Location: ' . sh_admin_url('subscribers.php'));
        exit;
    }
}

$subs = sh_subscribers_load();
$flash = $_SESSION['sh_admin_flash'] ?? null;
unset($_SESSION['sh_admin_flash']);

require __DIR__ . '/includes/layout.php';
?>

<?php if ($flash): ?>
<div class="adm-alert adm-alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="adm-card">
    <div class="adm-card-head">
        <h2><i class="fas fa-paper-plane"></i> <?= htmlspecialchars($page_title) ?></h2>
        <a href="<?= sh_admin_url('settings-smtp.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm">
            <i class="fas fa-envelope"></i> <?= htmlspecialchars($sp['smtp_settings'] ?? 'SMTP settings') ?>
        </a>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help"><?= htmlspecialchars($sp['help'] ?? 'Emails collected from the footer «Subscribe» form and newsletter blocks.') ?></p>
        <?php if ($subs === []): ?>
        <p class="adm-muted"><?= htmlspecialchars($sp['empty'] ?? 'No subscribers yet.') ?></p>
        <?php else: ?>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($sp['col_email'] ?? 'Email') ?></th>
                        <th><?= htmlspecialchars($sp['col_lang'] ?? 'Lang') ?></th>
                        <th><?= htmlspecialchars($sp['col_date'] ?? 'Subscribed') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $sub): ?>
                    <tr>
                        <td><a href="mailto:<?= htmlspecialchars((string) ($sub['email'] ?? '')) ?>"><?= htmlspecialchars((string) ($sub['email'] ?? '')) ?></a></td>
                        <td><?= htmlspecialchars(strtoupper((string) ($sub['lang'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars((string) ($sub['created_at'] ?? '')) ?></td>
                        <td>
                            <form method="post" class="adm-inline-form" onsubmit="return confirm('<?= htmlspecialchars($sp['confirm_delete'] ?? 'Remove subscriber?') ?>');">
                                <input type="hidden" name="subscriber_id" value="<?= htmlspecialchars((string) ($sub['id'] ?? '')) ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm"><i class="fas fa-trash"></i></button>
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