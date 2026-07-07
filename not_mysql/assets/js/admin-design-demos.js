(function () {
    'use strict';
    var root = document.getElementById('admDesignDemos');
    if (!root) return;

    var api = root.dataset.api || '';
    var previewBase = root.dataset.previewBase || '../index.php';
    var activeTheme = root.dataset.active || 'nordic';
    var search = document.getElementById('admDdSearch');
    var list = document.getElementById('admDdList');
    var preview = document.getElementById('admDdPreview');
    var iframe = document.getElementById('admDdIframe');
    var openLive = document.getElementById('admDdOpenLive');
    var applyBtn = document.getElementById('admDdApply');
    var msgEl = document.getElementById('admDdMsg');
    var cards = list ? Array.prototype.slice.call(list.querySelectorAll('.adm-dd-card')) : [];
    var selectedId = activeTheme;

    function showMsg(text, ok) {
        if (!msgEl) return;
        msgEl.hidden = false;
        msgEl.textContent = text;
        msgEl.className = 'adm-dd-msg ' + (ok ? 'is-ok' : 'is-err');
    }

    function previewUrl(themeId) {
        var sep = previewBase.indexOf('?') >= 0 ? '&' : '?';
        return previewBase + sep + 'theme_preview=' + encodeURIComponent(themeId);
    }

    function updateIframe(themeId) {
        if (!iframe) return;
        iframe.src = previewUrl(themeId);
        if (openLive) openLive.href = previewUrl(themeId);
    }

    function filterCards(q) {
        var query = (q || '').toLowerCase().trim();
        cards.forEach(function (card) {
            var hay = (card.dataset.search || '').toLowerCase();
            var show = query === '' || hay.indexOf(query) !== -1;
            card.hidden = !show;
            card.classList.toggle('is-hidden', !show);
        });
        var visible = cards.filter(function (c) { return !c.hidden; });
        if (visible.length && !visible.some(function (c) { return c.classList.contains('is-selected'); })) {
            selectCard(visible[0]);
        }
    }

    function selectCard(card) {
        if (!card || !preview) return;
        cards.forEach(function (c) { c.classList.remove('is-selected'); });
        card.classList.add('is-selected');
        selectedId = card.dataset.themeId || 'nordic';
        var themeClass = card.dataset.themeClass || '';
        preview.className = 'adm-dd-live-preview ' + themeClass;
        var previewTitle = document.getElementById('admDdPreviewTitle');
        var previewDesc = document.getElementById('admDdPreviewDesc');
        if (previewTitle) previewTitle.textContent = card.dataset.title || '';
        if (previewDesc) previewDesc.textContent = card.dataset.desc || '';
        updateIframe(selectedId);
    }

    if (search) {
        search.addEventListener('input', function () { filterCards(search.value); });
    }

    cards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            if (e.target.closest('a')) return;
            selectCard(card);
        });
    });

    var initial = cards.find(function (c) { return (c.dataset.themeId || '') === activeTheme; }) || cards[0];
    if (initial) selectCard(initial);

    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            applyBtn.disabled = true;
            fetch(api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ theme_id: selectedId })
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res.ok) {
                    activeTheme = res.theme_id || selectedId;
                    cards.forEach(function (c) {
                        c.classList.toggle('is-live', (c.dataset.themeId || '') === activeTheme);
                    });
                    showMsg('Theme applied: ' + activeTheme, true);
                } else {
                    showMsg(res.error || 'Apply failed', false);
                }
                applyBtn.disabled = false;
            }).catch(function () {
                showMsg('Network error', false);
                applyBtn.disabled = false;
            });
        });
    }
})();