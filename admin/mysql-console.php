<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

$mysqlPath = dirname(__DIR__) . '/includes/mysql-console.php';
if (!is_file($mysqlPath)) {
    http_response_code(404);
    exit('MySQL console is available only in MySQL edition.');
}
require_once $mysqlPath;

if (!sh_mysql_storage_available()) {
    http_response_code(503);
    exit('Database not connected. Run install.php or check data/db.config.php.');
}

if (!sh_mysql_console_visible()) {
    header('Location: ' . sh_admin_url('index.php'), true, 302);
    exit;
}

$admin_page = 'mysql-console';
$ta = $t['admin'] ?? [];
$mp = $ta['mysql_console_page'] ?? [];
$page_title = $mp['title'] ?? 'MySQL console';

$result = null;
$query = '';
$limit = 200;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = trim((string) ($_POST['sql'] ?? ''));
    $limit = max(1, min(500, (int) ($_POST['limit'] ?? 200)));
    if ($query !== '') {
        $result = sh_mysql_run_query($query, $limit);
    }
}

$tables = sh_mysql_table_list();
$prefix = function_exists('sh_db_prefix') ? sh_db_prefix() : 'sh_';

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-card">
    <div class="adm-card-head">
        <h2><i class="fas fa-database"></i> <?= htmlspecialchars($page_title) ?></h2>
        <span class="adm-badge adm-badge-info"><?= htmlspecialchars($mp['mysql_badge'] ?? 'MySQL') ?></span>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help"><?= htmlspecialchars($mp['help'] ?? 'Read-only queries: SELECT, SHOW, DESCRIBE, EXPLAIN. Single statement only.') ?></p>

        <?php if ($tables !== []): ?>
        <div class="adm-mysql-tables">
            <strong><?= htmlspecialchars($mp['tables'] ?? 'Tables') ?>:</strong>
            <?php foreach ($tables as $tbl): ?>
            <button type="button" class="adm-btn adm-btn-outline adm-btn-sm adm-mysql-table-btn" data-sql="SELECT * FROM `<?= htmlspecialchars($tbl) ?>` LIMIT 20"><?= htmlspecialchars($tbl) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="post" class="adm-mysql-form">
            <div class="adm-field">
                <label for="shMysqlSql"><?= htmlspecialchars($mp['query_label'] ?? 'SQL query') ?></label>
                <textarea id="shMysqlSql" name="sql" rows="8" class="adm-textarea adm-code-textarea" spellcheck="false" placeholder="SELECT * FROM `<?= htmlspecialchars($prefix) ?>products` LIMIT 10"><?= htmlspecialchars($query) ?></textarea>
            </div>
            <div class="adm-field adm-field--inline">
                <label for="shMysqlLimit"><?= htmlspecialchars($mp['limit_label'] ?? 'Row limit') ?></label>
                <input type="number" id="shMysqlLimit" name="limit" value="<?= (int) $limit ?>" min="1" max="500" class="adm-input-sm">
            </div>
            <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-play"></i> <?= htmlspecialchars($mp['run'] ?? 'Run query') ?></button>
        </form>

        <?php if ($result !== null): ?>
        <div class="adm-mysql-result adm-alert adm-alert-<?= $result['ok'] ? 'success' : 'error' ?> adm-alert-compact">
            <?php if ($result['ok']): ?>
            <?= htmlspecialchars(sprintf($mp['result_ok'] ?? '%d rows in %s ms', $result['row_count'], $result['elapsed_ms'])) ?>
            <?php else: ?>
            <?= htmlspecialchars($result['error']) ?>
            <?php endif; ?>
        </div>
        <?php if ($result['ok'] && $result['rows'] !== []): ?>
        <div class="adm-table-wrap adm-mysql-table-wrap">
            <table class="adm-table adm-table-compact">
                <thead>
                    <tr>
                        <?php foreach ($result['columns'] as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($result['columns'] as $col): ?>
                        <td><?= htmlspecialchars(is_scalar($row[$col] ?? '') ? (string) ($row[$col] ?? '') : json_encode($row[$col], JSON_UNESCAPED_UNICODE)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php elseif ($result['ok']): ?>
        <p class="adm-muted"><?= htmlspecialchars($mp['no_rows'] ?? 'Query OK — no rows returned.') ?></p>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.adm-mysql-table-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var ta = document.getElementById('shMysqlSql');
        if (ta) ta.value = btn.getAttribute('data-sql') || '';
    });
});
</script>

<?php require __DIR__ . '/includes/layout-end.php'; ?>