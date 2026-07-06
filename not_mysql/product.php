<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/store-settings.php';
$sh_quick_buy = sh_quick_buy_enabled();

$id = trim($_GET['id'] ?? '');
$product = $id !== '' ? sh_product_by_id($id) : null;

if (!$product) {
    http_response_code(404);
    $page_title = '404';
    $page_desc  = $t['meta']['description'];
    $canonical  = $site_url . '/product.php';
    require __DIR__ . '/includes/header.php';
    echo '<div class="sh-container"><div class="sh-form-card sh-empty-state"><i class="fas fa-box-open"></i><p>Product not found.</p><a href="' . htmlspecialchars(sh_url('search.php')) . '" class="sh-btn-primary">' . htmlspecialchars($t['breadcrumb_home']) . '</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$added = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
    if (sh_cart_add($id, $qty)) {
        $added = true;
    }
}

$current_page = 'product';
$name         = sh_localized($product, 'name', $lang);
$short_desc   = sh_localized($product, 'desc', $lang);
$long_desc    = sh_product_long_desc($product, $lang);
$product_images = sh_product_images($product);
$cat          = $product['category'] ?? '';
$price        = sh_product_price($product);
$original     = sh_product_original_price($product);
$on_sale      = sh_product_on_sale($product);
$in_stock     = (int)($product['stock'] ?? 0) > 0;
$related      = sh_related_products($product);
$highlights   = sh_product_highlights($product, $lang);
$canon_abs    = sh_product_canonical($product, $lang);
$canonical    = $canon_abs;
$page_title   = sh_product_meta_title($product, $lang);
$page_desc    = sh_product_meta_description($product, $lang);
$seo_keywords = sh_product_meta_keywords($product, $lang);
$seo_settings = sh_seo_settings();
$seo_schemas  = [];

if (sh_seo_flag($seo_settings, 'seo_schema_organization', true)) {
    $seo_schemas[] = sh_seo_organization();
}
if (sh_product_schema_enabled($product, 'product', true) && sh_seo_flag($seo_settings, 'seo_schema_product', true)) {
    $seo_schemas[] = sh_seo_product($product, $lang, $canon_abs);
}
$seo_schemas[] = sh_seo_webpage($canon_abs, $page_title, $page_desc);
if (sh_product_schema_enabled($product, 'breadcrumb', true) && sh_seo_flag($seo_settings, 'seo_schema_breadcrumbs', true)) {
    $seo_schemas[] = sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => sh_category_label($cat, $lang), 'url' => sh_absolute_url(sh_url('search.php?category=' . urlencode($cat)))],
        ['name' => $name, 'url' => $canon_abs],
    ]);
}
$seo_og_image = sh_product_og_image($product);
$seo_og_type  = 'product';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/admin-storefront.php';
sh_render_admin_storefront_bar($product['id'] ?? $id);
?>

