<?php
/**
 * Cron: refresh EUR FX rates for subscription pricing banners.
 * Call: GET /shop/api/cron-pricing-fx.php?token=YOUR_SECRET
 * Hostinger cron: 0 6 * * * curl -s "https://bilohash.com/shop/api/cron-pricing-fx.php?token=..."
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/billing-pricing.php';

header('Content-Type: application/json; charset=utf-8');

$expected = '';
$tokenFile = dirname(__DIR__) . '/data/cron-token.txt';
if (is_file($tokenFile)) {
    $expected = trim((string) file_get_contents($tokenFile));
}
if ($expected === '') {
    $expected = 'shop-cms-fx-' . substr(hash('sha256', SH_DOMAIN . SH_BASE_PATH), 0, 16);
}

$token = trim((string) ($_GET['token'] ?? $_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
if ($token === '' || !hash_equals($expected, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = sh_billing_fx_refresh();
http_response_code($result['ok'] ? 200 : 502);
echo json_encode([
    'ok'      => $result['ok'],
    'updated' => $result['fx']['updated'] ?? '',
    'source'  => $result['fx']['source'] ?? '',
    'rates'   => $result['fx']['rates'] ?? [],
    'error'   => $result['error'],
], JSON_UNESCAPED_UNICODE);