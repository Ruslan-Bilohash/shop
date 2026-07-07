<?php
$ss = $t['screenshots'] ?? [];
if (($ss['title'] ?? '') === '') {
    return;
}

global $lang;
$langQ = ($lang ?? 'no') !== 'no' ? '?lang=' . urlencode((string) $lang) : '';
$carousel_src = shs_url('screenshots-carousel.php') . $langQ;
$loading = $ss['loading'] ?? 'Loading gallery…';
$load_error = $ss['load_error'] ?? 'Could not load gallery.';
?>
<section class="shs-section shs-screenshots" id="screenshots">
    <div class="shs-container">
        <h2 class="shs-section-title"><?= htmlspecialchars($ss['title']) ?></h2>
        <?php if (!empty($ss['intro'])): ?>
        <p class="shs-lead shs-text-center shs-screenshots-intro"><?= htmlspecialchars($ss['intro']) ?></p>
        <?php endif; ?>
        <div
            id="shsScreenshotsHost"
            class="shs-screenshots-host"
            data-shs-carousel-src="<?= htmlspecialchars($carousel_src) ?>"
            data-shs-error="<?= htmlspecialchars($load_error) ?>"
            aria-live="polite"
        >
            <p class="shs-screenshots-loading" aria-busy="true"><?= htmlspecialchars($loading) ?></p>
        </div>
    </div>
</section>