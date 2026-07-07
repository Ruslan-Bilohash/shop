<?php
/**
 * Shop CMS — JSON → MySQL migration (full script transition).
 * Copyright (c) 2024–2026 Ruslan Bilohash
 */
declare(strict_types=1);

const SH_MIGRATE_VERSION = '1.6.0';

/** @return array<string, array{file:string,table:string,id_key:string,sort:bool,sort_key?:string,assoc?:bool}> */
function sh_migrate_json_catalog(): array
{
    return [
        'products'          => ['file' => 'products.json', 'table' => 'products', 'id_key' => 'id', 'sort' => false],
        'categories'        => ['file' => 'categories.json', 'table' => 'categories', 'id_key' => 'slug', 'sort' => true, 'sort_key' => 'sort'],
        'news'              => ['file' => 'news.json', 'table' => 'news', 'id_key' => 'id', 'sort' => false],
        'leads'             => ['file' => 'quick-leads.json', 'table' => 'leads', 'id_key' => 'id', 'sort' => false],
        'orders'            => ['file' => 'orders.json', 'table' => 'orders', 'id_key' => 'id', 'sort' => false],
        'subscribers'       => ['file' => 'subscribers.json', 'table' => 'subscribers', 'id_key' => 'id', 'sort' => false],
        'customer_profiles' => ['file' => 'customer-profiles.json', 'table' => 'customer_profiles', 'id_key' => 'id', 'sort' => false, 'assoc' => true],
    ];
}

function sh_migrate_schema_paths(string $appRoot): array
{
    $candidates = [
        $appRoot . '/schema.sql',
        $appRoot . '/install/schema.sql',
    ];
    $out = [];
    foreach ($candidates as $path) {
        if (is_readable($path)) {
            $out[] = $path;
        }
    }
    return $out;
}

function sh_migrate_resolve_schema(string $appRoot): string
{
    $paths = sh_migrate_schema_paths($appRoot);
    if ($paths === []) {
        throw new RuntimeException('schema.sql not found.');
    }
    return $paths[0];
}

function sh_migrate_prefix_safe(string $prefix): string
{
    $prefix = preg_replace('/[^a-z0-9_]/i', '', $prefix) ?? 'sh_';
    return $prefix !== '' ? $prefix : 'sh_';
}

/** @return array{ok:bool,error:string,pdo:?PDO} */
function sh_migrate_connect(string $host, string $database, string $user, string $pass): array
{
    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4',
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return ['ok' => true, 'error' => '', 'pdo' => $pdo];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage(), 'pdo' => null];
    }
}

function sh_migrate_write_db_config(string $dataDir, array $cfg): bool
{
    $php = "<?php\nreturn [\n"
        . "    'host' => " . var_export($cfg['host'], true) . ",\n"
        . "    'database' => " . var_export($cfg['database'], true) . ",\n"
        . "    'user' => " . var_export($cfg['user'], true) . ",\n"
        . "    'pass' => " . var_export($cfg['pass'], true) . ",\n"
        . "    'prefix' => " . var_export($cfg['prefix'], true) . ",\n"
        . "];\n";
    return file_put_contents($dataDir . '/db.config.php', $php) !== false;
}

function sh_migrate_write_lock(string $lockFile, string $note = ''): bool
{
    $line = gmdate('c') . "\nShop CMS " . SH_MIGRATE_VERSION . " — MySQL\n";
    if ($note !== '') {
        $line .= $note . "\n";
    }
    $line .= "© Ruslan Bilohash\n";
    return file_put_contents($lockFile, $line) !== false;
}

