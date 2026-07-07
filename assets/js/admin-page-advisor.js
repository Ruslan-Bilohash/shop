(function () {
    'use strict';

    var root = document.getElementById('shPageAdvisor');
    if (!root) return;

    var apiUrl = root.getAttribute('data-api') || '';
    var slug = root.getAttribute('data-slug') || '';
    var lang = root.getAttribute('data-lang') || 'en';
    var scanning = root.getAttribute('data-scanning') || 'Scanning…';
    var errorLabel = root.getAttribute('data-error') || 'Request failed';
    var demoLabel = root.getAttribute('data-demo') || 'Demo mode';

    var btn = document.getElementById('shPageAdvisorScan');
    var resultEl = document.getElementById('shPageAdvisorResult');

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function collectPageData() {
        var data = { title: {}, content: {}, meta_title: {}, meta_description: {} };
        document.querySelectorAll('[name^="page_title_"]').forEach(function (el) {
            var code = el.name.replace('page_title_', '');
            data.title[code] = el.value;
        });
        document.querySelectorAll('textarea[name^="page_content_"], input[name^="page_content_"]').forEach(function (el) {
            var code = el.name.replace('page_content_', '');
            data.content[code] = el.value;
        });
        document.querySelectorAll('[name^="page_meta_title_"]').forEach(function (el) {
            var code = el.name.replace('page_meta_title_', '');
            data.meta_title[code] = el.value;
        });
        document.querySelectorAll('[name^="page_meta_description_"]').forEach(function (el) {
            var code = el.name.replace('page_meta_description_', '');
            data.meta_description[code] = el.value;
        });
        return data;
    }

    function render(data) {
        if (!resultEl) return;
        var html = '';
        if (data.summary) {
            html += '<p class="adm-pa-summary">' + escapeHtml(data.summary) + '</p>';
        }
        if (data.demo) {
            html += '<span class="adm-badge adm-badge-info">' + escapeHtml(demoLabel) + '</span>';
        }
        if (data.suggestions && data.suggestions.length) {
            html += '<ul class="adm-pa-list">';
            data.suggestions.forEach(function (s) {
                html += '<li class="adm-pa-item adm-pa-item--' + escapeHtml(s.priority || 'medium') + '">';
                html += '<strong>' + escapeHtml(s.title || '') + '</strong>';
                html += '<p>' + escapeHtml(s.detail || '') + '</p></li>';
            });
            html += '</ul>';
        } else {
            html += '<p class="adm-help">' + escapeHtml('No issues found.') + '</p>';
        }
        resultEl.innerHTML = html;
        resultEl.hidden = false;
    }

    if (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            var old = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + escapeHtml(scanning);

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    page_slug: slug,
                    lang: lang,
                    page: collectPageData()
                })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        if (resultEl) {
                            resultEl.innerHTML = '<p class="adm-alert adm-alert-danger">' + escapeHtml(data.error || errorLabel) + '</p>';
                            resultEl.hidden = false;
                        }
                        return;
                    }
                    render(data);
                })
                .catch(function () {
                    if (resultEl) {
                        resultEl.innerHTML = '<p class="adm-alert adm-alert-danger">' + escapeHtml(errorLabel) + '</p>';
                        resultEl.hidden = false;
                    }
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = old;
                });
        });
    }
})();