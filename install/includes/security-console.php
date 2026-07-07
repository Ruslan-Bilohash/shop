<?php
/**
 * Shop CMS — admin security mini-console: port probe + vulnerability checklist.
 */
declare(strict_types=1);

/** @return list<array{id:string,port:int,service:string,group:string}> */
function sh_sec_port_definitions(): array
{
    return [
        ['id' => 'ssh',      'port' => 22,    'service' => 'SSH',        'group' => 'remote'],
        ['id' => 'ftp',      'port' => 21,    'service' => 'FTP',        'group' => 'remote'],
        ['id' => 'smtp',     'port' => 25,    'service' => 'SMTP',     'group' => 'mail'],
        ['id' => 'smtps',    'port' => 465,   'service' => 'SMTPS',      'group' => 'mail'],
        ['id' => 'submission','port' => 587,  'service' => 'Submission', 'group' => 'mail'],
        ['id' => 'http',     'port' => 80,    'service' => 'HTTP',       'group' => 'web'],
        ['id' => 'https',    'port' => 443,   'service' => 'HTTPS',      'group' => 'web'],
        ['id' => 'http-alt', 'port' => 8080,  'service' => 'HTTP alt',   'group' => 'web'],
        ['id' => 'https-alt','port' => 8443,  'service' => 'HTTPS alt',  'group' => 'web'],
        ['id' => 'mysql',    'port' => 3306,  'service' => 'MySQL',      'group' => 'database'],
        ['id' => 'postgres', 'port' => 5432,  'service' => 'PostgreSQL', 'group' => 'database'],
        ['id' => 'redis',    'port' => 6379,  'service' => 'Redis',      'group' => 'cache'],
        ['id' => 'memcached','port' => 11211, 'service' => 'Memcached',  'group' => 'cache'],
    ];
}

function sh_sec_shop_root(): string
{
    return dirname(__DIR__);
}

function sh_sec_probe_port(string $host, int $port, float $timeout = 0.35): string
{
    $host = trim($host);
    if ($host === '' || $port < 1 || $port > 65535) {
        return 'invalid';
    }
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (is_resource($fp)) {
        fclose($fp);
        return 'open';
    }
    if ($errno === 111 || $errno === 10061) {
        return 'closed';
    }
    if ($errno === 110 || $errno === 10060) {
        return 'filtered';
    }
    return 'closed';
}

/**
 * @return array{host:string,ports:list<array<string,mixed>>,elapsed_ms:float,web_port:int,https:bool}
 */
function sh_sec_scan_ports(?string $host = null, float $timeout = 0.35): array
{
    $host = $host ?? '127.0.0.1';
    $start = microtime(true);
    $webPort = (int) ($_SERVER['SERVER_PORT'] ?? 80);
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $rows = [];

    foreach (sh_sec_port_definitions() as $def) {
        $port = (int) $def['port'];
        $status = sh_sec_probe_port($host, $port, $timeout);
        $isCurrent = ($https && $port === 443) || (!$https && $port === $webPort);
        if ($isCurrent && $status !== 'open') {
            $status = 'current';
        }
        $risk = 'info';
        if ($status === 'open') {
            $risk = match ($def['group']) {
                'database', 'cache', 'remote' => 'high',
                'mail' => 'medium',
                default => 'low',
            };
        }
        $rows[] = [
            'id'      => $def['id'],
            'port'    => $port,
            'service' => $def['service'],
            'group'   => $def['group'],
            'status'  => $status,
            'risk'    => $risk,
            'current' => $isCurrent,
        ];
    }

    return [
        'host'       => $host,
        'ports'      => $rows,
        'elapsed_ms' => round((microtime(true) - $start) * 1000, 1),
        'web_port'   => $webPort,
        'https'      => $https,
    ];
}

/** @return array<string, mixed> */
function sh_sec_server_snapshot(): array
{
    $root = sh_sec_shop_root();
    return [
        'php_version'    => PHP_VERSION,
        'php_sapi'       => PHP_SAPI,
        'os'             => PHP_OS_FAMILY,
        'server_software'=> (string) ($_SERVER['SERVER_SOFTWARE'] ?? ''),
        'document_root'  => (string) ($_SERVER['DOCUMENT_ROOT'] ?? ''),
        'shop_root'      => $root,
        'https'          => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'server_port'    => (int) ($_SERVER['SERVER_PORT'] ?? 0),
        'remote_addr'    => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        'ini_memory'     => ini_get('memory_limit') ?: '',
        'ini_max_upload' => ini_get('upload_max_filesize') ?: '',
    ];
}

