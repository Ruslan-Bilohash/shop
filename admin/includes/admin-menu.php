<?php
/**
 * WordPress-style categorized admin sidebar menu.
 */
require_once dirname(__DIR__, 2) . '/includes/site-settings.php';

/** @return list<array<string, mixed>> */
function sh_admin_sidebar_menu(): array
{
    $tabs = sh_settings_tabs();
    $groups = sh_settings_tab_groups();

    $menu = [
        [
            'id'   => 'dashboard',
            'type' => 'link',
            'label_key' => 'dashboard',
            'icon' => 'chart-pie',
            'url'  => 'index.php',
            'page' => 'dashboard',
        ],
    ];

    $catalogItems = [
        ['label_key' => 'products', 'icon' => 'box', 'url' => 'products.php', 'page' => 'products'],
        ['label_key' => 'products_io', 'icon' => 'file-import', 'url' => 'products-io.php', 'page' => 'products-io'],
        ['label_key' => 'categories', 'icon' => 'layer-group', 'url' => 'categories.php', 'page' => 'categories'],
        ['label_key' => 'quick_leads', 'icon' => 'bolt', 'url' => 'quick-leads.php', 'page' => 'quick-leads', 'badge' => 'leads'],
        ['label_key' => 'orders', 'icon' => 'receipt', 'url' => 'orders.php', 'page' => 'orders', 'badge' => 'orders'],
        ['label_key' => 'subscribers', 'icon' => 'paper-plane', 'url' => 'subscribers.php', 'page' => 'subscribers'],
    ];

    $menu[] = [
        'id'        => 'catalog',
        'type'      => 'group',
        'label_key' => 'nav_group_catalog',
        'icon'      => 'boxes-stacked',
        'items'     => $catalogItems,
    ];

    $menu[] = [
        'id'   => 'news',
        'type' => 'link',
        'label_key' => 'news',
        'icon' => 'newspaper',
        'url'  => 'news.php',
        'page' => 'news',
    ];

    $menu[] = [
        'id'   => 'google-analytics',
        'type' => 'link',
        'label_key' => 'google_analytics_menu',
        'icon' => 'chart-line',
        'url'  => 'settings-analytics.php',
        'page' => 'settings',
        'settings_tab' => 'analytics',
    ];

    $menu[] = [
        'id'   => 'ai-agent',
        'type' => 'link',
        'label_key' => 'ai_agent_console',
        'icon' => 'robot',
        'url'  => 'ai-agent.php',
        'page' => 'ai-agent',
    ];

    $menu[] = [
        'id'   => 'design-demos',
        'type' => 'link',
        'label_key' => 'design_demos_console',
        'icon' => 'swatchbook',
        'url'  => 'design-demos.php',
        'page' => 'design-demos',
    ];

    $menu[] = [
        'id'   => 'billing-demo',
        'type' => 'link',
        'label_key' => 'billing_demo_console',
        'icon' => 'credit-card',
        'url'  => 'billing-demo.php',
        'page' => 'billing-demo',
    ];

    $settingsOnlyGroups = ['shop', 'content', 'design', 'marketing', 'integrations', 'advanced'];
    foreach ($settingsOnlyGroups as $gkey) {
        if (!isset($groups[$gkey])) {
            continue;
        }
        $group = $groups[$gkey];
        $items = [];
        foreach ($group['tabs'] as $tabKey) {
            if (!isset($tabs[$tabKey])) {
                continue;
            }
            $tab = $tabs[$tabKey];
            $items[] = [
                'label_key'    => 'settings_tab_' . $tabKey,
                'icon'         => $tab['icon'],
                'url'          => $tab['file'],
                'settings_tab' => $tabKey,
            ];
        }
        if ($items === []) {
            continue;
        }
        $menu[] = [
            'id'        => $gkey,
            'type'      => 'group',
            'label_key' => $group['label_key'],
            'icon'      => $group['icon'],
            'items'     => $items,
        ];
    }

    $menu[] = [
        'id'        => 'view',
        'type'      => 'group',
        'label_key' => 'nav_group_view',
        'icon'      => 'eye',
        'items'     => [
            ['label_key' => 'view_catalog', 'icon' => 'store', 'href' => 'search.php', 'external' => true],
            ['label_key' => 'view_site', 'icon' => 'external-link-alt', 'href' => 'index.php', 'external' => true],
        ],
    ];

    $menu[] = [
        'id'   => 'code-editor',
        'type' => 'link',
        'label_key' => 'code_editor',
        'icon' => 'code',
        'url'  => 'code-editor.php',
        'page' => 'code-editor',
    ];

    $mysqlConsole = dirname(__DIR__, 2) . '/includes/mysql-console.php';
    if (is_file($mysqlConsole)) {
        require_once $mysqlConsole;
        if (function_exists('sh_mysql_storage_available') && sh_mysql_storage_available()) {
            $menu[] = [
                'id'   => 'mysql-console',
                'type' => 'link',
                'label_key' => 'mysql_console',
                'icon' => 'database',
                'url'  => 'mysql-console.php',
                'page' => 'mysql-console',
            ];
        }
    }

    $consoleItems = [];
    $healthInc = dirname(__DIR__, 2) . '/includes/site-health-console.php';
    if (is_file($healthInc)) {
        $consoleItems[] = [
            'label_key' => 'health_console',
            'icon'      => 'heart-pulse',
            'url'       => 'health-console.php',
            'page'      => 'health-console',
        ];
    }
    $consoleItems[] = [
        'label_key' => 'seo_agent_console',
        'icon'      => 'robot',
        'url'       => 'seo-agent-console.php',
        'page'      => 'seo-agent-console',
    ];
    $secConsole = dirname(__DIR__, 2) . '/includes/security-console.php';
    if (is_file($secConsole)) {
        $consoleItems[] = [
            'label_key' => 'security_console',
            'icon'      => 'shield-halved',
            'url'       => 'security-console.php',
            'page'      => 'security-console',
        ];
    }
    $changelogInc = dirname(__DIR__, 2) . '/includes/changelog-console.php';
    if (is_file($changelogInc)) {
        $consoleItems[] = [
            'label_key' => 'changelog_console',
            'icon'      => 'clock-rotate-left',
            'url'       => 'changelog-console.php',
            'page'      => 'changelog-console',
        ];
    }
    if ($consoleItems !== []) {
        $menu[] = [
            'id'        => 'consoles',
            'type'      => 'group',
            'label_key' => 'nav_group_consoles',
            'icon'      => 'gauge-high',
            'items'     => $consoleItems,
        ];
    }

    return $menu;
}

