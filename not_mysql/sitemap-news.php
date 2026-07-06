<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/sitemap-lib.php';

$settings = sh_sitemap_settings();
sh_sitemap_emit_headers();
sh_sitemap_open_urlset();
if (!empty($settings['sitemap_include_news'])) {
    sh_sitemap_render_entries(sh_sitemap_news_entries());
}
sh_sitemap_close_urlset();