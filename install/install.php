<?php
/**
 * Shop CMS — MySQL installer
 * Copyright (c) 2024–2026 Ruslan Bilohash — https://bilohash.com
 */
declare(strict_types=1);

const SH_INSTALL_VERSION = '1.4.1';

$appRoot = dirname(__DIR__);
$dataDir = $appRoot . '/data';
$lockFile = $dataDir . '/installed.lock';
$seedDir = __DIR__ . '/seed';

$seedFiles = ['products', 'categories', 'news', 'settings'];

function sh_install_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function sh_install_requirements(): array
{
    $checks = [
        ['ok' => version_compare(PHP_VERSION, '8.0.0', '>='), 'label' => 'PHP 8.0+', 'hint' => 'PHP ' . PHP_VERSION],
        ['ok' => extension_loaded('pdo'), 'label' => 'PDO extension', 'hint' => ''],
        ['ok' => extension_loaded('pdo_mysql'), 'label' => 'PDO MySQL', 'hint' => ''],
        ['ok' => extension_loaded('json'), 'label' => 'JSON', 'hint' => ''],
        ['ok' => function_exists('password_hash'), 'label' => 'password_hash()', 'hint' => ''],
    ];
    global $dataDir;
    $writable = is_dir($dataDir) ? is_writable($dataDir) : @mkdir($dataDir, 0755, true);
    $checks[] = ['ok' => $writable, 'label' => 'Writable data/ folder', 'hint' => $dataDir];
    return $checks;
}

function sh_install_prefix_safe(string $prefix): string
{
    $prefix = preg_replace('/[^a-z0-9_]/i', '', $prefix) ?? 'sh_';
    return $prefix !== '' ? $prefix : 'sh_';
}

/** @return array{ok:bool,error:string,pdo:?PDO} */
function sh_install_connect(array $cfg): array
{
    try {
        $dsn = 'mysql:host=' . $cfg['host'] . ';dbname=' . $cfg['database'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return ['ok' => true, 'error' => '', 'pdo' => $pdo];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage(), 'pdo' => null];
    }
}

function sh_install_run(array $post): array
{
    global $appRoot, $dataDir, $lockFile, $seedDir, $seedFiles;

    $host = trim((string) ($post['db_host'] ?? 'localhost'));
    $database = trim((string) ($post['db_name'] ?? ''));
    $user = trim((string) ($post['db_user'] ?? ''));
    $pass = (string) ($post['db_pass'] ?? '');
    $prefix = sh_install_prefix_safe((string) ($post['db_prefix'] ?? 'sh_'));
    $adminUser = trim((string) ($post['admin_user'] ?? 'admin'));
    $adminPass = (string) ($post['admin_pass'] ?? '');

    if ($database === '' || $user === '') {
        return ['ok' => false, 'error' => 'Database name and user are required.'];
    }
    if ($adminUser === '' || strlen($adminPass) < 6) {
        return ['ok' => false, 'error' => 'Admin username and password (min 6 chars) are required.'];
    }

    $conn = sh_install_connect([
        'host' => $host,
        'database' => $database,
        'user' => $user,
        'pass' => $pass,
    ]);
    if (!$conn['ok'] || !$conn['pdo'] instanceof PDO) {
        return ['ok' => false, 'error' => 'Database connection failed: ' . $conn['error']];
    }
    $pdo = $conn['pdo'];
    $table = $prefix . 'store';

    $schema = file_get_contents(__DIR__ . '/schema.sql') ?: '';
    $schema = str_replace('{prefix}', $prefix, $schema);
    $pdo->exec($schema);

    $stmt = $pdo->prepare(
        'INSERT INTO `' . $table . '` (store_key, content) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE content = VALUES(content)'
    );

    foreach ($seedFiles as $key) {
        $seedPath = $seedDir . '/' . $key . '.json';
        if (!is_readable($seedPath)) {
            return ['ok' => false, 'error' => 'Missing seed file: ' . $key . '.json'];
        }
        $content = file_get_contents($seedPath);
        if ($content === false || json_decode($content, true) === null) {
            return ['ok' => false, 'error' => 'Invalid JSON in seed: ' . $key . '.json'];
        }
        $stmt->execute([$key, $content]);
        file_put_contents($dataDir . '/' . $key . '.json', $content);
    }

    $dbConfig = "<?php\nreturn [\n"
        . "    'host' => " . var_export($host, true) . ",\n"
        . "    'database' => " . var_export($database, true) . ",\n"
        . "    'user' => " . var_export($user, true) . ",\n"
        . "    'pass' => " . var_export($pass, true) . ",\n"
        . "    'prefix' => " . var_export($prefix, true) . ",\n"
        . "];\n";

    if (file_put_contents($dataDir . '/db.config.php', $dbConfig) === false) {
        return ['ok' => false, 'error' => 'Could not write data/db.config.php'];
    }

    $adminConfig = "<?php\nreturn [\n"
        . "    'user' => " . var_export($adminUser, true) . ",\n"
        . "    'pass_hash' => " . var_export(password_hash($adminPass, PASSWORD_DEFAULT), true) . ",\n"
        . "];\n";

    if (file_put_contents($dataDir . '/admin.config.php', $adminConfig) === false) {
        return ['ok' => false, 'error' => 'Could not write data/admin.config.php'];
    }

    file_put_contents($lockFile, gmdate('c') . "\nShop CMS " . SH_INSTALL_VERSION . " installed with MySQL.\n");

    return [
        'ok' => true,
        'error' => '',
        'admin_user' => $adminUser,
        'table' => $table,
    ];
}

