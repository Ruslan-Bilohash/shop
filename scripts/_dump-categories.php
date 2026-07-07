<?php
require_once dirname(__DIR__) . '/init.php';
require_once dirname(__DIR__) . '/includes/category-storage.php';
foreach (sh_category_records(true) as $cat) {
    if (($cat['slug'] ?? '') === 'php-scripts') {
        echo json_encode($cat, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit(0);
    }
}
echo "MISSING\n";