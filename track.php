<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/track-carriers.php';
require_once __DIR__ . '/includes/site-integrations.php';
sh_boot_public_integrations();

$tr = $t['track'] ?? [];
$carriers = sh_track_carrier_options(null, $t);
$carrier = trim((string) ($_GET['c'] ?? $_POST['carrier'] ?? ''));
if ($carrier === '' || !sh_track_carrier_is_valid($carrier, null, $t)) {
    $carrier = sh_track_default_carrier_id(null, $t);
}
$number = trim($_GET['n'] ?? $_POST['tracking'] ?? '');
$result = null;
if ($number !== '') {
    $result = sh_track_lookup($carrier, $number);
}

$current_page = 'track';
$page_title = ($tr['title'] ?? 'Track parcel') . ' — ' . sh_seo_site_name();
$page_desc = $tr['meta'] ?? 'Track parcels by carrier';
$canonical = sh_url('track.php');
require __DIR__ . '/includes/header.php';

$statusClass = 'pending';
if ($result !== null && !empty($result['ok']) && empty($result['external'])) {
    $st = strtolower((string)($result['status'] ?? ''));
    if (str_contains($st, 'deliver') || str_contains($st, 'lever')) {
        $statusClass = 'delivered';
    } elseif (str_contains($st, 'transit') || str_contains($st, 'send')) {
        $statusClass = 'transit';
    }
}
$carrierLabel = '';
foreach ($carriers as $c) {
    if ($c['id'] === $carrier) {
        $carrierLabel = $c['label'];
        break;
    }
}
?>

<div class="sh-container sh-track-page">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($tr['title'] ?? 'Track parcel') ?></span>
    </nav>

    <div class="sh-track-hero">
        <div class="sh-track-hero-icon" aria-hidden="true"><i class="fas fa-truck-fast"></i></div>
        <h1><?= htmlspecialchars($tr['title'] ?? 'Track parcel') ?></h1>
        <p class="sh-track-sub"><?= htmlspecialchars($tr['subtitle'] ?? 'Choose carrier and enter tracking number') ?></p>
    </div>

    <div class="sh-track-search-card">
        <form method="get" action="<?= sh_url('track.php') ?>" class="sh-track-form">
            <?php if (count($carriers) > 1): ?>
            <label for="carrier"><?= htmlspecialchars($tr['carrier_label'] ?? 'Carrier') ?></label>
            <div class="sh-track-carrier-row">
                <select id="carrier" name="c" class="sh-track-carrier-select" required>
                    <?php foreach ($carriers as $c): ?>
                    <option value="<?= htmlspecialchars($c['id']) ?>"<?= $carrier === $c['id'] ? ' selected' : '' ?>><?= htmlspecialchars($c['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <input type="hidden" name="c" value="<?= htmlspecialchars($carrier) ?>">
            <?php endif; ?>
            <label for="tracking"><?= htmlspecialchars($tr['number_label'] ?? 'Tracking number') ?></label>
            <div class="sh-track-input-row">
                <span class="sh-track-input-icon" aria-hidden="true"><i class="fas fa-barcode"></i></span>
                <input type="text" id="tracking" name="n" value="<?= htmlspecialchars($number) ?>" placeholder="<?= $carrier === 'nova_poshta' ? '20450123456789' : '12345678901' ?>" required autocomplete="off" inputmode="<?= $carrier === 'nova_poshta' ? 'numeric' : 'text' ?>">
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
        <div class="sh-track-empty-icon" aria-hidden="true"><i class="fas fa-box-open"></i></div>
        <p class="sh-alert sh-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($tr['not_found'] ?? 'Tracking not found or disabled.') ?></p>
    </div>
        <?php elseif (!empty($result['external'])): ?>
    <div class="sh-track-result-card sh-track-result-card--external">
        <p class="sh-track-external-note"><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i> <?= htmlspecialchars($tr['external_note'] ?? 'Open the carrier website to track this parcel.') ?></p>
        <div class="sh-track-status-row">
            <div class="sh-track-status-badge sh-track-status-badge--transit">
                <i class="fas fa-truck" aria-hidden="true"></i>
                <span><?= htmlspecialchars($result['status']) ?></span>
            </div>
            <div class="sh-track-number">
                <span class="sh-track-number-label"><?= htmlspecialchars($tr['number_label'] ?? 'Number') ?></span>
                <code><?= htmlspecialchars($result['number']) ?></code>
            </div>
        </div>
        <a href="<?= htmlspecialchars($result['url']) ?>" class="sh-btn-primary sh-track-external-btn" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-external-link-alt" aria-hidden="true"></i>
            <?= htmlspecialchars(sprintf($tr['external_cta'] ?? 'Track on %s website', $carrierLabel)) ?>
        </a>
    </div>
        <?php else: ?>
    <div class="sh-track-result-card">
        <?php if (!empty($result['demo'])): ?>
        <p class="sh-track-demo"><i class="fas fa-flask" aria-hidden="true"></i> <?= htmlspecialchars($carrier === 'nova_poshta' ? ($t['track_np']['demo_note'] ?? $tr['demo_note'] ?? '') : ($tr['demo_note'] ?? '')) ?></p>
        <?php endif; ?>

        <div class="sh-track-status-row">
            <div class="sh-track-status-badge sh-track-status-badge--<?= htmlspecialchars($statusClass) ?>">
                <i class="fas fa-<?= $statusClass === 'delivered' ? 'circle-check' : ($statusClass === 'transit' ? 'shipping-fast' : 'clock') ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($result['status']) ?></span>
            </div>
            <div class="sh-track-number">
                <span class="sh-track-number-label"><?= htmlspecialchars($tr['number_label'] ?? 'Number') ?></span>
                <code><?= htmlspecialchars($result['number']) ?></code>
            </div>
        </div>

        <h2 class="sh-track-timeline-title"><i class="fas fa-route" aria-hidden="true"></i> <?= htmlspecialchars($tr['timeline'] ?? 'Shipment timeline') ?></h2>
        <ol class="sh-track-timeline">
            <?php foreach ($result['events'] as $i => $ev):
                $isLatest = $i === 0;
            ?>
            <li class="sh-track-event<?= $isLatest ? ' is-latest' : '' ?>">
                <div class="sh-track-event-dot" aria-hidden="true"></div>
                <div class="sh-track-event-body">
                    <time datetime="<?= htmlspecialchars($ev['date']) ?>"><?= htmlspecialchars(substr($ev['date'], 0, 16)) ?></time>
                    <strong><?= htmlspecialchars($ev['label']) ?></strong>
                    <?php if (!empty($ev['location'])): ?>
                    <span class="sh-track-event-loc"><i class="fas fa-location-dot" aria-hidden="true"></i> <?= htmlspecialchars($ev['location']) ?></span>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>