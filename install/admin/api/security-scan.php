<?php
/**
 * JSON port rescan for security console (admin only).
 */
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/security-console.php';

header('Content-Type: application/json; charset=utf-8');

$host = trim((string) ($_GET['host'] ?? $_POST['host'] ?? '127.0.0.1'));
if ($host === '' || !preg_match('/^[a-zA-Z0-9.\-:]+$/', $host)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid host'], JSON_UNESCAPED_UNICODE);
    exit;
}

$scan = sh_sec_use_demo_snapshot() ? sh_sec_demo_port_scan($host) : sh_sec_scan_ports($host);
echo json_encode(['ok' => true, 'scan' => $scan, 'demo' => sh_sec_use_demo_snapshot()], JSON_UNESCAPED_UNICODE);