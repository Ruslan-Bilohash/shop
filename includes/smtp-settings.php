<?php
/**
 * Shop CMS — SMTP and newsletter settings.
 */
declare(strict_types=1);

function sh_smtp_settings_defaults(): array
{
    return [
        'smtp_enabled'              => false,
        'smtp_host'                 => '',
        'smtp_port'                 => 465,
        'smtp_encryption'           => 'ssl',
        'smtp_username'             => '',
        'smtp_password'             => '',
        'smtp_from_email'           => '',
        'smtp_from_name'            => 'Shop CMS',
        'newsletter_enabled'        => true,
        'newsletter_notify_email'   => '',
        'newsletter_welcome_subject'=> 'Welcome to our newsletter',
        'newsletter_welcome_body'   => 'Thank you for subscribing! You will receive deals and new arrivals.',
    ];
}

function sh_smtp_settings_keys(): array
{
    return array_keys(sh_smtp_settings_defaults());
}

function sh_smtp_merge_settings(array $settings): array
{
    $defaults = sh_smtp_settings_defaults();
    foreach ($defaults as $key => $val) {
        if (!array_key_exists($key, $settings)) {
            $settings[$key] = $val;
        }
    }
    $settings['smtp_enabled'] = !empty($settings['smtp_enabled']);
    $settings['newsletter_enabled'] = !empty($settings['newsletter_enabled']);
    $settings['smtp_port'] = max(1, min(65535, (int) ($settings['smtp_port'] ?? 465)));
    $enc = strtolower((string) ($settings['smtp_encryption'] ?? 'ssl'));
    if (!in_array($enc, ['ssl', 'tls', 'none'], true)) {
        $settings['smtp_encryption'] = 'ssl';
    }
    return $settings;
}

function sh_smtp_settings_apply_post(array $post, array $settings): array
{
    $settings = sh_smtp_merge_settings($settings);
    $settings['smtp_enabled'] = !empty($post['smtp_enabled']);
    $settings['smtp_host'] = trim((string) ($post['smtp_host'] ?? ''));
    $settings['smtp_port'] = max(1, min(65535, (int) ($post['smtp_port'] ?? 465)));
    $enc = strtolower(trim((string) ($post['smtp_encryption'] ?? 'ssl')));
    $settings['smtp_encryption'] = in_array($enc, ['ssl', 'tls', 'none'], true) ? $enc : 'ssl';
    $settings['smtp_username'] = trim((string) ($post['smtp_username'] ?? ''));
    $postedPass = trim((string) ($post['smtp_password'] ?? ''));
    if ($postedPass !== '') {
        $settings['smtp_password'] = $postedPass;
    }
    $settings['smtp_from_email'] = trim((string) ($post['smtp_from_email'] ?? ''));
    $settings['smtp_from_name'] = trim((string) ($post['smtp_from_name'] ?? 'Shop CMS'));
    $settings['newsletter_enabled'] = !empty($post['newsletter_enabled']);
    $settings['newsletter_notify_email'] = trim((string) ($post['newsletter_notify_email'] ?? ''));
    $settings['newsletter_welcome_subject'] = trim((string) ($post['newsletter_welcome_subject'] ?? ''));
    $settings['newsletter_welcome_body'] = trim((string) ($post['newsletter_welcome_body'] ?? ''));
    return $settings;
}

function sh_smtp_is_configured(?array $settings = null): bool
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    $s = sh_smtp_merge_settings(is_array($settings) ? $settings : []);
    if (empty($s['smtp_enabled'])) {
        return false;
    }
    return trim((string) ($s['smtp_host'] ?? '')) !== ''
        && trim((string) ($s['smtp_username'] ?? '')) !== ''
        && trim((string) ($s['smtp_password'] ?? '')) !== '';
}