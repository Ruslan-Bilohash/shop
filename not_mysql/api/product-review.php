<?php
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/ecosystem-load.php';
sh_require_ecosystem('cms-contact.php');
require_once dirname(__DIR__) . '/includes/product-reviews-storage.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = $_POST;
if ($payload === [] || !isset($payload['product_id'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

if (!empty($payload['website'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'spam'], JSON_UNESCAPED_UNICODE);
    exit;
}

$settings = sh_site_settings();
if (!cms_verify_recaptcha($payload['g-recaptcha-response'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'recaptcha'], JSON_UNESCAPED_UNICODE);
    exit;
}

global $lang;
$result = sh_product_review_add([
    'product_id' => (string) ($payload['product_id'] ?? ''),
    'author'       => (string) ($payload['author'] ?? ''),
    'title'        => (string) ($payload['title'] ?? ''),
    'body'         => (string) ($payload['body'] ?? ''),
    'rating'       => (int) ($payload['rating'] ?? 0),
    'lang'         => (string) ($payload['lang'] ?? ($lang ?? 'en')),
]);

if (empty($result['ok'])) {
    $code = match ($result['error'] ?? '') {
        'rate_limit' => 429,
        'invalid_product', 'invalid_rating', 'invalid_author', 'invalid_body', 'invalid_title' => 400,
        default => 500,
    };
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'error'], JSON_UNESCAPED_UNICODE);
    exit;
}

$agg = sh_product_reviews_aggregate((string) ($payload['product_id'] ?? ''));
echo json_encode([
    'ok'     => true,
    'review' => $result['review'] ?? null,
    'aggregate' => $agg,
], JSON_UNESCAPED_UNICODE);