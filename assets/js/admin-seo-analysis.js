(function () {
    'use strict';

    var root = document.getElementById('shSeoAnalysis');
    if (!root) return;

    var searchEl = document.getElementById('shSeoFilterSearch');
    var scoreEl = document.getElementById('shSeoFilterScore');
    var categoryEl = document.getElementById('shSeoFilterCategory');
    var issueEl = document.getElementById('shSeoFilterIssue');
    var countEl = document.getElementById('shSeoFilterCount');
    var emptyEl = document.getElementById('shSeoFilterEmpty');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.adm-seo-product-row'));
    var total = rows.length;

    function scoreMatches(grade, score) {
        if (!grade) return true;
        if (grade === 'poor') return score < 50;
        if (grade === 'fair') return score < 75;
        if (grade === 'good') return score >= 75 && score < 90;
        if (grade === 'excellent') return score >= 90;
        return true;
    }

    function applyFilters() {
        var q = (searchEl && searchEl.value || '').trim().toLowerCase();
        var scoreFilter = scoreEl ? scoreEl.value : '';
        var cat = categoryEl ? categoryEl.value : '';
        var issue = issueEl ? issueEl.value : '';
        var visible = 0;

        rows.forEach(function (row) {
            var name = row.getAttribute('data-name') || '';
            var id = (row.getAttribute('data-id') || '').toLowerCase();
            var rowCat = row.getAttribute('data-category') || '';
            var rowScore = parseInt(row.getAttribute('data-score') || '0', 10);
            var issues = (row.getAttribute('data-issues') || '').split(',').filter(Boolean);

            var ok = true;
            if (q && name.indexOf(q) === -1 && id.indexOf(q) === -1) ok = false;
            if (ok && cat && rowCat !== cat) ok = false;
            if (ok && scoreFilter && !scoreMatches(scoreFilter, rowScore)) ok = false;
            if (ok && issue && issues.indexOf(issue) === -1) ok = false;

            row.hidden = !ok;
            if (ok) visible++;
        });

        if (countEl) {
            var tpl = root.getAttribute('data-label-count') || 'Showing {n} of {total}';
            countEl.textContent = tpl.replace('{n}', String(visible)).replace('{total}', String(total));
        }
        if (emptyEl) {
            emptyEl.hidden = visible > 0 || total === 0;
        }
    }

    [searchEl, scoreEl, categoryEl, issueEl].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
    });

    applyFilters();
})();