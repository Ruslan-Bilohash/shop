<?php
/**
 * Shop CMS — Installation wizard (MySQL)
 * Copyright (c) 2024–2026 Ruslan Bilohash — https://bilohash.com
 *
 * Upload this folder to your server, open install.php in the browser,
 * enter database credentials — the shop will be ready with demo data.
 */
declare(strict_types=1);

const SH_INSTALL_VERSION = '1.5.0';

$appRoot = __DIR__;
$dataDir = $appRoot . '/data';
$lockFile = $dataDir . '/installed.lock';
$seedFile = __DIR__ . '/seed/load.php';

function sh_install_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function sh_install_requirements(string $dataDir, string $appRoot): array
{
    $checks = [
        ['ok' => version_compare(PHP_VERSION, '8.0.0', '>='), 'label' => 'PHP 8.0+', 'hint' => PHP_VERSION],
        ['ok' => extension_loaded('pdo'), 'label' => 'PDO', 'hint' => ''],
        ['ok' => extension_loaded('pdo_mysql'), 'label' => 'PDO MySQL', 'hint' => ''],
        ['ok' => extension_loaded('json'), 'label' => 'JSON', 'hint' => ''],
        ['ok' => function_exists('password_hash'), 'label' => 'password_hash()', 'hint' => ''],
    ];
    $writable = is_dir($dataDir) ? is_writable($dataDir) : @mkdir($dataDir, 0755, true);
    $checks[] = ['ok' => $writable, 'label' => 'Writable data/', 'hint' => ''];
    $uploads = $appRoot . '/uploads';
    $upOk = is_dir($uploads) ? is_writable($uploads) : @mkdir($uploads, 0755, true);
    $checks[] = ['ok' => $upOk, 'label' => 'Writable uploads/', 'hint' => ''];
    return $checks;
}

function sh_install_prefix_safe(string $prefix): string
{
    $prefix = preg_replace('/[^a-z0-9_]/i', '', $prefix) ?? 'sh_';
    return $prefix !== '' ? $prefix : 'sh_';
}

function sh_install_connect(string $host, string $database, string $user, string $pass): array
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

function sh_install_write_config(string $dataDir, array $cfg): bool
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

