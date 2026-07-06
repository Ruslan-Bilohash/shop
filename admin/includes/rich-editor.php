<?php
/**
 * Built-in rich text editor (contenteditable + toolbar, no external API).
 */
function sh_admin_rich_editor(string $name, string $value, string $editorId, array $ta = []): void
{
    require_once dirname(__DIR__, 2) . '/includes/service-pages.php';
    $placeholder = sh_settings_admin_label('page_content_editor_ph', $ta);
    $surfaceHtml = str_contains($value, '<')
        ? sh_sanitize_service_html($value)
        : nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    ?>
    <div class="adm-rich-editor" data-editor-id="<?= htmlspecialchars($editorId) ?>">
        <div class="adm-rich-toolbar" id="<?= htmlspecialchars($editorId) ?>-toolbar">
            <span class="adm-rich-toolbar-group">
                <button type="button" class="adm-rich-btn" data-format="bold" title="Bold"><i class="fas fa-bold"></i></button>
                <button type="button" class="adm-rich-btn" data-format="italic" title="Italic"><i class="fas fa-italic"></i></button>
                <button type="button" class="adm-rich-btn" data-format="underline" title="Underline"><i class="fas fa-underline"></i></button>
            </span>
            <span class="adm-rich-toolbar-group">
                <button type="button" class="adm-rich-btn" data-format="header" data-value="2" title="Heading"><i class="fas fa-heading"></i></button>
                <button type="button" class="adm-rich-btn" data-format="list" data-value="bullet" title="List"><i class="fas fa-list-ul"></i></button>
                <button type="button" class="adm-rich-btn" data-format="list" data-value="ordered" title="Numbered"><i class="fas fa-list-ol"></i></button>
            </span>
            <span class="adm-rich-toolbar-group">
                <button type="button" class="adm-rich-btn" data-format="link" title="Link"><i class="fas fa-link"></i></button>
                <button type="button" class="adm-rich-btn" data-format="blockquote" title="Quote"><i class="fas fa-quote-right"></i></button>
                <button type="button" class="adm-rich-btn" data-format="clean" title="Clear"><i class="fas fa-eraser"></i></button>
            </span>
        </div>
        <div class="adm-rich-surface" id="<?= htmlspecialchars($editorId) ?>" contenteditable="true" data-placeholder="<?= htmlspecialchars($placeholder) ?>"><?= $surfaceHtml ?></div>
        <textarea name="<?= htmlspecialchars($name) ?>" id="<?= htmlspecialchars($editorId) ?>-input" class="adm-rich-input" hidden><?= htmlspecialchars($value) ?></textarea>
    </div>
    <?php
}