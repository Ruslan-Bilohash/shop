(function () {
    'use strict';
    var page = document.querySelector('.adm-dd-page');
    if (!page) return;

    var search = document.getElementById('admDdSearch');
    var list = document.getElementById('admDdList');
    var preview = document.getElementById('admDdPreview');
    var previewTitle = document.getElementById('admDdPreviewTitle');
    var previewDesc = document.getElementById('admDdPreviewDesc');
    var cards = list ? Array.prototype.slice.call(list.querySelectorAll('.adm-dd-card')) : [];

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
        var themeClass = card.dataset.themeClass || '';
        preview.className = 'adm-dd-live-preview ' + themeClass;
        if (previewTitle) previewTitle.textContent = card.dataset.title || '';
        if (previewDesc) previewDesc.textContent = card.dataset.desc || '';
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

    if (cards.length) selectCard(cards[0]);
})();