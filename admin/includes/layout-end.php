        </div>
    </main>
</div>
<?php if (!empty($admin_extra_js) && is_array($admin_extra_js)): ?>
    <?php foreach ($admin_extra_js as $js): ?>
<script src="<?= htmlspecialchars($js) ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'ai'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-ai-settings.js')) ?>?v=2" defer></script>
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
<?php if (($settings_tab ?? '') === 'taxes'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-taxes.js')) ?>?v=1" defer></script>
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
<?php if (($settings_tab ?? '') === 'homepage' || ($settings_tab ?? '') === 'block_builder' || ($settings_tab ?? '') === 'advanced' || ($admin_page ?? '') === 'code-editor'): ?>
<?php require __DIR__ . '/code-editor-scripts.php'; ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-code-editor.js')) ?>?v=6"></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'homepage'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-homepage.js')) ?>?v=4"></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'block_builder'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-block-builder-delete.js')) ?>?v=1" defer></script>
<script src="<?= htmlspecialchars(sh_asset('js/admin-block-builder.js')) ?>?v=7" defer></script>
<?php endif; ?>
<?php if (($settings_tab ?? '') === 'nova_poshta'): ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-nova-poshta.js')) ?>?v=1" defer></script>
<?php endif; ?>
<script src="<?= htmlspecialchars(sh_asset('js/admin-topbar.js')) ?>?v=1" defer></script>
<script src="<?= htmlspecialchars(sh_asset('js/admin-flash.js')) ?>?v=1" defer></script>
<?php
$taWidget = $ta ?? ($t['admin'] ?? []);
$aiWidget = is_array($taWidget['ai_agent_widget'] ?? null) ? $taWidget['ai_agent_widget'] : [];
if (($admin_page ?? '') !== 'ai-agent' && ($aiWidget['enabled'] ?? true) !== false):
?>
<button type="button" class="adm-ai-fab" id="shAiAgentFab" aria-expanded="false" aria-controls="shAiAgentWidget" title="<?= htmlspecialchars($aiWidget['fab_title'] ?? 'AI Advisor') ?>">
    <i class="fas fa-robot" aria-hidden="true"></i>
</button>
<div class="adm-ai-widget" id="shAiAgentWidget" hidden
     data-api="<?= htmlspecialchars(sh_admin_url('api/ai-admin-agent.php')) ?>"
     data-lang="<?= htmlspecialchars($lang ?? 'en') ?>"
     data-thinking="<?= htmlspecialchars($aiWidget['thinking'] ?? 'Thinking…') ?>"
     data-error="<?= htmlspecialchars($aiWidget['error_generic'] ?? 'Request failed') ?>"
     data-demo="<?= htmlspecialchars($aiWidget['demo_badge'] ?? 'Demo') ?>"
     data-placeholder="<?= htmlspecialchars($aiWidget['input_ph'] ?? 'Ask the AI advisor…') ?>">
    <div class="adm-ai-widget-head">
        <strong><i class="fas fa-robot"></i> <?= htmlspecialchars($aiWidget['title'] ?? 'AI Advisor') ?></strong>
        <div class="adm-ai-widget-head-actions">
            <a href="<?= htmlspecialchars(sh_admin_url('ai-agent.php')) ?>" class="adm-ai-widget-expand"><?= htmlspecialchars($aiWidget['open_full'] ?? 'Open full') ?></a>
            <button type="button" class="adm-ai-widget-close" data-sh-ai-widget-close aria-label="<?= htmlspecialchars($aiWidget['close'] ?? 'Close') ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="adm-ai-messages adm-ai-messages--widget" id="shAiWidgetMessages" aria-live="polite"></div>
    <div id="shAiWidgetTips" class="adm-ai-widget-tips" hidden></div>
    <form class="adm-ai-compose adm-ai-compose--widget" id="shAiWidgetForm">
        <textarea id="shAiWidgetInput" rows="2" placeholder="<?= htmlspecialchars($aiWidget['input_ph'] ?? '') ?>"></textarea>
        <button type="submit" class="adm-btn adm-btn-primary adm-btn-sm"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
<script src="<?= htmlspecialchars(sh_asset('js/admin-ai-agent.js')) ?>?v=4" defer></script>
<?php endif; ?>
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