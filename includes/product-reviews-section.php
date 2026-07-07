<?php
/**
 * Product reviews — Google-style stars, list + form with reCAPTCHA.
 * Expects $product, $lang, $t, sh_url().
 */
require_once __DIR__ . '/product-reviews-storage.php';

function sh_product_reviews_stars_html(float $rating, string $label = ''): string
{
    $rating = max(0.0, min(5.0, $rating));
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);
    $html = '<span class="sh-review-stars" role="img" aria-label="' . htmlspecialchars($label) . '">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<i class="fas fa-star" aria-hidden="true"></i>';
    }
    if ($half) {
        $html .= '<i class="fas fa-star-half-alt" aria-hidden="true"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<i class="far fa-star" aria-hidden="true"></i>';
    }
    $html .= '</span>';
    return $html;
}

function sh_product_review_date_label(string $iso, string $lang): string
{
    $ts = strtotime($iso);
    if ($ts === false) {
        return '';
    }
    $months = [
        'en' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'uk' => ['січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'],
        'no' => ['jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'des'],
        'ru' => ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'],
        'sv' => ['jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
    ];
    $m = $months[$lang] ?? $months['en'];
    return (int) date('j', $ts) . ' ' . ($m[(int) date('n', $ts) - 1] ?? '') . ' ' . date('Y', $ts);
}

function sh_render_product_reviews_section(array $product, string $lang): void
{
    global $t;
    $pr = $t['product']['reviews'] ?? [];
    $productId = (string) ($product['id'] ?? '');
    if ($productId === '') {
        return;
    }
    $reviews = sh_product_reviews_for_product($productId);
    $agg = sh_product_reviews_aggregate($productId);
    $recapKey = cms_recaptcha_site_key();
    $apiUrl = sh_url('api/product-review.php');
    ?>
<section class="sh-product-reviews" id="shProductReviews"
         data-api="<?= htmlspecialchars($apiUrl) ?>"
         data-product-id="<?= htmlspecialchars($productId) ?>"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-err-recaptcha="<?= htmlspecialchars($pr['err_recaptcha'] ?? 'Complete reCAPTCHA.') ?>"
         data-err-rating="<?= htmlspecialchars($pr['err_rating'] ?? 'Select a star rating.') ?>"
         data-err-author="<?= htmlspecialchars($pr['err_author'] ?? 'Enter your name.') ?>"
         data-err-body="<?= htmlspecialchars($pr['err_body'] ?? 'Write at least 10 characters.') ?>"
         data-err-generic="<?= htmlspecialchars($pr['err_generic'] ?? 'Could not submit review.') ?>"
         data-ok="<?= htmlspecialchars($pr['success'] ?? 'Thank you! Your review is published.') ?>"
         data-rate-limit="<?= htmlspecialchars($pr['err_rate_limit'] ?? 'Too many reviews — try again tomorrow.') ?>">
    <div class="sh-product-reviews-head">
        <h2><?= htmlspecialchars($pr['title'] ?? 'Customer reviews') ?></h2>
        <?php if ($agg !== null): ?>
        <div class="sh-product-reviews-summary">
            <?= sh_product_reviews_stars_html((float) $agg['rating'], sprintf($pr['stars_aria'] ?? '%s out of 5', (string) $agg['rating'])) ?>
            <strong class="sh-product-reviews-score"><?= htmlspecialchars((string) $agg['rating']) ?></strong>
            <span class="sh-product-reviews-count"><?= htmlspecialchars(sprintf($pr['count'] ?? '%d reviews', (int) $agg['count'])) ?></span>
        </div>
        <?php else: ?>
        <p class="sh-product-reviews-empty-hint"><?= htmlspecialchars($pr['empty'] ?? 'No reviews yet — be the first.') ?></p>
        <?php endif; ?>
    </div>

    <?php if ($reviews !== []): ?>
    <div class="sh-product-reviews-list">
        <?php foreach ($reviews as $review):
            $rating = (int) ($review['rating'] ?? 0);
            $starsLabel = sprintf($pr['stars_aria'] ?? '%d out of 5', $rating);
            ?>
        <article class="sh-product-review-card">
            <div class="sh-product-review-top">
                <div class="sh-product-review-author-wrap">
                    <span class="sh-product-review-avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr((string) ($review['author'] ?? '?'), 0, 1))) ?></span>
                    <div>
                        <strong class="sh-product-review-author"><?= htmlspecialchars((string) ($review['author'] ?? '')) ?></strong>
                        <time class="sh-product-review-date" datetime="<?= htmlspecialchars((string) ($review['created_at'] ?? '')) ?>">
                            <?= htmlspecialchars(sh_product_review_date_label((string) ($review['created_at'] ?? ''), $lang)) ?>
                        </time>
                    </div>
                </div>
                <?= sh_product_reviews_stars_html((float) $rating, $starsLabel) ?>
            </div>
            <?php if (trim((string) ($review['title'] ?? '')) !== ''): ?>
            <h3 class="sh-product-review-title"><?= htmlspecialchars((string) $review['title']) ?></h3>
            <?php endif; ?>
            <p class="sh-product-review-body"><?= nl2br(htmlspecialchars((string) ($review['body'] ?? ''))) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="sh-product-review-form-wrap">
        <h3 class="sh-product-review-form-title"><?= htmlspecialchars($pr['write_title'] ?? 'Write a review') ?></h3>
        <p class="sh-product-review-form-hint"><?= htmlspecialchars($pr['write_hint'] ?? 'Share your experience — rating and comment appear after moderation on production stores.') ?></p>
        <form class="sh-product-review-form" id="shProductReviewForm" novalidate>
            <div class="sh-review-rating-picker" role="group" aria-label="<?= htmlspecialchars($pr['rating_label'] ?? 'Your rating') ?>">
                <span class="sh-review-rating-label"><?= htmlspecialchars($pr['rating_label'] ?? 'Your rating') ?></span>
                <div class="sh-review-rating-stars" data-review-stars>
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <button type="button" class="sh-review-star-btn" data-value="<?= $s ?>" aria-label="<?= htmlspecialchars(sprintf($pr['star_n'] ?? '%d stars', $s)) ?>">
                        <i class="far fa-star" aria-hidden="true"></i>
                    </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="shReviewRating" value="0" required>
            </div>
            <div class="sh-review-fields">
                <label>
                    <span><?= htmlspecialchars($pr['name_label'] ?? 'Your name') ?></span>
                    <input type="text" name="author" maxlength="80" required autocomplete="name" placeholder="<?= htmlspecialchars($pr['name_ph'] ?? 'First name or nickname') ?>">
                </label>
                <label>
                    <span><?= htmlspecialchars($pr['title_label'] ?? 'Review title (optional)') ?></span>
                    <input type="text" name="title" maxlength="120" placeholder="<?= htmlspecialchars($pr['title_ph'] ?? 'Summarize your experience') ?>">
                </label>
                <label class="sh-review-field-wide">
                    <span><?= htmlspecialchars($pr['body_label'] ?? 'Your review') ?></span>
                    <textarea name="body" rows="4" maxlength="2000" required placeholder="<?= htmlspecialchars($pr['body_ph'] ?? 'What did you like or dislike?') ?>"></textarea>
                </label>
            </div>
            <input type="text" name="website" class="sh-hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <?php if ($recapKey !== ''): ?>
            <div class="sh-review-recaptcha">
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recapKey) ?>"></div>
            </div>
            <?php endif; ?>
            <button type="submit" class="sh-btn-primary">
                <i class="fas fa-paper-plane"></i> <?= htmlspecialchars($pr['submit'] ?? 'Post review') ?>
            </button>
            <p class="sh-product-review-msg" id="shProductReviewMsg" hidden role="status"></p>
        </form>
    </div>
</section>
    <?php
}