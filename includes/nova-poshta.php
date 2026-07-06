<?php

/**
 * Nova Poshta (Нова пошта) — Ukraine parcel API v2.0.
 * Docs: https://developers.novaposhta.ua/
 */

function sh_nova_poshta_settings(?array $settings = null): array
{
    if ($settings === null && function_exists('sh_site_settings')) {
        $settings = sh_site_settings();
    }
    require_once __DIR__ . '/store-settings.php';
    $s = sh_merge_store_settings(is_array($settings) ? $settings : []);
    $apiKey = trim((string) ($s['nova_poshta_api_key'] ?? ''));
    return [
        'enabled'           => !empty($s['nova_poshta_enabled']),
        'track_enabled'     => !empty($s['nova_poshta_track_enabled']),
        'checkout_enabled'  => !empty($s['nova_poshta_checkout_enabled']),
        'demo_mode'         => !empty($s['nova_poshta_demo_mode']) || $apiKey === '',
        'api_key'           => $apiKey,
        'sender_city_ref'   => trim((string) ($s['nova_poshta_sender_city_ref'] ?? '')),
        'sender_city_name'  => trim((string) ($s['nova_poshta_sender_city_name'] ?? '')),
        'sender_warehouse_ref'  => trim((string) ($s['nova_poshta_sender_warehouse_ref'] ?? '')),
        'sender_warehouse_name' => trim((string) ($s['nova_poshta_sender_warehouse_name'] ?? '')),
        'sender_phone'      => trim((string) ($s['nova_poshta_sender_phone'] ?? '')),
        'default_weight_kg' => max(0.1, min(30.0, (float) ($s['nova_poshta_default_weight_kg'] ?? 1))),
    ];
}

function sh_nova_poshta_tracking_valid(string $number): bool
{
    $n = preg_replace('/\s+/', '', trim($number));
    return $n !== '' && (bool) preg_match('/^\d{11,14}$/', $n);
}

/**
 * @return array{ok:bool,error:string,data:array}
 */
function sh_nova_poshta_api_call(string $apiKey, string $model, string $method, array $props = []): array
{
    $apiKey = trim($apiKey);
    if ($apiKey === '') {
        return ['ok' => false, 'error' => 'API key required', 'data' => []];
    }

    $payload = json_encode([
        'apiKey'           => $apiKey,
        'modelName'        => $model,
        'calledMethod'     => $method,
        'methodProperties' => (object) $props,
    ], JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        return ['ok' => false, 'error' => 'JSON encode failed', 'data' => []];
    }

    $raw = false;
    if (function_exists('curl_init')) {
        $ch = curl_init('https://api.novaposhta.ua/v2.0/json/');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $raw = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $err ?: 'cURL failed', 'data' => []];
        }
        curl_close($ch);
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 20,
            ],
        ]);
        $raw = @file_get_contents('https://api.novaposhta.ua/v2.0/json/', false, $ctx);
    }

    if ($raw === false || $raw === '') {
        return ['ok' => false, 'error' => 'Empty API response', 'data' => []];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'Invalid JSON from Nova Poshta', 'data' => []];
    }
    if (!empty($decoded['errors']) && is_array($decoded['errors']) && $decoded['errors'] !== []) {
        $msg = implode('; ', array_map('strval', $decoded['errors']));
        return ['ok' => false, 'error' => $msg, 'data' => $decoded];
    }
    if (isset($decoded['success']) && $decoded['success'] === false) {
        $msg = is_array($decoded['errors'] ?? null) ? implode('; ', $decoded['errors']) : 'API error';
        return ['ok' => false, 'error' => $msg, 'data' => $decoded];
    }

    return ['ok' => true, 'error' => '', 'data' => $decoded];
}

