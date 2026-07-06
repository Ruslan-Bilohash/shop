<?php
$current = shs_langs()[$lang] ?? shs_langs()['no'];
$variant = $variant ?? 'header';
$isMobile = $variant === 'mobile';
?>
<details class="shs-lang-details<?= $isMobile ? ' shs-lang-details--mobile' : '' ?>" id="shsLangDetails">
    <summary class="shs-lang-btn" aria-label="<?= htmlspecialchars($t['nav']['lang_menu'] ?? 'Language') ?>">
        <span class="shs-lang-flag" aria-hidden="true"><?= $current['flag'] ?></span>
        <?php if (!$isMobile): ?>
        <span class="shs-lang-code"><?= htmlspecialchars($current['label']) ?></span>
        <span class="shs-lang-chevron"><i class="fas fa-chevron-down" aria-hidden="true"></i></span>
        <?php endif; ?>
    </summary>
    <ul class="shs-lang-menu" role="list">
        <?php foreach (shs_langs() as $code => $info): ?>
        <li>
            <a href="<?= htmlspecialchars(shs_lang_url($code)) ?>" class="<?= $lang === $code ? 'active' : '' ?>" hreflang="<?= $code === 'uk' ? 'uk' : $code ?>">
                <span aria-hidden="true"><?= $info['flag'] ?></span> <?= htmlspecialchars($info['name']) ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</details>