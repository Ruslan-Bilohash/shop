<?php

require_once __DIR__ . '/homepage-blocks.php';

function sh_render_homepage_block(array $block): void
{
    global $t, $lang;
    $type = (string) ($block['type'] ?? '');
    $limit = max(1, (int) ($block['limit'] ?? 6));

    switch ($type) {
        case 'about':
            ?>
<section class="sh-about-script">
    <div class="sh-container sh-about-script-inner">
        <div class="sh-about-script-text">
            <h2><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['about_script']['title'] ?? '')) ?></h2>
            <p><?= htmlspecialchars(sh_home_block_label($block, 'subtitle', $lang, $t['about_script']['text'] ?? '')) ?></p>
            <?php $sh_vdefs = sh_vertical_defs(); if ($sh_vdefs !== []): ?>
            <p class="sh-about-use-label"><?= htmlspecialchars($t['about_script']['use_label'] ?? '') ?></p>
            <div class="sh-about-usecases">
                <?php foreach ($sh_vdefs as $vslug => $vdef): ?>
                <a href="<?= htmlspecialchars(sh_vertical_url($vslug)) ?>" class="sh-about-usecase"><?= htmlspecialchars($vdef[$lang] ?? $vdef['en']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <ul class="sh-about-features">
                <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($t['about_script']['f1']) ?></li>
                <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($t['about_script']['f2']) ?></li>
                <li><i class="fas fa-check-circle"></i> <?= htmlspecialchars($t['about_script']['f3']) ?></li>
            </ul>
            <div class="sh-about-actions">
                <a href="<?= sh_url('index.php') ?>" class="sh-btn-primary"><i class="fas fa-play-circle"></i> <?= htmlspecialchars($t['about_script']['demo_btn']) ?></a>
                <a href="<?= sh_url('admin/login.php') ?>" class="sh-btn-outline-dark"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($t['about_script']['admin_btn']) ?></a>
            </div>
            <p class="sh-about-creds"><i class="fas fa-key"></i> <?= htmlspecialchars($t['about_script']['creds']) ?></p>
        </div>
        <div class="sh-about-script-visual">
            <div class="sh-about-mock">
                <div class="sh-mock-header"><span></span><span></span><span></span></div>
                <div class="sh-mock-body">
                    <div class="sh-mock-product"></div>
                    <div class="sh-mock-product"></div>
                    <div class="sh-mock-product"></div>
                </div>
            </div>
        </div>
    </div>
</section>
            <?php
            break;

        case 'stats':
            $stats = sh_platform_stats();
            ?>
<section class="sh-stats-strip">
    <div class="sh-container sh-stats-grid">
        <div class="sh-stat-item"><i class="fas fa-box"></i><strong><?= (int)$stats['products'] ?></strong><span><?= htmlspecialchars($t['home']['stats_products']) ?></span></div>
        <div class="sh-stat-item"><i class="fas fa-star"></i><strong><?= (int)$stats['featured'] ?></strong><span><?= htmlspecialchars($t['home']['stats_featured']) ?></span></div>
        <div class="sh-stat-item"><i class="fas fa-layer-group"></i><strong><?= (int)$stats['categories'] ?></strong><span><?= htmlspecialchars($t['home']['stats_cats']) ?></span></div>
        <div class="sh-stat-item"><i class="fas fa-shopping-cart"></i><strong><?= (int)$stats['cart_items'] ?></strong><span><?= htmlspecialchars($t['home']['stats_cart']) ?></span></div>
    </div>
</section>
            <?php
            break;

        case 'featured':
            require_once __DIR__ . '/product-3d-lib.php';
            $featured = sh_featured_products($limit);
            $featured_3d = sh_homepage_3d_products();
            $ids3d = array_flip(array_map(static fn($p) => (string) ($p['id'] ?? ''), $featured_3d));
            $featured_rest = array_values(array_filter(
                $featured,
                static fn($p) => !isset($ids3d[(string) ($p['id'] ?? '')])
            ));
            ?>
<div class="sh-container">
    <div class="sh-section-head">
        <div>
            <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['home']['featured'])) ?></h2>
            <p class="sh-section-sub"><?= htmlspecialchars(sh_home_block_label($block, 'subtitle', $lang, $t['home']['featured_sub'])) ?></p>
        </div>
        <a href="<?= sh_url('search.php') ?>" class="sh-link-more"><?= htmlspecialchars($t['home']['view_all']) ?> →</a>
    </div>
    <?php if ($featured_3d !== []): ?>
    <div class="sh-product-3d-grid" aria-label="<?= htmlspecialchars($t['home']['featured_3d'] ?? '3D featured products') ?>">
        <?php foreach ($featured_3d as $product): require __DIR__ . '/product-3d-card.php'; endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($featured_rest !== []): ?>
    <div class="sh-product-grid sh-product-grid--after-3d">
        <?php foreach ($featured_rest as $product): require __DIR__ . '/product-card.php'; endforeach; ?>
    </div>
    <?php endif; ?>
