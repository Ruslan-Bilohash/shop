<?php
/** Demo news articles for MySQL import (install seed only). */
$json = __DIR__ . '/news.json';
if (!is_readable($json)) {
    return [];
}
$data = json_decode(file_get_contents($json) ?: '[]', true);
return is_array($data) ? $data : [];