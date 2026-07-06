<?php
/**
 * Legacy entry — redirects to sitemap index (sitemap.xml).
 */
require_once __DIR__ . '/init.php';
header('Location: ' . sh_absolute_url(sh_url('sitemap-index.php')), true, 301);
exit;