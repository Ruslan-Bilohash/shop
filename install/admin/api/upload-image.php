<?php
require_once dirname(__DIR__, 2) . '/init.php';
require_once dirname(__DIR__, 2) . '/includes/admin-auth.php';
require_once dirname(__DIR__, 2) . '/includes/ai.php';
require_once dirname(__DIR__, 2) . '/includes/image-upload.php';

sh_admin_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sh_json_response(['ok' => false, 'error' => 'POST required'], 405);
}

$action = trim($_POST['action'] ?? 'upload');

if ($action === 'delete') {
    $url = trim($_POST['url'] ?? '');
    $deleted = sh_delete_uploaded_file($url);
    sh_json_response(['ok' => $deleted, 'error' => $deleted ? '' : 'Could not delete file.'], $deleted ? 200 : 400);
}

if (empty($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'] ?? '')) {
    sh_json_response(['ok' => false, 'error' => 'No image uploaded.'], 400);
}

$tmp = $_FILES['image']['tmp_name'];
$maxBytes = 8 * 1024 * 1024;
if (($_FILES['image']['size'] ?? 0) > $maxBytes) {
    sh_json_response(['ok' => false, 'error' => 'Image too large (max 8 MB).'], 400);
}

$result = sh_process_uploaded_image($tmp, 'products');
if (!$result['ok']) {
    sh_json_response(['ok' => false, 'error' => $result['error'] ?? 'Upload failed.'], 400);
}

sh_json_response([
    'ok'     => true,
    'url'    => $result['url'],
    'format' => $result['format'] ?? 'webp',
    'width'  => $result['width'] ?? 0,
    'height' => $result['height'] ?? 0,
]);