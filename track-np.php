<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/nova-poshta.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$number = trim($_GET['n'] ?? $_POST['tracking'] ?? '');
$result = null;
if ($number !== '') {
    $result = sh_nova_poshta_track($number);
}

$current_page = 'track-np';
$tr = $t['track_np'] ?? $t['track'] ?? [];
$page_title = ($tr['title'] ?? 'Track Nova Poshta') . ' — ' . sh_seo_site_name();
$page_desc = $tr['meta'] ?? 'Track Nova Poshta parcels in Ukraine';
$canonical = sh_url('track-np.php');
require __DIR__ . '/includes/header.php';

$statusClass = 'pending';
if ($result !== null && !empty($result['ok'])) {
    $st = mb_strtolower((string) ($result['status'] ?? ''));
    if (str_contains($st, 'отрим') || str_contains($st, 'deliver') || str_contains($st, 'видан')) {
        $statusClass = 'delivered';
    } elseif (str_contains($st, 'дороз') || str_contains($st, 'transit') || str_contains($st, 'відправ')) {
        $statusClass = 'transit';
    }
}
?>

<div class="sh-container sh-track-page sh-track-page--np">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($tr['title'] ?? 'Nova Poshta') ?></span>
    </nav>

    <div class="sh-track-hero">
        <div class="sh-track-hero-icon sh-track-hero-icon--np" aria-hidden="true"><i class="fas fa-warehouse"></i></div>
        <h1><?= htmlspecialchars($tr['title'] ?? 'Track Nova Poshta') ?></h1>
        <p class="sh-track-sub"><?= htmlspecialchars($tr['subtitle'] ?? 'Enter TTN (waybill) number') ?></p>
    </div>

    <div class="sh-track-search-card">
        <form method="get" action="<?= sh_url('track-np.php') ?>" class="sh-track-form">
            <label for="tracking"><?= htmlspecialchars($tr['number_label'] ?? 'TTN number') ?></label>
            <div class="sh-track-input-row">
                <span class="sh-track-input-icon" aria-hidden="true"><i class="fas fa-barcode"></i></span>
                <input type="text" id="tracking" name="n" value="<?= htmlspecialchars($number) ?>" placeholder="20450000000000" required autocomplete="off" inputmode="numeric" pattern="\d{11,14}">
                <button type="submit" class="sh-btn-primary sh-track-submit">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($tr['submit'] ?? 'Track') ?></span>
                </button>
            </div>
        </form>
    </div>

    <?php if ($number !== '' && $result !== null): ?>
        <?php if (!$result['ok']): ?>
    <div class="sh-track-empty">
        <p class="sh-alert sh-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($tr['not_found'] ?? 'Tracking not found or disabled.') ?></p>
    </div>
        <?php else: ?>
    <div class="sh-track-result-card">
        <?php if (!empty($result['demo'])): ?>
        <p class="sh-track-demo"><i class="fas fa-flask" aria-hidden="true"></i> <?= htmlspecialchars($tr['demo_note'] ?? 'Demo timeline — add API key in Admin → Nova Poshta.') ?></p>
        <?php endif; ?>
        <div class="sh-track-status-row">
            <div class="sh-track-status-badge sh-track-status-badge--<?= htmlspecialchars($statusClass) ?>">
                <i class="fas fa-<?= $statusClass === 'delivered' ? 'circle-check' : ($statusClass === 'transit' ? 'shipping-fast' : 'clock') ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($result['status']) ?></span>
            </div>
            <div class="sh-track-number">
                <span class="sh-track-number-label"><?= htmlspecialchars($tr['number_label'] ?? 'TTN') ?></span>
                <code><?= htmlspecialchars($result['number']) ?></code>
            </div>
        </div>
        <?php if (!empty($result['events'])): ?>
        <ol class="sh-track-timeline">
            <?php foreach ($result['events'] as $ev): ?>
            <li>
                <time datetime="<?= htmlspecialchars($ev['date'] ?? '') ?>"><?= htmlspecialchars($ev['date'] ?? '') ?></time>
                <strong><?= htmlspecialchars($ev['label'] ?? '') ?></strong>
                <?php if (!empty($ev['location'])): ?>
                <span><?= htmlspecialchars($ev['location']) ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>