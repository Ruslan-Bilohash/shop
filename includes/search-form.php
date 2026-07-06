<?php
$p = $search_params ?? sh_search_params();
?>
<form class="sh-search" action="<?= sh_url('search.php') ?>" method="get" role="search">
    <div class="sh-search-field sh-search-field--keyword">
        <label for="q"><?= htmlspecialchars($t['search']['keyword']) ?></label>
        <input type="search" id="q" name="q" value="<?= htmlspecialchars($p['q']) ?>"
               placeholder="<?= htmlspecialchars($t['search']['placeholder']) ?>" autocomplete="off">
    </div>
    <div class="sh-search-field">
        <label for="category"><?= htmlspecialchars($t['search']['category']) ?></label>
        <select id="category" name="category">
            <option value=""><?= htmlspecialchars($t['search']['all_cats']) ?></option>
            <?php foreach (sh_categories() as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $p['category'] === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars(sh_category_label($cat, $lang)) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="sh-search-field">
        <label for="sort"><?= htmlspecialchars($t['search_page']['sort']) ?></label>
        <select id="sort" name="sort">
            <option value="featured" <?= $p['sort'] === 'featured' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_featured']) ?></option>
            <option value="price_low" <?= $p['sort'] === 'price_low' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_price_l']) ?></option>
            <option value="price_high" <?= $p['sort'] === 'price_high' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_price_h']) ?></option>
            <option value="name" <?= $p['sort'] === 'name' ? 'selected' : '' ?>><?= htmlspecialchars($t['search_page']['sort_name']) ?></option>
        </select>
    </div>
    <div class="sh-search-btn-wrap">
        <button type="submit" class="sh-search-btn"><i class="fas fa-search"></i> <?= htmlspecialchars($t['search']['search_btn']) ?></button>
    </div>
</form>