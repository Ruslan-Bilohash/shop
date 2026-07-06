<?php

require_once __DIR__ . '/database.php';

/** @return list<array<string, mixed>> */
function sh_news_load(): array
{
    if (!sh_is_installed()) {
        return [];
    }
    try {
        return sh_db_load_news();
    } catch (Throwable $e) {
        return [];
    }
}

function sh_news_save(array $list): bool
{
    if (!sh_is_installed()) {
        return false;
    }
    $ok = sh_db_save_news(array_values($list));
    if ($ok) {
        sh_news_touch_sitemap();
    }
    return $ok;
}

function sh_news_touch_sitemap(): void
{
    if (!function_exists('sh_load_settings')) {
        require_once __DIR__ . '/payment-settings.php';
    }
    if (!function_exists('sh_sitemap_regenerate')) {
        require_once __DIR__ . '/site-settings.php';
    }
    $settings = sh_load_settings();
    if (empty($settings['sitemap_enabled'])) {
        return;
    }
    $settings = sh_sitemap_regenerate($settings);
    sh_save_settings($settings);
}

/** Fill missing language labels from the first available translation. */
function sh_news_normalize_localized(array $fields): array
{
    if (!function_exists('sh_langs')) {
        require_once __DIR__ . '/store-settings.php';
    }
    $fallback = '';
    foreach (array_merge(['en', 'no', 'uk', 'ru', 'sv'], array_keys($fields)) as $code) {
        $val = trim((string) ($fields[$code] ?? ''));
        if ($val !== '') {
            $fallback = $val;
            break;
        }
    }
    foreach (array_keys(sh_langs()) as $code) {
        if (trim((string) ($fields[$code] ?? '')) === '' && $fallback !== '') {
            $fields[$code] = $fallback;
        }
    }
    return $fields;
}

function sh_news_by_slug(string $slug, bool $include_inactive = true): ?array
{
    $slug = trim($slug);
    foreach (sh_news_load() as $article) {
        $articleSlug = (string) ($article['slug'] ?? $article['id'] ?? '');
        if ($articleSlug !== $slug) {
            continue;
        }
        if (!$include_inactive && ($article['active'] ?? true) === false) {
            return null;
        }
        return $article;
    }
    return null;
}

function sh_news_slug_valid(string $slug): bool
{
    return (bool) preg_match('/^[a-z][a-z0-9_-]{1,48}$/', $slug);
}

function sh_news_upsert(array $record): bool
{
    $slug = trim((string) ($record['slug'] ?? $record['id'] ?? ''));
    if (!sh_news_slug_valid($slug)) {
        return false;
    }

    $list = sh_news_load();
    $found = false;
    $existing = null;
    foreach ($list as $article) {
        $articleSlug = (string) ($article['slug'] ?? $article['id'] ?? '');
        if ($articleSlug === $slug) {
            $existing = $article;
            break;
        }
    }

    $publishedAt = trim((string) ($record['published_at'] ?? ''));
    if ($publishedAt === '') {
        $publishedAt = gmdate('Y-m-d\TH:i:s\Z');
    }

    $payload = [
        'id'           => $slug,
        'slug'         => $slug,
        'active'       => ($record['active'] ?? true) !== false,
        'featured'     => !empty($record['featured']),
        'published_at' => $publishedAt,
        'image'        => trim((string) ($record['image'] ?? '')),
        'name'         => sh_news_normalize_localized(is_array($record['name'] ?? null) ? $record['name'] : []),
        'excerpt'      => sh_news_normalize_localized(is_array($record['excerpt'] ?? null) ? $record['excerpt'] : []),
        'body'         => is_array($record['body'] ?? null) ? $record['body'] : (is_array($record['content'] ?? null) ? $record['content'] : []),
    ];
    $payload['body'] = sh_news_normalize_localized($payload['body']);

    if (!empty($record['seo']) && is_array($record['seo'])) {
        $payload['seo'] = $record['seo'];
    } elseif (is_array($existing['seo'] ?? null)) {
        $payload['seo'] = $existing['seo'];
    }

    foreach ($list as $i => $article) {
        if (($article['slug'] ?? $article['id'] ?? '') === $slug) {
            $list[$i] = array_merge($article, $payload);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $list[] = $payload;
    }

    return sh_news_save($list);
}

function sh_news_delete(string $slug): bool
{
    $slug = trim($slug);
    if ($slug === '' || sh_news_by_slug($slug, true) === null) {
        return false;
    }
    $list = array_values(array_filter(
        sh_news_load(),
        static fn(array $article): bool => (string) ($article['slug'] ?? $article['id'] ?? '') !== $slug
    ));
    return sh_news_save($list);
}

/** @return list<array<string, mixed>> */
function sh_news_active_list(bool $featured_first = false): array
{
    $now = gmdate('Y-m-d\TH:i:s\Z');
    $list = array_values(array_filter(sh_news_load(), static function (array $article) use ($now): bool {
        if (($article['active'] ?? true) === false) {
            return false;
        }
        $published = trim((string) ($article['published_at'] ?? ''));
        return $published === '' || $published <= $now;
    }));
    usort($list, static function (array $a, array $b) use ($featured_first): int {
        if ($featured_first) {
            $af = !empty($a['featured']) ? 1 : 0;
            $bf = !empty($b['featured']) ? 1 : 0;
            if ($af !== $bf) {
                return $bf <=> $af;
            }
        }
        $da = (string) ($a['published_at'] ?? '');
        $db = (string) ($b['published_at'] ?? '');
        return strcmp($db, $da);
    });
    return $list;
}

function sh_news_image(array $article): string
{
    $image = trim((string) ($article['image'] ?? ''));
    if ($image !== '') {
        return $image;
    }
    return sh_placeholder_image();
}

function sh_news_published_label(array $article, string $lang): string
{
    $raw = trim((string) ($article['published_at'] ?? ''));
    if ($raw === '') {
        return '';
    }
    $ts = strtotime($raw);
    if ($ts === false) {
        return substr($raw, 0, 10);
    }
    $formats = [
        'no' => 'd.m.Y',
        'en' => 'M j, Y',
        'uk' => 'd.m.Y',
        'ru' => 'd.m.Y',
        'sv' => 'Y-m-d',
    ];
    return date($formats[$lang] ?? 'Y-m-d', $ts);
}