<?php
/** Language dropdown — expects $lang, sh_langs(), sh_lang_url(); optional $lang_dropdown_variant = 'header' | 'mobile' */
$current = sh_langs()[$lang] ?? sh_langs()['no'];
$variant = $lang_dropdown_variant ?? '';
$idSuffix = match ($variant) {
    'header' => 'Header',
    'mobile' => 'Mobile',
    default  => '',
};
$rootClass = 'sh-lang-dropdown' . ($variant !== '' ? ' sh-lang-dropdown--' . $variant : '');
?>
<div class="<?= $rootClass ?>" id="shLangDropdown<?= $idSuffix ?>">
    <button type="button" class="sh-lang-dropdown-btn" id="shLangBtn<?= $idSuffix ?>" aria-expanded="false" aria-haspopup="listbox" aria-controls="shLangMenu<?= $idSuffix ?>" aria-label="<?= htmlspecialchars($t['nav']['lang_menu'] ?? $current['name']) ?>">
        <span class="sh-lang-dropdown-current">
            <?php if ($variant === 'mobile'): ?>
            <span class="sh-lang-flag-only" aria-hidden="true"><?= $current['flag'] ?></span>
            <?php else: ?>
            <span class="sh-lang-flag" aria-hidden="true"><?= $current['flag'] ?></span>
            <span class="sh-lang-code"><?= htmlspecialchars($current['label']) ?></span>
            <?php endif; ?>
        </span>
        <?php if ($variant !== 'mobile'): ?>
        <span class="sh-lang-chevron" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
        <?php endif; ?>
    </button>
    <ul class="sh-lang-dropdown-menu" id="shLangMenu<?= $idSuffix ?>" role="listbox" hidden>
        <?php foreach (sh_langs() as $code => $info): ?>
        <li role="option">
            <a href="<?= htmlspecialchars(sh_lang_url($code)) ?>" class="<?= $lang === $code ? 'active' : '' ?>" <?= $lang === $code ? 'aria-current="true"' : '' ?> hreflang="<?= $code === 'uk' ? 'uk' : $code ?>">
                <span class="sh-lang-flag"><?= $info['flag'] ?></span>
                <span class="sh-lang-name"><?= htmlspecialchars($info['name']) ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>