function sh_sec_file_writable(string $path): bool
{
    return is_file($path) && is_writable($path);
}

function sh_sec_dir_writable(string $path): bool
{
    return is_dir($path) && is_writable($path);
}

function sh_sec_path_exists(string $rel): bool
{
    return is_file(sh_sec_shop_root() . '/' . ltrim($rel, '/'))
        || is_dir(sh_sec_shop_root() . '/' . ltrim($rel, '/'));
}

/**
 * @return list<array{id:string,severity:string,ok:bool,label:string,detail:string,fix_url:string}>
 */
function sh_sec_vulnerability_checks(array $labels = []): array
{
    require_once __DIR__ . '/admin-auth.php';
    require_once __DIR__ . '/payment-settings.php';

    $root = sh_sec_shop_root();
    $settings = sh_load_settings();
    $creds = sh_admin_credentials();
    $checks = [];

    $add = static function (
        string $id,
        string $severity,
        bool $ok,
        string $labelKey,
        string $detailKey = '',
        string $fixUrl = ''
    ) use (&$checks, $labels): void {
        $checks[] = [
            'id'       => $id,
            'severity' => $severity,
            'ok'       => $ok,
            'label'    => $labels[$labelKey] ?? $labelKey,
            'detail'   => $detailKey !== '' ? ($labels[$detailKey] ?? $detailKey) : '',
            'fix_url'  => $fixUrl,
        ];
    };

    $usingDemoAdmin = $creds['pass_hash'] === null;
    $add('demo_admin', 'critical', !$usingDemoAdmin, 'check_demo_admin', 'detail_demo_admin', 'login.php');

    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
    $add('https', 'high', $https || $isLocal, 'check_https', 'detail_https', '');

    $displayErrors = filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN);
    $add('display_errors', 'high', !$displayErrors, 'check_display_errors', 'detail_display_errors', '');

    $exposePhp = filter_var(ini_get('expose_php'), FILTER_VALIDATE_BOOLEAN);
    $add('expose_php', 'medium', !$exposePhp, 'check_expose_php', 'detail_expose_php', '');

    $allowUrlInclude = filter_var(ini_get('allow_url_include'), FILTER_VALIDATE_BOOLEAN);
    $add('allow_url_include', 'critical', !$allowUrlInclude, 'check_allow_url_include', 'detail_allow_url_include', '');

    $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
    $add('php_version', 'medium', $phpOk, 'check_php_version', 'detail_php_version', '');

    $installDir = is_dir($root . '/install');
    $add('install_dir', 'high', !$installDir, 'check_install_dir', 'detail_install_dir', '');

    $healthFile = is_file($root . '/_health.php');
    $add('health_file', 'medium', !$healthFile, 'check_health_file', 'detail_health_file', '');

    $sessionHttpOnly = filter_var(ini_get('session.cookie_httponly'), FILTER_VALIDATE_BOOLEAN);
    $add('session_httponly', 'medium', $sessionHttpOnly, 'check_session_httponly', 'detail_session_httponly', '');

    $sessionSecure = filter_var(ini_get('session.cookie_secure'), FILTER_VALIDATE_BOOLEAN);
    $add('session_secure', 'medium', !$https || $sessionSecure, 'check_session_secure', 'detail_session_secure', '');

    $recaptchaOk = !empty($settings['recaptcha_enabled']) && trim($settings['recaptcha_site_key'] ?? '') !== '';
    $add('recaptcha', 'medium', $recaptchaOk, 'check_recaptcha', 'detail_recaptcha', 'settings-recaptcha.php');

    $writableIncludes = sh_sec_dir_writable($root . '/includes');
    $add('writable_includes', 'high', !$writableIncludes, 'check_writable_includes', 'detail_writable_includes', '');

    $writableAdmin = sh_sec_dir_writable($root . '/admin');
    $add('writable_admin', 'high', !$writableAdmin, 'check_writable_admin', 'detail_writable_admin', '');

    $dbConfig = $root . '/data/db.config.php';
    $dbWorldWritable = is_file($dbConfig) && (fileperms($dbConfig) & 0x0002) !== 0;
    $add('db_config_perms', 'high', !$dbWorldWritable, 'check_db_config_perms', 'detail_db_config_perms', '');

    $sensitivePatterns = ['backup.sql', 'dump.sql', 'database.sql', '.env', 'config.bak'];
    $foundSensitive = [];
    foreach ($sensitivePatterns as $name) {
        if (is_file($root . '/' . $name)) {
            $foundSensitive[] = $name;
        }
    }
    $add('sensitive_files', 'high', $foundSensitive === [], 'check_sensitive_files',
        $foundSensitive === [] ? '' : sprintf($labels['detail_sensitive_files'] ?? '%s', implode(', ', $foundSensitive)),
        '');

    $demoMode = defined('SH_DEMO_MODE') && SH_DEMO_MODE;
    $add('demo_mode', 'low', !$demoMode, 'check_demo_mode', 'detail_demo_mode', '');

    return $checks;
}

