<?php
/**
 * Shop CMS — JSON file storage (no MySQL).
 * Copyright (c) 2024–2026 Ruslan Bilohash
 */
declare(strict_types=1);

function sh_json_store_read(string $filePath)
{
    if (!is_readable($filePath)) {
        return false;
    }
    $raw = file_get_contents($filePath);
    return $raw === false ? false : $raw;
}

function sh_json_store_write(string $filePath, string $json): bool
{
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return file_put_contents($filePath, $json, LOCK_EX) !== false;
}

function sh_json_store_decode(string $filePath, bool $assoc = true)
{
    $raw = sh_json_store_read($filePath);
    if ($raw === false || trim($raw) === '') {
        return $assoc ? [] : null;
    }
    $decoded = json_decode($raw, $assoc);
    if ($assoc) {
        return is_array($decoded) ? $decoded : [];
    }
    return $decoded;
}

function sh_is_installed(): bool
{
    return is_file(__DIR__ . '/../data/settings.json')
        || is_file(__DIR__ . '/../data/products.json');
}