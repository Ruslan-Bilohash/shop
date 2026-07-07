<?php

/**
 * Unified parcel tracking — Posten/Bring API, Nova Poshta API, external Norwegian carriers.
 */

require_once __DIR__ . '/posten.php';
require_once __DIR__ . '/nova-poshta.php';
require_once __DIR__ . '/norwegian-carriers.php';

/** @return list<array{id:string,label:string,icon:string,type:string}> */
function sh_track_carrier_options(?array $settings = null, ?array $t = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    $tr = is_array($t['track']['carriers'] ?? null) ? $t['track']['carriers'] : [];
    $catalog = sh_norwegian_carrier_catalog();
    $out = [];

    $posten = sh_posten_settings($settings);
    if (!empty($posten['enabled'])) {
        $ship = sh_norwegian_shipping_settings($settings);
        foreach (['posten', 'bring'] as $id) {
            if ($id === 'posten' || !empty($ship['carriers'][$id])) {
                $meta = $catalog[$id];
                $out[] = [
                    'id'    => $id,
                    'label' => trim((string) ($tr[$id] ?? '')) !== '' ? (string) $tr[$id] : $meta['label'],
                    'icon'  => $meta['icon'],
                    'type'  => 'bring',
                ];
            }
        }
    }

    $np = sh_nova_poshta_settings($settings);
    if (!empty($np['enabled']) && !empty($np['track_enabled'])) {
        $out[] = [
            'id'    => 'nova_poshta',
            'label' => trim((string) ($tr['nova_poshta'] ?? '')) !== '' ? (string) $tr['nova_poshta'] : 'Nova Poshta',
            'icon'  => 'fas fa-shipping-fast',
            'type'  => 'nova_poshta',
        ];
    }

    $ship = sh_norwegian_shipping_settings($settings);
    if (!empty($ship['enabled'])) {
        foreach (['helthjem', 'instabox', 'porterbuddy'] as $id) {
            if (empty($ship['carriers'][$id])) {
                continue;
            }
            $meta = $catalog[$id];
            $out[] = [
                'id'    => $id,
                'label' => trim((string) ($tr[$id] ?? '')) !== '' ? (string) $tr[$id] : $meta['label'],
                'icon'  => $meta['icon'],
                'type'  => 'external',
            ];
        }
    }

    if ($out === [] && !empty($posten['enabled'])) {
        $out[] = [
            'id'    => 'posten',
            'label' => trim((string) ($tr['posten'] ?? '')) !== '' ? (string) $tr['posten'] : 'Posten',
            'icon'  => 'fas fa-mail-bulk',
            'type'  => 'bring',
        ];
    }

    return $out;
}

function sh_track_default_carrier_id(?array $settings = null, ?array $t = null): string
{
    $options = sh_track_carrier_options($settings, $t);
    return $options[0]['id'] ?? 'posten';
}

function sh_track_carrier_is_valid(string $carrierId, ?array $settings = null, ?array $t = null): bool
{
    foreach (sh_track_carrier_options($settings, $t) as $opt) {
        if ($opt['id'] === $carrierId) {
            return true;
        }
    }
    return false;
}

/**
 * @return array{ok:bool,demo:bool,external:bool,url:string,number:string,status:string,events:list<array{date:string,label:string,location:string}>}
 */
function sh_track_lookup(string $carrierId, string $trackingNumber, ?array $settings = null): array
{
    $number = trim($trackingNumber);
    $empty = ['ok' => false, 'demo' => true, 'external' => false, 'url' => '', 'number' => $number, 'status' => '', 'events' => []];

    if ($number === '') {
        return $empty;
    }

    foreach (sh_track_carrier_options($settings) as $opt) {
        if ($opt['id'] !== $carrierId) {
            continue;
        }
        if ($opt['type'] === 'bring') {
            $r = sh_posten_track($number, $settings);
            return array_merge(['external' => false, 'url' => ''], $r);
        }
        if ($opt['type'] === 'nova_poshta') {
            $r = sh_nova_poshta_track($number, $settings);
            return array_merge(['external' => false, 'url' => ''], $r);
        }
        if ($opt['type'] === 'external') {
            return [
                'ok'       => true,
                'demo'     => false,
                'external' => true,
                'url'      => sh_norwegian_carrier_track_url($carrierId, $number),
                'number'   => $number,
                'status'   => $opt['label'],
                'events'   => [],
            ];
        }
    }

    return $empty;
}