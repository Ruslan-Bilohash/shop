<?php
/**
 * Demo activity monitor — IP, country, action (admin billing, logins).
 */
declare(strict_types=1);

define('SH_DEMO_STATS_MAX', 500);

function sh_demo_stats_path(): string
{
    return dirname(__DIR__) . '/data/billing-demo-stats.json';
}

/** @return array{events:list<array<string,mixed>>,summary:array<string,int>} */
function sh_demo_stats_defaults(): array
{
    return ['events' => [], 'summary' => []];
}

/** @return array{events:list<array<string,mixed>>,summary:array<string,int>} */
function sh_demo_stats_load(): array
{
    $path = sh_demo_stats_path();
    if (!is_file($path)) {
        return sh_demo_stats_defaults();
    }
    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        return sh_demo_stats_defaults();
    }
    $data['events'] = is_array($data['events'] ?? null) ? $data['events'] : [];
    $data['summary'] = is_array($data['summary'] ?? null) ? $data['summary'] : [];
    return array_merge(sh_demo_stats_defaults(), $data);
}

/** @param array{events:list<array<string,mixed>>,summary:array<string,int>} $data */
function sh_demo_stats_save(array $data): bool
{
    $dir = dirname(sh_demo_stats_path());
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents(sh_demo_stats_path(), $json . "\n", LOCK_EX) !== false;
}

function sh_demo_stats_client_ip(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];
    foreach ($keys as $key) {
        $raw = trim((string) ($_SERVER[$key] ?? ''));
        if ($raw === '') {
            continue;
        }
        if ($key === 'HTTP_X_FORWARDED_FOR') {
            $parts = array_map('trim', explode(',', $raw));
            $raw = $parts[0] ?? $raw;
        }
        if (filter_var($raw, FILTER_VALIDATE_IP)) {
            return $raw;
        }
    }
    return '0.0.0.0';
}

/** @return array<string, string> */
function sh_demo_stats_lang_countries(): array
{
    return [
        'no' => 'NO',
        'uk' => 'UA',
        'en' => 'US',
        'ru' => 'RU',
        'sv' => 'SE',
        'lt' => 'LT',
    ];
}

function sh_demo_stats_client_country(string $lang = 'en'): string
{
    $hdr = strtoupper(trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_COUNTRY_CODE'] ?? '')));
    if ($hdr !== '' && $hdr !== 'XX' && preg_match('/^[A-Z]{2}$/', $hdr)) {
        return $hdr;
    }
    $map = sh_demo_stats_lang_countries();
    return $map[$lang] ?? '—';
}

/**
 * @param array<string, mixed> $extra
 */
function sh_demo_stats_record(string $action, array $extra = []): void
{
    $action = trim($action);
    if ($action === '') {
        return;
    }

    $lang = trim((string) ($extra['lang'] ?? ($_GET['lang'] ?? 'en'))) ?: 'en';
    $ip = sh_demo_stats_client_ip();
    $country = sh_demo_stats_client_country($lang);

    $event = [
        'ts'      => gmdate('c'),
        'action'  => $action,
        'ip'      => $ip,
        'country' => $country,
        'lang'    => $lang,
        'user'    => trim((string) ($extra['user'] ?? '')),
        'role'    => trim((string) ($extra['role'] ?? '')),
        'plan'    => trim((string) ($extra['plan'] ?? '')),
        'ua'      => substr(trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 180),
    ];

    $data = sh_demo_stats_load();
    array_unshift($data['events'], $event);
    if (count($data['events']) > SH_DEMO_STATS_MAX) {
        $data['events'] = array_slice($data['events'], 0, SH_DEMO_STATS_MAX);
    }

    $data['summary']['total'] = (int) ($data['summary']['total'] ?? 0) + 1;
    $data['summary']['action:' . $action] = (int) ($data['summary']['action:' . $action] ?? 0) + 1;
    $data['summary']['country:' . $country] = (int) ($data['summary']['country:' . $country] ?? 0) + 1;

    sh_demo_stats_save($data);
}

/** @return array{total:int,unique_ips:int,top_countries:list<array{code:string,count:int}>,recent:list<array<string,mixed>>} */
function sh_demo_stats_summary(): array
{
    $data = sh_demo_stats_load();
    $events = $data['events'];
    $ips = [];
    $countries = [];

    foreach ($events as $ev) {
        $ip = (string) ($ev['ip'] ?? '');
        if ($ip !== '' && $ip !== '0.0.0.0') {
            $ips[$ip] = true;
        }
        $cc = (string) ($ev['country'] ?? '');
        if ($cc !== '' && $cc !== '—') {
            $countries[$cc] = ($countries[$cc] ?? 0) + 1;
        }
    }

    arsort($countries);
    $top = [];
    foreach (array_slice($countries, 0, 8, true) as $code => $count) {
        $top[] = ['code' => (string) $code, 'count' => (int) $count];
    }

    return [
        'total'         => count($events),
        'unique_ips'    => count($ips),
        'top_countries' => $top,
        'recent'        => array_slice($events, 0, 40),
    ];
}