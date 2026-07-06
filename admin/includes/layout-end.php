        </div>
    </main>
</div>
<?php if (!empty($admin_extra_js) && is_array($admin_extra_js)): ?>
    <?php foreach ($admin_extra_js as $js): ?>
<script src="<?= htmlspecialchars($js) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'ai'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-ai-settings.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'footer'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-footer.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'header'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-header-nav.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'languages'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-languages.js')) ?>?v=3" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'store'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-store.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'pages'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-service-pages.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'seo' && empty($admin_extra_js)): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-seo-settings.js')) ?>?v=1" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'chat'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-chat-settings.js')) ?>?v=2" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'homepage' || ($settings_tab ?? '') === 'block_builder' || ($admin_page ?? '') === 'code-editor'): ?>
<?php require __DIR__ . '/code-editor-scripts.php'; ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-code-editor.js')) ?>?v=5"></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'homepage'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-homepage.js')) ?>?v=3"></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'block_builder'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-block-builder-delete.js')) ?>?v=1" defer></script>
<script src="<?= htmlspecialchars(sh_asset('js/admin-block-builder.js')) ?>?v=7" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'nova_poshta'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-nova-poshta.js')) ?>?v=1" defer></script>
<?php endif; ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-topbar.js')) ?>?v=1" defer></script>
<script>
(function () {
    var btn = document.getElementById('admMenuBtn');
    var sidebar = document.getElementById('admSidebar');
    var overlay = document.getElementById('admOverlay');
    if (!btn || !sidebar) return;
    function toggle(open) {
        sidebar.classList.toggle('open', open);
        if (overlay) { overlay.hidden = !open; }
        document.body.classList.toggle('adm-nav-open', open);
    }
    btn.addEventListener('click', function () { toggle(!sidebar.classList.contains('open')); });
    if (overlay) overlay.addEventListener('click', function () { toggle(false); });
})();
</script>
</body>
</html>