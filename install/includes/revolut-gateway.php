<?php
/**
 * Revolut Merchant API — create checkout order and redirect URL.
 */
declare(strict_types=1);

function sh_revolut_api_base(array $cfg): string
{
    return ($cfg['mode'] ?? 'sandbox') === 'production'
        ? 'https://merchant.revolut.com/api/1.0'
        : 'https://sandbox-merchant.revolut.com/api/1.0';
}

/** @param array<string, mixed> $order */
function sh_revolut_create_checkout(array $order, array $settings): array
{
    $cfg = $settings['revolut'] ?? [];
    $secret = trim((string) ($cfg['secret_key'] ?? ''));
    if ($secret === '') {
        throw new RuntimeException('Revolut is not configured.');
    }

    $total = (int) ($order['totals']['total'] ?? 0);
    $currency = strtoupper((string) ($order['totals']['currency'] ?? 'EUR'));
    $orderId = (string) ($order['id'] ?? '');
    $customer = is_array($order['customer'] ?? null) ? $order['customer'] : [];

    $payload = [
        'amount'   => $total * 100,
        'currency' => $currency,
        'capture_mode' => 'AUTOMATIC',
        'merchant_order_ext_ref' => $orderId,
        'description' => 'Order ' . ($order['invoice_no'] ?? $orderId),
        'customer_email' => (string) ($customer['email'] ?? ''),
        'redirect_url' => sh_absolute_url(sh_url('checkout.php?paid=1&order=' . urlencode($orderId))),
    ];

    $url = sh_revolut_api_base($cfg) . '/orders';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $secret,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_THROW_ON_ERROR),
        CURLOPT_TIMEOUT        => 30,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!is_string($body) || $body === '') {
        throw new RuntimeException('Revolut API returned empty response.');
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Revolut API returned invalid JSON.');
    }
    if ($code < 200 || $code >= 300) {
        $msg = (string) ($decoded['message'] ?? $decoded['error'] ?? 'HTTP ' . $code);
        throw new RuntimeException('Revolut API error: ' . $msg);
    }

    $checkoutUrl = (string) ($decoded['checkout_url'] ?? '');
    if ($checkoutUrl === '') {
        throw new RuntimeException('Revolut API did not return checkout_url.');
    }

    return [
        'checkout_url' => $checkoutUrl,
        'revolut_order_id' => (string) ($decoded['id'] ?? ''),
        'raw' => $decoded,
    ];
}