function sh_install_run_schema(PDO $pdo, string $prefix): void
{
    $sql = file_get_contents(__DIR__ . '/schema.sql') ?: '';
    $sql = str_replace('{prefix}', $prefix, $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
}

function sh_install_import_demo(PDO $pdo, string $prefix, array $demo): void
{
    $tables = [
        ['products', 'id', false],
        ['categories', 'slug', true],
        ['news', 'id', false],
        ['leads', 'id', false],
    ];
    foreach ($tables as [$name, $idKey, $hasSort]) {
        $tbl = $prefix . $name;
        $pdo->exec('DELETE FROM `' . $tbl . '`');
        $items = $demo[$name] ?? [];
        if (!is_array($items) || $items === []) {
            continue;
        }
        $cols = $hasSort ? '(`id`, `data`, `sort_order`)' : '(`id`, `data`)';
        $vals = $hasSort ? '(?, ?, ?)' : '(?, ?)';
        $stmt = $pdo->prepare('INSERT INTO `' . $tbl . '` ' . $cols . ' VALUES ' . $vals);
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = trim((string) ($item[$idKey] ?? $item['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $json = json_encode($item, JSON_UNESCAPED_UNICODE);
            if ($hasSort) {
                $stmt->execute([$id, $json, max(0, (int) ($item['sort'] ?? 99))]);
            } else {
                $stmt->execute([$id, $json]);
            }
        }
    }
    $settings = $demo['settings'] ?? [];
    if (is_array($settings) && $settings !== []) {
        $stmt = $pdo->prepare('INSERT INTO `' . $prefix . 'settings` (id, data) VALUES (1, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)');
        $stmt->execute([json_encode($settings, JSON_UNESCAPED_UNICODE)]);
    }
}

function sh_install_run(array $post, string $appRoot, string $dataDir, string $lockFile, string $seedFile): array
{
    $host = trim((string) ($post['db_host'] ?? 'localhost'));
    $database = trim((string) ($post['db_name'] ?? ''));
    $user = trim((string) ($post['db_user'] ?? ''));
    $pass = (string) ($post['db_pass'] ?? '');
    $prefix = sh_install_prefix_safe((string) ($post['db_prefix'] ?? 'sh_'));
    $adminUser = trim((string) ($post['admin_user'] ?? 'admin'));
    $adminPass = (string) ($post['admin_pass'] ?? '');

    if ($database === '' || $user === '') {
        return ['ok' => false, 'error' => 'Вкажіть ім\'я бази та користувача MySQL.'];
    }
    if ($adminUser === '' || strlen($adminPass) < 6) {
        return ['ok' => false, 'error' => 'Логін адміна та пароль (мін. 6 символів) обов\'язкові.'];
    }
    if (!is_readable($seedFile)) {
        return ['ok' => false, 'error' => 'Відсутній файл демо-даних seed/load.php'];
    }

    $conn = sh_install_connect($host, $database, $user, $pass);
    if (!$conn['ok'] || !$conn['pdo'] instanceof PDO) {
        return ['ok' => false, 'error' => 'Помилка підключення до MySQL: ' . $conn['error']];
    }

    if (!sh_install_write_config($dataDir, [
        'host' => $host, 'database' => $database, 'user' => $user, 'pass' => $pass, 'prefix' => $prefix,
    ])) {
        return ['ok' => false, 'error' => 'Не вдалося записати data/db.config.php'];
    }

    $pdo = $conn['pdo'];
    try {
        sh_install_run_schema($pdo, $prefix);
        $demo = require $seedFile;
        sh_install_import_demo($pdo, $prefix, $demo);
    } catch (Throwable $e) {
        @unlink($dataDir . '/db.config.php');
        return ['ok' => false, 'error' => 'Помилка імпорту: ' . $e->getMessage()];
    }

    $adminPhp = "<?php\nreturn [\n"
        . "    'user' => " . var_export($adminUser, true) . ",\n"
        . "    'pass_hash' => " . var_export(password_hash($adminPass, PASSWORD_DEFAULT), true) . ",\n"
        . "];\n";
    if (file_put_contents($dataDir . '/admin.config.php', $adminPhp) === false) {
        return ['ok' => false, 'error' => 'Не вдалося записати admin.config.php'];
    }

    file_put_contents($lockFile, gmdate('c') . "\nShop CMS " . SH_INSTALL_VERSION . " — MySQL\n© Ruslan Bilohash\n");

    return ['ok' => true, 'error' => '', 'admin_user' => $adminUser];
}

$installed = is_file($lockFile);
$requirements = sh_install_requirements($dataDir, $appRoot);
$reqOk = !in_array(false, array_column($requirements, 'ok'), true);
$error = '';
$success = null;

if (!$installed && $reqOk && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $success = sh_install_run($_POST, $appRoot, $dataDir, $lockFile, $seedFile);
    if (!empty($success['ok'])) {
        $installed = true;
    } else {
        $error = $success['error'] ?? 'Installation failed.';
        $success = null;
    }
}

$self = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/install.php');
$base = rtrim(str_replace('\\', '/', dirname($self)), '/');
$shopUrl = $base === '' ? '/' : $base . '/';
?><!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Shop CMS — Установка</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        :root { --bg:#0b1220; --card:#111b2e; --text:#e2e8f0; --muted:#94a3b8; --p:#3b82f6; --ok:#22c55e; --err:#f87171; --border:rgba(148,163,184,.18); }
        *{box-sizing:border-box} body{margin:0;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;color:var(--text);
        background:radial-gradient(900px 500px at 0 -10%,rgba(59,130,246,.22),transparent),var(--bg);padding:24px 16px 48px}
        .wrap{max-width:720px;margin:0 auto} .hero{text-align:center;margin-bottom:24px}
        .logo{width:72px;height:72px;margin:0 auto 12px;border-radius:20px;background:linear-gradient(135deg,var(--p),#6366f1);
        display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff}
        h1{margin:0 0 8px;font-size:1.7rem} .sub{margin:0;color:var(--muted);font-size:15px;line-height:1.5}
        .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:16px}
        .card h2{margin:0 0 14px;font-size:1.05rem;display:flex;align-items:center;gap:8px}
        .req{display:flex;justify-content:space-between;padding:8px 10px;border-radius:8px;background:rgba(15,23,42,.5);margin-bottom:6px;font-size:14px}
        .ok{color:var(--ok)} .bad{color:var(--err)}
        .grid{display:grid;gap:12px} @media(min-width:560px){.g2{grid-template-columns:1fr 1fr}}
        label{display:block;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em}
        input{width:100%;padding:12px;border-radius:10px;border:1px solid var(--border);background:#0b1220;color:var(--text);font-size:15px}
        .btn{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--p),#2563eb);
        color:#fff;font-weight:700;font-size:16px;cursor:pointer;margin-top:8px}
        .btn:disabled{opacity:.5;cursor:not-allowed}
        .alert{padding:12px;border-radius:10px;margin-bottom:12px;font-size:14px}
        .alert-e{background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#fecaca}
        .alert-ok{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#bbf7d0}
        .links{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
        .link{flex:1;min-width:130px;text-align:center;padding:12px;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px}
        .lp{background:var(--p);color:#fff} .lo{border:1px solid var(--border);color:var(--text)}
        .foot{text-align:center;margin-top:24px;color:var(--muted);font-size:12px;line-height:1.6}
        .foot a{color:#93c5fd;text-decoration:none} .hint{font-size:12px;color:var(--muted);margin-top:6px}
        ul.steps{margin:0;padding-left:20px;color:var(--muted);font-size:14px;line-height:1.7}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="logo"><i class="fas fa-store"></i></div>
        <h1>Shop CMS</h1>
        <p class="sub">Установка · MySQL · демо-каталог · v<?= sh_install_h(SH_INSTALL_VERSION) ?></p>
    </div>

    <?php if ($installed && $success): ?>
    <div class="card">
        <div class="alert alert-ok"><i class="fas fa-check-circle"></i> Установку завершено!</div>
        <p>Демо-дані імпортовано в MySQL. Вхід в адмінку:</p>
        <p><strong><?= sh_install_h((string) ($success['admin_user'] ?? 'admin')) ?></strong> / ваш пароль</p>
        <div class="links">
            <a class="link lp" href="<?= sh_install_h($shopUrl) ?>"><i class="fas fa-store"></i> Відкрити магазин</a>
            <a class="link lo" href="<?= sh_install_h($shopUrl . 'admin/') ?>"><i class="fas fa-lock"></i> Адмін-панель</a>
        </div>
        <p class="hint" style="margin-top:14px">З міркувань безпеки видаліть або перейменуйте <code>install.php</code> після установки.</p>
    </div>
    <?php elseif ($installed): ?>
    <div class="card">
        <div class="alert alert-ok">Магазин уже встановлено.</div>
        <div class="links">
            <a class="link lp" href="<?= sh_install_h($shopUrl) ?>">Магазин</a>
            <a class="link lo" href="<?= sh_install_h($shopUrl . 'admin/') ?>">Адмін</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <h2><i class="fas fa-server"></i> Перевірка сервера</h2>
        <?php foreach ($requirements as $r): ?>
        <div class="req">
            <span><?= sh_install_h($r['label']) ?></span>
            <span class="<?= $r['ok'] ? 'ok' : 'bad' ?>"><?= $r['ok'] ? '✓' : '✗' ?> <?= sh_install_h($r['hint']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <form method="post" class="card">
        <h2><i class="fas fa-database"></i> База даних MySQL</h2>
        <?php if ($error !== ''): ?><div class="alert alert-e"><?= sh_install_h($error) ?></div><?php endif; ?>
        <div class="grid g2">
            <div><label for="db_host">Хост</label><input id="db_host" name="db_host" value="<?= sh_install_h($_POST['db_host'] ?? 'localhost') ?>" required></div>
            <div><label for="db_name">База даних</label><input id="db_name" name="db_name" value="<?= sh_install_h($_POST['db_name'] ?? '') ?>" required placeholder="shop_db"></div>
            <div><label for="db_user">Користувач</label><input id="db_user" name="db_user" value="<?= sh_install_h($_POST['db_user'] ?? '') ?>" required></div>
            <div><label for="db_pass">Пароль</label><input id="db_pass" name="db_pass" type="password" autocomplete="new-password"></div>
            <div><label for="db_prefix">Префікс таблиць</label><input id="db_prefix" name="db_prefix" value="<?= sh_install_h($_POST['db_prefix'] ?? 'sh_') ?>" maxlength="16"></div>
        </div>
        <h2 style="margin-top:20px"><i class="fas fa-user-shield"></i> Адміністратор</h2>
        <div class="grid g2">
            <div><label for="admin_user">Логін</label><input id="admin_user" name="admin_user" value="<?= sh_install_h($_POST['admin_user'] ?? 'admin') ?>" required></div>
            <div><label for="admin_pass">Пароль</label><input id="admin_pass" name="admin_pass" type="password" minlength="6" required autocomplete="new-password"><p class="hint">Мінімум 6 символів</p></div>
        </div>
        <p class="hint" style="margin-top:12px">Автоматично імпортуються: товари, категорії, новини, налаштування демо-магазину.</p>
        <button type="submit" class="btn" <?= $reqOk ? '' : 'disabled' ?>><i class="fas fa-rocket"></i> Встановити Shop CMS</button>
    </form>
    <div class="card">
        <h2><i class="fas fa-list-ol"></i> Після покупки</h2>
        <ul class="steps">
            <li>Завантажте папку <strong>install</strong> на хостинг (public_html/shop або окремий домен)</li>
            <li>Створіть базу MySQL у панелі хостингу</li>
            <li>Відкрийте <strong>install.php</strong> у браузері</li>
            <li>Введіть дані БД та логін адміна → готово</li>
        </ul>
    </div>
    <?php endif; ?>
    <p class="foot">© <?= date('Y') ?> <a href="https://bilohash.com/">Ruslan Bilohash</a> — Shop CMS<br>Всі права захищені. Комерційне використання — лише з дозволу автора.</p>
</div>
</body>
</html>