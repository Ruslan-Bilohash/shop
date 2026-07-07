<?php

function sh_telegram_settings_defaults(): array
{
    return [
        'telegram_enabled'           => false,
        'telegram_bot_token'         => '',
        'telegram_chat_id'           => '',
        'telegram_notify_orders'     => true,
        'telegram_notify_quick_buy'  => true,
        'telegram_parse_mode'        => 'HTML',
    ];
}

function sh_telegram_merge_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    return array_merge(sh_telegram_settings_defaults(), is_array($settings) ? $settings : []);
}

function sh_telegram_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_telegram_merge_settings($settings);
    $settings['telegram_enabled'] = !empty($post['telegram_enabled']);
    $settings['telegram_notify_orders'] = !empty($post['telegram_notify_orders']);
    $settings['telegram_notify_quick_buy'] = !empty($post['telegram_notify_quick_buy']);

    $token = trim($post['telegram_bot_token'] ?? '');
    if ($token !== '') {
        $settings['telegram_bot_token'] = $token;
    }

    $chatId = trim($post['telegram_chat_id'] ?? '');
    if ($chatId !== '') {
        $settings['telegram_chat_id'] = $chatId;
    }

    $mode = strtoupper(trim($post['telegram_parse_mode'] ?? 'HTML'));
    $settings['telegram_parse_mode'] = in_array($mode, ['HTML', 'Markdown', 'MarkdownV2'], true) ? $mode : 'HTML';

    return $settings;
}

function sh_telegram_is_configured(?array $settings = null): bool
{
    $s = sh_telegram_merge_settings($settings);
    return !empty($s['telegram_enabled'])
        && trim((string) ($s['telegram_bot_token'] ?? '')) !== ''
        && trim((string) ($s['telegram_chat_id'] ?? '')) !== '';
}

/**
 * @return array{ok:bool,error:string}
 */
function sh_telegram_send_message(string $text, ?array $settings = null): array
{
    $s = sh_telegram_merge_settings($settings);
    if (!sh_telegram_is_configured($s)) {
        return ['ok' => false, 'error' => 'Telegram not configured'];
    }

    $token = trim((string) $s['telegram_bot_token']);
    $chatId = trim((string) $s['telegram_chat_id']);
    $parseMode = (string) ($s['telegram_parse_mode'] ?? 'HTML');

    $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $payload = [
        'chat_id' => $chatId,
        'text' => mb_substr($text, 0, 4000),
        'disable_web_page_preview' => true,
    ];
    if ($parseMode !== '') {
        $payload['parse_mode'] = $parseMode;
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($payload),
            'timeout' => 12,
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'error' => 'HTTP request failed'];
    }

    $json = json_decode($raw, true);
    if (!is_array($json) || empty($json['ok'])) {
        $desc = is_array($json) ? (string) ($json['description'] ?? 'Unknown error') : 'Invalid response';
        return ['ok' => false, 'error' => $desc];
    }

    return ['ok' => true, 'error' => ''];
}

function sh_telegram_escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sh_telegram_notify_order(array $order, ?array $settings = null): void
{
    $s = sh_telegram_merge_settings($settings);
    if (!sh_telegram_is_configured($s) || empty($s['telegram_notify_orders'])) {
        return;
    }

    require_once __DIR__ . '/store-settings.php';
    $cust = $order['customer'] ?? [];
    $total = sh_format_price((int) (($order['totals']['total'] ?? 0)), $settings);
    $lines = [];
    foreach ($order['lines'] ?? [] as $line) {
        $lines[] = '• ' . sh_telegram_escape((string) ($line['name'] ?? ''))
            . ' × ' . (int) ($line['qty'] ?? 1)
            . ' — ' . sh_telegram_escape(sh_format_price((int) ($line['subtotal'] ?? 0), $settings));
    }

    $adminUrl = function_exists('sh_admin_url')
        ? sh_admin_url('order-view.php?id=' . rawurlencode($order['id'] ?? ''))
        : '';

    $msg = "🛒 <b>Нове замовлення</b>\n"
        . '<b>' . sh_telegram_escape($order['invoice_no'] ?? '') . "</b>\n"
        . 'Сума: <b>' . sh_telegram_escape($total) . "</b>\n"
        . 'Оплата: ' . sh_telegram_escape($order['payment_method'] ?? '') . "\n\n"
        . '<b>Покупець</b>: ' . sh_telegram_escape($cust['name'] ?? '—') . "\n"
        . 'Email: ' . sh_telegram_escape($cust['email'] ?? '—') . "\n"
        . 'Тел: ' . sh_telegram_escape($cust['phone'] ?? '—') . "\n\n"
        . "<b>Товари</b>\n" . implode("\n", $lines);

    if ($adminUrl !== '') {
        $msg .= "\n\n<a href=\"" . sh_telegram_escape($adminUrl) . "\">Відкрити в адмінці</a>";
    }

    sh_telegram_send_message($msg, $s);
}

function sh_telegram_notify_quick_buy(array $lead, ?array $settings = null): void
{
    $s = sh_telegram_merge_settings($settings);
    if (!sh_telegram_is_configured($s) || empty($s['telegram_notify_quick_buy'])) {
        return;
    }

    $msg = "⚡ <b>Швидка покупка</b>\n"
        . 'Тел: <b>' . sh_telegram_escape($lead['phone'] ?? '') . "</b>\n"
        . 'Товар: ' . sh_telegram_escape($lead['product_name'] ?? '') . "\n"
        . 'ID: ' . sh_telegram_escape($lead['product_id'] ?? '');

    if (!empty($lead['name'])) {
        $msg .= "\nІмʼя: " . sh_telegram_escape($lead['name']);
    }

    sh_telegram_send_message($msg, $s);
}

function sh_telegram_send_test(?array $settings = null): array
{
    return sh_telegram_send_message(
        "✅ <b>Shop CMS</b>\nTelegram notifications are working.",
        $settings
    );
}