</div>
            <?php
            break;

        case 'categories':
            require_once __DIR__ . '/category-storage.php';
            $cat_counts = sh_category_counts();
            $cat_records = sh_category_records(true);
            $cat_visible_limit = max(1, min(24, (int) ($block['limit'] ?? 6)));
            $cat_visible = array_slice($cat_records, 0, $cat_visible_limit);
            $cat_hidden = array_slice($cat_records, $cat_visible_limit);
            $cat_more_n = count($cat_hidden);
            $cat_more_id = 'shCatMore-' . preg_replace('/[^a-z0-9_-]/', '', (string) ($block['id'] ?? 'categories'));
            $cat_render_card = static function (array $catRec) use ($cat_counts, $lang, $t): void {
                $slug = (string) ($catRec['slug'] ?? '');
                if ($slug === '') {
                    return;
                }
                $icon = trim((string) ($catRec['icon'] ?? ''));
                if ($icon === '') {
                    $icon = sh_category_icon($slug);
                }
                ?>
        <a href="<?= htmlspecialchars(sh_url('search.php?category=' . urlencode($slug))) ?>" class="sh-cat-card">
            <span class="sh-cat-card-icon" aria-hidden="true"><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i></span>
            <strong><?= htmlspecialchars(sh_category_label($slug, $lang)) ?></strong>
            <span><?= (int)($cat_counts[$slug] ?? 0) ?> <?= htmlspecialchars($t['home']['products']) ?></span>
        </a>
                <?php
            };
            ?>
<div class="sh-container sh-section-tight sh-cat-section">
    <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['home']['categories'])) ?></h2>
    <div class="sh-cat-grid">
        <?php foreach ($cat_visible as $catRec) { $cat_render_card($catRec); } ?>
    </div>
    <?php if ($cat_more_n > 0): ?>
    <div
        class="sh-cat-grid sh-cat-more"
        id="<?= htmlspecialchars($cat_more_id) ?>"
        data-cat-more-list
        hidden
        aria-hidden="true"
    >
        <?php foreach ($cat_hidden as $catRec) { $cat_render_card($catRec); } ?>
    </div>
    <div class="sh-cat-more-actions">
        <button
            type="button"
            class="sh-cat-more-btn"
            data-cat-more-btn
            aria-expanded="false"
            aria-controls="<?= htmlspecialchars($cat_more_id) ?>"
            data-label-more="<?= htmlspecialchars(sprintf($t['home']['categories_show_more'] ?? 'Show more (%d)', $cat_more_n)) ?>"
            data-label-less="<?= htmlspecialchars($t['home']['categories_show_less'] ?? 'Show less') ?>"
        ><?= htmlspecialchars(sprintf($t['home']['categories_show_more'] ?? 'Show more (%d)', $cat_more_n)) ?></button>
    </div>
    <?php endif; ?>
</div>
            <?php
            break;

        case 'new':
            $new_arrivals = sh_new_arrivals($limit);
            ?>
