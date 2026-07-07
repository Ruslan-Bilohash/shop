(function () {
    'use strict';

    var root = document.getElementById('shSeoAgent');
    if (!root) return;

    var apiUrl = root.getAttribute('data-api') || '';
    var lang = root.getAttribute('data-lang') || 'en';
    var scanningLabel = root.getAttribute('data-scanning') || 'Analyzing…';
    var errorLabel = root.getAttribute('data-error') || 'Request failed';
    var demoLabel = root.getAttribute('data-demo') || 'Demo mode';
    var updatedTpl = root.getAttribute('data-updated') || 'Just updated · {time}';
    var progressTpl = root.getAttribute('data-scan-progress') || 'Scanning {current} of {total}…';
    var scanDoneLabel = root.getAttribute('data-scan-done') || 'Batch scan complete';

    var searchEl = document.getElementById('shSeoAgentSearch');
    var filterEl = document.getElementById('shSeoAgentFilter');
    var progressEl = document.getElementById('shSeoAgentProgress');
    var pages = Array.prototype.slice.call(document.querySelectorAll('.adm-sa-page'));
    var batchRunning = false;

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

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatTime(d) {
        try {
            return d.toLocaleTimeString(lang, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        } catch (e) {
            return d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }

    function renderScanningPanel(label) {
        return '<div class="adm-sa-scan-panel">' +
            '<span class="adm-sa-scan-radar" aria-hidden="true"></span>' +
            '<p class="adm-sa-scan-panel-text"><strong>' + escapeHtml(scanningLabel) + '</strong>' +
            escapeHtml(label) + '</p></div>';
    }

    function setPageScanning(article, on) {
        if (!article) return;
        if (on) {
            article.classList.add('is-scanning');
            article.classList.remove('is-just-updated');
        } else {
            article.classList.remove('is-scanning');
        }
    }

    function showUpdatedBadge(article) {
        if (!article) return;
        var badge = article.querySelector('.adm-sa-updated-badge');
        if (!badge) return;
        var now = new Date();
        var timeStr = formatTime(now);
        article.setAttribute('data-scanned-at', timeStr);
        badge.innerHTML = '<i class="fas fa-circle-check"></i> ' + escapeHtml(updatedTpl.replace('{time}', timeStr));
        badge.hidden = false;
        badge.classList.remove('is-new');
        void badge.offsetWidth;
        badge.classList.add('is-new');
        article.classList.add('is-just-updated');
        window.setTimeout(function () {
            article.classList.remove('is-just-updated');
        }, 3200);
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

    function setBatchProgress(current, total, done) {
        if (!progressEl) return;
        if (done) {
            progressEl.textContent = scanDoneLabel;
            window.setTimeout(function () {
                progressEl.hidden = true;
                progressEl.textContent = '';
            }, 2800);
            return;
        }
        if (!total) {
            progressEl.hidden = true;
            progressEl.textContent = '';
            return;
        }
        progressEl.textContent = progressTpl
            .replace('{current}', String(current))
            .replace('{total}', String(total));
        progressEl.hidden = false;
    }

    function analyzePage(btn) {
        var pageJson = btn.getAttribute('data-page');
        if (!pageJson) return Promise.resolve(false);

        var page;
        try { page = JSON.parse(pageJson); } catch (e) { return Promise.resolve(false); }

        var article = btn.closest('.adm-sa-page');
        var resultEl = article ? article.querySelector('.adm-sa-result') : null;
        if (!resultEl) return Promise.resolve(false);

        var pageLabel = page.label || '';
        btn.disabled = true;
        var oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + escapeHtml(scanningLabel);
        setPageScanning(article, true);
        resultEl.innerHTML = renderScanningPanel(pageLabel);
        resultEl.hidden = false;

        return fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ page: page, lang: lang })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    resultEl.innerHTML = '<p class="adm-sa-error">' + escapeHtml(data.error || errorLabel) + '</p>';
                    return false;
                }
                renderResult(resultEl, data);
                showUpdatedBadge(article);
                return true;
            })
            .catch(function () {
                resultEl.innerHTML = '<p class="adm-sa-error">' + escapeHtml(errorLabel) + '</p>';
                return false;
            })
            .finally(function () {
                setPageScanning(article, false);
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
            if (batchRunning) return;

            var weak = pages.filter(function (p) {
                return !p.hidden && parseInt(p.getAttribute('data-score') || '0', 10) < 60;
            });
            if (weak.length === 0) {
                weak = pages.filter(function (p) { return !p.hidden; }).slice(0, 3);
            }
            if (weak.length === 0) return;

            batchRunning = true;
            scanAllBtn.disabled = true;
            var scanOldHtml = scanAllBtn.innerHTML;
            scanAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + escapeHtml(scanningLabel);

            var idx = 0;
            var total = weak.length;

            function next() {
                if (idx >= total) {
                    setBatchProgress(total, total, true);
                    batchRunning = false;
                    scanAllBtn.disabled = false;
                    scanAllBtn.innerHTML = scanOldHtml;
                    return;
                }
                setBatchProgress(idx + 1, total, false);
                var btn = weak[idx].querySelector('.sh-sa-analyze');
                var run = btn ? analyzePage(btn) : Promise.resolve(false);
                idx++;
                run.then(next);
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