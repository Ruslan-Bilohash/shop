<?php
/**
 * Shop CMS — admin changelog console (aggregates version.php + site lang entries).
 */
declare(strict_types=1);

/** @return list<array{version:string,date:string,items:list<string>,is_current:bool}> */
function sh_changelog_admin_releases(): array
{
    require_once __DIR__ . '/version.php';

    $siteLangPath = dirname(__DIR__) . '/site/lang/en.php';
    $changelogItems = [];
    if (is_file($siteLangPath)) {
        $siteLang = require $siteLangPath;
        $changelogItems = is_array($siteLang['changelog_items'] ?? null) ? $siteLang['changelog_items'] : [];
    }

    $current = sh_version();
    $releases = [];
    foreach (sh_version_releases() as $rel) {
        $ver = (string) ($rel['version'] ?? '');
        $items = $changelogItems[$ver] ?? [];
        if (!is_array($items)) {
            $items = $items !== '' ? [(string) $items] : [];
        }
        $releases[] = [
            'version'    => $ver,
            'date'       => (string) ($rel['date'] ?? ''),
            'items'      => array_values(array_map('strval', $items)),
            'is_current' => $ver === $current,
        ];
    }
    return $releases;
}

/** @return array{total_releases:int,total_items:int,current_version:string,latest_date:string} */
function sh_changelog_admin_stats(): array
{
    $releases = sh_changelog_admin_releases();
    $itemCount = 0;
    foreach ($releases as $r) {
        $itemCount += count($r['items']);
    }
    $latest = $releases[0]['date'] ?? sh_version_date();
    return [
        'total_releases'  => count($releases),
        'total_items'     => $itemCount,
        'current_version' => sh_version(),
        'latest_date'     => $latest,
    ];
}

/**
 * @return list<array{version:string,date:string,items:list<string>,is_current:bool}>
 */
function sh_changelog_search(string $query): array
{
    $query = mb_strtolower(trim($query));
    $releases = sh_changelog_admin_releases();
    if ($query === '') {
        return $releases;
    }
    return array_values(array_filter($releases, static function (array $rel) use ($query): bool {
        if (str_contains(mb_strtolower($rel['version']), $query)) {
            return true;
        }
        if (str_contains(mb_strtolower($rel['date']), $query)) {
            return true;
        }
        foreach ($rel['items'] as $item) {
            if (str_contains(mb_strtolower($item), $query)) {
                return true;
            }
        }
        return false;
    }));
}