<?php
/**
 * Paysera WebToPay redirect builder (no external SDK).
 */
declare(strict_types=1);

function sh_paysera_pay_url(bool $test): string
{
    return $test
        ? 'https://sandbox.paysera.com/pay/'
        : 'https://www.paysera.com/pay/';
}

/** @param array<string, mixed> $order */
function sh_paysera_build_payment_url(array $order, array $settings): string
{
    $cfg = $settings['paysera'] ?? [];
    $projectId = (int) ($cfg['project_id'] ?? 0);
    $password = (string) ($cfg['sign_password'] ?? '');
    if ($projectId < 1 || $password === '') {
        throw new RuntimeException('Paysera is not configured.');
    }

    $test = ($cfg['mode'] ?? 'test') === 'test';
    $total = (int) ($order['totals']['total'] ?? 0);
    $currency = strtoupper((string) ($cfg['currency'] ?? ($order['totals']['currency'] ?? 'EUR')));
    $customer = is_array($order['customer'] ?? null) ? $order['customer'] : [];
    $orderId = (string) ($order['id'] ?? '');

    $accept = sh_absolute_url(sh_url('checkout.php?paid=1&order=' . urlencode($orderId)));
    $cancel = sh_absolute_url(sh_url('checkout.php'));
    $callback = sh_absolute_url(sh_url('api/paysera-callback.php'));

    $params = [
        'projectid'     => $projectId,
        'orderid'       => $orderId,
        'amount'        => $total * 100,
        'currency'      => $currency,
        'accepturl'     => $accept,
        'cancelurl'     => $cancel,
        'callbackurl'   => $callback,
        'payment'       => '',
        'country'       => 'LT',
        'lang'          => strtoupper((string) ($order['lang'] ?? 'EN')),
        'p_email'       => (string) ($customer['email'] ?? ''),
        'p_firstname'   => (string) ($customer['name'] ?? ''),
    ];
    if ($test) {
        $params['test'] = 1;
    }

    $encoded = http_build_query($params);
    $sign = md5($encoded . $password);

    return sh_paysera_pay_url($test) . '?' . $encoded . '&sign=' . $sign;
}

/** @return array<string, mixed>|null */
function sh_paysera_parse_callback(string $rawData, string $password): ?array
{
    if ($rawData === '' || $password === '') {
        return null;
    }
    parse_str($rawData, $fields);
    $sign = (string) ($fields['sign'] ?? '');
    unset($fields['sign']);
    $encoded = http_build_query($fields);
    if ($sign === '' || !hash_equals(md5($encoded . $password), $sign)) {
        return null;
    }
    return $fields;
}