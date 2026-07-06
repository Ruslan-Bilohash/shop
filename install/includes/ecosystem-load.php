<?php
/**
 * Load Bilohash ecosystem includes — works in dev (/bilohash/shop) and standalone install/ package.
 */
declare(strict_types=1);

function sh_require_ecosystem(string $filename): void
{
    static $loaded = [];
    if (isset($loaded[$filename])) {
        return;
    }
    $candidates = [
        __DIR__ . '/' . $filename,
        dirname(__DIR__) . '/includes/' . $filename,
        dirname(__DIR__, 2) . '/includes/' . $filename,
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            require_once $path;
            $loaded[$filename] = true;
            return;
        }
    }
    throw new RuntimeException('Missing ecosystem file: ' . $filename);
}