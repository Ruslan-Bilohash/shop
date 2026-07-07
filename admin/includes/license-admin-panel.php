<?php
/**
 * Owner-only license strip — connected sites, expiry, verify & renew.
 *
 * @var array $ta Admin translations
 */
if (!function_exists('sh_admin_is_owner') || !sh_admin_is_owner()) {
    return;
}

require_once dirname(__DIR__, 2) . '/includes/license-runtime.php';

$lp = $ta['license_page'] ?? [];
$pn = $lp['panel'] ?? [];
$licenseStatus = sh_license_status();
$licenseInfo = sh_license_verify_current(false);
$statusKey = (string) ($licenseStatus['status'] ?? 'trial');

$statusBadgeClass = 'adm-badge-info';
$statusLabel = sprintf($pn['status_trial'] ?? 'Trial: %d days left', (int) ($licenseStatus['trial_days_left'] ?? 0));
if ($statusKey === 'licensed') {
    $statusBadgeClass = 'adm-badge-green';
    $statusLabel = $pn['status_licensed'] ?? 'Licensed';
} elseif ($statusKey === 'expired') {
    $statusBadgeClass = 'adm-badge-warn';
    $statusLabel = $pn['status_expired'] ?? 'License expired';
}

$sites = is_array($licenseInfo['sites'] ?? null) ? $licenseInfo['sites'] : [];
$sitesCount = max(1, (int) ($licenseInfo['sites_count'] ?? count($sites)));
$daysLeft = (int) ($licenseInfo['days_left'] ?? ($licenseStatus['trial_days_left'] ?? 0));
$expLabel = (string) ($licenseInfo['exp_label'] ?? '');
if ($statusKey === 'trial') {
    $expLabel = $pn['trial_expires'] ?? 'Trial period';
} elseif ($expLabel === '' && $statusKey === 'expired') {
    $expLabel = $pn['expired_label'] ?? 'Expired';
}

?>
<div class="adm-card adm-license-strip" id="shLicensePanel"
     data-api="<?= htmlspecialchars(sh_admin_url('api/license-status.php')) ?>"
     data-status="<?= htmlspecialchars($statusKey) ?>"
     data-label-verifying="<?= htmlspecialchars($pn['verifying'] ?? 'Verifying license…') ?>"
     data-label-verified="<?= htmlspecialchars($pn['verified'] ?? 'License verified') ?>"
     data-label-error="<?= htmlspecialchars($pn['verify_error'] ?? 'Could not verify license') ?>"
     data-label-status-licensed="<?= htmlspecialchars($pn['status_licensed'] ?? 'Licensed') ?>"
     data-label-status-trial="<?= htmlspecialchars($pn['status_trial'] ?? 'Trial: %d days left') ?>"
     data-label-status-expired="<?= htmlspecialchars($pn['status_expired'] ?? 'License expired') ?>"
     data-label-renew-soon="<?= htmlspecialchars($pn['renew_soon'] ?? 'Renew soon') ?>"
     data-label-days="<?= htmlspecialchars($pn['days_left'] ?? '%d days left') ?>"
     data-label-trial-expires="<?= htmlspecialchars($pn['trial_expires'] ?? 'Trial period') ?>"
     data-label-expired="<?= htmlspecialchars($pn['expired_label'] ?? 'Expired') ?>"
     data-label-current="<?= htmlspecialchars($pn['current_site'] ?? 'This site') ?>">
    <div class="adm-card-head">
        <h2><i class="fas fa-key"></i> <?= htmlspecialchars($pn['title'] ?? 'License overview') ?></h2>
        <span class="adm-badge <?= htmlspecialchars($statusBadgeClass) ?>" id="shLicenseStatusBadge"><?= htmlspecialchars($statusLabel) ?></span>
    </div>
    <div class="adm-card-body padded">
        <div class="adm-license-grid">
            <div class="adm-license-stat">
                <span class="adm-license-stat-label"><?= htmlspecialchars($pn['sites_count'] ?? 'Connected sites') ?></span>
                <strong class="adm-license-stat-value" id="shLicenseSitesCount"><?= (int) $sitesCount ?></strong>
            </div>
            <div class="adm-license-stat">
                <span class="adm-license-stat-label"><?= htmlspecialchars($pn['expires'] ?? 'Valid until') ?></span>
                <strong class="adm-license-stat-value" id="shLicenseExp"><?= htmlspecialchars($expLabel) ?></strong>
                <?php if (!empty($licenseInfo['renew_soon'])): ?>
                <span class="adm-badge adm-badge-warn adm-license-renew-badge" id="shLicenseRenewSoon"><?= htmlspecialchars($pn['renew_soon'] ?? 'Renew soon') ?></span>
                <?php else: ?>
                <span class="adm-badge adm-badge-warn adm-license-renew-badge" id="shLicenseRenewSoon" style="display:none"><?= htmlspecialchars($pn['renew_soon'] ?? 'Renew soon') ?></span>
                <?php endif; ?>
            </div>
            <div class="adm-license-stat">
                <span class="adm-license-stat-label"><?= htmlspecialchars($pn['days_remaining'] ?? 'Days remaining') ?></span>
                <strong class="adm-license-stat-value" id="shLicenseDaysLeft"><?= (int) $daysLeft ?></strong>
            </div>
        </div>

        <?php if ($sites !== []): ?>
        <ul class="adm-license-sites" id="shLicenseSitesList">
            <?php foreach ($sites as $site):
                if (!is_array($site)) {
                    continue;
                }
                $siteDomain = (string) ($site['domain'] ?? '');
                if ($siteDomain === '') {
                    continue;
                }
                $isCurrent = !empty($site['current']);
            ?>
            <li class="adm-license-site<?= $isCurrent ? ' is-current' : '' ?>">
                <i class="fas fa-globe" aria-hidden="true"></i>
                <span><?= htmlspecialchars($siteDomain) ?></span>
                <?php if ($isCurrent): ?>
                <span class="adm-badge adm-badge-info adm-badge-sm"><?= htmlspecialchars($pn['current_site'] ?? 'This site') ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <ul class="adm-license-sites" id="shLicenseSitesList"></ul>
        <?php endif; ?>

        <div class="adm-license-actions">
            <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shLicenseVerifyBtn">
                <i class="fas fa-shield-check"></i> <?= htmlspecialchars($pn['verify'] ?? 'Verify license') ?>
            </button>
            <?php require_once dirname(__DIR__, 2) . '/includes/subscription-links.php'; ?>
            <a href="<?= htmlspecialchars(sh_subscription_url()) ?>" class="adm-btn adm-btn-outline adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                <i class="fas fa-rotate"></i> <?= htmlspecialchars($pn['renew'] ?? 'Renew license') ?>
            </a>
            <a href="<?= htmlspecialchars(sh_admin_url('license.php')) ?>" class="adm-btn adm-btn-outline adm-btn-sm">
                <i class="fas fa-cog"></i> <?= htmlspecialchars($pn['manage'] ?? 'Manage license') ?>
            </a>
        </div>
        <p class="adm-muted adm-license-msg" id="shLicenseMsg"></p>
    </div>
</div>