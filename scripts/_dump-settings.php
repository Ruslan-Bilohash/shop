<?php
require_once dirname(__DIR__) . '/init.php';
$settings = sh_load_settings();
$langs = sh_active_langs($settings);
echo "LANGS=" . implode(',', array_keys($langs)) . "\n";
$ai = sh_ai_settings($settings);
echo "ai_enabled=" . (sh_ai_enabled($settings) ? '1' : '0') . "\n";
echo "ai_source=" . ($ai['ai_source_lang'] ?? 'en') . "\n";
echo "has_openai_key=" . (trim((string)($ai['openai_api_key'] ?? '')) !== '' ? '1' : '0') . "\n";