<?php
require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/subscribers-storage.php';
require_once dirname(__DIR__, 2) . '/includes/shop-mail.php';
require_once dirname(__DIR__, 2) . '/includes/payment-settings.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    $data = $_POST;
}

$subscriberId = trim((string) ($data['subscriber_id'] ?? ''));
$to = trim((string) ($data['to'] ?? ''));
$subject = trim((string) ($data['subject'] ?? ''));
$body = trim((string) ($data['body'] ?? ''));

$ta = $t['admin']['subscribers_page'] ?? [];

if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $ta['email_invalid'] ?? 'Invalid email address']);
    exit;
}

if ($subject === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $ta['email_subject_required'] ?? 'Subject is required']);
    exit;
}

if ($body === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $ta['email_body_required'] ?? 'Message body is required']);
    exit;
}

if ($subscriberId !== '') {
    $found = false;
    foreach (sh_subscribers_load() as $sub) {
        if ((string) ($sub['id'] ?? '') === $subscriberId) {
            $found = true;
            if (strcasecmp((string) ($sub['email'] ?? ''), $to) !== 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => $ta['email_mismatch'] ?? 'Email does not match subscriber']);
                exit;
            }
            break;
        }
    }
    if (!$found) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => $ta['subscriber_not_found'] ?? 'Subscriber not found']);
        exit;
    }
}

$settings = sh_load_settings();
$html = '<div style="font-family:sans-serif;line-height:1.5;color:#0f172a">' . nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')) . '</div>';
$sent = sh_send_mail($to, $subject, $html, null, null, $body, $settings);

if (!$sent) {
    $err = sh_mail_last_error() ?: ($ta['email_send_failed'] ?? 'Could not send email');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $err]);
    exit;
}

echo json_encode([
    'ok'      => true,
    'message' => $ta['email_sent'] ?? 'Email sent successfully',
]);