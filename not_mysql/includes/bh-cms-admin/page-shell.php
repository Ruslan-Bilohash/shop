<?php
/**
 * Shared settings page handler.
 * Required vars: $settings_tab (appearance|recaptcha|chat), $bh_cms_load_settings, $bh_cms_save_settings, $bh_cms_admin_url
 */
require_once dirname(__DIR__) . '/bh-cms-site-settings.php';

$settings = is_callable($bh_cms_load_settings) ? $bh_cms_load_settings() : call_user_func($bh_cms_load_settings);
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('fk_demo_readonly') && fk_demo_readonly()
        && function_exists('fk_admin_logged') && fk_admin_logged()) {
        $flash = 'demo_blocked';
    } else {
        $settings = bh_cms_settings_apply_post($settings_tab, $_POST, $settings);
        $saved = is_callable($bh_cms_save_settings) ? $bh_cms_save_settings($settings) : call_user_func($bh_cms_save_settings, $settings);
        $flash = $saved ? 'success' : 'error';
        $settings = is_callable($bh_cms_load_settings) ? $bh_cms_load_settings() : call_user_func($bh_cms_load_settings);
    }
}

$adminUrlFn = $bh_cms_admin_url;