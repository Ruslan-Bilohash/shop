<?php
require_once __DIR__ . '/init.php';
$ta = $t['admin'] ?? [];

if (sh_admin_logged()) {
    header('Location: ' . sh_admin_url('index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (sh_admin_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: ' . sh_admin_url('index.php'));
        exit;
    }
    $error = $ta['login_error'] ?? 'Invalid username or password';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_meta['html']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($ta['login_title'] ?? 'Shop CMS Admin') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/admin.css')) ?>?v=1">
</head>
<body>
<div class="adm-login-wrap">
    <div class="adm-login-box">
        <div class="logo">
            <div class="icon">S</div>
            <h1><?= htmlspecialchars($ta['login_title'] ?? 'Shop CMS Admin') ?></h1>
            <p class="sub"><?= htmlspecialchars($ta['login_sub'] ?? 'Demo administration panel') ?></p>
        </div>
        <div class="adm-demo-hint"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($ta['demo_accounts_title'] ?? 'Demo accounts') ?></div>
        <div class="adm-demo-accounts">
            <button type="button" class="adm-demo-acc adm-demo-acc--owner" data-user="bilohash" data-pass="bilohash2026">
                <i class="fas fa-crown"></i>
                <span><strong><?= htmlspecialchars($ta['demo_role_owner'] ?? 'Administrator (you)') ?></strong><small>bilohash / bilohash2026</small></span>
            </button>
            <button type="button" class="adm-demo-acc adm-demo-acc--demo" data-user="demo" data-pass="demo2026">
                <i class="fas fa-user"></i>
                <span><strong><?= htmlspecialchars($ta['demo_role_staff'] ?? 'Demo user') ?></strong><small>demo / demo2026</small></span>
            </button>
        </div>
        <?php if ($error): ?>
        <div class="adm-login-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="adm-field">
                <label for="username"><?= htmlspecialchars($ta['username'] ?? 'Username') ?></label>
                <input type="text" id="username" name="username" required autocomplete="username" value="demo">
            </div>
            <div class="adm-field">
                <label for="password"><?= htmlspecialchars($ta['password'] ?? 'Password') ?></label>
                <input type="password" id="password" name="password" required autocomplete="current-password" value="demo2026">
            </div>
            <button type="submit" class="adm-btn adm-btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:8px">
                <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($ta['login_btn'] ?? 'Log in') ?>
            </button>
        </form>
        <script>
        document.querySelectorAll('.adm-demo-acc').forEach(function(btn){
            btn.addEventListener('click',function(){
                document.getElementById('username').value=btn.dataset.user||'';
                document.getElementById('password').value=btn.dataset.pass||'';
                var f=document.querySelector('.adm-login-box form');if(f)f.submit();
            });
        });
        </script>
        <p style="text-align:center;margin-top:20px;font-size:12px">
            <a href="<?= sh_url('index.php') ?>">← <?= htmlspecialchars($t['breadcrumb_home'] ?? 'Home') ?></a>
        </p>
        <div class="adm-lang-mini" style="margin-top:16px">
            <?php foreach (sh_langs() as $code => $info): ?>
            <a href="<?= htmlspecialchars(sh_admin_lang_url($code)) ?>" class="<?= $lang === $code ? 'active' : '' ?>" style="color:var(--adm-muted);border-color:var(--adm-border)"><?= $info['flag'] ?> <?= $info['label'] ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>