function sh_migrate_run_schema(PDO $pdo, string $prefix, string $schemaFile): void
{
    $sql = file_get_contents($schemaFile) ?: '';
    $sql = str_replace('{prefix}', sh_migrate_prefix_safe($prefix), $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
}

/**
 * @param list<array<string,mixed>>|array<string,array<string,mixed>> $items
 * @return array{imported:int,skipped:int}
 */
function sh_migrate_import_collection(PDO $pdo, string $prefix, string $table, array $items, string $idKey, bool $hasSort = false, string $sortKey = 'sort'): array
{
    $tbl = sh_migrate_prefix_safe($prefix) . $table;
    $pdo->exec('DELETE FROM `' . $tbl . '`');
    $imported = 0;
    $skipped = 0;

    if ($items === []) {
        return ['imported' => 0, 'skipped' => 0];
    }

    $cols = $hasSort ? '(`id`, `data`, `sort_order`)' : '(`id`, `data`)';
    $vals = $hasSort ? '(?, ?, ?)' : '(?, ?)';
    $stmt = $pdo->prepare('INSERT INTO `' . $tbl . '` ' . $cols . ' VALUES ' . $vals);

    foreach ($items as $key => $item) {
        if (!is_array($item)) {
            $skipped++;
            continue;
        }
        $id = trim((string) ($item[$idKey] ?? (is_string($key) ? $key : ($item['id'] ?? ''))));
        if ($id === '') {
            $skipped++;
            continue;
        }
        if (!isset($item['id']) && $idKey === 'id') {
            $item['id'] = $id;
        }
        $json = json_encode($item, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $skipped++;
            continue;
        }
        if ($hasSort) {
            $stmt->execute([$id, $json, max(0, (int) ($item[$sortKey] ?? 99))]);
        } else {
            $stmt->execute([$id, $json]);
        }
        $imported++;
    }

    return ['imported' => $imported, 'skipped' => $skipped];
}

/** @param array<string,mixed> $settings */
function sh_migrate_import_settings(PDO $pdo, string $prefix, array $settings): bool
{
    if ($settings === []) {
        return true;
    }
    $tbl = sh_migrate_prefix_safe($prefix) . 'settings';
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    $stmt = $pdo->prepare('INSERT INTO `' . $tbl . '` (id, data) VALUES (1, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)');
    return $stmt->execute([$json]);
}

/** @return list<string> */
function sh_migrate_json_files_found(string $dataDir): array
{
    $found = [];
    if (is_readable($dataDir . '/settings.json')) {
        $found[] = 'settings.json';
    }
    foreach (sh_migrate_json_catalog() as $meta) {
        $path = $dataDir . '/' . $meta['file'];
        if (is_readable($path)) {
            $found[] = $meta['file'];
        }
    }
    return array_values(array_unique($found));
}

function sh_migrate_json_edition_detected(string $dataDir): bool
{
    return sh_migrate_json_files_found($dataDir) !== [];
}

function sh_migrate_mysql_installed(string $dataDir): bool
{
    $lock = $dataDir . '/installed.lock';
    $cfg = $dataDir . '/db.config.php';
    return is_file($lock) && is_readable($cfg);
}

/** @return array<string,mixed>|null */
function sh_migrate_read_json_file(string $path, bool $assocList = true)
{
    if (!is_readable($path)) {
        return null;
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $assocList ? [] : null;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $assocList ? [] : null;
    }
    return $decoded;
}

/**
 * Import all JSON from data/ into MySQL.
 *
 * @return array{ok:bool,error:string,stats:array<string,array{imported:int,skipped:int}>}
 */
function sh_migrate_import_json_dir(PDO $pdo, string $prefix, string $dataDir): array
{
    $stats = [];
    $catalog = sh_migrate_json_catalog();

    foreach ($catalog as $name => $meta) {
        $path = $dataDir . '/' . $meta['file'];
        $data = sh_migrate_read_json_file($path, empty($meta['assoc']));
        if (!is_array($data) || $data === []) {
            continue;
        }
        if (!empty($meta['assoc'])) {
            $list = [];
            foreach ($data as $id => $row) {
                if (is_array($row)) {
                    $list[] = $row;
                }
            }
            $data = $list;
        }
        $stats[$name] = sh_migrate_import_collection(
            $pdo,
            $prefix,
            $meta['table'],
            $data,
            $meta['id_key'],
            !empty($meta['sort']),
            (string) ($meta['sort_key'] ?? 'sort')
        );
    }

    $settingsPath = $dataDir . '/settings.json';
    $settings = sh_migrate_read_json_file($settingsPath, false);
    if (is_array($settings) && $settings !== []) {
        sh_migrate_import_settings($pdo, $prefix, $settings);
        $stats['settings'] = ['imported' => 1, 'skipped' => 0];
    }

    return ['ok' => true, 'error' => '', 'stats' => $stats];
}

/**
 * @param array{products?:list,categories?:list,news?:list,leads?:list,orders?:list,subscribers?:list,customer_profiles?:list,settings?:array} $demo
 * @return array<string,array{imported:int,skipped:int}>
 */
function sh_migrate_import_seed(PDO $pdo, string $prefix, array $demo): array
{
    $stats = [];
    $map = [
        'products'          => ['table' => 'products', 'id_key' => 'id', 'sort' => false],
        'categories'        => ['table' => 'categories', 'id_key' => 'slug', 'sort' => true],
        'news'              => ['table' => 'news', 'id_key' => 'id', 'sort' => false],
        'leads'             => ['table' => 'leads', 'id_key' => 'id', 'sort' => false],
        'orders'            => ['table' => 'orders', 'id_key' => 'id', 'sort' => false],
        'subscribers'       => ['table' => 'subscribers', 'id_key' => 'id', 'sort' => false],
        'customer_profiles' => ['table' => 'customer_profiles', 'id_key' => 'id', 'sort' => false],
    ];
    foreach ($map as $key => $meta) {
        $items = $demo[$key] ?? [];
        if (!is_array($items) || $items === []) {
            continue;
        }
        $stats[$key] = sh_migrate_import_collection(
            $pdo,
            $prefix,
            $meta['table'],
            $items,
            $meta['id_key'],
            $meta['sort']
        );
    }
    $settings = $demo['settings'] ?? [];
    if (is_array($settings) && $settings !== []) {
        sh_migrate_import_settings($pdo, $prefix, $settings);
        $stats['settings'] = ['imported' => 1, 'skipped' => 0];
    }
    return $stats;
}

/** @return array{ok:bool,error:string,backup_dir:string} */
function sh_migrate_backup_json(string $dataDir): array
{
    $files = sh_migrate_json_files_found($dataDir);
    if ($files === []) {
        return ['ok' => true, 'error' => '', 'backup_dir' => ''];
    }
    $backupDir = $dataDir . '/archive/json-pre-mysql-' . gmdate('Ymd-His');
    if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true)) {
        return ['ok' => false, 'error' => 'Could not create backup directory.', 'backup_dir' => ''];
    }
    foreach ($files as $file) {
        $src = $dataDir . '/' . $file;
        if (!@copy($src, $backupDir . '/' . $file)) {
            return ['ok' => false, 'error' => 'Backup failed for ' . $file, 'backup_dir' => $backupDir];
        }
    }
    $ht = $backupDir . '/.htaccess';
    if (!is_file($ht)) {
        file_put_contents($ht, "Require all denied\n");
    }
    return ['ok' => true, 'error' => '', 'backup_dir' => $backupDir];
}

