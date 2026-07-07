<?php

/**
 * Norwegian parcel carriers for checkout and tracking (Posten, Bring, Helthjem, Instabox, Porterbuddy).
 */

function sh_norwegian_carrier_catalog(): array
{
    return [
        'posten' => [
            'id'      => 'posten',
            'label'   => 'Posten',
            'icon'    => 'fas fa-mail-bulk',
            'track'   => 'https://sporing.posten.no/sporing/',
            'api'     => 'bring',
        ],
        'bring' => [
            'id'      => 'bring',
            'label'   => 'Bring',
            'icon'    => 'fas fa-truck',
            'track'   => 'https://sporing.bring.no/sporing/',
            'api'     => 'bring',
        ],
        'helthjem' => [
            'id'      => 'helthjem',
            'label'   => 'Helthjem',
            'icon'    => 'fas fa-home',
            'track'   => 'https://www.helthjem.no/sporing/',
            'api'     => 'demo',
        ],
        'instabox' => [
            'id'      => 'instabox',
            'label'   => 'Instabox',
            'icon'    => 'fas fa-box',
            'track'   => 'https://instabox.io/no/track',
            'api'     => 'demo',
        ],
        'porterbuddy' => [
            'id'      => 'porterbuddy',
            'label'   => 'Porterbuddy',
            'icon'    => 'fas fa-bicycle',
            'track'   => 'https://porterbuddy.com/no/',
            'api'     => 'demo',
        ],
    ];
}

function sh_norwegian_shipping_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_load_settings')) {
        $settings = sh_load_settings();
    }
    require_once __DIR__ . '/store-settings.php';
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $catalog = sh_norwegian_carrier_catalog();
    $enabled = [];
    foreach (array_keys($catalog) as $id) {
        $key = 'shipping_carrier_' . $id;
        $enabled[$id] = array_key_exists($key, $s) ? !empty($s[$key]) : ($id === 'posten' || $id === 'bring');
    }
    return [
        'enabled'   => !empty($s['shipping_norway_enabled']),
        'demo_mode' => !empty($s['shipping_norway_demo_mode']) || empty($s['posten_api_key'] ?? ''),
        'carriers'  => $enabled,
    ];
}

/** @return list<array{id:string,label:string,icon:string}> */
/** @param array<string, mixed>|null $t Storefront translations ($t from i18n). */
function sh_norwegian_shipping_checkout_options(?array $settings = null, ?array $t = null): array
{
    $cfg = sh_norwegian_shipping_settings($settings);
    if (!$cfg['enabled']) {
        return [];
    }
    $catalog = sh_norwegian_carrier_catalog();
    $tr = is_array($t['checkout']['shipping_carriers'] ?? null) ? $t['checkout']['shipping_carriers'] : [];
    $out = [];
    foreach ($catalog as $id => $meta) {
        if (empty($cfg['carriers'][$id])) {
            continue;
        }
        $out[] = [
            'id'    => $id,
            'label' => trim((string) ($tr[$id] ?? '')) !== '' ? (string) $tr[$id] : $meta['label'],
            'icon'  => $meta['icon'],
        ];
    }
    return $out;
}

function sh_norwegian_shipping_apply_post(array $post, array $settings): array
{
    require_once __DIR__ . '/store-settings.php';
    $settings = sh_merge_store_settings($settings);
    $settings['shipping_norway_enabled'] = !empty($post['shipping_norway_enabled']);
    $settings['shipping_norway_demo_mode'] = !empty($post['shipping_norway_demo_mode']);
    foreach (array_keys(sh_norwegian_carrier_catalog()) as $id) {
        $settings['shipping_carrier_' . $id] = !empty($post['shipping_carrier_' . $id]);
    }
    return $settings;
}

function sh_norwegian_carrier_track_url(string $carrierId, string $trackingNumber): string
{
    $catalog = sh_norwegian_carrier_catalog();
    $meta = $catalog[$carrierId] ?? $catalog['posten'];
    $base = rtrim((string) ($meta['track'] ?? ''), '/');
    $num = rawurlencode(trim($trackingNumber));
    return $base . ($num !== '' ? '/' . $num : '');
}