$installed = is_file($lockFile);
$requirements = sh_install_requirements();
$reqOk = !in_array(false, array_column($requirements, 'ok'), true);
$error = '';
$success = null;

if (!$installed && $reqOk && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $success = sh_install_run($_POST);
    if (!empty($success['ok'])) {
        $installed = true;
    } else {
        $error = $success['error'] ?? 'Installation failed.';
        $success = null;
    }
}

$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/install/install.php')), '/');
$shopUrl = preg_replace('#/install$#', '', $baseUrl) ?: '/';
?><!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Shop CMS Install</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        :root {
            --bg: #0b1220;
            --card: #111b2e;
            --card2: #0f172a;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --primary: #3b82f6;
            --primary2: #2563eb;
            --ok: #22c55e;
            --err: #f87171;
            --border: rgba(148, 163, 184, .18);
            --radius: 16px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text);
            background: radial-gradient(1200px 600px at 10% -10%, rgba(59,130,246,.25), transparent),
                        radial-gradient(900px 500px at 90% 0%, rgba(99,102,241,.18), transparent),
                        var(--bg);
            padding: 24px 16px 48px;
        }
        .wrap { max-width: 720px; margin: 0 auto; }
        .hero { text-align: center; margin-bottom: 28px; }
        .logo {
            width: 72px; height: 72px; margin: 0 auto 14px; border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #fff; box-shadow: 0 12px 40px rgba(37,99,235,.35);
        }
        h1 { margin: 0 0 8px; font-size: 1.75rem; letter-spacing: -.02em; }
        .sub { margin: 0; color: var(--muted); font-size: 15px; line-height: 1.5; }
        .card {
            background: linear-gradient(180deg, var(--card), var(--card2));
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 24px; margin-bottom: 18px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }
        .card h2 { margin: 0 0 16px; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; }
        .req-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .req-list li {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 10px 12px; border-radius: 10px; background: rgba(15,23,42,.6); font-size: 14px;
        }
        .req-ok { color: var(--ok); }
        .req-bad { color: var(--err); }
        .grid { display: grid; gap: 14px; }
        @media (min-width: 560px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
        label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        input {
            width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border);
            background: #0b1220; color: var(--text); font-size: 15px;
        }
        input:focus { outline: 2px solid rgba(59,130,246,.45); border-color: var(--primary); }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 14px 20px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            color: #fff; font-weight: 700; font-size: 16px; cursor: pointer;
            box-shadow: 0 10px 30px rgba(37,99,235,.35);
        }
        .btn:disabled { opacity: .55; cursor: not-allowed; box-shadow: none; }
        .alert { padding: 12px 14px; border-radius: 10px; font-size: 14px; margin-bottom: 14px; }
        .alert-err { background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.35); color: #fecaca; }
        .alert-ok { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.35); color: #bbf7d0; }
        .links { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
        .link {
            flex: 1; min-width: 140px; text-align: center; text-decoration: none;
            padding: 12px 16px; border-radius: 10px; font-weight: 600; font-size: 14px;
        }
        .link-primary { background: var(--primary); color: #fff; }
        .link-outline { border: 1px solid var(--border); color: var(--text); }
        .foot { text-align: center; margin-top: 28px; color: var(--muted); font-size: 12px; line-height: 1.6; }
        .foot a { color: #93c5fd; text-decoration: none; }
        .hint { font-size: 12px; color: var(--muted); margin-top: 6px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="logo"><i class="fas fa-store"></i></div>
        <h1>Shop CMS Install</h1>
        <p class="sub">MySQL setup with demo products, categories and news · v<?= sh_install_h(SH_INSTALL_VERSION) ?></p>
    </div>

    <div class="card">
        <h2><i class="fas fa-server"></i> Requirements</h2>
        <ul class="req-list">
            <?php foreach ($requirements as $r): ?>
            <li>
                <span><?= sh_install_h($r['label']) ?></span>
                <span class="<?= $r['ok'] ? 'req-ok' : 'req-bad' ?>">
                    <i class="fas fa-<?= $r['ok'] ? 'check-circle' : 'times-circle' ?>"></i>
                    <?= $r['hint'] !== '' ? sh_install_h($r['hint']) : ($r['ok'] ? 'OK' : 'Fail') ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($installed && $success): ?>
    <div class="card">
        <div class="alert alert-ok"><i class="fas fa-check-circle"></i> Installation completed successfully.</div>
        <p>Demo data imported into MySQL. Admin login:</p>
        <p><strong><?= sh_install_h((string) ($success['admin_user'] ?? 'admin')) ?></strong> / your chosen password</p>
        <div class="links">
            <a class="link link-primary" href="<?= sh_install_h($shopUrl . '/') ?>"><i class="fas fa-store"></i> Open shop</a>
            <a class="link link-outline" href="<?= sh_install_h($shopUrl . '/admin/') ?>"><i class="fas fa-lock"></i> Admin panel</a>
        </div>
        <p class="hint" style="margin-top:16px">For security, delete the <code>install/</code> folder after setup.</p>
    </div>
    <?php elseif ($installed): ?>
    <div class="card">
        <div class="alert alert-ok"><i class="fas fa-info-circle"></i> Shop CMS is already installed.</div>
        <div class="links">
            <a class="link link-primary" href="<?= sh_install_h($shopUrl . '/') ?>">Open shop</a>
            <a class="link link-outline" href="<?= sh_install_h($shopUrl . '/admin/') ?>">Admin</a>
        </div>
    </div>
    <?php else: ?>
    <form method="post" class="card">
        <h2><i class="fas fa-database"></i> Database</h2>
        <?php if ($error !== ''): ?>
        <div class="alert alert-err"><?= sh_install_h($error) ?></div>
        <?php endif; ?>
        <div class="grid grid-2">
            <div>
                <label for="db_host">Host</label>
                <input id="db_host" name="db_host" value="<?= sh_install_h($_POST['db_host'] ?? 'localhost') ?>" required>
            </div>
            <div>
                <label for="db_name">Database</label>
                <input id="db_name" name="db_name" value="<?= sh_install_h($_POST['db_name'] ?? '') ?>" required placeholder="shop_cms">
            </div>
            <div>
                <label for="db_user">User</label>
                <input id="db_user" name="db_user" value="<?= sh_install_h($_POST['db_user'] ?? '') ?>" required>
            </div>
            <div>
                <label for="db_pass">Password</label>
                <input id="db_pass" name="db_pass" type="password" value="" autocomplete="new-password">
            </div>
            <div>
                <label for="db_prefix">Table prefix</label>
                <input id="db_prefix" name="db_prefix" value="<?= sh_install_h($_POST['db_prefix'] ?? 'sh_') ?>" maxlength="16">
            </div>
        </div>

        <h2 style="margin-top:22px"><i class="fas fa-user-shield"></i> Admin account</h2>
        <div class="grid grid-2">
            <div>
                <label for="admin_user">Username</label>
                <input id="admin_user" name="admin_user" value="<?= sh_install_h($_POST['admin_user'] ?? 'admin') ?>" required>
            </div>
            <div>
                <label for="admin_pass">Password</label>
                <input id="admin_pass" name="admin_pass" type="password" minlength="6" required autocomplete="new-password">
                <p class="hint">Minimum 6 characters</p>
            </div>
        </div>

        <p class="hint" style="margin-top:14px">Demo catalog, categories, news and settings will be imported automatically.</p>

        <div style="margin-top:20px">
            <button type="submit" class="btn" <?= $reqOk ? '' : 'disabled' ?>>
                <i class="fas fa-rocket"></i> Install Shop CMS
            </button>
        </div>
    </form>
    <?php endif; ?>

    <p class="foot">
        © <?= date('Y') ?> <a href="https://bilohash.com/">Ruslan Bilohash</a> — Shop CMS<br>
        All rights reserved. Commercial use requires written permission.
    </p>
</div>
</body>
</html>