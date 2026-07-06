<?php

require_once dirname(__DIR__) . '/includes/bh-cms-site-settings.php';
require_once __DIR__ . '/payment-settings.php';

function sh_site_settings(): array
{
    static $s = null;
    if ($s === null) {
        $s = sh_load_settings();
        $GLOBALS['bh_cms_site_settings'] = $s;
        bh_cms_bind_recaptcha_settings($s);
    }
    return $s;
}

function sh_boot_public_integrations(): void
{
    sh_site_settings();
}