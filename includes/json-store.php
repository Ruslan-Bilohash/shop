<?php

function sh_db_config_file(): string
{
    return __DIR__ . '/../data/db.config.php';
}

/** @return array<string, mixed>|null */
function sh_db_config(): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $file = sh_db_config_file();
    if (!is_readable($file)) {
        $cache = null;
        return null;
    }
    $data = require $file;
    $cache = is_array($data) ? $data : null;
    return $cache;
}

function sh_db_table(): string
{
    $cfg = sh_db_config();
    $prefix = preg_replace('/[^a-z0-9_]/i', '', (string) ($cfg['prefix'] ?? 'sh_'));
    return $prefix . 'store';
}

function sh_db_pdo(): ?PDO
{
    static $pdo = null;
    static $failed = false;
    if ($failed) {
        return null;
    }
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = sh_db_config();
    if ($cfg === null) {
        return null;
    }
    $host = (string) ($cfg['host'] ?? 'localhost');
    $name = (string) ($cfg['database'] ?? $cfg['dbname'] ?? '');
    $user = (string) ($cfg['user'] ?? $cfg['username'] ?? '');
    $pass = (string) ($cfg['pass'] ?? $cfg['password'] ?? '');
    if ($name === '' || $user === '') {
        return null;
    }
    try {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        $failed = true;
        return null;
    }
    return $pdo;
}

function sh_uses_mysql_store(): bool
{
    return sh_db_pdo() instanceof PDO;
}

function sh_store_key_from_path(string $path): string
{
    return basename($path, '.json');
}

/** @return string|false */
function sh_json_store_read(string $filePath)
{
    $key = sh_store_key_from_path($filePath);
    $pdo = sh_db_pdo();
    if ($pdo instanceof PDO) {
        $table = sh_db_table();
        $stmt = $pdo->prepare('SELECT content FROM `' . $table . '` WHERE store_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (is_array($row) && array_key_exists('content', $row)) {
            return (string) $row['content'];
        }
    }
    if (is_readable($filePath)) {
        $raw = file_get_contents($filePath);
        return $raw === false ? false : $raw;
    }
    return false;
}

function sh_json_store_write(string $filePath, string $json): bool
{
    $key = sh_store_key_from_path($filePath);
    $pdo = sh_db_pdo();
    $ok = false;
    if ($pdo instanceof PDO) {
        $table = sh_db_table();
        $stmt = $pdo->prepare(
            'INSERT INTO `' . $table . '` (store_key, content) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE content = VALUES(content)'
        );
        $ok = $stmt->execute([$key, $json]);
    }
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $fileOk = file_put_contents($filePath, $json, LOCK_EX) !== false;
    return $ok || $fileOk;
}

function sh_json_store_decode(string $filePath, bool $assoc = true)
{
    $raw = sh_json_store_read($filePath);
    if ($raw === false || trim($raw) === '') {
        return $assoc ? [] : null;
    }
    return json_decode($raw, $assoc);
}

function sh_is_installed(): bool
{
    return is_file(__DIR__ . '/../data/installed.lock') || sh_uses_mysql_store();
}