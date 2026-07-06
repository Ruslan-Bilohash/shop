<?php
/**
 * Shop CMS — single source of truth for script version.
 * Used on /shop/site/ and /shop/admin/ (must always match).
 */
define('SH_VERSION', '1.3.5');
define('SH_VERSION_DATE', '2026-07-06');

function sh_version(): string
{
    return SH_VERSION;
}

function sh_version_label(): string
{
    return 'v' . SH_VERSION;
}

function sh_version_date(): string
{
    return SH_VERSION_DATE;
}

/** Production cap — do not publish changelog entries above this version. */
define('SH_VERSION_PUBLIC_CAP', '1.3.5');

/** @return list<array{version:string,date:string}> */
function sh_version_releases(): array
{
    return [
        ['version' => '1.3.5', 'date' => '2026-07-06'],
        ['version' => '1.3.0', 'date' => '2026-07-06'],
        ['version' => '1.2.2', 'date' => '2026-07-06'],
        ['version' => '1.2.1', 'date' => '2026-07-06'],
        ['version' => '1.2.0', 'date' => '2026-07-06'],
        ['version' => '1.1.1', 'date' => '2026-07-06'],
        ['version' => '1.1.0', 'date' => '2026-07-06'],
        ['version' => '1.0.0', 'date' => '2026-06-15'],
    ];
}

/** @return list<array{version:string,date:string}> */
function sh_version_releases_public(): array
{
    $cap = SH_VERSION_PUBLIC_CAP;
    return array_values(array_filter(
        sh_version_releases(),
        static fn(array $r): bool => version_compare($r['version'], $cap, '<=')
    ));
}

function sh_public_style_version(): string
{
    return '24';
}

function sh_public_script_version(): string
{
    return '11';
}