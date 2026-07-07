<?php
/** @return array{products:list,categories:list,news:list,settings:array} */
return [
    'products'   => require __DIR__ . '/products-ecosystem.php',
    'categories' => require __DIR__ . '/categories.php',
    'news'       => require __DIR__ . '/news.php',
    'settings'   => require __DIR__ . '/settings.php',
];