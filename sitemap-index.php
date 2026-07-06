<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/includes/sitemap-lib.php';

$settings = sh_sitemap_settings();
sh_sitemap_emit_headers();

if (empty($settings['sitemap_enabled'])) {
    http_response_code(404);
    exit;
}

sh_sitemap_open_index();
foreach (sh_sitemap_index_entries($settings) as $map) {
    echo '    <sitemap>' . "\n";
    echo '        <loc>' . htmlspecialchars($map['loc']) . '</loc>' . "\n";
    echo '        <lastmod>' . htmlspecialchars($map['lastmod']) . '</lastmod>' . "\n";
    echo '    </sitemap>' . "\n";
}
sh_sitemap_close_index();