/** @return array{ok:bool,message:string} */
function sh_nova_poshta_test_connection(?array $settings = null): array
{
    $cfg = sh_nova_poshta_settings($settings);
    if ($cfg['demo_mode']) {
        return ['ok' => true, 'message' => 'Demo mode — API key not set'];
    }
    $resp = sh_nova_poshta_api_call($cfg['api_key'], 'Address', 'getCities', [
        'Page' => '1',
        'Limit' => '1',
        'FindByString' => 'Київ',
    ]);
    if (!$resp['ok']) {
        return ['ok' => false, 'message' => $resp['error']];
    }
    $rows = $resp['data']['data'] ?? [];
    if (!is_array($rows) || $rows === []) {
        return ['ok' => false, 'message' => 'No cities returned'];
    }
    return ['ok' => true, 'message' => 'Connected — Nova Poshta API OK'];
}

/** @return list<array{ref:string,name:string,area:string}> */
function sh_nova_poshta_search_cities(string $query, ?array $settings = null, int $limit = 20): array
{
    $cfg = sh_nova_poshta_settings($settings);
    if ($cfg['demo_mode']) {
        return sh_nova_poshta_demo_cities($query);
    }
    $resp = sh_nova_poshta_api_call($cfg['api_key'], 'Address', 'getCities', [
        'FindByString' => mb_substr(trim($query), 0, 80),
        'Limit'        => (string) max(1, min(50, $limit)),
        'Page'         => '1',
    ]);
    if (!$resp['ok']) {
        return [];
    }
    $out = [];
    foreach ($resp['data']['data'] ?? [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ref = trim((string) ($row['Ref'] ?? ''));
        $name = trim((string) ($row['Description'] ?? $row['DescriptionRu'] ?? ''));
        if ($ref === '' || $name === '') {
            continue;
        }
        $out[] = [
            'ref'  => $ref,
            'name' => $name,
            'area' => trim((string) ($row['AreaDescription'] ?? '')),
        ];
    }
    return $out;
}

/** @return list<array{ref:string,name:string,number:string,city_ref:string}> */
function sh_nova_poshta_warehouses(string $cityRef, ?array $settings = null, int $limit = 50): array
{
    $cityRef = trim($cityRef);
    if ($cityRef === '') {
        return [];
    }
    $cfg = sh_nova_poshta_settings($settings);
    if ($cfg['demo_mode']) {
        return sh_nova_poshta_demo_warehouses($cityRef);
    }
    $resp = sh_nova_poshta_api_call($cfg['api_key'], 'Address', 'getWarehouses', [
        'CityRef' => $cityRef,
        'Limit'   => (string) max(1, min(200, $limit)),
        'Page'    => '1',
    ]);
    if (!$resp['ok']) {
        return [];
    }
    $out = [];
    foreach ($resp['data']['data'] ?? [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $ref = trim((string) ($row['Ref'] ?? ''));
        $name = trim((string) ($row['Description'] ?? ''));
        if ($ref === '' || $name === '') {
            continue;
        }
        $out[] = [
            'ref'      => $ref,
            'name'     => $name,
            'number'   => trim((string) ($row['Number'] ?? '')),
            'city_ref' => $cityRef,
        ];
    }
    return $out;
}

/**
 * @return array{ok:bool,demo:bool,number:string,status:string,events:list<array{date:string,label:string,location:string}>}
 */
function sh_nova_poshta_track(string $trackingNumber, ?array $settings = null): array
{
    $number = preg_replace('/\s+/', '', trim($trackingNumber));
    if (!sh_nova_poshta_tracking_valid($number)) {
        return ['ok' => false, 'demo' => true, 'number' => $number, 'status' => '', 'events' => []];
    }

    $cfg = sh_nova_poshta_settings($settings);
    if (!$cfg['enabled'] || !$cfg['track_enabled']) {
        return ['ok' => false, 'demo' => true, 'number' => $number, 'status' => '', 'events' => []];
    }

    if ($cfg['demo_mode']) {
        return sh_nova_poshta_demo_tracking($number);
    }

    $doc = ['DocumentNumber' => $number];
    if ($cfg['sender_phone'] !== '') {
        $doc['Phone'] = preg_replace('/\D+/', '', $cfg['sender_phone']);
    }

    $resp = sh_nova_poshta_api_call($cfg['api_key'], 'TrackingDocument', 'getStatusDocuments', [
        'Documents' => [$doc],
    ]);
    if (!$resp['ok']) {
        return sh_nova_poshta_demo_tracking($number);
    }

    $row = $resp['data']['data'][0] ?? null;
    if (!is_array($row)) {
        return ['ok' => false, 'demo' => false, 'number' => $number, 'status' => 'unknown', 'events' => []];
    }

    $status = trim((string) ($row['Status'] ?? $row['StatusCode'] ?? 'In transit'));
    $events = [];
    $ts = trim((string) ($row['DateCreated'] ?? $row['RecipientDateTime'] ?? ''));
    if ($ts !== '') {
        $events[] = ['date' => $ts, 'label' => $status, 'location' => trim((string) ($row['CityRecipient'] ?? $row['WarehouseRecipient'] ?? ''))];
    }
    $scan = trim((string) ($row['WarehouseRecipient'] ?? ''));
    if ($scan !== '' && ($events === [] || $events[0]['location'] !== $scan)) {
        $events[] = [
            'date'     => trim((string) ($row['ScheduledDeliveryDate'] ?? $ts)),
            'label'    => trim((string) ($row['Status'] ?? 'Warehouse')),
            'location' => $scan,
        ];
    }
    if ($events === []) {
        $events[] = ['date' => gmdate('Y-m-d\TH:i:s\Z'), 'label' => $status, 'location' => ''];
    }

    return [
        'ok'     => true,
        'demo'   => false,
        'number' => $number,
        'status' => $status,
        'events' => $events,
    ];
}

/** @return array{ok:bool,demo:bool,number:string,status:string,events:list<array{date:string,label:string,location:string}>} */
function sh_nova_poshta_demo_tracking(string $number): array
{
    $now = time();
    return [
        'ok'     => true,
        'demo'   => true,
        'number' => $number,
        'status' => 'В дорозі до відділення',
        'events' => [
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 86400 * 2), 'label' => 'Створено відправлення', 'location' => 'Київ, логістичний центр'],
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 86400), 'label' => 'В дорозі', 'location' => 'Регіональний хаб'],
            ['date' => gmdate('Y-m-d\TH:i:s\Z', $now - 3600 * 4), 'label' => 'Прибуло у відділення', 'location' => 'Відділення №1'],
        ],
    ];
}

