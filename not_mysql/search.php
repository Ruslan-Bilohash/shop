<?php
require_once __DIR__ . '/init.php';
$current_page = 'search';
$search_params = sh_search_params();
$results = sh_filter_products($search_params, $lang);
$category_record = null;
if ($search_params['category'] !== '') {
    require_once __DIR__ . '/includes/category-storage.php';
    $category_record = sh_category_by_slug($search_params['category'], true);
}

$is_category_landing = $category_record !== null && $search_params['q'] === '';
$seo_settings = sh_seo_settings();

if ($is_category_landing) {
    $page_title = sh_category_meta_title($category_record, $lang);
    $page_desc = sh_category_meta_description($category_record, $lang);
    $seo_noindex = false;
} else {
    $page_title = $t['search_page']['title'] . ($search_params['q'] ? ' — ' . $search_params['q'] : '');
    $page_desc = sprintf($t['search_page']['found'], count($results)) . '. ' . $t['meta']['description'];
    $seo_noindex = $search_params['q'] !== '' || $search_params['sale'] !== '' || $search_params['min_price'] > 0 || $search_params['max_price'] > 0;
}

$canonical = $site_url . '/search.php?' . http_build_query(array_filter($search_params));
$canon_abs = sh_absolute_url($canonical);
$seo_schemas = [sh_seo_webpage($canon_abs, $page_title, $page_desc)];

if ($is_category_landing && sh_category_schema_enabled($category_record, 'collection', true)) {
    $seo_schemas[] = sh_seo_collection_page($canon_abs, $page_title, $page_desc);
}
if (sh_seo_flag($seo_settings, 'seo_schema_itemlist', true)
    && (!$is_category_landing || sh_category_schema_enabled($category_record, 'itemlist', true))) {
    $seo_schemas[] = sh_seo_item_list($results, $lang, $canon_abs);
}
if (sh_seo_flag($seo_settings, 'seo_schema_breadcrumbs', true)
    && (!$is_category_landing || sh_category_schema_enabled($category_record, 'breadcrumb', true))) {
    $crumb_name = $is_category_landing
        ? sh_localized($category_record, 'name', $lang)
        : $t['search_page']['title'];
    $seo_schemas[] = sh_seo_breadcrumbs([
        ['name' => $t['breadcrumb_home'], 'url' => sh_absolute_url(sh_url('index.php'))],
        ['name' => $crumb_name, 'url' => $canon_abs],
    ]);
}
$category_intro = $is_category_landing ? sh_category_intro($category_record, $lang) : '';
require __DIR__ . '/includes/header.php';
?>

<section class="sh-hero sh-hero-compact">
    <div class="sh-hero-inner">
        <?php require __DIR__ . '/includes/search-form.php'; ?>
    </div>
</section>

<div class="sh-container">
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= sh_url('index.php') ?>"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
        <span>/</span>
        <?php if ($is_category_landing && $category_record): ?>
        <span><?= htmlspecialchars(sh_localized($category_record, 'name', $lang)) ?></span>
        <?php else: ?>
        <span><?= htmlspecialchars($t['search_page']['title']) ?></span>
        <?php endif; ?>
    </nav>
    <div class="sh-search-layout">
        <aside class="sh-filters">
            <h3><?= htmlspecialchars($t['search_page']['filter']) ?></h3>
            <form method="get" action="<?= sh_url('search.php') ?>">
                <input type="hidden" name="q" value="<?= htmlspecialchars($search_params['q']) ?>">
                <div class="sh-filter-group">
                    <label><?= htmlspecialchars($t['search_page']['category']) ?></label>
                    <select name="category" onchange="this.form.submit()">
                        <option value=""><?= htmlspecialchars($t['search_page']['all']) ?></option>
                        <?php foreach (sh_categories() as $cat): ?>
                        <option value="<?= $cat ?>" <?= $search_params['category'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars(sh_category_label($cat, $lang)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sh-filter-group">
                    <label><?= htmlspecialchars($t['search_page']['sale_only']) ?></label>
                    <select name="sale" onchange="this.form.submit()">
                        <option value=""><?= htmlspecialchars($t['search_page']['all']) ?></option>
                        <option value="1" <?= $search_params['sale'] === '1' ? 'selected' : '' ?>><?= htmlspecialchars($t['card']['sale']) ?></option>
                    </select>
                </div>
                <div class="sh-filter-group">
                    <label><?= htmlspecialchars($t['search_page']['sort']) ?></label>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="featured" <?= $search_params['sort'] === 'featured' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_featured']) ?></option>
                        <option value="price_low" <?= $search_params['sort'] === 'price_low' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_price_l']) ?></option>
                        <option value="price_high" <?= $search_params['sort'] === 'price_high' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_price_h']) ?></option>
                        <option value="name" <?= $search_params['sort'] === 'name' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_name']) ?></option>
                        <option value="newest" <?= $search_params['sort'] === 'newest' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_new']) ?></option>
                    </select>
                </div>
                <div class="sh-filter-group">
                    <label><?= htmlspecialchars($t['search_page']['price']) ?> (min)</label>
                    <input type="number" name="min_price" value="<?= (int)$search_params['min_price'] ?>" min="0" step="50" placeholder="0">
                </div>
                <div class="sh-filter-group">
                    <label><?= htmlspecialchars($t['search_page']['price']) ?> (max)</label>
                    <input type="number" name="max_price" value="<?= (int)$search_params['max_price'] ?>" min="0" step="50" placeholder="100000">
                </div>
                <button type="submit" class="sh-btn-primary" style="width:100%"><?= htmlspecialchars($t['search']['search_btn']) ?></button>
            </form>
        </aside>

        <div>
            <?php if ($category_intro !== ''): ?>
            <p class="sh-category-intro"><?= nl2br(htmlspecialchars($category_intro)) ?></p>
            <?php endif; ?>
            <div class="sh-results-header">
                <h1><?= $is_category_landing ? htmlspecialchars(sh_localized($category_record, 'name', $lang)) : sprintf(htmlspecialchars($t['search_page']['found']), count($results)) ?></h1>
                <?php if ($search_params['q']): ?>
                <span class="sh-results-meta"><i class="fas fa-search"></i> <?= htmlspecialchars($search_params['q']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($results)): ?>
            <div class="sh-form-card sh-empty-state">
                <i class="fas fa-store"></i>
                <p><?= htmlspecialchars($t['search_page']['no_results']) ?></p>
                <a href="<?= sh_url('index.php') ?>" class="sh-btn-primary"><?= htmlspecialchars($t['breadcrumb_home']) ?></a>
            </div>
            <?php else: ?>
            <div class="sh-product-grid sh-product-grid-list">
                <?php foreach ($results as $product):
                    require __DIR__ . '/includes/product-card.php';
                endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>