/** @param list<array{severity:string,ok:bool}> $checks */
function sh_sec_score(array $checks): array
{
    $weights = ['critical' => 25, 'high' => 15, 'medium' => 8, 'low' => 3];
    $max = 0;
    $lost = 0;
    foreach ($checks as $c) {
        $w = $weights[$c['severity']] ?? 5;
        $max += $w;
        if (empty($c['ok'])) {
            $lost += $w;
        }
    }
    $score = $max > 0 ? (int) round(100 * (1 - $lost / $max)) : 100;
    $grade = match (true) {
        $score >= 90 => 'excellent',
        $score >= 75 => 'good',
        $score >= 50 => 'fair',
        default      => 'poor',
    };
    return ['score' => $score, 'grade' => $grade, 'failed' => count(array_filter($checks, fn($c) => empty($c['ok'])))];
}

function sh_sec_severity_icon(string $severity): string
{
    return match ($severity) {
        'critical' => 'circle-xmark',
        'high'     => 'triangle-exclamation',
        'medium'   => 'circle-exclamation',
        'low'      => 'circle-info',
        default    => 'circle',
    };
}

function sh_sec_port_status_class(string $status): string
{
    return match ($status) {
        'open'     => 'is-open',
        'closed'   => 'is-closed',
        'filtered' => 'is-filtered',
        'current'  => 'is-current',
        default    => '',
    };
}

/** Demo admin sees polished snapshot (like a properly hardened production site). */
function sh_sec_use_demo_snapshot(): bool
{
    if (!function_exists('sh_admin_is_demo_user')) {
        $auth = __DIR__ . '/admin-auth.php';
        if (is_file($auth)) {
            require_once $auth;
        }
    }
    return function_exists('sh_admin_is_demo_user') && sh_admin_is_demo_user();
}

/** @return array{host:string,ports:list<array<string,mixed>>,elapsed_ms:float,web_port:int,https:bool,demo:bool} */
function sh_sec_demo_port_scan(string $host = '127.0.0.1'): array
{
    $rows = [];
    foreach (sh_sec_port_definitions() as $def) {
        $port = (int) $def['port'];
        $status = match ($def['group']) {
            'web' => $port === 443 ? 'current' : ($port === 80 ? 'closed' : 'closed'),
            default => 'closed',
        };
        $risk = $status === 'current' ? 'low' : 'info';
        $rows[] = [
            'id'      => $def['id'],
            'port'    => $port,
            'service' => $def['service'],
            'group'   => $def['group'],
            'status'  => $status,
            'risk'    => $risk,
            'current' => $port === 443,
        ];
    }
    return [
        'host'       => $host,
        'ports'      => $rows,
        'elapsed_ms' => 4.2,
        'web_port'   => 443,
        'https'      => true,
        'demo'       => true,
    ];
}

