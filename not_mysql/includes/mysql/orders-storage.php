<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/invoice-settings.php';
require_once __DIR__ . '/tax-settings.php';
require_once __DIR__ . '/store-settings.php';

function sh_db_ensure_orders_table(): void
{
    static $done = false;
    if ($done || !sh_is_installed()) {
        return;
    }
    $pdo = sh_db_pdo();
    if (!$pdo instanceof PDO) {
        return;
    }
    $tbl = sh_db_table('orders');
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `' . $tbl . '` (
          `id` VARCHAR(64) NOT NULL,
          `data` JSON NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}

/** @return list<array<string, mixed>> */
function sh_orders_load(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        sh_db_ensure_orders_table();
        return sh_db_load_orders();
    } catch (Throwable $e) {
        return [];
    }
}

function sh_orders_save(array $orders): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    sh_db_ensure_orders_table();
    return sh_db_save_orders(array_values($orders));
}

function sh_order_by_id(string $id): ?array
{
    $id = trim($id);
    if ($id === '') {
        return null;
    }
    foreach (sh_orders_load() as $order) {
        if (($order['id'] ?? '') === $id) {
            return $order;
        }
    }
    return null;
}

function sh_order_by_token(string $token): ?array
{
    $token = trim($token);
    if ($token === '') {
        return null;
    }
    foreach (sh_orders_load() as $order) {
        if (hash_equals((string) ($order['access_token'] ?? ''), $token)) {
            return $order;
        }
    }
    return null;
}

/**
 * @param list<array<string, mixed>> $cartLines
 * @param array<string, mixed> $customer
 */
function sh_order_create(array $cartLines, array $customer, string $paymentMethod, ?array $settings = null, ?string $lang = null): ?array
{
    if ($cartLines === []) {
        return null;
    }
    if ($settings === null && function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
        $settings = sh_load_settings();
    }
    $settings = sh_invoice_merge_settings(is_array($settings) ? $settings : []);
    if (empty($settings['invoice_enabled'])) {
        return null;
    }

    require_once __DIR__ . '/payment-settings.php';
    $subtotal = 0;
    $lines = [];
    foreach ($cartLines as $line) {
        $qty = max(1, (int) ($line['qty'] ?? 1));
        $price = (int) ($line['price'] ?? 0);
        $subtotal += $price * $qty;
        $lines[] = [
            'id'        => (string) ($line['id'] ?? ''),
            'name'      => (string) ($line['name'] ?? ''),
            'qty'       => $qty,
            'unit_price'=> $price,
            'subtotal'  => $price * $qty,
        ];
    }

    $tax = sh_tax_breakdown($subtotal, $settings, $lang);
    $invoiceNo = sh_invoice_allocate_number($settings);
    if (function_exists('sh_save_settings')) {
        sh_save_settings($settings);
    }

    $now = gmdate('Y-m-d');
    $dueDays = (int) ($settings['invoice_due_days'] ?? 14);
    $due = gmdate('Y-m-d', strtotime('+' . $dueDays . ' days'));

    $order = [
        'id'              => 'ord-' . bin2hex(random_bytes(8)),
        'invoice_no'      => $invoiceNo,
        'status'          => 'pending',
        'payment_method'  => $paymentMethod,
        'payment_status'  => 'demo',
        'lang'            => $lang ?? 'en',
        'customer'        => [
            'name'    => trim($customer['name'] ?? ''),
            'email'   => trim($customer['email'] ?? ''),
            'phone'   => trim($customer['phone'] ?? ''),
            'address' => trim($customer['address'] ?? ''),
            'city'    => trim($customer['city'] ?? ''),
            'postal'  => trim($customer['postal'] ?? ''),
            'country' => trim($customer['country'] ?? ''),
        ],
        'seller'          => sh_invoice_company_block($settings),
        'lines'           => $lines,
        'totals'          => [
            'subtotal' => $tax['subtotal'],
            'net'      => $tax['net'],
            'tax'      => $tax['tax'],
            'total'    => $tax['total'],
            'currency' => sh_site_currency($settings),
            'tax_rate' => $tax['rate'],
            'tax_label'=> $tax['label'],
        ],
        'notes'           => trim($settings['invoice_notes'] ?? ''),
        'print_design'    => $settings['invoice_print_design'],
        'print_format'    => $settings['invoice_print_format'],
        'print_margin'    => $settings['invoice_print_margin'],
        'invoice_date'    => $now,
        'due_date'        => $due,
        'created_at'      => gmdate('Y-m-d\TH:i:s\Z'),
        'invoice_sent_at' => null,
        'access_token'    => bin2hex(random_bytes(16)),
    ];

    $orders = sh_orders_load();
    array_unshift($orders, $order);
    if (count($orders) > 2000) {
        $orders = array_slice($orders, 0, 2000);
    }
    return sh_orders_save($orders) ? $order : null;
}

