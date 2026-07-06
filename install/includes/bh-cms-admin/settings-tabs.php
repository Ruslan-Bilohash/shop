<?php
/** @var callable $adminUrlFn @var array $ta */
if (!function_exists('bh_cms_settings_tabs')) {
    require_once dirname(__DIR__) . '/bh-cms-site-settings.php';
}
?>
<nav class="adm-settings-tabs" aria-label="Settings sections">
    <?php foreach (bh_cms_settings_tabs() as $key => $tab): ?>
    <a href="<?= htmlspecialchars($adminUrlFn($tab['file'])) ?>"
       class="adm-settings-tab <?= bh_cms_settings_tab_active($key) ? 'active' : '' ?>">
        <i class="fas fa-<?= htmlspecialchars($tab['icon']) ?>"></i>
        <span><?= htmlspecialchars(bh_cms_admin_label('settings_tab_' . $key, $ta)) ?></span>
    </a>
    <?php endforeach; ?>
</nav>