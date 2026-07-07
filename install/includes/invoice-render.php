<?php

require_once __DIR__ . '/invoice-print-designs.php';
require_once __DIR__ . '/store-settings.php';

/**
 * @param array<string, mixed> $doc
 * @param array<string, string> $labels
 */
function sh_render_invoice_article(array $doc, array $labels, string $design, string $format, bool $wrap = true, ?array $settings = null): void
{
    $seller = $doc['seller'] ?? [];
    $buyer = $doc['buyer'] ?? [];
    $lines = $doc['lines'] ?? [];
    $totals = $doc['totals'] ?? [];
    $classes = sh_inv_print_classes($design, $format);
    $tagOpen = $wrap
        ? '<article class="sh-inv-preview ' . htmlspecialchars($classes, ENT_QUOTES) . '"'
        : '<div class="sh-inv-preview-inner"';
    echo $wrap ? $tagOpen . ' id="sh-inv-preview">' : $tagOpen . '>';
    $logo = trim((string) ($seller['logo'] ?? ''));
    ?>
    <div class="sh-inv-preview-header">
        <div class="sh-inv-preview-brand">
            <?php if ($logo !== ''): ?>
            <img src="<?= htmlspecialchars($logo, ENT_QUOTES) ?>" alt="" class="sh-inv-logo" width="120" height="48">
            <?php endif; ?>
            <strong class="sh-inv-preview-title"><?= htmlspecialchars($seller['name'] ?? ($labels['title'] ?? 'Invoice'), ENT_QUOTES) ?></strong>
        </div>
        <div class="sh-inv-preview-meta">
            <div><strong><?= htmlspecialchars($labels['invoice_no'] ?? 'No.') ?>:</strong> <?= htmlspecialchars($doc['invoice_no'] ?? '', ENT_QUOTES) ?></div>
            <div><strong><?= htmlspecialchars($labels['invoice_date'] ?? 'Date') ?>:</strong> <?= htmlspecialchars($doc['invoice_date'] ?? '', ENT_QUOTES) ?></div>
            <div><strong><?= htmlspecialchars($labels['due_date'] ?? 'Due') ?>:</strong> <?= htmlspecialchars($doc['due_date'] ?? '', ENT_QUOTES) ?></div>
        </div>
    </div>
    <div class="sh-inv-grid sh-inv-grid--2 sh-inv-preview-parties">
        <div class="sh-inv-preview-party sh-inv-preview-seller">
            <strong><?= htmlspecialchars($labels['seller'] ?? 'Seller', ENT_QUOTES) ?></strong>
            <?php foreach (['name','org_nr','vat_nr','address','city','postal','country','email','phone','bank','iban','bic'] as $f): ?>
                <?php if (!empty($seller[$f])): ?><div><?= htmlspecialchars((string) $seller[$f], ENT_QUOTES) ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="sh-inv-preview-party sh-inv-preview-buyer">
            <strong><?= htmlspecialchars($labels['buyer'] ?? 'Buyer', ENT_QUOTES) ?></strong>
            <?php foreach (['name','org_nr','vat_nr','address','city','postal','country','email','phone'] as $f): ?>
                <?php if (!empty($buyer[$f])): ?><div><?= htmlspecialchars((string) $buyer[$f], ENT_QUOTES) ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <table class="sh-inv-preview-table">
        <thead>
            <tr>
                <th><?= htmlspecialchars($labels['line_desc'] ?? 'Description', ENT_QUOTES) ?></th>
                <th><?= htmlspecialchars($labels['line_qty'] ?? 'Qty', ENT_QUOTES) ?></th>
                <th><?= htmlspecialchars($labels['line_price'] ?? 'Price', ENT_QUOTES) ?></th>
                <th><?= htmlspecialchars($labels['line_total'] ?? 'Total', ENT_QUOTES) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $line):
                $qty = max(1, (int) ($line['qty'] ?? 1));
                $unit = (int) ($line['unit_price'] ?? $line['price'] ?? 0);
                $sum = (int) ($line['subtotal'] ?? ($unit * $qty));
            ?>
            <tr>
                <td><?= htmlspecialchars((string) ($line['desc'] ?? $line['name'] ?? ''), ENT_QUOTES) ?></td>
                <td><?= (int) $qty ?></td>
                <td><?= htmlspecialchars(sh_format_price($unit, $settings), ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars(sh_format_price($sum, $settings), ENT_QUOTES) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="sh-inv-totals">
        <div><span><?= htmlspecialchars($labels['subtotal'] ?? 'Subtotal', ENT_QUOTES) ?></span><span><?= htmlspecialchars(sh_format_price((int) ($totals['net'] ?? $totals['subtotal'] ?? 0), $settings), ENT_QUOTES) ?></span></div>
        <?php if (!empty($totals['tax']) && (float) ($totals['tax_rate'] ?? 0) > 0): ?>
        <div class="sh-inv-sum-tax-row">
            <span><?= htmlspecialchars(($totals['tax_label'] ?? ($labels['tax'] ?? 'Tax')) . ' (' . ($totals['tax_rate'] ?? 0) . '%)', ENT_QUOTES) ?></span>
            <span><?= htmlspecialchars(sh_format_price((int) ($totals['tax'] ?? 0), $settings), ENT_QUOTES) ?></span>
        </div>
        <?php endif; ?>
        <div class="sh-inv-total-final"><span><?= htmlspecialchars($labels['total'] ?? 'Total', ENT_QUOTES) ?></span><span><?= htmlspecialchars(sh_format_price((int) ($totals['total'] ?? 0), $settings), ENT_QUOTES) ?></span></div>
    </div>
    <?php
    $payParts = [];
    if (!empty($seller['iban']) || !empty($seller['bank'])) {
        $pay = ($labels['payment'] ?? 'Payment') . ': ';
        if (!empty($seller['bank'])) {
            $pay .= $seller['bank'] . ' ';
        }
        if (!empty($seller['iban'])) {
            $pay .= 'IBAN ' . $seller['iban'];
        }
        $payParts[] = htmlspecialchars($pay, ENT_QUOTES);
    }
    if (!empty($doc['payment_purpose'])) {
        $payParts[] = '<strong>' . htmlspecialchars($labels['payment_purpose'] ?? 'Reference', ENT_QUOTES) . ':</strong> '
            . htmlspecialchars((string) $doc['payment_purpose'], ENT_QUOTES);
    }
    if ($payParts !== []): ?>
    <div class="sh-inv-preview-payment"><?= implode('<br>', $payParts) ?></div>
    <?php endif; ?>
    <div class="sh-inv-preview-footer">
        <?php if (!empty($doc['notes'])): ?>
        <p class="sh-inv-preview-notes"><?= htmlspecialchars((string) $doc['notes'], ENT_QUOTES) ?></p>
        <?php endif; ?>
    </div>
    <?php
    echo $wrap ? '</article>' : '</div>';
}

/** @return array<string, string> */
function sh_invoice_labels(?string $lang = null, array $extra = []): array
{
    $lang = strtolower((string) ($lang ?? 'en'));
    $keys = [
        'title', 'invoice_no', 'invoice_date', 'due_date', 'seller', 'buyer',
        'line_desc', 'line_qty', 'line_price', 'line_total', 'subtotal', 'tax', 'total',
        'payment', 'payment_purpose', 'print', 'save_pdf',
    ];
    $out = [];
    foreach ($keys as $key) {
        $out[$key] = sh_invoice_label($key, $lang, $extra);
    }
    return $out;
}