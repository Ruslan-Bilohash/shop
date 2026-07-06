<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/orders-storage.php';
require_once __DIR__ . '/includes/invoice-render.php';
require_once __DIR__ . '/includes/payment-settings.php';

$id = trim($_GET['id'] ?? '');
$token = trim($_GET['token'] ?? '');
$order = sh_order_by_id($id);

if ($order === null || $token === '' || !hash_equals((string) ($order['access_token'] ?? ''), $token)) {
    http_response_code(404);
    $page_title = '404 — ' . ($t['meta']['site_name'] ?? 'Shop');
    require __DIR__ . '/includes/header.php';
    echo '<div class="sh-container"><h1>404</h1><p><a href="' . htmlspecialchars(sh_url('index.php')) . '">' . htmlspecialchars($t['breadcrumb_home'] ?? 'Home') . '</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$settings = sh_load_settings();
$doc = sh_order_to_invoice_doc($order);
$labels = sh_invoice_labels($order['lang'] ?? $lang ?? 'en', $t['invoice'] ?? []);
$printDesign = $doc['print_design'] ?? 'classic-blue';
$printFormat = $doc['print_format'] ?? 'a4';
$printMargin = $order['print_margin'] ?? ($settings['invoice_print_margin'] ?? '8mm');
$embed = !empty($_GET['embed']);

$page_title = ($order['invoice_no'] ?? 'Invoice') . ' — ' . ($t['meta']['site_name'] ?? 'Shop');
$seo_noindex = true;
$extra_css = [
    sh_asset('css/invoice-print.css') . '?v=1',
];
$body_class = sh_inv_print_classes($printDesign, $printFormat);

require __DIR__ . '/includes/header.php';
?>

<div class="sh-inv-page sh-inv-no-print">
    <div class="sh-inv-actions">
        <a href="<?= htmlspecialchars(sh_url('index.php')) ?>" class="sh-btn-outline"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($t['breadcrumb_home'] ?? 'Home') ?></a>
        <button type="button" class="sh-btn-primary" id="sh-inv-print-btn"><i class="fas fa-print"></i> <?= htmlspecialchars($labels['print'] ?? 'Print / Save PDF') ?></button>
        <span class="sh-inv-badge"><?= htmlspecialchars(sh_inv_print_design_name($printDesign, $lang)) ?> · <?= htmlspecialchars(sh_inv_print_format_name($printFormat, $lang)) ?></span>
    </div>
</div>

<div class="sh-inv-page">
<?php sh_render_invoice_article($doc, $labels, $printDesign, $printFormat, true, $settings); ?>
</div>

<script>
(function () {
    var btn = document.getElementById('sh-inv-print-btn');
    if (!btn) return;
    var fmt = <?= json_encode($printFormat) ?>;
    var margin = <?= json_encode($printMargin) ?>;
    var formats = <?= json_encode(sh_inv_print_formats()) ?>;
    btn.addEventListener('click', function () {
        var pageCss = (formats[fmt] && formats[fmt].css) ? formats[fmt].css : 'A4';
        var s = document.getElementById('sh-inv-dynamic-page');
        if (!s) {
            s = document.createElement('style');
            s.id = 'sh-inv-dynamic-page';
            document.head.appendChild(s);
        }
        s.textContent = '@page { size: ' + pageCss + '; margin: ' + margin + '; }';
        window.print();
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>