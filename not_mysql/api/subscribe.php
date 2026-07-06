<?php
require_once dirname(__DIR__) . '/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$payload = $_POST;
if ($payload === [] || !isset($payload['email'])) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

$email = strtolower(trim((string) ($payload['email'] ?? '')));
$lang = strtolower(trim((string) ($payload['lang'] ?? '')));
if ($lang === '') {
    global $lang;
    $lang = (isset($lang) && is_string($lang) && $lang !== '') ? $lang : 'en';
}

require_once dirname(__DIR__) . '/includes/payment-settings.php';
require_once dirname(__DIR__) . '/includes/smtp-settings.php';
require_once dirname(__DIR__) . '/includes/subscribers-storage.php';
require_once dirname(__DIR__) . '/includes/shop-mail.php';

$settings = sh_load_settings();
$smtp = sh_smtp_merge_settings($settings);

if (empty($smtp['newsletter_enabled'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'newsletter_disabled']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_email']);
    exit;
}

$sub = sh_subscriber_add($email, $lang);
if ($sub === null) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'error' => 'already_subscribed']);
    exit;
}

$notify = trim((string) ($smtp['newsletter_notify_email'] ?? ''));
if ($notify !== '' && filter_var($notify, FILTER_VALIDATE_EMAIL)) {
    $site = htmlspecialchars((string) ($settings['seo_site_name'] ?? 'Shop CMS'));
    sh_send_mail(
        $notify,
        'New newsletter subscriber — ' . $site,
        '<p>New subscriber: <strong>' . htmlspecialchars($email) . '</strong></p><p>Language: ' . htmlspecialchars($lang) . '</p>',
        $email,
        null,
        null,
        $settings
    );
}

$welcomeSubject = trim((string) ($smtp['newsletter_welcome_subject'] ?? ''));
$welcomeBody = trim((string) ($smtp['newsletter_welcome_body'] ?? ''));
if ($welcomeSubject !== '' && $welcomeBody !== '' && sh_smtp_is_configured($smtp)) {
    sh_send_mail(
        $email,
        $welcomeSubject,
        '<p>' . nl2br(htmlspecialchars($welcomeBody)) . '</p>',
        null,
        null,
        $welcomeBody,
        $settings
    );
}

echo json_encode(['ok' => true, 'message' => 'subscribed']);