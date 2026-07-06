<?php
/**
 * Shop CMS — outbound mail via admin SMTP settings.
 */
declare(strict_types=1);

require_once __DIR__ . '/smtp-settings.php';

function sh_mail_load_phpmailer(): bool
{
    static $loaded = false;
    if ($loaded) {
        return true;
    }
    $bases = [
        dirname(__DIR__, 2) . '/PHPMailer/src',
        dirname(__DIR__, 3) . '/PHPMailer/src',
        ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/PHPMailer/src',
    ];
    foreach ($bases as $base) {
        if (!is_file($base . '/PHPMailer.php')) {
            continue;
        }
        require_once $base . '/Exception.php';
        require_once $base . '/PHPMailer.php';
        require_once $base . '/SMTP.php';
        $loaded = true;
        return true;
    }
    require_once __DIR__ . '/ecosystem-load.php';
    $bhMail = __DIR__ . '/bh-mail.php';
    if (!is_file($bhMail)) {
        $bhMail = dirname(__DIR__, 2) . '/includes/bh-mail.php';
    }
    if (is_file($bhMail)) {
        require_once $bhMail;
        return function_exists('bh_mail_load_phpmailer') && bh_mail_load_phpmailer();
    }
    return false;
}

function sh_mail_last_error(): string
{
    return $GLOBALS['sh_mail_last_error'] ?? '';
}

/**
 * @param string|array<int, string> $to
 */
function sh_send_mail(
    string|array $to,
    string $subject,
    string $htmlBody,
    ?string $replyToEmail = null,
    ?string $replyToName = null,
    ?string $plainBody = null,
    ?array $settings = null
): bool {
    $GLOBALS['sh_mail_last_error'] = '';

    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $s = sh_smtp_merge_settings(is_array($settings) ? $settings : []);

    if (!sh_smtp_is_configured($s)) {
        if (function_exists('bh_send_mail')) {
            return bh_send_mail($to, $subject, $htmlBody, $replyToEmail, $replyToName, $plainBody);
        }
        $GLOBALS['sh_mail_last_error'] = 'SMTP not configured';
        return false;
    }

    if (!sh_mail_load_phpmailer()) {
        $GLOBALS['sh_mail_last_error'] = 'PHPMailer not found';
        return false;
    }

    $recipients = is_array($to) ? $to : [$to];
    $recipients = array_values(array_filter(array_map('trim', $recipients)));
    if ($recipients === []) {
        $GLOBALS['sh_mail_last_error'] = 'No recipient';
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = (string) $s['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $s['smtp_username'];
        $mail->Password = (string) $s['smtp_password'];
        $enc = (string) ($s['smtp_encryption'] ?? 'ssl');
        if ($enc === 'none') {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        } elseif ($enc === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }
        $mail->Port = (int) ($s['smtp_port'] ?? 465);
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $fromEmail = trim((string) ($s['smtp_from_email'] ?? ''));
        if ($fromEmail === '') {
            $fromEmail = (string) $s['smtp_username'];
        }
        $fromName = trim((string) ($s['smtp_from_name'] ?? 'Shop CMS'));
        $mail->setFrom($fromEmail, $fromName);

        foreach ($recipients as $addr) {
            if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($addr);
            }
        }
        if ($replyToEmail && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyToEmail, $replyToName ?? '');
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody ?? strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        $mail->send();
        return true;
    } catch (Throwable $e) {
        $GLOBALS['sh_mail_last_error'] = $e->getMessage();
        return false;
    }
}