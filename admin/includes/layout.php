<?php
/** @var string $admin_page @var array $ta @var string $page_title */
$layout_title = $page_title ?? ($ta['dashboard'] ?? 'Admin');
$current_lang_info = sh_langs()[$lang] ?? ['label' => strtoupper($lang), 'name' => $lang, 'flag' => '🌐'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang_meta['html']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($layout_title) ?> — <?= htmlspecialchars($ta['title_suffix'] ?? 'Shop CMS Admin') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/admin.css')) ?>?v=27">
    <link rel="stylesheet" href="<?= htmlspecialchars(bh_cms_admin_settings_css_href()) ?>">
    <?php if (($settings_tab ?? '') === 'homepage' || ($settings_tab ?? '') === 'block_builder' || ($admin_page ?? '') === 'code-editor'): ?>
    <?php require __DIR__ . '/code-editor-assets.php'; ?>
    <?php endif; ?>
</head>
<body class="adm-body">
<div class="adm-sidebar-overlay" id="admOverlay" hidden></div>
<div class="adm-layout">
    <aside class="adm-sidebar" id="admSidebar">
        <a href="<?= sh_admin_url('index.php') ?>" class="adm-sidebar-brand">
            <div class="icon">S</div>
            <div>
                <span>Shop CMS</span>
                <small><?= htmlspecialchars($ta['title'] ?? 'Admin') ?> · <?= htmlspecialchars(sh_version_label()) ?></small>
            </div>
        </a>
        <nav class="adm-nav" aria-label="<?= htmlspecialchars($ta['main_nav'] ?? 'Main navigation') ?>">
            <?php
            require_once __DIR__ . '/admin-menu.php';
            sh_render_admin_sidebar_nav($ta, $admin_page ?? 'dashboard', $settings_tab ?? null);
            ?>
        </nav>
        <div class="adm-sidebar-foot">
            <small class="adm-sidebar-copy">© Shop CMS</small>
        </div>
    </aside>
    <main class="adm-main">
        <header class="adm-topbar">
            <button type="button" class="adm-menu-btn" id="admMenuBtn" aria-label="<?= htmlspecialchars($ta['menu'] ?? 'Menu') ?>"><i class="fas fa-bars"></i></button>
            <h1><?= htmlspecialchars($layout_title) ?></h1>
            <div class="adm-topbar-actions">
                <div class="adm-lang-dropdown" id="admLangDropdown">
                    <button type="button" class="adm-lang-dropdown-btn" id="admLangBtn" aria-haspopup="listbox" aria-expanded="false">
                        <span class="adm-lang-dropdown-current">
                            <span class="adm-lang-flag"><?= $current_lang_info['flag'] ?? '🌐' ?></span>
                            <span class="adm-lang-code"><?= htmlspecialchars($current_lang_info['label'] ?? strtoupper($lang)) ?></span>
                        </span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="adm-lang-dropdown-menu" id="admLangMenu" role="listbox" hidden>
                        <?php foreach (sh_langs() as $code => $info): ?>
                        <a href="<?= htmlspecialchars(sh_admin_lang_url($code)) ?>"
                           class="adm-lang-dropdown-item <?= $lang === $code ? 'is-active' : '' ?>"
                           role="option" aria-selected="<?= $lang === $code ? 'true' : 'false' ?>">
                            <span class="adm-lang-flag"><?= $info['flag'] ?? '🌐' ?></span>
                            <span class="adm-lang-dropdown-name"><?= htmlspecialchars($info['name']) ?></span>
                            <span class="adm-lang-dropdown-code"><?= htmlspecialchars($info['label']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="<?= sh_admin_url('logout.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm adm-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?= htmlspecialchars($ta['logout'] ?? 'Log out') ?></span>
                </a>
            </div>
        </header>
        <div class="adm-content">