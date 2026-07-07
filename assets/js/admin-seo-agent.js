(function () {
    'use strict';

    var root = document.getElementById('shSeoAgent');
    if (!root) return;

    var apiUrl = root.getAttribute('data-api') || '';
    var lang = root.getAttribute('data-lang') || 'en';
    var scanningLabel = root.getAttribute('data-scanning') || 'Analyzing…';
    var errorLabel = root.getAttribute('data-error') || 'Request failed';
    var demoLabel = root.getAttribute('data-demo') || 'Demo mode';

    var searchEl = document.getElementById('shSeoAgentSearch');
    var filterEl = document.getElementById('shSeoAgentFilter');
    var pages = Array.prototype.slice.call(document.querySelectorAll('.adm-sa-page'));

    function scoreMatches(grade, score) {
        if (!grade) return true;
        if (grade === 'poor') return score < 50;
        if (grade === 'fair') return score >= 50 && score < 75;
        if (grade === 'good') return score >= 75;
        return true;
    }

    function applyFilters() {
        var q = (searchEl && searchEl.value || '').trim().toLowerCase();
        var grade = filterEl ? filterEl.value : '';
        pages.forEach(function (page) {
            var name = page.getAttribute('data-name') || '';
            var score = parseInt(page.getAttribute('data-score') || '0', 10);
            var ok = true;
            if (q && name.indexOf(q) === -1) ok = false;
            if (ok && grade && !scoreMatches(grade, score)) ok = false;
            page.hidden = !ok;
        });
    }

    function renderResult(container, data) {
        var html = '';
        if (data.summary) {
            html += '<p class="adm-sa-summary">' + escapeHtml(data.summary) + '</p>';
        }
        if (data.demo) {
            html += '<span class="adm-badge adm-badge-info">' + escapeHtml(demoLabel) + '</span> ';
        }
        if (data.suggestions && data.suggestions.length) {
            html += '<ul class="adm-sa-suggestions">';
            data.suggestions.forEach(function (s) {
                html += '<li class="adm-sa-sug adm-sa-sug--' + escapeHtml(s.priority || 'medium') + '">';
                html += '<strong>' + escapeHtml(s.title || '') + '</strong>';
                html += '<p>' + escapeHtml(s.detail || '') + '</p>';
                html += '</li>';
            });
            html += '</ul>';
        }
        container.innerHTML = html;
        container.hidden = false;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function analyzePage(btn) {
        var pageJson = btn.getAttribute('data-page');
        if (!pageJson) return;

        var page;
        try { page = JSON.parse(pageJson); } catch (e) { return; }

        var article = btn.closest('.adm-sa-page');
        var resultEl = article ? article.querySelector('.adm-sa-result') : null;
        if (!resultEl) return;

        btn.disabled = true;
        var oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + escapeHtml(scanningLabel);
        resultEl.hidden = true;
        resultEl.innerHTML = '<p class="adm-muted"><i class="fas fa-spinner fa-spin"></i> ' + escapeHtml(scanningLabel) + '</p>';
        resultEl.hidden = false;

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ page: page, lang: lang })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    resultEl.innerHTML = '<p class="adm-sa-error">' + escapeHtml(data.error || errorLabel) + '</p>';
                    return;
                }
                renderResult(resultEl, data);
            })
            .catch(function () {
                resultEl.innerHTML = '<p class="adm-sa-error">' + escapeHtml(errorLabel) + '</p>';
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            });
    }

    document.querySelectorAll('.sh-sa-analyze').forEach(function (btn) {
        btn.addEventListener('click', function () { analyzePage(btn); });
    });

    var scanAllBtn = document.getElementById('shSeoAgentScanAll');
    if (scanAllBtn) {
        scanAllBtn.addEventListener('click', function () {
            var weak = pages.filter(function (p) {
                return !p.hidden && parseInt(p.getAttribute('data-score') || '0', 10) < 60;
            });
            if (weak.length === 0) {
                weak = pages.filter(function (p) { return !p.hidden; }).slice(0, 3);
            }
            var idx = 0;
            function next() {
                if (idx >= weak.length) return;
                var btn = weak[idx].querySelector('.sh-sa-analyze');
                if (btn) analyzePage(btn);
                idx++;
                setTimeout(next, 2500);
            }
            next();
        });
    }

    [searchEl, filterEl].forEach(function (el) {
        if (!el) return;
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
    });
})();