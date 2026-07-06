<?php
require_once __DIR__ . '/init.php';
require_once dirname(__DIR__) . '/includes/code-editor-lib.php';
sh_admin_require();

$admin_page = 'code-editor';
$tp = $ta;
$page_title = $ta['code_editor'] ?? 'Code editor';
$files = sh_code_editor_files();
$fileId = trim($_GET['file'] ?? 'llms.txt');
if (!sh_code_editor_file_id_valid($fileId)) {
    $fileId = 'llms.txt';
}

$flash = '';
$errors = [];
$content = sh_code_editor_read($fileId);
if ($content === null) {
    $errors[] = $ta['code_editor_read_error'] ?? 'Could not read file.';
    $content = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = trim($_POST['file_id'] ?? '');
    $postContent = (string) ($_POST['file_content'] ?? '');
    if (!sh_code_editor_file_id_valid($postId)) {
        $errors[] = $ta['code_editor_invalid_file'] ?? 'Unknown file.';
    } elseif (!sh_code_editor_write($postId, $postContent)) {
        $errors[] = $ta['code_editor_save_error'] ?? 'Could not save file.';
        $flash = 'error';
    } else {
        $_SESSION['sh_admin_flash'] = [
            'type' => 'success',
            'msg'  => sprintf($ta['code_editor_saved'] ?? 'File %s saved.', $files[$postId]['label'] ?? $postId),
        ];
        header('Location: ' . sh_admin_url('code-editor.php?file=' . urlencode($postId)));
        exit;
    }
    $fileId = $postId;
    $content = $postContent;
}

$fileMeta = $files[$fileId];
$admin_flash = ($flash === 'error' && $errors !== [])
    ? ['type' => 'error', 'msg' => implode(' ', $errors)]
    : null;

require __DIR__ . '/includes/layout.php';
?>

<p class="adm-lead adm-lead-compact"><?= htmlspecialchars($ta['code_editor_lead'] ?? 'Edit storefront text and PHP files with syntax highlighting.') ?></p>

<div class="adm-code-files-layout">
    <aside class="adm-code-files-nav" aria-label="<?= htmlspecialchars($ta['code_editor_files'] ?? 'Files') ?>">
        <h2 class="adm-code-files-nav-title"><i class="fas fa-folder-open"></i> <?= htmlspecialchars($ta['code_editor_files'] ?? 'Files') ?></h2>
        <ul class="adm-code-files-list">
            <?php foreach ($files as $id => $meta): ?>
            <li>
                <a href="<?= sh_admin_url('code-editor.php?file=' . urlencode($id)) ?>"
                   class="adm-code-files-link<?= $id === $fileId ? ' is-active' : '' ?>">
                    <i class="fas fa-<?= ($meta['ext'] ?? '') === 'php' ? 'file-code' : 'file-lines' ?>" aria-hidden="true"></i>
                    <span>
                        <strong><?= htmlspecialchars($meta['label']) ?></strong>
                        <small><?= htmlspecialchars($meta['description']) ?></small>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php if (!empty($ta['code_editor_custom_moved'])): ?>
        <p class="adm-code-files-note">
            <i class="fas fa-circle-info"></i>
            <?= htmlspecialchars($ta['code_editor_custom_moved']) ?>
            <a href="<?= sh_admin_url('settings-advanced.php#advanced-custom-code') ?>"><?= htmlspecialchars($ta['settings_tab_advanced'] ?? 'General') ?></a>
        </p>
        <?php endif; ?>
    </aside>

    <div class="adm-code-files-main">
        <form method="post" class="adm-settings-form" id="shCodeFilesForm">
            <input type="hidden" name="file_id" value="<?= htmlspecialchars($fileId) ?>">
            <div class="adm-card">
                <div class="adm-card-head adm-card-head--stack">
                    <h2>
                        <i class="fas fa-<?= ($fileMeta['ext'] ?? '') === 'php' ? 'file-code' : 'file-alt' ?>"></i>
                        <?= htmlspecialchars($fileMeta['label']) ?>
                    </h2>
                    <?php if (!str_starts_with($fileId, 'data/')): ?>
                    <a href="<?= htmlspecialchars(sh_url($fileId)) ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($ta['code_editor_view_public'] ?? 'View on site') ?>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="adm-card-body padded">
                    <p class="adm-help adm-help-compact"><?= htmlspecialchars($fileMeta['description']) ?></p>
                    <div class="adm-cm-wrap adm-cm-wrap--tall">
                        <textarea name="file_content" rows="22" class="adm-code-input adm-code-mirror adm-code-mirror--file"
                                  data-mode="<?= htmlspecialchars($fileMeta['mode']) ?>" id="shCodeFileEditor"><?= htmlspecialchars($content) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="adm-form-actions adm-form-actions-sticky">
                <button type="submit" class="adm-btn adm-btn-primary"><i class="fas fa-save"></i> <?= htmlspecialchars(sh_settings_admin_label('save', $ta)) ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-end.php';