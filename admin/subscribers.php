<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/subscribers-storage.php';

$admin_page = 'subscribers';
$ta = $t['admin'] ?? [];
$sp = $ta['subscribers_page'] ?? [];
$page_title = $sp['title'] ?? 'Newsletter subscribers';
$admin_extra_js = [sh_asset('js/admin-subscribers.js') . '?v=1'];

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

require __DIR__ . '/includes/layout.php';
?>

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
            <table class="adm-table adm-table--cards">
                <thead>
                    <tr>
                        <th><?= htmlspecialchars($sp['col_email'] ?? 'Email') ?></th>
                        <th><?= htmlspecialchars($sp['col_lang'] ?? 'Lang') ?></th>
                        <th><?= htmlspecialchars($sp['col_date'] ?? 'Subscribed') ?></th>
                        <th><?= htmlspecialchars($sp['col_actions'] ?? 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $sub): ?>
                    <tr>
                        <td data-label="<?= htmlspecialchars($sp['col_email'] ?? 'Email') ?>">
                            <a href="mailto:<?= htmlspecialchars((string) ($sub['email'] ?? '')) ?>"><?= htmlspecialchars((string) ($sub['email'] ?? '')) ?></a>
                        </td>
                        <td data-label="<?= htmlspecialchars($sp['col_lang'] ?? 'Lang') ?>"><?= htmlspecialchars(strtoupper((string) ($sub['lang'] ?? ''))) ?></td>
                        <td data-label="<?= htmlspecialchars($sp['col_date'] ?? 'Subscribed') ?>"><?= htmlspecialchars((string) ($sub['created_at'] ?? '')) ?></td>
                        <td data-label="<?= htmlspecialchars($sp['col_actions'] ?? 'Actions') ?>">
                            <div class="adm-actions-row">
                                <button type="button" class="adm-btn adm-btn-outline adm-btn-sm"
                                        data-subscriber-email
                                        data-id="<?= htmlspecialchars((string) ($sub['id'] ?? '')) ?>"
                                        data-email="<?= htmlspecialchars((string) ($sub['email'] ?? '')) ?>">
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($sp['send_email'] ?? 'Send email') ?>
                                </button>
                                <form method="post" class="adm-inline-form" onsubmit="return confirm('<?= htmlspecialchars($sp['confirm_delete'] ?? 'Remove subscriber?') ?>');">
                                    <input type="hidden" name="subscriber_id" value="<?= htmlspecialchars((string) ($sub['id'] ?? '')) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="adm-btn adm-btn-danger adm-btn-sm"><i class="fas fa-trash"></i></button>
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
</div>

<div class="adm-modal" id="shSubscriberEmailModal"
     data-api="<?= htmlspecialchars(sh_admin_url('api/subscriber-email.php')) ?>"
     role="dialog" aria-modal="true" aria-labelledby="shSubscriberEmailTitle" hidden>
    <div class="adm-modal-backdrop" data-close="subscriber-modal"></div>
    <div class="adm-modal-panel">
        <div class="adm-modal-head">
            <h3 id="shSubscriberEmailTitle"><i class="fas fa-envelope"></i> <?= htmlspecialchars($sp['email_modal_title'] ?? 'Send email') ?></h3>
            <button type="button" class="adm-modal-close" data-close="subscriber-modal" aria-label="<?= htmlspecialchars($sp['email_modal_close'] ?? 'Close') ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="shSubscriberEmailForm" class="adm-modal-body">
            <input type="hidden" name="subscriber_id" value="">
            <div class="adm-field">
                <label for="shSubscriberEmailTo"><?= htmlspecialchars($sp['email_to'] ?? 'To') ?></label>
                <input type="email" id="shSubscriberEmailTo" name="to" required readonly>
            </div>
            <div class="adm-field">
                <label for="shSubscriberEmailSubject"><?= htmlspecialchars($sp['email_subject'] ?? 'Subject') ?> *</label>
                <input type="text" id="shSubscriberEmailSubject" name="subject" required>
            </div>
            <div class="adm-field">
                <label for="shSubscriberEmailBody"><?= htmlspecialchars($sp['email_body'] ?? 'Message') ?> *</label>
                <textarea id="shSubscriberEmailBody" name="body" rows="8" class="adm-textarea" required></textarea>
            </div>
            <p id="shSubscriberEmailStatus" class="adm-ai-status adm-ai-status--block" hidden></p>
        </form>
        <div class="adm-modal-foot">
            <button type="button" class="adm-btn adm-btn-outline" data-close="subscriber-modal"><?= htmlspecialchars($sp['email_cancel'] ?? 'Cancel') ?></button>
            <button type="submit" form="shSubscriberEmailForm" class="adm-btn adm-btn-primary" id="shSubscriberEmailSend"
                    data-sending="<?= htmlspecialchars($sp['email_sending'] ?? 'Sending…') ?>"
                    data-sent="<?= htmlspecialchars($sp['email_sent'] ?? 'Email sent') ?>"
                    data-failed="<?= htmlspecialchars($sp['email_send_failed'] ?? 'Could not send email') ?>">
                <i class="fas fa-paper-plane"></i> <?= htmlspecialchars($sp['email_send'] ?? 'Send') ?>
            </button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>