<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$payload = $_POST;
if ($payload === [] || !isset($payload['product_name'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$productName = trim((string) ($payload['product_name'] ?? ''));
$category = trim((string) ($payload['category'] ?? ''));
$sourceLang = trim((string) ($payload['source_lang'] ?? ''));
$brief = trim((string) ($payload['brief_description'] ?? ''));

if ($sourceLang === '' || !array_key_exists($sourceLang, sh_langs())) {
    $settings = sh_load_settings();
    $sourceLang = (string) (sh_ai_settings($settings)['ai_source_lang'] ?? 'en');
}

try {
    require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';
    $settings = sh_load_settings();
    $result = sh_ai_generate_product($settings, $productName, $category, $sourceLang, $brief);
    if (!$result['ok'] && $productName !== '') {
        $result = [
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_product_fallback($productName, $category, $sourceLang, $brief),
            'error' => $result['error'] ?? 'AI unavailable',
        ];
    }
    sh_json_response([
        'ok'    => $result['ok'],
        'demo'  => $result['demo'] ?? false,
        'data'  => $result['data'] ?? [],
        'error' => $result['error'] ?? '',
    ], $result['ok'] ? 200 : 400);
} catch (Throwable $e) {
    if ($productName !== '') {
        sh_json_response([
            'ok'    => true,
            'demo'  => true,
            'data'  => sh_ai_product_fallback($productName, $category, $sourceLang, $brief),
            'error' => $e->getMessage(),
        ], 200);
    }
    sh_json_response(['ok' => false, 'demo' => false, 'data' => [], 'error' => $e->getMessage()], 500);
}