/** @return list<array{ref:string,name:string,area:string}> */
function sh_nova_poshta_demo_cities(string $query): array
{
    $all = [
        ['ref' => 'demo-kyiv', 'name' => 'Київ', 'area' => 'Київська'],
        ['ref' => 'demo-lviv', 'name' => 'Львів', 'area' => 'Львівська'],
        ['ref' => 'demo-odesa', 'name' => 'Одеса', 'area' => 'Одеська'],
        ['ref' => 'demo-dnipro', 'name' => 'Дніпро', 'area' => 'Дніпропетровська'],
    ];
    $q = mb_strtolower(trim($query));
    if ($q === '') {
        return $all;
    }
    return array_values(array_filter($all, static function (array $c) use ($q): bool {
        return str_contains(mb_strtolower($c['name']), $q) || str_contains(mb_strtolower($c['area']), $q);
    }));
}

/** @return list<array{ref:string,name:string,number:string,city_ref:string}> */
function sh_nova_poshta_demo_warehouses(string $cityRef): array
{
    return [
        ['ref' => 'demo-wh-1', 'name' => 'Відділення №1 (демо)', 'number' => '1', 'city_ref' => $cityRef],
        ['ref' => 'demo-wh-2', 'name' => 'Відділення №2 (демо)', 'number' => '2', 'city_ref' => $cityRef],
        ['ref' => 'demo-wh-3', 'name' => 'Поштомат №3 (демо)', 'number' => '3', 'city_ref' => $cityRef],
    ];
}