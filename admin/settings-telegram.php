<?php
require_once __DIR__ . '/init.php';
sh_admin_require();
require_once dirname(__DIR__) . '/includes/telegram-notify.php';

$settings_tab = 'telegram';
$bh_cms_load_settings = 'sh_load_settings';
$bh_cms_save_settings = 'sh_save_settings';
$bh_cms_admin_url = 'sh_admin_url';
$bh_cms_layout = __DIR__ . '/includes/layout.php';
$bh_cms_layout_end = __DIR__ . '/includes/layout-end.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['telegram_test'] ?? '') === '1') {
    $settings = sh_load_settings();
    $result = sh_telegram_send_test($settings);
    $_SESSION['sh_admin_flash'] = [
        'type' => $result['ok'] ? 'success' : 'error',
        'msg'  => $result['ok']
            ? ($t['admin']['telegram_test_ok'] ?? 'Test message sent to Telegram.')
            : (($t['admin']['telegram_test_fail'] ?? 'Telegram error: ') . ($result['error'] ?? '')),
    ];
    header('Location: ' . sh_admin_url('settings-telegram.php'));
    exit;
}

require __DIR__ . '/includes/complete-settings-page.php';