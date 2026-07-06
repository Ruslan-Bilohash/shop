<?php
/**
 * Shop CMS — MySQL storage layer (required after installation).
 * Copyright (c) 2024–2026 Ruslan Bilohash
 */
declare(strict_types=1);

function sh_db_config_file(): string
{
    return __DIR__ . '/../data/db.config.php';
}

function sh_installed_lock_file(): string
{
    return __DIR__ . '/../data/installed.lock';
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

function sh_db_prefix(): string
{
    $cfg = sh_db_config();
    $prefix = preg_replace('/[^a-z0-9_]/i', '', (string) ($cfg['prefix'] ?? 'sh_'));
    return $prefix !== '' ? $prefix : 'sh_';
}

function sh_db_table(string $name): string
{
    return sh_db_prefix() . $name;
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

function sh_db_require_pdo(): PDO
{
    $pdo = sh_db_pdo();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database not configured. Run install.php first.');
    }
    return $pdo;
}

function sh_is_installed(): bool
{
    return is_file(sh_installed_lock_file()) && sh_db_pdo() instanceof PDO;
}

function sh_is_install_script(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($script, '/install.php') || str_ends_with($script, '/install/install.php');
}

function sh_install_url(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base = ($dir === '' || $dir === '.') ? '' : $dir;
    $appRoot = dirname(__DIR__);
    if (is_file($appRoot . '/install.php')) {
        return ($base === '') ? '/install.php' : $base . '/install.php';
    }
    if (is_file($appRoot . '/install/install.php')) {
        return ($base === '') ? '/install/install.php' : $base . '/install/install.php';
    }
    return ($base === '') ? '/install.php' : $base . '/install.php';
}

function sh_install_redirect_if_needed(): void
{
    if (sh_is_installed() || sh_is_install_script()) {
        return;
    }
    $base = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
    if (in_array($base, ['_health.php'], true)) {
        return;
    }
    header('Location: ' . sh_install_url(), true, 302);
    exit;
}

/** @return array<string, mixed> */
function sh_db_row_to_array(array $row): array
{
    if (isset($row['data'])) {
        $decoded = is_string($row['data']) ? json_decode($row['data'], true) : $row['data'];
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    unset($row['data']);
    return $row;
}

/** @return list<array<string, mixed>> */
function sh_db_load_collection(string $table, string $sortColumn = ''): array
{
    $pdo = sh_db_require_pdo();
    $sql = 'SELECT * FROM `' . sh_db_table($table) . '`';
    if ($sortColumn !== '') {
        $sql .= ' ORDER BY `' . $sortColumn . '` ASC';
    }
    $rows = $pdo->query($sql)->fetchAll();
    $out = [];
    foreach ($rows as $row) {
        $out[] = sh_db_row_to_array($row);
    }
    return $out;
}

/** @param list<array<string, mixed>> $items */
function sh_db_save_collection(string $table, array $items, string $idKey, string $sortKey = ''): bool
{
    $pdo = sh_db_require_pdo();
    $tbl = sh_db_table($table);
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM `' . $tbl . '`');
        $stmt = $pdo->prepare('INSERT INTO `' . $tbl . '` (`id`, `data`' . ($sortKey !== '' ? ', `sort_order`' : '') . ') VALUES (?, ?' . ($sortKey !== '' ? ', ?' : '') . ')');
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = trim((string) ($item[$idKey] ?? ''));
            if ($id === '') {
                continue;
            }
            $json = json_encode($item, JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new RuntimeException('JSON encode failed for ' . $table);
            }
            if ($sortKey !== '') {
                $stmt->execute([$id, $json, max(0, (int) ($item[$sortKey] ?? 99))]);
            } else {
                $stmt->execute([$id, $json]);
            }
        }
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

/** @return array<string, mixed> */
function sh_db_load_settings(): array
{
    $pdo = sh_db_require_pdo();
    $stmt = $pdo->prepare('SELECT data FROM `' . sh_db_table('settings') . '` WHERE id = 1 LIMIT 1');
    $stmt->execute();
    $row = $stmt->fetch();
    if (!$row) {
        return [];
    }
    $decoded = is_string($row['data'] ?? null) ? json_decode($row['data'], true) : $row['data'];
    return is_array($decoded) ? $decoded : [];
}

/** @param array<string, mixed> $settings */
function sh_db_save_settings(array $settings): bool
{
    $pdo = sh_db_require_pdo();
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO `' . sh_db_table('settings') . '` (id, data) VALUES (1, ?)
         ON DUPLICATE KEY UPDATE data = VALUES(data)'
    );
    return $stmt->execute([$json]);
}

/** @return list<array<string, mixed>> */
function sh_db_load_products(): array
{
    return sh_db_load_collection('products');
}

/** @param list<array<string, mixed>> $list */
function sh_db_save_products(array $list): bool
{
    return sh_db_save_collection('products', array_values($list), 'id');
}

/** @return list<array<string, mixed>> */
function sh_db_load_categories(): array
{
    return sh_db_load_collection('categories', 'sort_order');
}

/** @param list<array<string, mixed>> $list */
function sh_db_save_categories(array $list): bool
{
    return sh_db_save_collection('categories', array_values($list), 'slug', 'sort');
}

/** @return list<array<string, mixed>> */
function sh_db_load_news(): array
{
    return sh_db_load_collection('news');
}

/** @param list<array<string, mixed>> $list */
function sh_db_save_news(array $list): bool
{
    return sh_db_save_collection('news', array_values($list), 'id');
}

/** @return list<array<string, mixed>> */
function sh_db_load_leads(): array
{
    return sh_db_load_collection('leads');
}

/** @param list<array<string, mixed>> $list */
function sh_db_save_leads(array $list): bool
{
    return sh_db_save_collection('leads', array_values($list), 'id');
}

/** @return list<array<string, mixed>> */
function sh_db_load_subscribers(): array
{
    return sh_db_load_collection('subscribers');
}

/** @param list<array<string, mixed>> $list */
function sh_db_save_subscribers(array $list): bool
{
    return sh_db_save_collection('subscribers', array_values($list), 'id');
}

/** @return array<string, array<string, mixed>> */
function sh_db_load_customer_profiles(): array
{
    $rows = sh_db_load_collection('customer_profiles');
    $out = [];
    foreach ($rows as $row) {
        $id = (string) ($row['id'] ?? '');
        if ($id !== '') {
            $out[$id] = $row;
        }
    }
    return $out;
}

/** @param array<string, array<string, mixed>> $profiles */
function sh_db_save_customer_profiles(array $profiles): bool
{
    $list = [];
    foreach ($profiles as $id => $profile) {
        if (!is_array($profile)) {
            continue;
        }
        $profile['id'] = (string) $id;
        $list[] = $profile;
    }
    return sh_db_save_collection('customer_profiles', $list, 'id');
}