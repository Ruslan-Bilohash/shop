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
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/admin.css')) ?>?v=42">
    <link rel="stylesheet" href="<?= htmlspecialchars(bh_cms_admin_settings_css_href()) ?>">
    <?php foreach (($extra_css ?? []) as $cssHref): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars((string) $cssHref) ?>">
    <?php endforeach; ?>
    <?php if (($settings_tab ?? '') === 'invoice'): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/invoice-print-designs.css')) ?>?v=2">
    <link rel="stylesheet" href="<?= htmlspecialchars(sh_asset('css/invoice-print.css')) ?>?v=2">
    <?php endif; ?>
    <?php if (($settings_tab ?? '') === 'homepage' || ($settings_tab ?? '') === 'block_builder' || ($settings_tab ?? '') === 'advanced' || ($admin_page ?? '') === 'code-editor'): ?>
    <?php require __DIR__ . '/code-editor-assets.php'; ?>
    <?php endif; ?>
</head>
<body class="adm-body<?= ($admin_page ?? '') === 'code-editor' ? ' adm-body--code-editor' : '' ?>">
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
                <?php if (sh_admin_logged()): ?>
                <span class="adm-role-badge adm-role-badge--<?= htmlspecialchars(sh_admin_role()) ?>" title="<?= htmlspecialchars(sh_admin_display_name()) ?>">
                    <i class="fas fa-<?= sh_admin_is_owner() ? 'crown' : 'user' ?>"></i>
                    <?= htmlspecialchars(sh_admin_display_name()) ?>
                </span>
                <?php if (function_exists('sh_admin_is_owner') && sh_admin_is_owner()): ?>
                <?php $opTb = $ta['owner_page'] ?? []; ?>
                <a href="<?= sh_admin_url('owner.php') ?>" class="adm-api-badge adm-api-badge--topbar adm-api-badge--owner" title="<?= htmlspecialchars($opTb['api_hint'] ?? 'Unlimited BILOHASH AI API for owner') ?>">
                    <i class="fas fa-bolt"></i>
                    <?= htmlspecialchars($opTb['api_topbar'] ?? 'AI: ∞') ?>
                </a>
                <?php elseif (function_exists('sh_admin_is_demo_user') && sh_admin_is_demo_user()): ?>
                <?php
                require_once dirname(__DIR__, 2) . '/includes/admin-api-usage.php';
                $apiLimit = sh_admin_api_limit();
                $apiRemaining = sh_admin_api_remaining();
                $apiUsed = max(0, $apiLimit - ($apiRemaining >= 0 ? $apiRemaining : $apiLimit));
                ?>
                <span class="adm-api-badge adm-api-badge--topbar" title="<?= htmlspecialchars($ta['api_quota_hint'] ?? 'Demo AI API test quota') ?>">
                    <i class="fas fa-bolt"></i>
                    <?= htmlspecialchars(strtr($ta['api_quota'] ?? 'API: {used}/{limit}', [
                        '{used}'  => (string) $apiUsed,
                        '{limit}' => (string) $apiLimit,
                    ])) ?>
                </span>
                <?php endif; ?>
                <?php
                $aiWidgetTa = is_array($ta['ai_agent_widget'] ?? null) ? $ta['ai_agent_widget'] : [];
                if (($admin_page ?? '') !== 'ai-agent' && ($aiWidgetTa['enabled'] ?? true) !== false):
                ?>
                <button type="button" class="adm-topbar-ai-btn" id="shAiAgentTopbarBtn"
                        aria-expanded="false" aria-controls="shAiAgentWidget"
                        title="<?= htmlspecialchars($aiWidgetTa['fab_title'] ?? 'AI Advisor') ?>">
                    <i class="fas fa-robot" aria-hidden="true"></i>
                    <span class="adm-topbar-ai-label"><?= htmlspecialchars($aiWidgetTa['title_short'] ?? ($aiWidgetTa['title'] ?? 'AI Advisor')) ?></span>
                </button>
                <?php endif; ?>
                <?php endif; ?>
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
        <?php
        require_once __DIR__ . '/admin-flash.php';
        sh_admin_render_flash_toast(sh_admin_flash_resolve($ta), $ta);
        require_once dirname(__DIR__, 2) . '/includes/billing-pricing.php';
        sh_billing_render_admin_banner($ta, $lang);
        ?>