/** @param list<string> $files */
function sh_migrate_quarantine_json(string $dataDir, array $files): void
{
    foreach ($files as $file) {
        $base = basename($file);
        if ($base === '' || $base !== $file) {
            continue;
        }
        $src = $dataDir . '/' . $base;
        if (is_file($src)) {
            @rename($src, $dataDir . '/' . $base . '.migrated');
        }
    }
}

function sh_migrate_write_admin_config(string $dataDir, string $adminUser, string $adminPass): bool
{
    if ($adminUser === '' || strlen($adminPass) < 6) {
        return false;
    }
    $php = "<?php\nreturn [\n"
        . "    'user' => " . var_export($adminUser, true) . ",\n"
        . "    'pass_hash' => " . var_export(password_hash($adminPass, PASSWORD_DEFAULT), true) . ",\n"
        . "];\n";
    return file_put_contents($dataDir . '/admin.config.php', $php) !== false;
}

/**
 * Full JSON → MySQL migration.
 *
 * @return array{ok:bool,error:string,stats:array<string,array{imported:int,skipped:int}>,backup_dir:string}
 */
function sh_migrate_json_to_mysql(array $opts): array
{
    $appRoot = (string) ($opts['app_root'] ?? '');
    $dataDir = (string) ($opts['data_dir'] ?? ($appRoot . '/data'));
    $lockFile = (string) ($opts['lock_file'] ?? ($dataDir . '/installed.lock'));
    $host = trim((string) ($opts['db_host'] ?? 'localhost'));
    $database = trim((string) ($opts['db_name'] ?? ''));
    $user = trim((string) ($opts['db_user'] ?? ''));
    $pass = (string) ($opts['db_pass'] ?? '');
    $prefix = sh_migrate_prefix_safe((string) ($opts['db_prefix'] ?? 'sh_'));
    $backup = !empty($opts['backup_json']);
    $quarantine = !empty($opts['quarantine_json']);
    $adminUser = trim((string) ($opts['admin_user'] ?? ''));
    $adminPass = (string) ($opts['admin_pass'] ?? '');

    if ($database === '' || $user === '') {
        return ['ok' => false, 'error' => 'Database name and user are required.', 'stats' => [], 'backup_dir' => ''];
    }
    if (sh_migrate_mysql_installed($dataDir)) {
        return ['ok' => false, 'error' => 'MySQL edition already installed (installed.lock exists).', 'stats' => [], 'backup_dir' => ''];
    }
    if (!sh_migrate_json_edition_detected($dataDir)) {
        return ['ok' => false, 'error' => 'No JSON data files found in data/. Nothing to migrate.', 'stats' => [], 'backup_dir' => ''];
    }

    $conn = sh_migrate_connect($host, $database, $user, $pass);
    if (!$conn['ok'] || !$conn['pdo'] instanceof PDO) {
        return ['ok' => false, 'error' => 'MySQL connection failed: ' . $conn['error'], 'stats' => [], 'backup_dir' => ''];
    }

    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }

    $backupDir = '';
    if ($backup) {
        $bk = sh_migrate_backup_json($dataDir);
        if (!$bk['ok']) {
            return ['ok' => false, 'error' => $bk['error'], 'stats' => [], 'backup_dir' => ''];
        }
        $backupDir = $bk['backup_dir'];
    }

    if (!sh_migrate_write_db_config($dataDir, [
        'host' => $host, 'database' => $database, 'user' => $user, 'pass' => $pass, 'prefix' => $prefix,
    ])) {
        return ['ok' => false, 'error' => 'Could not write data/db.config.php', 'stats' => [], 'backup_dir' => $backupDir];
    }

    try {
        $schema = sh_migrate_resolve_schema($appRoot);
        sh_migrate_run_schema($conn['pdo'], $prefix, $schema);
        $import = sh_migrate_import_json_dir($conn['pdo'], $prefix, $dataDir);
    } catch (Throwable $e) {
        @unlink($dataDir . '/db.config.php');
        return ['ok' => false, 'error' => $e->getMessage(), 'stats' => [], 'backup_dir' => $backupDir];
    }

    $adminFile = $dataDir . '/admin.config.php';
    if (!is_readable($adminFile) && $adminUser !== '' && $adminPass !== '') {
        sh_migrate_write_admin_config($dataDir, $adminUser, $adminPass);
    }

    sh_migrate_write_lock($lockFile, 'Migrated from JSON edition');

    if ($quarantine) {
        sh_migrate_quarantine_json($dataDir, sh_migrate_json_files_found($dataDir));
    }

    sh_migrate_activate_mysql_runtime($appRoot);

    return [
        'ok'         => true,
        'error'      => '',
        'stats'      => $import['stats'],
        'backup_dir' => $backupDir,
    ];
}

/** Copy MySQL storage includes over JSON editions and switch init.php. */
function sh_migrate_activate_mysql_runtime(string $appRoot): void
{
    $includes = $appRoot . '/includes';
    $mysqlDir = $includes . '/mysql';
    if (is_dir($mysqlDir)) {
        $map = [
            'storage.php', 'category-storage.php', 'news-storage.php', 'leads-storage.php',
            'payment-settings.php', 'shop-mode.php', 'orders-storage.php',
            'subscribers-storage.php', 'customer-profile.php',
        ];
        foreach ($map as $file) {
            $src = $mysqlDir . '/' . $file;
            if (is_readable($src)) {
                @copy($src, $includes . '/' . $file);
            }
        }
    }

    $stub = $includes . '/mysql-init.stub.php';
    $init = $appRoot . '/init.php';
    if (is_readable($stub)) {
        @copy($stub, $init);
    }
}