function sh_order_update_status(string $id, string $status): bool
{
    $orders = sh_orders_load();
    $changed = false;
    foreach ($orders as &$order) {
        if (($order['id'] ?? '') !== $id) {
            continue;
        }
        $order['status'] = $status;
        $order['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        $changed = true;
        break;
    }
    unset($order);
    return $changed && sh_orders_save($orders);
}

function sh_order_update_payment_status(string $id, string $paymentStatus, ?string $gateway = null): bool
{
    $orders = sh_orders_load();
    $changed = false;
    foreach ($orders as &$order) {
        if (($order['id'] ?? '') !== $id) {
            continue;
        }
        $order['payment_status'] = $paymentStatus;
        if ($gateway !== null && $gateway !== '') {
            $order['payment_gateway'] = $gateway;
        }
        if ($paymentStatus === 'paid') {
            $order['status'] = 'paid';
        }
        $order['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        $changed = true;
        break;
    }
    unset($order);
    return $changed && sh_orders_save($orders);
}

function sh_order_mark_invoice_sent(string $id): bool
{
    $orders = sh_orders_load();
    $changed = false;
    foreach ($orders as &$order) {
        if (($order['id'] ?? '') !== $id) {
            continue;
        }
        $order['invoice_sent_at'] = gmdate('Y-m-d\TH:i:s\Z');
        $changed = true;
        break;
    }
    unset($order);
    return $changed && sh_orders_save($orders);
}

function sh_orders_count_by_status(string $status = 'pending'): int
{
    $n = 0;
    foreach (sh_orders_load() as $order) {
        if (($order['status'] ?? 'pending') === $status) {
            $n++;
        }
    }
    return $n;
}

function sh_order_delete(string $id): bool
{
    $orders = array_values(array_filter(
        sh_orders_load(),
        static fn(array $o): bool => ($o['id'] ?? '') !== $id
    ));
    return sh_orders_save($orders);
}

/** @return array<string, mixed> */
function sh_order_to_invoice_doc(array $order): array
{
    $customer = $order['customer'] ?? [];
    return [
        'id'           => $order['id'] ?? '',
        'invoice_no'   => $order['invoice_no'] ?? '',
        'invoice_date' => $order['invoice_date'] ?? '',
        'due_date'     => $order['due_date'] ?? '',
        'seller'       => $order['seller'] ?? sh_invoice_company_block(),
        'buyer'        => [
            'name'    => $customer['name'] ?? '',
            'email'   => $customer['email'] ?? '',
            'phone'   => $customer['phone'] ?? '',
            'address' => $customer['address'] ?? '',
            'city'    => $customer['city'] ?? '',
            'postal'  => $customer['postal'] ?? '',
            'country' => $customer['country'] ?? '',
        ],
        'lines'   => $order['lines'] ?? [],
        'totals'  => $order['totals'] ?? [],
        'notes'   => $order['notes'] ?? '',
        'print_design' => $order['print_design'] ?? 'classic-blue',
        'print_format' => $order['print_format'] ?? 'a4',
        'payment_purpose' => $order['invoice_no'] ?? '',
    ];
}