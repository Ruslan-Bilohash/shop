<?php

/**
 * Posten / Bring parcel tracking (Norway).
 * Demo mode returns sample timeline; production uses Bring Tracking API when configured.
 */

function sh_posten_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    require_once __DIR__ . '/store-settings.php';
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    return [
        'enabled'    => !empty($s['posten_enabled']),
        'api_key'    => trim((string) ($s['posten_api_key'] ?? '')),
        'client_id'  => trim((string) ($s['posten_client_id'] ?? '')),
        'demo_mode'  => !empty($s['posten_demo_mode']) || trim((string) ($s['posten_api_key'] ?? '')) === '',
    ];
}

function sh_posten_tracking_valid(string $number): bool
{
    $n = preg_replace('/\s+/', '', trim($number));
    return $n !== '' && (bool) preg_match('/^[A-Z0-9]{8,20}$/i', $n);
}

/**
 * @return array{ok:bool,demo:bool,number:string,status:string,events:list<array{date:string,label:string,location:string}>}
 */
function sh_posten_track(string $trackingNumber, ?array $settings = null): array
{
    $number = strtoupper(preg_replace('/\s+/', '', trim($trackingNumber)));
    if (!sh_posten_tracking_valid($number)) {
        return ['ok' => false, 'demo' => true, 'number' => $number, 'status' => '', 'events' => []];
    }

    $cfg = sh_posten_settings($settings);
    if (!$cfg['enabled']) {
        return ['ok' => false, 'demo' => true, 'number' => $number, 'status' => '', 'events' => []];
    }

    if ($cfg['demo_mode']) {
        return sh_posten_demo_tracking($number);
    }

    $url = 'https://api.bring.com/tracking/api/v2/tracking.json?q=' . rawurlencode($number);
    $headers = ['Accept: application/json'];
    if ($cfg['api_key'] !== '') {
        $headers[] = 'X-Bring-Client-URL: bilohash.com/shop';
        $headers[] = 'X-Bring-API-Key: ' . $cfg['api_key'];
    }
    if ($cfg['client_id'] !== '') {
        $headers[] = 'X-Bring-Client-ID: ' . $cfg['client_id'];
    }

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => implode("\r\n", $headers) . "\r\n",
            'timeout' => 12,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return sh_posten_demo_tracking($number);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'demo' => false, 'number' => $number, 'status' => 'unknown', 'events' => []];
    }

    $events = [];
    $consignment = $data['consignmentSet'][0]['consignment'] ?? null;
    $packages = is_array($consignment) ? ($consignment[0]['packageSet'][0]['package'] ?? []) : [];
    if (!is_array($packages)) {
        $packages = [];
    }
    foreach ($packages as $pkg) {
        foreach ($pkg['eventSet'] ?? [] as $ev) {
            $events[] = [
                'date'     => trim($ev['dateIso'] ?? $ev['date'] ?? ''),
                'label'    => trim($ev['description'] ?? $ev['status'] ?? ''),
                'location' => trim($ev['unitId'] ?? $ev['city'] ?? ''),
            ];
        }
    }
    $status = $events[0]['label'] ?? ($consignment[0]['statusDescription'] ?? 'In transit');

    return [
        'ok'     => true,
        'demo'   => false,
        'number' => $number,
        'status' => $status,
        'events' => $events,
    ];
}

/** @return array{ok:bool,demo:bool,number:string,status:string,events:list<array{date:string,label:string,location:string}>} */
function sh_posten_demo_tracking(string $number): array
{
    $now = time();
    return [
        'ok'     => true,
        'demo'   => true,
        'number' => $number,
        'status' => 'Out for delivery',
        'events' => [
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 86400 * 2), 'label' => 'Shipment registered', 'location' => 'Oslo logistics'],
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 86400), 'label' => 'In transit — Norway', 'location' => 'Bergen hub'],
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 3600 * 5), 'label' => 'Out for delivery', 'location' => 'Local depot'],
        ],
    ];
}