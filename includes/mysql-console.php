<?php
/**
 * Shop CMS — safe read-only MySQL query runner (MySQL edition only).
 */
declare(strict_types=1);

function sh_mysql_storage_available(): bool
{
    return is_file(__DIR__ . '/database.php')
        && function_exists('sh_db_pdo')
        && sh_db_pdo() instanceof PDO;
}

function sh_mysql_query_allowed(string $sql): bool
{
    $sql = trim($sql);
    if ($sql === '') {
        return false;
    }
    if (preg_match('/;.*\S/s', $sql)) {
        return false;
    }
    $first = strtolower(preg_replace('/^\s*(\/\*[\s\S]*?\*\/|--[^\n]*\n|\s)*/', '', $sql));
    return (bool) preg_match('/^(select|show|describe|desc|explain)\b/', $first);
}

/**
 * @return array{ok:bool,rows:list<array<string,mixed>>,columns:list<string>,error:string,elapsed_ms:float,row_count:int}
 */
function sh_mysql_run_query(string $sql, int $limit = 200): array
{
    $empty = ['ok' => false, 'rows' => [], 'columns' => [], 'error' => '', 'elapsed_ms' => 0.0, 'row_count' => 0];
    if (!sh_mysql_storage_available()) {
        $empty['error'] = 'MySQL storage not available';
        return $empty;
    }
    $sql = trim($sql);
    if (!sh_mysql_query_allowed($sql)) {
        $empty['error'] = 'Only SELECT, SHOW, DESCRIBE and EXPLAIN are allowed (single statement).';
        return $empty;
    }
    $limit = max(1, min(500, $limit));
    try {
        $pdo = sh_db_require_pdo();
        $start = microtime(true);
        $stmt = $pdo->query($sql);
        if (!$stmt instanceof PDOStatement) {
            $empty['error'] = 'Query did not return a result set';
            return $empty;
        }
        $rows = [];
        $count = 0;
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            if ($count >= $limit) {
                break;
            }
            $rows[] = $row;
            $count++;
        }
        $columns = $rows !== [] ? array_keys($rows[0]) : [];
        if ($columns === [] && $stmt->columnCount() > 0) {
            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $meta = $stmt->getColumnMeta($i);
                $columns[] = (string) ($meta['name'] ?? 'col' . $i);
            }
        }
        return [
            'ok'         => true,
            'rows'       => $rows,
            'columns'    => $columns,
            'error'      => '',
            'elapsed_ms' => round((microtime(true) - $start) * 1000, 2),
            'row_count'  => $count,
        ];
    } catch (Throwable $e) {
        $empty['error'] = $e->getMessage();
        return $empty;
    }
}

/** @return list<string> */
function sh_mysql_table_list(): array
{
    if (!sh_mysql_storage_available()) {
        return [];
    }
    try {
        $pdo = sh_db_require_pdo();
        $prefix = sh_db_prefix();
        $stmt = $pdo->query('SHOW TABLES');
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $name = (string) ($row[0] ?? '');
            if ($name !== '' && str_starts_with($name, $prefix)) {
                $tables[] = $name;
            }
        }
        sort($tables);
        return $tables;
    } catch (Throwable $e) {
        return [];
    }
}