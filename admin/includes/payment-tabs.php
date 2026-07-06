<?php
/** @var array $ta @var string $payment_tab */
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
$tp = $ta['payments_page'] ?? [];
$tab_labels = $tp['tabs'] ?? [];
?>
<nav class="adm-settings-tabs" aria-label="<?= htmlspecialchars($tp['nav_label'] ?? 'Payment providers') ?>">
    <?php foreach (sh_payment_tabs() as $key => $meta):
        $label = $tab_labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
        $url = sh_admin_url('payments.php?tab=' . urlencode($key));
    ?>
    <a href="<?= htmlspecialchars($url) ?>"
       class="adm-settings-tab <?= ($payment_tab ?? '') === $key ? 'active' : '' ?>">
        <i class="<?= htmlspecialchars($meta['icon']) ?>"></i>
        <span><?= htmlspecialchars($label) ?></span>
    </a>
    <?php endforeach; ?>
</nav>