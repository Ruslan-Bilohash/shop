<?php

require_once __DIR__ . '/shop-mail.php';
require_once __DIR__ . '/store-settings.php';
require_once __DIR__ . '/orders-storage.php';
require_once __DIR__ . '/invoice-settings.php';

function sh_invoice_public_url(array $order): string
{
    $id = $order['id'] ?? '';
    $token = $order['access_token'] ?? '';
    $path = 'invoice.php?id=' . rawurlencode($id) . '&token=' . rawurlencode($token);
    return function_exists('sh_url') ? sh_url($path) : '/' . ltrim($path, '/');
}

function sh_invoice_email_content(array $order, ?array $settings = null, ?string $lang = null): array
{
    $lang = strtolower((string) ($lang ?? $order['lang'] ?? 'en'));
    $labels = sh_invoice_labels($lang);
    $invoiceNo = $order['invoice_no'] ?? '';
    $company = ($order['seller']['name'] ?? '') ?: sh_invoice_company_block($settings)['name'];
    $total = sh_format_price((int) (($order['totals']['total'] ?? 0)), $settings);
    $link = sh_invoice_public_url($order);

    $subject = sprintf(
        $labels['email_subject'] ?? 'Invoice %s from %s',
        $invoiceNo,
        $company
    );

    $body = '<div style="font-family:system-ui,sans-serif;max-width:560px;line-height:1.5">';
    $body .= '<p>' . htmlspecialchars($labels['email_greeting'] ?? 'Hello', ENT_QUOTES) . ',</p>';
    $body .= '<p>' . htmlspecialchars($labels['email_intro'] ?? 'Please find your invoice below.', ENT_QUOTES) . '</p>';
    $body .= '<p><strong>' . htmlspecialchars($labels['invoice_no'] ?? 'Invoice', ENT_QUOTES) . ':</strong> '
        . htmlspecialchars($invoiceNo, ENT_QUOTES) . '<br>';
    $body .= '<strong>' . htmlspecialchars($labels['total'] ?? 'Total', ENT_QUOTES) . ':</strong> '
        . htmlspecialchars($total, ENT_QUOTES) . '</p>';
    $body .= '<p><a href="' . htmlspecialchars($link, ENT_QUOTES) . '" style="display:inline-block;padding:.65rem 1.2rem;background:#1e40af;color:#fff;text-decoration:none;border-radius:8px">'
        . htmlspecialchars($labels['email_view_btn'] ?? 'View & print invoice', ENT_QUOTES) . '</a></p>';
    $body .= '<p style="color:#64748b;font-size:.9em">' . htmlspecialchars($labels['email_footer'] ?? 'Thank you for your order.', ENT_QUOTES) . '</p>';
    $body .= '</div>';

    $plain = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body)) . "\n" . $link;

    return ['subject' => $subject, 'html' => $body, 'plain' => $plain];
}

function sh_send_order_invoice(string $orderId, ?string $toEmail = null, ?array $settings = null): array
{
    $order = sh_order_by_id($orderId);
    if ($order === null) {
        return ['ok' => false, 'error' => 'Order not found', 'demo' => false];
    }

    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }

    $to = trim($toEmail ?? ($order['customer']['email'] ?? ''));
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Invalid recipient email', 'demo' => false];
    }

    $mail = sh_invoice_email_content($order, $settings, $order['lang'] ?? null);
    $sent = sh_send_mail($to, $mail['subject'], $mail['html'], null, null, $mail['plain'], $settings);

    if ($sent) {
        sh_order_mark_invoice_sent($orderId);
        return ['ok' => true, 'error' => '', 'demo' => false];
    }

    return ['ok' => false, 'error' => sh_mail_last_error() ?: 'Send failed', 'demo' => false];
}