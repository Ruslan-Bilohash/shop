<?php
/** @var list<array{key:string,ok:bool,label:string,url:string}> $health */
/** @var array $dp */
/** @var bool $health_all_ok */
?>
<?php if ($health_all_ok): ?>
<div class="adm-launch-success" id="admLaunchSuccess">
    <div class="adm-launch-success-icon"><i class="fas fa-circle-check"></i></div>
    <div class="adm-launch-success-text">
        <strong><?= htmlspecialchars($dp['health_done_title'] ?? 'Launch checklist complete') ?></strong>
        <p><?= htmlspecialchars($dp['health_done_text'] ?? 'Your store is configured and ready for visitors. Great work!') ?></p>
    </div>
    <a href="<?= sh_url('index.php') ?>" class="adm-btn adm-btn-outline adm-btn-sm" target="_blank" rel="noopener noreferrer">
        <i class="fas fa-store"></i> <?= htmlspecialchars($dp['health_done_cta'] ?? 'View storefront') ?>
    </a>
</div>
<?php else: ?>
<div class="adm-card adm-card--checklist" id="admLaunchChecklist">
    <div class="adm-card-head">
        <h2><i class="fas fa-heart-pulse"></i> <?= htmlspecialchars($dp['health_title'] ?? 'Launch checklist') ?></h2>
        <?php
        $done = count(array_filter($health, fn($c) => !empty($c['ok'])));
        $total = count($health);
        ?>
        <span class="adm-badge adm-badge--blue"><?= $done ?>/<?= $total ?></span>
    </div>
    <div class="adm-card-body padded">
        <p class="adm-help adm-help-compact"><?= htmlspecialchars($dp['health_intro'] ?? 'Complete these steps before going live.') ?></p>
        <ul class="adm-health-list">
            <?php foreach ($health as $check): ?>
            <li class="adm-health-item <?= $check['ok'] ? 'is-ok' : 'is-pending' ?>">
                <span class="adm-health-icon"><i class="fas fa-<?= $check['ok'] ? 'check-circle' : 'circle' ?>"></i></span>
                <span class="adm-health-label"><?= htmlspecialchars($check['label']) ?></span>
                <?php if (!$check['ok']): ?>
                <a href="<?= htmlspecialchars($check['url']) ?>" class="adm-health-link"><?= htmlspecialchars($dp['health_fix'] ?? 'Configure') ?> <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>