<?php
/**
 * Shop CMS demo package download — requires accepted install terms (POST).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/demo-package.php';

$rootCabinet = dirname(__DIR__, 2) . '/includes/license-cabinet.php';
if (!is_file($rootCabinet)) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'cabinet_unavailable'], JSON_UNESCAPED_UNICODE);
    exit;
}
require_once $rootCabinet;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'method'], JSON_UNESCAPED_UNICODE);
    exit;
}

$terms = !empty($_POST['terms_accept']) || !empty($_POST['terms']);
if (!$terms) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'terms'], JSON_UNESCAPED_UNICODE);
    exit;
}

$auth = license_cabinet_require_download_auth();
if ($auth === null) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'cabinet_required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$lang = strtolower(trim((string) ($_POST['lang'] ?? 'en')));
$mode = trim((string) ($_POST['mode'] ?? 'download'));
$licenseKey = trim((string) ($_POST['license_key'] ?? ''));
$email = license_cabinet_normalize_email((string) ($auth['email'] ?? ''));
$postEmail = license_cabinet_normalize_email((string) ($_POST['email'] ?? ''));
if ($postEmail !== '' && $postEmail !== $email) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'cabinet_email_mismatch'], JSON_UNESCAPED_UNICODE);
    exit;
}
$domain = trim((string) ($_POST['domain'] ?? ''));

$log = [
    'lang'   => $lang,
    'mode'   => $mode,
    'email'  => $email,
    'domain' => $domain,
    'terms'  => true,
];

if ($mode === 'ftp') {
    $ftpCheck = sh_demo_package_validate_ftp_form($_POST);
    if (!$ftpCheck['ok']) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $ftpCheck['error']], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($licenseKey !== '') {
        $lic = sh_demo_package_verify_license_key($licenseKey, $domain);
        $log['license_ok'] = $lic['ok'];
        $log['license_plan'] = $lic['plan'];
    }
    $log['ftp_host'] = trim((string) ($_POST['ftp_host'] ?? ''));
    $log['ftp_user'] = trim((string) ($_POST['ftp_user'] ?? ''));
    $log['ftp_path'] = trim((string) ($_POST['ftp_path'] ?? ''));
    $log['note'] = 'ftp_install_request';
    sh_demo_package_log_request($log);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'queued' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($licenseKey !== '') {
    $lic = sh_demo_package_verify_license_key($licenseKey, $domain);
    if (!$lic['ok']) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'license', 'detail' => $lic['error']], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $log['license_ok'] = true;
    $log['license_plan'] = $lic['plan'];
}

$zip = sh_demo_package_latest_zip();
if ($zip === null || !is_readable($zip)) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'package_missing'], JSON_UNESCAPED_UNICODE);
    exit;
}

$log['file'] = basename($zip);
sh_demo_package_log_request($log);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zip) . '"');
header('Content-Length: ' . (string) filesize($zip));
header('Cache-Control: no-store');
readfile($zip);
exit;