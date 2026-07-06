<?php
/**
 * Entity edit sub-tabs (product / category).
 * Vars: $edit_tabs (array key=>label), $edit_tab (active key), $edit_tab_base_url
 */
?>
<nav class="adm-edit-tabs" aria-label="Edit sections">
    <?php foreach ($edit_tabs as $key => $label): ?>
    <a href="<?= htmlspecialchars($edit_tab_base_url . (str_contains($edit_tab_base_url, '?') ? '&' : '?') . 'tab=' . urlencode($key)) ?>"
       class="adm-edit-tab <?= ($edit_tab ?? '') === $key ? 'active' : '' ?>"
       data-tab="<?= htmlspecialchars($key) ?>">
        <?= htmlspecialchars($label) ?>
    </a>
    <?php endforeach; ?>
</nav>