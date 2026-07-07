<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/security-console.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$settings = sh_load_settings();
$ta = $GLOBALS['t']['admin'] ?? [];
$labels = $ta['security_console_page']['checks'] ?? [];

$checks = sh_sec_vulnerability_checks($labels);
$scoreResult = sh_sec_score($checks);
$result = sh_ai_security_recommendations($settings, $checks, (int) ($scoreResult['score'] ?? 0));

sh_json_response([
    'ok'              => $result['ok'],
    'demo'            => $result['demo'],
    'summary'         => $result['summary'],
    'recommendations' => $result['recommendations'],
    'score'           => $scoreResult['score'],
    'failed'          => $scoreResult['failed'],
    'error'           => $result['error'],
], $result['ok'] ? 200 : 400);