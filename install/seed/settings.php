<?php
/**
 * Demo settings for fresh install — no API secrets.
 */
$json = __DIR__ . '/settings.json';
if (is_readable($json)) {
    $data = json_decode(file_get_contents($json) ?: '{}', true);
    if (is_array($data)) {
        $data['ai_enabled'] = false;
        $data['ai_api_key'] = '';
        $data['chat_api_key'] = '';
        $data['posten_api_key'] = '';
        $data['nova_poshta_api_key'] = '';
        $data['shop_maintenance_enabled'] = false;
        return $data;
    }
}
return [
    'ai_enabled' => false,
    'ai_api_key' => '',
    'ai_provider' => 'grok',
    'ai_model' => 'grok-3-mini',
    'shop_maintenance_enabled' => false,
    'site_currency' => 'NOK',
    'currency_symbol' => 'kr',
    'currency_decimals' => 0,
];