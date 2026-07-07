<?php
/**
 * Shop CMS — Migrate JSON edition → MySQL (full transition).
 * Open in browser when data/*.json exists and MySQL is not configured yet.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/mysql-migrate.php';

$appRoot = __DIR__;
$dataDir = $appRoot . '/data';
$lockFile = $dataDir . '/installed.lock';

function sh_migrate_page_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$alreadyMysql = sh_migrate_mysql_installed($dataDir);
$jsonFiles = sh_migrate_json_files_found($dataDir);
$hasJson = $jsonFiles !== [];
$error = '';
$result = null;

if (!$alreadyMysql && $hasJson && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = sh_migrate_json_to_mysql([
        'app_root'         => $appRoot,
        'data_dir'         => $dataDir,
        'lock_file'        => $lockFile,
        'db_host'          => $_POST['db_host'] ?? 'localhost',
        'db_name'          => $_POST['db_name'] ?? '',
        'db_user'          => $_POST['db_user'] ?? '',
        'db_pass'          => $_POST['db_pass'] ?? '',
        'db_prefix'        => $_POST['db_prefix'] ?? 'sh_',
        'backup_json'      => !empty($_POST['backup_json']),
        'quarantine_json'  => !empty($_POST['quarantine_json']),
        'admin_user'       => $_POST['admin_user'] ?? '',
        'admin_pass'       => $_POST['admin_pass'] ?? '',
    ]);
    if (!empty($result['ok'])) {
        $alreadyMysql = true;
    } else {
        $error = $result['error'] ?? 'Migration failed.';
        $result = null;
    }
}

$self = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/migrate-to-mysql.php');
$base = rtrim(str_replace('\\', '/', dirname($self)), '/');
$shopUrl = $base === '' ? '/' : $base . '/';
$hasAdminCfg = is_readable($dataDir . '/admin.config.php');
?><!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Shop CMS — Міграція на MySQL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        :root { --bg:#0b1220; --card:#111b2e; --text:#e2e8f0; --muted:#94a3b8; --p:#8b5cf6; --ok:#22c55e; --err:#f87171; --border:rgba(148,163,184,.18); }
        *{box-sizing:border-box} body{margin:0;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;color:var(--text);
        background:radial-gradient(900px 500px at 100% -10%,rgba(139,92,246,.2),transparent),var(--bg);padding:24px 16px 48px}
        .wrap{max-width:760px;margin:0 auto} .hero{text-align:center;margin-bottom:24px}
        .logo{width:72px;height:72px;margin:0 auto 12px;border-radius:20px;background:linear-gradient(135deg,var(--p),#6366f1);
        display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff}
        h1{margin:0 0 8px;font-size:1.7rem} .sub{margin:0;color:var(--muted);font-size:15px;line-height:1.5}
        .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:16px}
        .card h2{margin:0 0 14px;font-size:1.05rem;display:flex;align-items:center;gap:8px}
        .grid{display:grid;gap:12px} @media(min-width:560px){.g2{grid-template-columns:1fr 1fr}}
        label{display:block;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em}
        input[type=text],input[type=password]{width:100%;padding:12px;border-radius:10px;border:1px solid var(--border);background:#0b1220;color:var(--text);font-size:15px}
        .chk{display:flex;align-items:flex-start;gap:10px;margin:10px 0;font-size:14px;color:var(--muted)}
        .chk input{margin-top:3px}
        .btn{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--p),#7c3aed);
        color:#fff;font-weight:700;font-size:16px;cursor:pointer;margin-top:8px}
        .alert{padding:12px;border-radius:10px;margin-bottom:12px;font-size:14px}
        .alert-e{background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#fecaca}
        .alert-ok{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#bbf7d0}
        .alert-i{background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.3);color:#bfdbfe}
        .files{list-style:none;margin:0;padding:0;font-size:13px;color:var(--muted)}
        .files li{padding:6px 10px;border-radius:8px;background:rgba(15,23,42,.5);margin-bottom:4px}
        .stats{font-size:13px;color:var(--muted);line-height:1.7}
        .links{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
        .link{flex:1;min-width:130px;text-align:center;padding:12px;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px}
        .lp{background:var(--p);color:#fff} .lo{border:1px solid var(--border);color:var(--text)}
        .foot{text-align:center;margin-top:24px;color:var(--muted);font-size:12px}
        .hint{font-size:12px;color:var(--muted);margin-top:6px}
        code{background:rgba(15,23,42,.6);padding:2px 6px;border-radius:4px;font-size:12px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="logo"><i class="fas fa-database"></i></div>
        <h1>Міграція на MySQL</h1>
        <p class="sub">Повний перехід Shop CMS з JSON-файлів на MySQL · v<?= sh_migrate_page_h(SH_MIGRATE_VERSION) ?></p>
    </div>

    <?php if ($alreadyMysql && $result): ?>
    <div class="card">
        <div class="alert alert-ok"><i class="fas fa-check-circle"></i> Міграцію завершено! Магазин працює на MySQL.</div>
        <?php if (!empty($result['stats'])): ?>
        <div class="stats">
            <strong>Імпортовано:</strong>
            <ul>
                <?php foreach ($result['stats'] as $name => $st): ?>
                <li><?= sh_migrate_page_h($name) ?>: <?= (int) ($st['imported'] ?? 0) ?> записів</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (!empty($result['backup_dir'])): ?>
        <p class="hint">Резервна копія JSON: <code><?= sh_migrate_page_h(basename(dirname($result['backup_dir'])) . '/' . basename($result['backup_dir'])) ?></code></p>
        <?php endif; ?>
        <div class="links">
            <a class="link lp" href="<?= sh_migrate_page_h($shopUrl) ?>"><i class="fas fa-store"></i> Магазин</a>
            <a class="link lo" href="<?= sh_migrate_page_h($shopUrl . 'admin/') ?>"><i class="fas fa-lock"></i> Адмін</a>
        </div>
        <p class="hint" style="margin-top:14px">Видаліть <code>migrate-to-mysql.php</code> після перевірки. JSON-файли більше не використовуються.</p>
    </div>

    <?php elseif ($alreadyMysql): ?>
    <div class="card">
        <div class="alert alert-ok">MySQL-редакція вже встановлена (<code>installed.lock</code>).</div>
        <div class="links">
            <a class="link lp" href="<?= sh_migrate_page_h($shopUrl) ?>">Магазин</a>
            <a class="link lo" href="<?= sh_migrate_page_h($shopUrl . 'admin/') ?>">Адмін</a>
        </div>
    </div>

    <?php elseif (!$hasJson): ?>
    <div class="card">
        <div class="alert alert-i"><i class="fas fa-info-circle"></i> JSON-дані не знайдено в <code>data/</code>.</div>
        <p class="hint">Для чистої установки MySQL відкрийте <a href="<?= sh_migrate_page_h($shopUrl . 'install.php') ?>" style="color:#c4b5fd">install.php</a>.</p>
        <p class="hint">Очікувані файли: <code>settings.json</code>, <code>products.json</code>, <code>categories.json</code> тощо.</p>
    </div>

    <?php else: ?>
    <div class="card">
        <h2><i class="fas fa-file-code"></i> Знайдені JSON-файли</h2>
        <ul class="files">
            <?php foreach ($jsonFiles as $f): ?>
            <li><i class="fas fa-check ok" style="color:#22c55e"></i> <?= sh_migrate_page_h($f) ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="hint">Усі дані будуть імпортовані в MySQL. Рекомендується резервна копія перед міграцією.</p>
    </div>

    <form method="post" class="card">
        <h2><i class="fas fa-database"></i> Підключення MySQL</h2>
        <?php if ($error !== ''): ?><div class="alert alert-e"><?= sh_migrate_page_h($error) ?></div><?php endif; ?>
        <div class="grid g2">
            <div><label for="db_host">Хост</label><input id="db_host" name="db_host" value="<?= sh_migrate_page_h($_POST['db_host'] ?? 'localhost') ?>" required></div>
            <div><label for="db_name">База даних</label><input id="db_name" name="db_name" value="<?= sh_migrate_page_h($_POST['db_name'] ?? '') ?>" required placeholder="shop_db"></div>
            <div><label for="db_user">Користувач</label><input id="db_user" name="db_user" value="<?= sh_migrate_page_h($_POST['db_user'] ?? '') ?>" required></div>
            <div><label for="db_pass">Пароль</label><input id="db_pass" name="db_pass" type="password" autocomplete="new-password"></div>
            <div><label for="db_prefix">Префікс таблиць</label><input id="db_prefix" name="db_prefix" value="<?= sh_migrate_page_h($_POST['db_prefix'] ?? 'sh_') ?>" maxlength="16"></div>
        </div>

        <?php if (!$hasAdminCfg): ?>
        <h2 style="margin-top:20px"><i class="fas fa-user-shield"></i> Адмін (опційно)</h2>
        <p class="hint">Якщо <code>admin.config.php</code> відсутній — задайте логін/пароль для адмінки.</p>
        <div class="grid g2">
            <div><label for="admin_user">Логін</label><input id="admin_user" name="admin_user" value="<?= sh_migrate_page_h($_POST['admin_user'] ?? 'admin') ?>"></div>
            <div><label for="admin_pass">Пароль</label><input id="admin_pass" name="admin_pass" type="password" minlength="6" autocomplete="new-password"></div>
        </div>
        <?php else: ?>
        <p class="hint" style="margin-top:12px"><i class="fas fa-check"></i> <code>admin.config.php</code> збережено — логін адміна не змінюється.</p>
        <?php endif; ?>

        <label class="chk"><input type="checkbox" name="backup_json" value="1" checked> Створити резервну копію JSON у <code>data/archive/</code></label>
        <label class="chk"><input type="checkbox" name="quarantine_json" value="1" checked> Перейменувати JSON-файли в <code>*.json.migrated</code> після успіху</label>

        <button type="submit" class="btn"><i class="fas fa-arrow-right"></i> Мігрувати на MySQL</button>
    </form>
    <?php endif; ?>

    <p class="foot">© <?= date('Y') ?> Ruslan Bilohash — Shop CMS</p>
</div>
</body>
</html>