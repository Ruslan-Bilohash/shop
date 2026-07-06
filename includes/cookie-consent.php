<?php
/**
 * GDPR-style cookie consent banner for Shop CMS storefront.
 * Expects $t['cookies_banner'] and sh_cookie_consent_enabled().
 */
if (!function_exists('sh_cookie_consent_enabled') || !sh_cookie_consent_enabled()) {
    return;
}
$cb = $t['cookies_banner'] ?? [];
$privacyUrl = $sh_cookie_privacy_url ?? (function_exists('sh_url') ? sh_url('page.php?slug=privacy') : 'https://bilohash.com/website/privacy-policy.php');
$cookiesUrl = $sh_cookie_cookies_url ?? (function_exists('sh_url') ? sh_url('page.php?slug=cookies') : 'https://bilohash.com/website/cookies.php');
$consentKey = 'sh_cookie_consent';
?>
<div id="sh-cookie-banner" class="sh-cookie-banner hidden" role="dialog" aria-labelledby="sh-cookie-title" aria-modal="true">
    <div class="sh-cookie-inner">
        <div class="sh-cookie-text">
            <strong id="sh-cookie-title"><?= htmlspecialchars($cb['title'] ?? 'Cookies') ?></strong>
            <p><?= htmlspecialchars($cb['text'] ?? '') ?></p>
            <div class="sh-cookie-links">
                <a href="<?= htmlspecialchars($privacyUrl) ?>"><?= htmlspecialchars($cb['privacy'] ?? 'Privacy') ?></a>
                <a href="<?= htmlspecialchars($cookiesUrl) ?>"><?= htmlspecialchars($cb['more'] ?? 'Cookies') ?></a>
            </div>
        </div>
        <div class="sh-cookie-actions">
            <button type="button" class="sh-cookie-btn sh-cookie-reject" data-sh-cookie="reject"><?= htmlspecialchars($cb['reject'] ?? 'Reject') ?></button>
            <button type="button" class="sh-cookie-btn sh-cookie-settings" data-sh-cookie="settings"><?= htmlspecialchars($cb['settings'] ?? 'Settings') ?></button>
            <button type="button" class="sh-cookie-btn sh-cookie-accept" data-sh-cookie="accept"><?= htmlspecialchars($cb['accept'] ?? 'Accept') ?></button>
        </div>
    </div>
</div>
<div id="sh-cookie-reject-bar" class="sh-cookie-reject-bar hidden" role="status">
    <p><?= htmlspecialchars($cb['warning'] ?? '') ?></p>
    <button type="button" class="sh-cookie-btn sh-cookie-accept" data-sh-cookie="accept-again"><?= htmlspecialchars($cb['accept_again'] ?? 'Accept') ?></button>
</div>
<div id="sh-cookie-modal" class="sh-cookie-modal hidden" role="dialog" aria-modal="true">
    <div class="sh-cookie-modal-panel">
        <h3><?= htmlspecialchars($cb['modal_title'] ?? 'Cookie settings') ?></h3>
        <p><?= htmlspecialchars($cb['modal_text'] ?? '') ?></p>
        <div class="sh-cookie-modal-actions">
            <button type="button" class="sh-cookie-btn sh-cookie-settings" data-sh-cookie="modal-cancel"><?= htmlspecialchars($cb['modal_cancel'] ?? 'Cancel') ?></button>
            <button type="button" class="sh-cookie-btn sh-cookie-accept" data-sh-cookie="modal-save"><?= htmlspecialchars($cb['modal_save'] ?? 'Save') ?></button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = <?= json_encode($consentKey, JSON_UNESCAPED_UNICODE) ?>;
    var banner = document.getElementById('sh-cookie-banner');
    var rejectBar = document.getElementById('sh-cookie-reject-bar');
    var modal = document.getElementById('sh-cookie-modal');
    if (!banner) return;
    function status() { try { return localStorage.getItem(KEY) || 'pending'; } catch (e) { return 'pending'; } }
    function setStatus(s) { try { localStorage.setItem(KEY, s); } catch (e) {} }
    function hide(el) { if (el) { el.classList.add('hidden'); el.classList.remove('is-visible'); } }
    function show(el) { if (el) { el.classList.remove('hidden'); requestAnimationFrame(function(){ el.classList.add('is-visible'); }); } }
    document.querySelectorAll('[data-sh-cookie]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var a = btn.getAttribute('data-sh-cookie');
            if (a === 'accept' || a === 'accept-again' || a === 'modal-save') {
                setStatus(a === 'modal-save' ? 'custom' : 'accepted');
                hide(banner); hide(rejectBar); hide(modal);
            } else if (a === 'reject') {
                setStatus('rejected'); hide(banner); show(rejectBar);
            } else if (a === 'settings') {
                show(modal);
            } else if (a === 'modal-cancel') {
                hide(modal);
            }
        });
    });
    var st = status();
    if (st === 'accepted' || st === 'custom') { hide(banner); }
    else if (st === 'rejected') { hide(banner); show(rejectBar); }
    else { setTimeout(function(){ show(banner); }, 900); }
})();
</script>