<div class="sh-container sh-product-detail">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <a href="<?= sh_url('search.php?category=' . urlencode($cat)) ?>"><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></a>
        <span>/</span>
        <span><?= htmlspecialchars($name) ?></span>
    </nav>

    <?php if ($added): ?>
    <div class="sh-alert sh-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($t['product']['added']) ?> <a href="<?= sh_url('cart.php') ?>"><?= htmlspecialchars($t['nav']['cart']) ?> →</a></div>
    <?php endif; ?>

    <div class="sh-product-layout">
        <div class="sh-product-gallery">
            <div class="sh-product-gallery-main">
                <img id="shProductMainImg" src="<?= htmlspecialchars(sh_product_image($product)) ?>" alt="<?= htmlspecialchars($name) ?>" width="600" height="450" onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
                <?php if ($on_sale): ?><span class="sh-sale-badge-lg"><?= htmlspecialchars($t['card']['sale']) ?></span><?php endif; ?>
            </div>
            <?php if (count($product_images) > 1): ?>
            <div class="sh-product-gallery-thumbs" role="list" aria-label="<?= htmlspecialchars($name) ?>">
                <?php foreach ($product_images as $idx => $img): ?>
                <button type="button"
                        class="sh-product-gallery-thumb<?= $idx === 0 ? ' active' : '' ?>"
                        role="listitem"
                        data-src="<?= htmlspecialchars($img) ?>"
                        aria-label="<?= htmlspecialchars($name . ' — ' . ($idx + 1)) ?>"
                        aria-pressed="<?= $idx === 0 ? 'true' : 'false' ?>">
                    <img src="<?= htmlspecialchars($img) ?>" alt="" width="80" height="60" loading="lazy" onerror="this.onerror=null;this.src='<?= htmlspecialchars(sh_placeholder_image()) ?>';">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="sh-product-info">
            <a href="<?= sh_url('search.php?category=' . urlencode($cat)) ?>" class="sh-cat-tag sh-cat-tag-link"><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></a>
            <h1><?= htmlspecialchars($name) ?></h1>
            <div class="sh-product-price-row">
                <strong class="sh-price-lg"><?= sh_price($price) ?></strong>
                <?php if ($on_sale): ?><span class="sh-price-was-lg"><?= sh_price($original) ?></span><?php endif; ?>
            </div>
            <p class="sh-product-stock <?= $in_stock ? 'in' : 'out' ?>">
                <i class="fas fa-<?= $in_stock ? 'check' : 'times' ?>-circle"></i>
                <?= htmlspecialchars($in_stock ? $t['card']['in_stock'] : $t['card']['out_stock']) ?>
                · <?= htmlspecialchars($t['product']['stock']) ?>: <?= (int)($product['stock'] ?? 0) ?>
            </p>
            <p class="sh-product-desc-lead"><?= htmlspecialchars($short_desc) ?></p>
            <?php if ($long_desc !== '' && $long_desc !== $short_desc): ?>
            <p class="sr-only"><?= htmlspecialchars($long_desc) ?></p>
            <?php endif; ?>

            <?php if ($in_stock): ?>
            <form method="post" class="sh-add-cart-form">
                <input type="hidden" name="action" value="add">
                <label for="qty"><?= htmlspecialchars($t['product']['qty']) ?></label>
                <div class="sh-qty-row">
                    <input type="number" id="qty" name="qty" value="1" min="1" max="99">
                    <button type="submit" class="sh-btn-primary sh-btn-lg"><i class="fas fa-cart-plus"></i> <?= htmlspecialchars($t['product']['add_cart']) ?></button>
                </div>
            </form>
            <?php if ($sh_quick_buy): ?>
            <div class="sh-quick-buy" id="shQuickBuy"
                 data-api="<?= htmlspecialchars(sh_url('api/quick-buy.php')) ?>"
                 data-product-id="<?= htmlspecialchars($product['id']) ?>"
                 data-product-name="<?= htmlspecialchars($name) ?>">
                <label for="shQuickPhone"><?= htmlspecialchars($t['quick_buy']['phone_label'] ?? 'Your phone') ?></label>
                <input type="tel" id="shQuickPhone" name="phone" inputmode="tel" autocomplete="tel" placeholder="+47 400 00 000">
                <button type="button" class="sh-btn-primary sh-btn-lg sh-quick-buy-btn" id="shQuickBuyBtn" hidden>
                    <i class="fas fa-bolt"></i> <?= htmlspecialchars($t['quick_buy']['submit'] ?? 'Quick purchase') ?>
                </button>
                <p class="sh-quick-buy-msg" id="shQuickBuyMsg" hidden
                   data-ok="<?= htmlspecialchars($t['quick_buy']['success'] ?? 'Request sent! We will call you back.') ?>"></p>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <p class="sh-demo-note"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($t['product']['demo_note']) ?></p>

            <dl class="sh-specs-mini">
                <div><dt><?= htmlspecialchars($t['product']['sku']) ?></dt><dd><?= htmlspecialchars($product['sku'] ?? $product['id']) ?></dd></div>
                <div><dt><?= htmlspecialchars($t['product']['category']) ?></dt><dd><a href="<?= sh_url('search.php?category=' . urlencode($cat)) ?>" class="sh-cat-link"><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></a></dd></div>
            </dl>
        </div>
    </div>

    <div class="sh-tabs-wrap">
        <div class="sh-tabs" role="tablist">
            <button type="button" class="sh-tab active" data-tab="overview" role="tab" aria-selected="true"><?= htmlspecialchars($t['product']['tab_overview']) ?></button>
            <button type="button" class="sh-tab" data-tab="details" role="tab"><?= htmlspecialchars($t['product']['tab_details']) ?></button>
        </div>
        <div id="tab-overview" class="sh-tab-panel active">
            <p><?= nl2br(htmlspecialchars($long_desc)) ?></p>
            <?php if (!empty($highlights)): ?>
            <h3><?= htmlspecialchars($t['product']['highlights']) ?></h3>
            <ul class="sh-highlights">
                <?php foreach ($highlights as $hl): ?>
                <li><i class="fas fa-check"></i> <?= htmlspecialchars($hl) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <div id="tab-details" class="sh-tab-panel" hidden>
            <dl class="sh-specs-table">
                <div><dt><?= htmlspecialchars($t['product']['sku']) ?></dt><dd><?= htmlspecialchars($product['sku'] ?? '') ?></dd></div>
                <div><dt><?= htmlspecialchars($t['product']['category']) ?></dt><dd><a href="<?= sh_url('search.php?category=' . urlencode($cat)) ?>" class="sh-cat-link"><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></a></dd></div>
                <div><dt><?= htmlspecialchars($t['product']['price']) ?></dt><dd><?= sh_price($price) ?></dd></div>
                <div><dt><?= htmlspecialchars($t['product']['stock']) ?></dt><dd><?= (int)($product['stock'] ?? 0) ?></dd></div>
            </dl>
            <h3><?= htmlspecialchars($t['product']['shipping']) ?></h3>
            <p><?= htmlspecialchars($t['product']['shipping_note']) ?></p>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="sh-related-section">
        <h2><?= htmlspecialchars($t['product']['related']) ?></h2>
        <div class="sh-product-grid">
            <?php foreach ($related as $related_product):
                $product = $related_product;
                require __DIR__ . '/includes/product-card.php';
            endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>