<div class="sh-container sh-section-tight">
    <div class="sh-section-head">
        <div>
            <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['home']['new'])) ?></h2>
            <p class="sh-section-sub"><?= htmlspecialchars(sh_home_block_label($block, 'subtitle', $lang, $t['home']['new_sub'])) ?></p>
        </div>
        <a href="<?= sh_url('search.php?sort=newest') ?>" class="sh-link-more"><?= htmlspecialchars($t['home']['view_all']) ?> →</a>
    </div>
    <div class="sh-product-grid">
        <?php foreach ($new_arrivals as $product): require __DIR__ . '/product-card.php'; endforeach; ?>
    </div>
</div>
            <?php
            break;

        case 'platform':
            require __DIR__ . '/platform-features.php';
            break;

        case 'steps':
            ?>
<div class="sh-container sh-section-tight">
    <div class="sh-section-head sh-section-head-center">
        <div>
            <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['how_it_works']['title'])) ?></h2>
            <p class="sh-section-sub"><?= htmlspecialchars(sh_home_block_label($block, 'subtitle', $lang, $t['how_it_works']['subtitle'])) ?></p>
        </div>
    </div>
    <div class="sh-steps-grid">
        <?php foreach ($t['how_it_works']['steps'] as $i => $step): ?>
        <article class="sh-step-card">
            <div class="sh-step-num"><?= $i + 1 ?></div>
            <div class="sh-step-icon"><i class="fas fa-<?= htmlspecialchars($step['icon']) ?>"></i></div>
            <h3><?= htmlspecialchars($step['title']) ?></h3>
            <p><?= htmlspecialchars($step['desc']) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
</div>
            <?php
            break;

        case 'why':
            ?>
<div class="sh-container sh-section-tight">
    <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['home']['why'])) ?></h2>
    <div class="sh-why-grid">
        <div class="sh-why-item"><i class="fas fa-cart-shopping"></i><h4><?= htmlspecialchars($t['home']['why_1_t']) ?></h4><p><?= htmlspecialchars($t['home']['why_1_d']) ?></p></div>
        <div class="sh-why-item"><i class="fas fa-shield-halved"></i><h4><?= htmlspecialchars($t['home']['why_2_t']) ?></h4><p><?= htmlspecialchars($t['home']['why_2_d']) ?></p></div>
        <div class="sh-why-item"><i class="fas fa-globe-europe"></i><h4><?= htmlspecialchars($t['home']['why_3_t']) ?></h4><p><?= htmlspecialchars($t['home']['why_3_d']) ?></p></div>
    </div>
</div>
            <?php
            break;

        case 'faq':
            ?>
<div class="sh-container sh-section-tight sh-faq-section sh-section-last">
    <h2 class="sh-section-title"><?= htmlspecialchars(sh_home_block_label($block, 'title', $lang, $t['faq']['title'])) ?></h2>
    <div class="sh-faq-list">
        <?php foreach ($t['faq']['items'] as $i => $item): ?>
        <details class="sh-faq-item"<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= htmlspecialchars($item['q']) ?></summary>
            <p><?= htmlspecialchars($item['a']) ?></p>
        </details>
        <?php endforeach; ?>
    </div>
</div>
            <?php
            break;

        case 'custom':
            $title = sh_home_block_label($block, 'title', $lang, '');
            $subtitle = sh_home_block_label($block, 'subtitle', $lang, '');
            $body = sh_home_block_label($block, 'body', $lang, '');
            if ($title === '' && $subtitle === '' && $body === '') {
                break;
            }
            echo '<div class="sh-container sh-section-tight sh-home-custom-block">';
            if ($title !== '') {
                echo '<h2 class="sh-section-title">' . htmlspecialchars($title) . '</h2>';
            }
            if ($subtitle !== '') {
                echo '<p class="sh-section-sub">' . htmlspecialchars($subtitle) . '</p>';
            }
            if ($body !== '') {
                echo $body;
            }
            echo '</div>';
            break;
    }
}

function sh_render_homepage_blocks(?array $settings = null): void
{
    $settings ??= function_exists('sh_site_settings') ? sh_site_settings() : [];
    foreach (sh_home_blocks_sorted_active($settings) as $block) {
        sh_render_homepage_block($block);
    }
}