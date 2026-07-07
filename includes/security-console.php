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