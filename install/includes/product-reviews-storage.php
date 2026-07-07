<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

/** @return list<array<string, mixed>> */
function sh_product_reviews_load(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        return sh_db_load_product_reviews();
    } catch (Throwable $e) {
        return [];
    }
}

/** @param list<array<string, mixed>> $list */
function sh_product_reviews_save(array $list): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    return sh_db_save_product_reviews(array_values($list));
}

/** @return list<array<string, mixed>> */
function sh_product_reviews_for_product(string $productId, bool $publishedOnly = true): array
{
    $productId = trim($productId);
    if ($productId === '') {
        return [];
    }
    $rows = array_values(array_filter(
        sh_product_reviews_load(),
        static function (array $row) use ($productId, $publishedOnly): bool {
            if ((string) ($row['product_id'] ?? '') !== $productId) {
                return false;
            }
            if ($publishedOnly && ($row['status'] ?? 'published') !== 'published') {
                return false;
            }
            return true;
        }
    ));
    usort($rows, static fn(array $a, array $b): int => strcmp(
        (string) ($b['created_at'] ?? ''),
        (string) ($a['created_at'] ?? '')
    ));
    return $rows;
}

/** @return array{rating:float,count:int}|null */
function sh_product_reviews_aggregate(string $productId): ?array
{
    $rows = sh_product_reviews_for_product($productId, true);
    if ($rows === []) {
        return null;
    }
    $sum = 0;
    $count = 0;
    foreach ($rows as $row) {
        $rating = (int) ($row['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            continue;
        }
        $sum += $rating;
        $count++;
    }
    if ($count === 0) {
        return null;
    }
    return [
        'rating' => round($sum / $count, 1),
        'count'  => $count,
    ];
}

function sh_product_review_add(array $input): array
{
    $productId = trim((string) ($input['product_id'] ?? ''));
    $author = trim((string) ($input['author'] ?? ''));
    $title = trim((string) ($input['title'] ?? ''));
    $body = trim((string) ($input['body'] ?? ''));
    $rating = (int) ($input['rating'] ?? 0);
    $lang = strtolower(substr(trim((string) ($input['lang'] ?? 'en')), 0, 8));

    if ($productId === '' || !sh_product_by_id($productId)) {
        return ['ok' => false, 'error' => 'invalid_product'];
    }
    if ($rating < 1 || $rating > 5) {
        return ['ok' => false, 'error' => 'invalid_rating'];
    }
    if (mb_strlen($author) < 2 || mb_strlen($author) > 80) {
        return ['ok' => false, 'error' => 'invalid_author'];
    }
    if (mb_strlen($body) < 10 || mb_strlen($body) > 2000) {
        return ['ok' => false, 'error' => 'invalid_body'];
    }
    if ($title !== '' && mb_strlen($title) > 120) {
        return ['ok' => false, 'error' => 'invalid_title'];
    }

    $reviews = sh_product_reviews_load();
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $dayAgo = gmdate('Y-m-d\TH:i:s\Z', time() - 86400);
    $recentSameIp = 0;
    foreach ($reviews as $row) {
        if ((string) ($row['product_id'] ?? '') !== $productId) {
            continue;
        }
        if ($ip !== '' && ($row['ip'] ?? '') === $ip && ($row['created_at'] ?? '') >= $dayAgo) {
            $recentSameIp++;
        }
    }
    if ($recentSameIp >= 3) {
        return ['ok' => false, 'error' => 'rate_limit'];
    }

    $review = [
        'id'         => 'rev-' . bin2hex(random_bytes(6)),
        'product_id' => $productId,
        'author'     => $author,
        'title'      => $title,
        'body'       => $body,
        'rating'     => $rating,
        'lang'       => $lang !== '' ? $lang : 'en',
        'status'     => 'published',
        'ip'         => $ip,
        'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
    ];
    array_unshift($reviews, $review);
    if (count($reviews) > 10000) {
        $reviews = array_slice($reviews, 0, 10000);
    }
    if (!sh_product_reviews_save($reviews)) {
        return ['ok' => false, 'error' => 'save_failed'];
    }
    return ['ok' => true, 'review' => $review];
}

function sh_product_reviews_seed_demo(): void
{
    static $seeded = false;
    if ($seeded || !sh_is_installed()) {
        return;
    }
    $seeded = true;
    $existing = sh_product_reviews_load();
    if ($existing !== []) {
        return;
    }
    $now = gmdate('Y-m-d\TH:i:s\Z');
    $demo = [
        [
            'id' => 'rev-demo-headphones-1', 'product_id' => 'wireless-headphones-pro',
            'author' => 'Ingrid M.', 'title' => 'Excellent noise cancelling',
            'body' => 'Bought these for the Oslo commute — battery lasts all week and the ANC is surprisingly strong for a demo listing. Comfortable for long calls too.',
            'rating' => 5, 'lang' => 'en', 'status' => 'published', 'ip' => '', 'created_at' => gmdate('Y-m-d\TH:i:s\Z', strtotime('-12 days')),
        ],
        [
            'id' => 'rev-demo-headphones-2', 'product_id' => 'wireless-headphones-pro',
            'author' => 'Олена К.', 'title' => 'Якісний звук',
            'body' => 'Демо-сторінка виглядає як справжній магазин. Навушники зручні, bass чистий, упаковка описана детально — добре для презентації клієнтам.',
            'rating' => 4, 'lang' => 'uk', 'status' => 'published', 'ip' => '', 'created_at' => gmdate('Y-m-d\TH:i:s\Z', strtotime('-5 days')),
        ],
        [
            'id' => 'rev-demo-watch-1', 'product_id' => 'smartwatch-fitness',
            'author' => 'Lars H.', 'title' => 'Solid fitness tracker',
            'body' => 'GPS locks quickly outdoors in Drammen. Heart rate and sleep stats look realistic on the product page — nice showcase for sports niche shops.',
            'rating' => 5, 'lang' => 'en', 'status' => 'published', 'ip' => '', 'created_at' => $now,
        ],
    ];
    sh_product_reviews_save($demo);
}