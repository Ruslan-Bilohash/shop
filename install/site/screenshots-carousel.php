<?php
/**
 * Lazy-loaded screenshot gallery fragment for /shop/site/
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/i18n.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex');

require __DIR__ . '/includes/screenshots-carousel-body.php';