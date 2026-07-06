<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once dirname(__DIR__, 2) . '/includes/store-settings.php';
require_once dirname(__DIR__, 2) . '/includes/lang-registry.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';

sh_admin_require();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST only']);
    exit;
}

$body = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($body)) {
    $body = [];
}

$settings = sh_load_settings();
$ai = sh_ai_settings($settings);
$source = 'en';
$target = strtolower(trim((string) ($body['target'] ?? '')));
$addActive = !empty($body['add_active']);

if ($target === '' || $target === $source) {
    echo json_encode(['ok' => false, 'error' => 'Select a target language (not English).']);
    exit;
}

if (!preg_match('/^[a-z]{2}([a-z0-9-]{0,8})?$/', $target)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid language code.']);
    exit;
}

$meta = sh_world_language_meta($target);
if ($meta === null) {
    echo json_encode(['ok' => false, 'error' => 'Unknown language code.']);
    exit;
}

$enFile = dirname(__DIR__, 2) . '/lang/en.php';
if (!is_file($enFile)) {
    echo json_encode(['ok' => false, 'error' => 'Source lang/en.php missing']);
    exit;
}

$sourceData = require $enFile;
$demo = !sh_ai_enabled($settings);
$outFile = sh_lang_file_path($target);

if ($demo) {
    if (!is_file($outFile)) {
        file_put_contents($outFile, "<?php\nreturn " . var_export($sourceData, true) . ";\n");
    }
} else {
    $result = sh_ai_translate_lang_file($sourceData, $source, $target, $ai);
    if (!$result['ok']) {
        echo json_encode(['ok' => false, 'error' => $result['error']]);
        exit;
    }
    file_put_contents($outFile, "<?php\nreturn " . var_export($result['data'], true) . ";\n", LOCK_EX);
}

$langAdded = false;
if ($addActive) {
    $settings = sh_merge_store_settings($settings);
    $rows = $settings['site_languages'] ?? [];
    if ($rows === []) {
        foreach (sh_builtin_langs() as $code => $info) {
            $rows[] = array_merge(['code' => $code, 'active' => true], $info);
        }
    }
    $exists = false;
    foreach ($rows as $row) {
        if (($row['code'] ?? '') === $target) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $rows[] = [
            'code'   => $target,
            'label'  => $meta['label'],
            'name'   => $meta['name'],
            'flag'   => $meta['flag'],
            'locale' => $meta['locale'],
            'html'   => $meta['html'],
            'active' => true,
        ];
        $settings['site_languages'] = $rows;
        sh_save_settings($settings);
        $langAdded = true;
    }
}

echo json_encode([
    'ok'         => true,
    'demo'       => $demo,
    'target'     => $target,
    'target_name'=> $meta['name'],
    'file'       => 'lang/' . $target . '.php',
    'lang_added' => $langAdded,
]);