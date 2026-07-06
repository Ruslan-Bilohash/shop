<?php
/**
 * Cart / checkout order totals with optional tax breakdown.
 * Expects: $t (translations), optional $settings.
 */
require_once __DIR__ . '/tax-settings.php';

if (!isset($settings) || !is_array($settings)) {
    $settings = function_exists('sh_load_settings') ? sh_load_settings() : [];
}

$cart_subtotal = sh_cart_total();
$tax_bd = sh_tax_breakdown($cart_subtotal, $settings, $lang ?? null);
$show_breakdown = $tax_bd['enabled'] && !empty(sh_tax_merge_settings($settings)['tax_show_breakdown']);
?>
<div class="sh-order-totals">
    <?php if ($show_breakdown): ?>
    <div class="sh-cart-total-row sh-cart-total-row--sub">
        <span><?= htmlspecialchars($t['cart']['subtotal_net'] ?? $t['cart']['subtotal'] ?? 'Subtotal') ?></span>
        <strong><?= sh_price($tax_bd['net']) ?></strong>
    </div>
    <div class="sh-cart-total-row sh-cart-total-row--tax">
        <?php
        $rateStr = rtrim(rtrim(number_format($tax_bd['rate'], 2, '.', ''), '0'), '.');
        $taxLine = sprintf($t['tax']['line'] ?? '%s (%s%%)', $tax_bd['label'], $rateStr);
        ?>
        <span><?= htmlspecialchars($taxLine) ?></span>
        <strong><?= sh_price($tax_bd['tax']) ?></strong>
    </div>
    <?php endif; ?>
    <div class="sh-cart-total-row sh-cart-total-row--grand">
        <span><?= htmlspecialchars($t['cart']['total']) ?></span>
        <strong><?= sh_price($tax_bd['total']) ?></strong>
    </div>
    <?php if ($tax_bd['enabled'] && trim((string) (sh_tax_merge_settings($settings)['tax_business_id'] ?? '')) !== ''): ?>
    <p class="sh-tax-business-id"><?= htmlspecialchars(sprintf($t['tax']['business_id'] ?? '%s: %s', sh_tax_country_catalog()[$tax_bd['country']]['business_label'] ?? 'ID', sh_tax_merge_settings($settings)['tax_business_id'])) ?></p>
    <?php endif; ?>
</div>