/** @return list<array{id:string,severity:string,ok:bool,label:string,detail:string,fix_url:string}> */
function sh_sec_demo_vulnerability_checks(array $labels = []): array
{
    $keys = [
        ['demo_admin', 'critical', 'check_demo_admin', 'detail_demo_admin_ok'],
        ['https', 'high', 'check_https', 'detail_https_ok'],
        ['display_errors', 'high', 'check_display_errors', 'detail_display_errors_ok'],
        ['expose_php', 'medium', 'check_expose_php', 'detail_expose_php_ok'],
        ['allow_url_include', 'critical', 'check_allow_url_include', 'detail_allow_url_include_ok'],
        ['php_version', 'medium', 'check_php_version', 'detail_php_version_ok'],
        ['install_dir', 'high', 'check_install_dir', 'detail_install_dir_ok'],
        ['health_file', 'medium', 'check_health_file', 'detail_health_file_ok'],
        ['session_httponly', 'medium', 'check_session_httponly', 'detail_session_httponly_ok'],
        ['session_secure', 'medium', 'check_session_secure', 'detail_session_secure_ok'],
        ['recaptcha', 'medium', 'check_recaptcha', 'detail_recaptcha_ok'],
        ['writable_includes', 'high', 'check_writable_includes', 'detail_writable_includes_ok'],
        ['writable_admin', 'high', 'check_writable_admin', 'detail_writable_admin_ok'],
        ['db_config_perms', 'high', 'check_db_config_perms', 'detail_db_config_perms_ok'],
        ['sensitive_files', 'high', 'check_sensitive_files', ''],
        ['demo_mode', 'low', 'check_demo_mode', 'detail_demo_mode_ok'],
    ];
    $checks = [];
    foreach ($keys as [$id, $severity, $labelKey, $detailKey]) {
        $checks[] = [
            'id'       => $id,
            'severity' => $severity,
            'ok'       => true,
            'label'    => $labels[$labelKey] ?? $labelKey,
            'detail'   => $detailKey !== '' ? ($labels[$detailKey] ?? '') : '',
            'fix_url'  => '',
        ];
    }
    return $checks;
}

/** @return array<string, mixed> */
function sh_sec_demo_server_snapshot(): array
{
    return [
        'php_version'     => '8.2.18',
        'php_sapi'        => 'fpm-fcgi',
        'os'              => 'Linux',
        'server_software' => 'LiteSpeed',
        'document_root'   => '/home/example/public_html',
        'shop_root'       => '/home/example/public_html/shop',
        'https'           => true,
        'server_port'     => 443,
        'remote_addr'     => '203.0.113.10',
        'ini_memory'      => '256M',
        'ini_max_upload'  => '32M',
        'demo'            => true,
    ];
}

/** @return list<array<string,mixed>> */
function sh_sec_demo_trend(int $days = 14): array
{
    $rows = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
        $sec = 92 + ($i % 4);
        $rows[] = [
            'date'       => $d,
            'overall'    => $sec + 2,
            'seo'        => $sec + 4,
            'security'   => $sec,
            'content'    => $sec + 1,
            'conversion' => $sec - 1,
            'demo'       => true,
        ];
    }
    return $rows;
}

/**
 * Console log lines for AI security scanner (admin AJAX).
 *
 * @param list<array{id:string,severity:string,ok:bool,label:string,detail:string}> $checks
 * @param array{score:int,failed:int,grade:string} $scoreResult
 * @param array{demo?:bool,summary?:string} $scanResult
 * @return list<array{type:string,text:string}>
 */
function sh_sec_ai_console_lines(array $checks, array $scoreResult, array $scanResult): array
{
    $failed = array_values(array_filter($checks, static fn($c) => empty($c['ok'])));
    $lines = [
        ['type' => 'info', 'text' => 'Security agent initialized'],
        ['type' => 'info', 'text' => 'Loaded ' . count($checks) . ' vulnerability checks'],
    ];
    if ($failed !== []) {
        $lines[] = ['type' => 'warn', 'text' => count($failed) . ' open issue(s) detected'];
        foreach (array_slice($failed, 0, 6) as $c) {
            $lines[] = [
                'type' => 'warn',
                'text' => '[' . strtoupper((string) ($c['severity'] ?? 'medium')) . '] ' . (string) ($c['label'] ?? 'Issue'),
            ];
        }
        if (count($failed) > 6) {
            $lines[] = ['type' => 'info', 'text' => '… and ' . (count($failed) - 6) . ' more'];
        }
    } else {
        $lines[] = ['type' => 'ok', 'text' => 'No open vulnerability checks'];
    }
    $lines[] = ['type' => 'info', 'text' => 'Security score: ' . (int) ($scoreResult['score'] ?? 0) . '/100'];
    if (!empty($scanResult['demo'])) {
        $lines[] = ['type' => 'demo', 'text' => 'Demo mode — rule-based remediation plan'];
    } else {
        $lines[] = ['type' => 'ok', 'text' => 'AI remediation plan generated'];
    }
    if (!empty($scanResult['summary'])) {
        $lines[] = ['type' => 'info', 'text' => (string) $scanResult['summary']];
    }
    $lines[] = ['type' => 'ok', 'text' => 'Scan complete'];
    return $lines;
}