function sh_admin_menu_item_active(array $item, string $adminPage, ?string $settingsTab): bool
{
    if (!empty($item['page'])) {
        $pageMatch = $adminPage === $item['page'];
        if (!empty($item['settings_tab'])) {
            return $pageMatch && ($settingsTab ?? '') === $item['settings_tab'];
        }
        return $pageMatch;
    }
    if (!empty($item['settings_tab'])) {
        return $adminPage === 'settings' && ($settingsTab ?? '') === $item['settings_tab'];
    }
    return false;
}

function sh_admin_menu_group_has_active(array $group, string $adminPage, ?string $settingsTab): bool
{
    foreach ($group['items'] ?? [] as $item) {
        if (($item['type'] ?? '') === 'divider') {
            continue;
        }
        if (sh_admin_menu_item_active($item, $adminPage, $settingsTab)) {
            return true;
        }
    }
    return false;
}

function sh_admin_menu_item_href(array $item): string
{
    if (!empty($item['href'])) {
        return sh_url($item['href']);
    }
    return sh_admin_url($item['url'] ?? 'index.php');
}

function sh_admin_menu_item_label(array $item, array $ta): string
{
    $key = $item['label_key'] ?? '';
    if ($key === '') {
        return '';
    }
    if (str_starts_with($key, 'settings_tab_') || str_starts_with($key, 'settings_group_')) {
        return sh_settings_admin_label($key, $ta);
    }
    return (string) ($ta[$key] ?? $key);
}

