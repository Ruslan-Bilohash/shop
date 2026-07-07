<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

require_once dirname(__DIR__) . '/includes/security-console.php';
require_once dirname(__DIR__) . '/includes/site-health-console.php';

$admin_page = 'security-console';
$ta = $t['admin'] ?? [];
$sp = $ta['security_console_page'] ?? [];
$page_title = $sp['title'] ?? 'Security console';

$labels = $sp['checks'] ?? [];
$scanHost = trim((string) ($_GET['host'] ?? '127.0.0.1'));
if ($scanHost === '' || !preg_match('/^[a-zA-Z0-9.\-:]+$/', $scanHost)) {
    $scanHost = '127.0.0.1';
}

$portScan = sh_sec_scan_ports($scanHost);
$server = sh_sec_server_snapshot();
$vulnChecks = sh_sec_vulnerability_checks($labels);
$score = sh_sec_score($vulnChecks);
$gradeLabels = $sp['grades'] ?? [];
$secTrend = sh_health_trend_data(14);
$admin_extra_js = [sh_asset('js/admin-security-console.js') . '?v=1'];

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-sec-console" id="shSecConsole"
     data-api-scan="<?= htmlspecialchars(sh_admin_url('api/security-scan.php')) ?>"
     data-api-ai="<?= htmlspecialchars(sh_admin_url('api/ai-security-scan.php')) ?>"
     data-trend="<?= htmlspecialchars(json_encode($secTrend, JSON_UNESCAPED_UNICODE)) ?>"
     data-scanning="<?= htmlspecialchars($sp['ajax_scanning'] ?? 'Scanning ports…') ?>"
     data-ai-scanning="<?= htmlspecialchars($sp['ai_scanning'] ?? 'AI security scan…') ?>">
    <div class="adm-sec-hero">
        <div class="adm-sec-hero-main">
            <span class="adm-sec-score adm-sec-score--<?= htmlspecialchars($score['grade']) ?>">
                <?= (int) $score['score'] ?>
            </span>
            <div>
                <h2 class="adm-sec-hero-title"><?= htmlspecialchars($sp['score_title'] ?? 'Security score') ?></h2>
                <p class="adm-sec-hero-text">
                    <?= htmlspecialchars($gradeLabels[$score['grade']] ?? $score['grade']) ?>
                    · <?= htmlspecialchars(sprintf($sp['failed_count'] ?? '%d issues', $score['failed'])) ?>
                </p>
            </div>
        </div>
        <div class="adm-sec-hero-meta">
            <span><i class="fas fa-server"></i> PHP <?= htmlspecialchars($server['php_version']) ?></span>
            <span><i class="fas fa-<?= $server['https'] ? 'lock' : 'unlock' ?>"></i> <?= $server['https'] ? 'HTTPS' : 'HTTP' ?>:<?= (int) $server['server_port'] ?></span>
        </div>
    </div>

    <div class="adm-sec-grid">
        <div class="adm-card adm-sec-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-network-wired"></i> <?= htmlspecialchars($sp['ports_title'] ?? 'Port scan') ?></h2>
                <span class="adm-badge adm-badge-info"><?= htmlspecialchars(sprintf($sp['scan_time'] ?? '%s ms', $portScan['elapsed_ms'])) ?></span>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($sp['ports_help'] ?? '') ?></p>
                <form method="get" class="adm-sec-scan-form">
                    <div class="adm-field adm-field--inline">
                        <label for="shSecHost"><?= htmlspecialchars($sp['host_label'] ?? 'Host') ?></label>
                        <input type="text" id="shSecHost" name="host" value="<?= htmlspecialchars($scanHost) ?>" class="adm-input-sm" pattern="[a-zA-Z0-9.\-:]+" maxlength="64">
                    </div>
                    <button type="submit" class="adm-btn adm-btn-outline adm-btn-sm"><i class="fas fa-rotate"></i> <?= htmlspecialchars($sp['rescan'] ?? 'Rescan') ?></button>
                    <button type="button" class="adm-btn adm-btn-outline adm-btn-sm" id="shSecRescanAjax"><i class="fas fa-bolt"></i> <?= htmlspecialchars($sp['rescan_ajax'] ?? 'Quick rescan') ?></button>
                </form>
                <div class="adm-table-wrap adm-sec-port-wrap">
                    <table class="adm-table adm-table-compact adm-sec-port-table">
                        <thead>
                            <tr>
                                <th><?= htmlspecialchars($sp['col_port'] ?? 'Port') ?></th>
                                <th><?= htmlspecialchars($sp['col_service'] ?? 'Service') ?></th>
                                <th><?= htmlspecialchars($sp['col_group'] ?? 'Group') ?></th>
                                <th><?= htmlspecialchars($sp['col_status'] ?? 'Status') ?></th>
                                <th><?= htmlspecialchars($sp['col_risk'] ?? 'Risk') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portScan['ports'] as $row):
                                $statusKey = 'status_' . $row['status'];
                                $riskKey = 'risk_' . $row['risk'];
                            ?>
                            <tr class="adm-sec-port-row <?= htmlspecialchars(sh_sec_port_status_class((string) $row['status'])) ?>">
                                <td>
                                    <code><?= (int) $row['port'] ?></code>
                                    <?php if (!empty($row['current'])): ?>
                                    <span class="adm-sec-tag"><?= htmlspecialchars($sp['current_port'] ?? 'active') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string) $row['service']) ?></td>
                                <td><?= htmlspecialchars($sp['group_' . $row['group']] ?? (string) $row['group']) ?></td>
                                <td><span class="adm-sec-pill adm-sec-pill--<?= htmlspecialchars((string) $row['status']) ?>"><?= htmlspecialchars($sp[$statusKey] ?? (string) $row['status']) ?></span></td>
                                <td><span class="adm-sec-risk adm-sec-risk--<?= htmlspecialchars((string) $row['risk']) ?>"><?= htmlspecialchars($sp[$riskKey] ?? (string) $row['risk']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="adm-card adm-sec-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-shield-halved"></i> <?= htmlspecialchars($sp['vuln_title'] ?? 'Vulnerability checks') ?></h2>
                <span class="adm-badge adm-badge--<?= $score['failed'] === 0 ? 'green' : 'orange' ?>"><?= (int) $score['failed'] ?></span>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($sp['vuln_help'] ?? '') ?></p>
                <ul class="adm-sec-checks">
                    <?php foreach ($vulnChecks as $check): ?>
                    <li class="adm-sec-check <?= $check['ok'] ? 'is-ok' : 'is-fail' ?> adm-sec-check--<?= htmlspecialchars($check['severity']) ?>">
                        <span class="adm-sec-check-icon">
                            <i class="fas fa-<?= $check['ok'] ? 'check-circle' : sh_sec_severity_icon($check['severity']) ?>"></i>
                        </span>
                        <div class="adm-sec-check-body">
                            <strong><?= htmlspecialchars($check['label']) ?></strong>
                            <?php if ($check['detail'] !== ''): ?>
                            <p><?= htmlspecialchars($check['detail']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$check['ok'] && $check['fix_url'] !== ''): ?>
                        <a href="<?= htmlspecialchars(sh_admin_url($check['fix_url'])) ?>" class="adm-health-link">
                            <?= htmlspecialchars($sp['fix'] ?? 'Fix') ?> <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="adm-sec-grid adm-sec-grid--extras">
        <div class="adm-card adm-sec-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-chart-line"></i> <?= htmlspecialchars($sp['trend_title'] ?? 'Security trend (14 days)') ?></h2>
            </div>
            <div class="adm-card-body padded">
                <canvas id="shSecTrendChart" class="adm-sec-chart" height="140" aria-label="<?= htmlspecialchars($sp['trend_title'] ?? 'Trend') ?>"></canvas>
            </div>
        </div>

        <div class="adm-card adm-sec-card">
            <div class="adm-card-head">
                <h2><i class="fas fa-robot"></i> <?= htmlspecialchars($sp['ai_title'] ?? 'AI security scanner') ?></h2>
                <button type="button" class="adm-btn adm-btn-primary adm-btn-sm" id="shSecAiScan">
                    <i class="fas fa-wand-magic-sparkles"></i> <?= htmlspecialchars($sp['ai_run'] ?? 'Run AI scan') ?>
                </button>
            </div>
            <div class="adm-card-body padded">
                <p class="adm-help adm-help-compact"><?= htmlspecialchars($sp['ai_help'] ?? 'Prioritized remediation plan based on open vulnerability checks. Works in demo mode without API key.') ?></p>
                <div id="shSecAiResult" class="adm-sec-ai-result" hidden></div>
            </div>
        </div>
    </div>

    <div class="adm-card adm-sec-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-microchip"></i> <?= htmlspecialchars($sp['server_title'] ?? 'Server snapshot') ?></h2>
        </div>
        <div class="adm-card-body padded">
            <dl class="adm-sec-dl">
                <?php
                $serverRows = [
                    'php_version'     => $sp['srv_php'] ?? 'PHP',
                    'php_sapi'        => $sp['srv_sapi'] ?? 'SAPI',
                    'os'              => $sp['srv_os'] ?? 'OS',
                    'server_software' => $sp['srv_software'] ?? 'Server',
                    'document_root'   => $sp['srv_docroot'] ?? 'Document root',
                    'shop_root'       => $sp['srv_shop'] ?? 'Shop root',
                    'ini_memory'      => $sp['srv_memory'] ?? 'Memory limit',
                    'ini_max_upload'  => $sp['srv_upload'] ?? 'Max upload',
                ];
                foreach ($serverRows as $key => $lbl):
                    $val = (string) ($server[$key] ?? '');
                    if ($val === '') continue;
                ?>
                <div class="adm-sec-dl-row">
                    <dt><?= htmlspecialchars($lbl) ?></dt>
                    <dd><code><?= htmlspecialchars($val) ?></code></dd>
                </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>