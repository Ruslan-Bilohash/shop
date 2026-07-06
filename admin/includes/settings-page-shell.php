<?php
/**
 * Shop settings page handler (appearance / recaptcha / chat / seo).
 * Required: $settings_tab, $bh_cms_load_settings, $bh_cms_save_settings
 */
require_once dirname(__DIR__, 2) . '/includes/site-settings.php';

$settings = is_callable($bh_cms_load_settings) ? $bh_cms_load_settings() : call_user_func($bh_cms_load_settings);
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['regenerate_sitemap']) && ($settings_tab ?? '') === 'seo') {
        $settings = sh_sitemap_regenerate($settings);
        $saved = is_callable($bh_cms_save_settings) ? $bh_cms_save_settings($settings) : call_user_func($bh_cms_save_settings, $settings);
        $flash = $saved ? 'sitemap_ok' : 'error';
    } else {
        $settings = sh_settings_apply_post($settings_tab, $_POST, $settings);
        $saved = is_callable($bh_cms_save_settings) ? $bh_cms_save_settings($settings) : call_user_func($bh_cms_save_settings, $settings);
        $flash = $saved ? 'success' : 'error';
    }
    $settings = is_callable($bh_cms_load_settings) ? $bh_cms_load_settings() : call_user_func($bh_cms_load_settings);
}