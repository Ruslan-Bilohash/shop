<?php
require_once __DIR__ . '/init.php';
sh_admin_require();

$admin_page = 'ai-agent';
$ta = $t['admin'] ?? [];
$ap = $ta['ai_agent_page'] ?? [];
$page_title = $ap['title'] ?? 'AI Advisor';

require_once dirname(__DIR__) . '/includes/subscription-links.php';
require_once dirname(__DIR__) . '/includes/billing-pricing.php';

$admin_extra_js = [sh_asset('js/admin-ai-agent.js') . '?v=2'];
$heroHelp = (string) ($ap['hero_help'] ?? '');
if (str_contains($heroHelp, '{tagline}')) {
    $heroHelp = str_replace('{tagline}', sh_billing_subscription_tagline($lang), $heroHelp);
}
$subUrl = sh_subscription_url();

require __DIR__ . '/includes/layout.php';
?>

<div class="adm-ai-console" id="shAiAgentConsole"
     data-api="<?= htmlspecialchars(sh_admin_url('api/ai-admin-agent.php')) ?>"
     data-lang="<?= htmlspecialchars($lang) ?>"
     data-thinking="<?= htmlspecialchars($ap['thinking'] ?? 'Thinking…') ?>"
     data-error="<?= htmlspecialchars($ap['error_generic'] ?? 'Request failed') ?>"
     data-demo="<?= htmlspecialchars($ap['demo_badge'] ?? 'Demo mode') ?>"
     data-placeholder="<?= htmlspecialchars($ap['input_ph'] ?? 'Ask about SEO, privacy, payments, design…') ?>">

    <div class="adm-card adm-ai-hero">
        <div class="adm-card-body padded">
            <h2 class="adm-ai-hero-title"><i class="fas fa-robot"></i> <?= htmlspecialchars($ap['hero_title'] ?? 'BILOHASH AI Advisor') ?></h2>
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($heroHelp !== '' ? $heroHelp : ($ap['hero_help'] ?? 'Try the assistant as a shop owner or demo visitor. Works without API key in demo mode.')) ?></p>
            <p class="adm-ai-hero-subscribe">
                <a href="<?= htmlspecialchars($subUrl) ?>" class="adm-btn adm-btn-outline adm-btn-sm" <?= sh_subscription_external_attrs() ?>>
                    <i class="fas fa-crown"></i> <?= htmlspecialchars($ap['hero_subscribe'] ?? 'BILOHASH subscription') ?>
                </a>
                <span class="adm-muted adm-ai-hero-tagline"><?= htmlspecialchars(sh_billing_subscription_tagline($lang)) ?></span>
            </p>
            <ul class="adm-ai-starters">
                <?php foreach (($ap['starters'] ?? []) as $starter): ?>
                <li><button type="button" class="adm-btn adm-btn-outline adm-btn-sm sh-ai-starter" data-text="<?= htmlspecialchars($starter) ?>"><?= htmlspecialchars($starter) ?></button></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="adm-card adm-ai-chat-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-comments"></i> <?= htmlspecialchars($ap['chat_title'] ?? 'Chat') ?></h2>
        </div>
        <div class="adm-card-body padded adm-ai-chat-body">
            <div class="adm-ai-messages" id="shAiAgentMessages" aria-live="polite">
                <div class="adm-ai-msg adm-ai-msg--bot">
                    <div class="adm-ai-msg-avatar"><i class="fas fa-robot"></i></div>
                    <div class="adm-ai-msg-bubble"><?= htmlspecialchars($ap['welcome'] ?? 'Hello! I am your Shop CMS advisor. Ask me anything.') ?></div>
                </div>
            </div>
            <form class="adm-ai-compose" id="shAiAgentForm">
                <textarea id="shAiAgentInput" rows="2" placeholder="<?= htmlspecialchars($ap['input_ph'] ?? '') ?>" required></textarea>
                <button type="submit" class="adm-btn adm-btn-primary" id="shAiAgentSend">
                    <i class="fas fa-paper-plane"></i> <?= htmlspecialchars($ap['send'] ?? 'Send') ?>
                </button>
            </form>
        </div>
    </div>

    <aside class="adm-card adm-ai-tips-card">
        <div class="adm-card-head">
            <h2><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($ap['tips_title'] ?? 'AI tips') ?></h2>
        </div>
        <div class="adm-card-body padded" id="shAiAgentTips">
            <p class="adm-help adm-help-compact"><?= htmlspecialchars($ap['tips_help'] ?? 'Tips appear after each reply.') ?></p>
        </div>
    </aside>
</div>

<?php require __DIR__ . '/includes/layout-end.php'; ?>