<?php
/** @var array $product */
/** @var array $t */
/** @var string $lang */
require_once __DIR__ . '/store-settings.php';
require_once __DIR__ . '/admin-storefront.php';
require_once __DIR__ . '/product-3d-lib.php';

$GLOBALS['sh_product_3d_enabled'] = true;

$card = sh_card_settings();
$detail_url = sh_product_url((string) ($product['id'] ?? ''));
$cart_url   = sh_url('cart.php?action=add&id=' . urlencode((string) ($product['id'] ?? '')));
$name       = sh_localized($product, 'name', $lang);
$desc       = sh_localized($product, 'desc', $lang);
$cat        = $product['category'] ?? '';
$cat_url    = $cat !== '' ? sh_url('search.php?category=' . urlencode($cat)) : sh_url('search.php');
$price      = sh_product_price($product);
$original   = sh_product_original_price($product);
$on_sale    = sh_product_on_sale($product);
$in_stock   = (int) ($product['stock'] ?? 0) > 0;
$discount   = $on_sale && $original > 0 ? (int) round((1 - $price / $original) * 100) : 0;
$excerptLen = (int) ($card['excerpt_len'] ?? 85);
$cfg3d      = sh_product_3d_config($product);
$poster     = sh_homepage_3d_poster($product);
$hint       = $t['card']['view_3d_hint'] ?? 'Drag to rotate · Scroll to zoom';
?>
<article class="sh-product-card sh-product-card--3d<?= $in_stock ? '' : ' is-out-of-stock' ?>">
    <?php sh_render_admin_product_edit_link((string) ($product['id'] ?? '')); ?>
    <div class="sh-product-media sh-product-media--3d">
        <div
            class="sh-product-3d-viewer"
            data-sh-product-3d
            data-preset="<?= htmlspecialchars($cfg3d['preset']) ?>"
            data-color="<?= htmlspecialchars($cfg3d['color']) ?>"
            <?php if (!empty($cfg3d['model'])): ?>data-model="<?= htmlspecialchars($cfg3d['model']) ?>"<?php endif; ?>
            aria-label="<?= htmlspecialchars($name) ?>"
            role="img"
        >
            <?php if ($poster !== ''): ?>
            <img class="sh-product-3d-poster" src="<?= htmlspecialchars($poster) ?>" alt="" loading="lazy" decoding="async">
            <?php else: ?>
            <div class="sh-product-3d-poster sh-product-3d-poster--placeholder" aria-hidden="true">
                <i class="fas fa-cube"></i>
            </div>
            <?php endif; ?>
        </div>
        <span class="sh-product-3d-hint"><i class="fas fa-arrows-alt" aria-hidden="true"></i> <?= htmlspecialchars($hint) ?></span>
        <div class="sh-product-badges">
            <?php if ($card['show_sale_badge'] && $on_sale): ?>
            <span class="sh-badge sh-badge-sale"><?= htmlspecialchars($t['card']['sale']) ?><?= $discount > 0 ? ' −' . $discount . '%' : '' ?></span>
            <?php endif; ?>
            <?php if ($card['show_featured'] && !empty($product['featured'])): ?>
            <span class="sh-badge sh-badge-featured" title="<?= htmlspecialchars($t['card']['featured']) ?>"><i class="fas fa-star" aria-hidden="true"></i></span>
            <?php endif; ?>
            <span class="sh-badge sh-badge-3d" title="3D">3D</span>
        </div>
    </div>
    <div class="sh-product-content">
        <div class="sh-product-meta">
            <?php if ($card['show_category']): ?>
            <a href="<?= htmlspecialchars($cat_url) ?>" class="sh-cat-tag sh-cat-tag-link"><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></a>
            <?php endif; ?>
            <?php if ($card['show_stock']): ?>
            <span class="sh-stock-pill <?= $in_stock ? 'in' : 'out' ?>">
                <i class="fas fa-circle" aria-hidden="true"></i>
                <?= htmlspecialchars($in_stock ? $t['card']['in_stock'] : $t['card']['out_stock']) ?>
            </span>
            <?php endif; ?>
        </div>
        <h3 class="sh-product-title">
            <a href="<?= htmlspecialchars($detail_url) ?>"><?= htmlspecialchars($name) ?></a>
        </h3>
        <?php if ($card['show_excerpt']): ?>
        <p class="sh-product-excerpt"><?= htmlspecialchars(bh_str_sub($desc, 0, $excerptLen)) ?>…</p>
        <?php endif; ?>
        <div class="sh-product-bottom">
            <div class="sh-price-wrap">
                <span class="sh-price-current"><?= sh_price($price) ?></span>
                <?php if ($on_sale): ?>
                <span class="sh-price-old"><?= sh_price($original) ?></span>
                <?php endif; ?>
                <?php
                if (!function_exists('sh_tax_price_suffix')) {
                    require_once __DIR__ . '/tax-settings.php';
                }
                $taxNote = sh_tax_price_suffix(null, $lang ?? null);
                if ($taxNote !== ''): ?>
                <span class="sh-tax-price-note"><?= htmlspecialchars($taxNote) ?></span>
                <?php endif; ?>
            </div>
            <div class="sh-product-actions">
                <?php if ($card['show_add_cart'] && $in_stock): ?>
                <a href="<?= htmlspecialchars($cart_url) ?>" class="sh-btn-card sh-btn-card-primary">
                    <i class="fas fa-cart-plus" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($t['card']['add_cart']) ?></span>
                </a>
                <?php elseif (!$in_stock): ?>
                <span class="sh-btn-card sh-btn-card-muted">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($t['card']['out_stock']) ?></span>
                </span>
                <?php endif; ?>
                <?php if ($card['show_view_btn']): ?>
                <a href="<?= htmlspecialchars($detail_url) ?>" class="sh-btn-card sh-btn-card-icon" title="<?= htmlspecialchars($t['card']['view_product']) ?>" aria-label="<?= htmlspecialchars($t['card']['view_product']) ?>">
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>