function sh_admin_menu_leads_badge(): int
{
    static $count = null;
    if ($count !== null) {
        return $count;
    }
    $path = dirname(__DIR__, 2) . '/includes/leads-storage.php';
    if (!is_file($path)) {
        $count = 0;
        return 0;
    }
    require_once $path;
    $count = function_exists('sh_leads_count_by_status') ? sh_leads_count_by_status('new') : 0;
    return $count;
}

function sh_admin_menu_orders_badge(): int
{
    static $count = null;
    if ($count !== null) {
        return $count;
    }
    $path = dirname(__DIR__, 2) . '/includes/orders-storage.php';
    if (!is_file($path)) {
        $count = 0;
        return 0;
    }
    require_once $path;
    $count = function_exists('sh_orders_count_by_status') ? sh_orders_count_by_status('pending') : 0;
    return $count;
}

function sh_render_admin_sidebar_nav(array $ta, string $adminPage, ?string $settingsTab = null): void
{
    foreach (sh_admin_sidebar_menu() as $entry) {
        if (($entry['type'] ?? '') === 'link') {
            $active = sh_admin_menu_item_active($entry, $adminPage, $settingsTab);
            ?>
            <a href="<?= htmlspecialchars(sh_admin_url($entry['url'] ?? 'index.php')) ?>"
               class="<?= $active ? 'active' : '' ?>">
                <i class="fas fa-<?= htmlspecialchars($entry['icon'] ?? 'circle') ?>"></i>
                <?= htmlspecialchars(sh_admin_menu_item_label($entry, $ta)) ?>
            </a>
            <?php
            continue;
        }

        if (($entry['type'] ?? '') !== 'group') {
            continue;
        }

        $open = sh_admin_menu_group_has_active($entry, $adminPage, $settingsTab);
        $groupActive = $open;
        ?>
        <details class="adm-nav-group<?= $groupActive ? ' is-active' : '' ?>"<?= $open ? ' open' : '' ?>>
            <summary class="adm-nav-group-toggle">
                <span class="adm-nav-group-label">
                    <i class="fas fa-<?= htmlspecialchars($entry['icon'] ?? 'folder') ?>"></i>
                    <?= htmlspecialchars(sh_admin_menu_item_label(['label_key' => $entry['label_key']], $ta)) ?>
                </span>
                <i class="fas fa-chevron-down adm-nav-group-chevron" aria-hidden="true"></i>
            </summary>
            <div class="adm-nav-sub">
                <?php foreach ($entry['items'] as $item):
                    if (($item['type'] ?? '') === 'divider'): ?>
                <span class="adm-nav-divider" aria-hidden="true"></span>
                    <?php continue; endif;
                    $active = sh_admin_menu_item_active($item, $adminPage, $settingsTab);
                    $href = sh_admin_menu_item_href($item);
                    $ext = !empty($item['external']);
                    $badge = 0;
                    if (($item['badge'] ?? '') === 'leads') {
                        $badge = sh_admin_menu_leads_badge();
                    }
                    if (($item['badge'] ?? '') === 'orders') {
                        $badge = sh_admin_menu_orders_badge();
                    }
                    ?>
                <a href="<?= htmlspecialchars($href) ?>"
                   class="<?= $active ? 'active' : '' ?>"
                   <?= $ext ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>
                    <i class="fas fa-<?= htmlspecialchars($item['icon'] ?? 'circle') ?>"></i>
                    <span><?= htmlspecialchars(sh_admin_menu_item_label($item, $ta)) ?></span>
                    <?php if ($badge > 0): ?>
                    <span class="adm-nav-badge"><?= (int) $badge ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </details>
        <?php
    }
}