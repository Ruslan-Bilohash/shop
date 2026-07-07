<?php
/**
 * Shop CMS — remote update check (GitHub releases), gated by license.
 */
declare(strict_types=1);

const SH_UPDATE_RELEASES_URL = 'https://api.github.com/repos/Ruslan-Bilohash/shop/releases/latest';

function sh_update_cache_path(): string
{
    return dirname(__DIR__) . '/data/update-check-cache.json';
}

/** @return array<string, mixed> */
function sh_update_cache_load(): array
{
    $path = sh_update_cache_path();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/** @param array<string, mixed> $cache */
function sh_update_cache_save(array $cache): bool
{
    $dir = dirname(sh_update_cache_path());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(sh_update_cache_path(), $json . "\n", LOCK_EX) !== false;
}

function sh_update_normalize_version(string $tag): string
{
    $tag = trim($tag);
    if ($tag === '') {
        return '';
    }
    return ltrim($tag, 'vV');
}

/**
 * @return array{ok:bool,version:string,date:string,url:string,name:string,error:string}
 */
function sh_update_fetch_latest(): array
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header'  => "Accept: application/vnd.github+json\r\nUser-Agent: ShopCMS-UpdateCheck/1.0\r\n",
        ],
    ]);
    $raw = @file_get_contents(SH_UPDATE_RELEASES_URL, false, $ctx);
    if ($raw === false || $raw === '') {
        return ['ok' => false, 'version' => '', 'date' => '', 'url' => '', 'name' => '', 'error' => 'Update server unreachable'];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'version' => '', 'date' => '', 'url' => '', 'name' => '', 'error' => 'Invalid update response'];
    }
    $tag = sh_update_normalize_version((string) ($data['tag_name'] ?? ''));
    if ($tag === '') {
        return ['ok' => false, 'version' => '', 'date' => '', 'url' => '', 'name' => '', 'error' => 'No release version in response'];
    }
    $published = (string) ($data['published_at'] ?? '');
    $date = $published !== '' ? gmdate('Y-m-d', strtotime($published) ?: time()) : '';
    return [
        'ok'      => true,
        'version' => $tag,
        'date'    => $date,
        'url'     => (string) ($data['html_url'] ?? ''),
        'name'    => (string) ($data['name'] ?? ('v' . $tag)),
        'error'   => '',
    ];
}

/**
 * @return array{
 *   ok:bool,
 *   blocked:bool,
 *   license_reason:string,
 *   license_message:string,
 *   current_version:string,
 *   latest_version:string,
 *   update_available:bool,
 *   release_url:string,
 *   release_name:string,
 *   release_date:string,
 *   checked_at:string,
 *   cached:bool,
 *   error:string
 * }
 */
function sh_update_check(bool $refresh = false): array
{
    require_once __DIR__ . '/version.php';

    $gate = function_exists('sh_license_can_check_updates')
        ? sh_license_can_check_updates()
        : ['allowed' => true, 'reason' => '', 'message' => '', 'status' => []];

    $current = sh_version();
    $base = [
        'ok'               => false,
        'blocked'          => !$gate['allowed'],
        'license_reason'   => (string) ($gate['reason'] ?? ''),
        'license_message'  => (string) ($gate['message'] ?? ''),
        'current_version'  => $current,
        'latest_version'   => $current,
        'update_available' => false,
        'release_url'      => '',
        'release_name'     => '',
        'release_date'     => '',
        'checked_at'       => '',
        'cached'           => false,
        'error'            => '',
    ];

    if (!$gate['allowed']) {
        $base['error'] = $base['license_message'];
        return $base;
    }

    if (!$refresh) {
        $cache = sh_update_cache_load();
        $cachedVer = sh_update_normalize_version((string) ($cache['latest_version'] ?? ''));
        if ($cachedVer !== '' && ($cache['current_version'] ?? '') === $current) {
            $base['ok'] = true;
            $base['latest_version'] = $cachedVer;
            $base['update_available'] = version_compare($cachedVer, $current, '>');
            $base['release_url'] = (string) ($cache['release_url'] ?? '');
            $base['release_name'] = (string) ($cache['release_name'] ?? '');
            $base['release_date'] = (string) ($cache['release_date'] ?? '');
            $base['checked_at'] = (string) ($cache['checked_at'] ?? '');
            $base['cached'] = true;
            return $base;
        }
    }

    $live = sh_update_fetch_latest();
    if (!$live['ok']) {
        $base['error'] = $live['error'];
        return $base;
    }

    $latest = $live['version'];
    $checkedAt = gmdate('c');
    $result = $base;
    $result['ok'] = true;
    $result['latest_version'] = $latest;
    $result['update_available'] = version_compare($latest, $current, '>');
    $result['release_url'] = $live['url'];
    $result['release_name'] = $live['name'];
    $result['release_date'] = $live['date'];
    $result['checked_at'] = $checkedAt;
    $result['cached'] = false;

    sh_update_cache_save([
        'current_version' => $current,
        'latest_version'  => $latest,
        'release_url'     => $live['url'],
        'release_name'    => $live['name'],
        'release_date'    => $live['date'],
        'checked_at'      => $checkedAt,
    ]);

    return $result;
}