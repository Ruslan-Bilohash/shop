(function () {
    var form = document.getElementById('shBlockBuilderForm');
    if (!form) return;

    var previewLang = form.getAttribute('data-preview-lang') || 'en';
    var preview = document.getElementById('shTplPreview');
    var promptInput = document.getElementById('shTplPrompt');
    var genBtn = document.getElementById('shTplGenerateBtn');
    var genStatus = document.getElementById('shTplGenerateStatus');
    var newPromptHidden = document.getElementById('shNewTplPrompt');
    var placementNew = document.getElementById('shNewTplPlacement');
    var pageWrapNew = document.getElementById('shNewTplPageWrap');

    function setStatus(msg, type) {
        if (!genStatus) return;
        genStatus.textContent = msg;
        genStatus.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        genStatus.hidden = !msg;
    }

    function getBodyValue(lang) {
        var ta = form.querySelector('.sh-tpl-body-input[data-lang="' + lang + '"]');
        if (!ta) return '';
        if (ta.nextSibling && ta.nextSibling.CodeMirror) {
            return ta.nextSibling.CodeMirror.getValue();
        }
        return ta.value || '';
    }

    function updatePreview() {
        if (!preview) return;
        var title = (form.querySelector('.sh-tpl-text-input[data-field="title"][data-lang="' + previewLang + '"]') || {}).value || '';
        var subtitle = (form.querySelector('.sh-tpl-text-input[data-field="subtitle"][data-lang="' + previewLang + '"]') || {}).value || '';
        var body = getBodyValue(previewLang);
        var html = '<!DOCTYPE html><html><head><meta charset="utf-8">'
            + '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">'
            + '<style>body{font-family:system-ui,sans-serif;margin:16px;color:#0f172a;line-height:1.5}'
            + 'h2{margin:0 0 8px;font-size:1.25rem}.sub{color:#64748b;margin:0 0 16px}</style></head><body>';
        if (title) html += '<h2>' + title.replace(/</g, '&lt;') + '</h2>';
        if (subtitle) html += '<p class="sub">' + subtitle.replace(/</g, '&lt;') + '</p>';
        html += body + '</body></html>';
        preview.srcdoc = html;
    }

    function fillNewTemplate(data, prompt) {
        if (newPromptHidden) newPromptHidden.value = prompt || '';
        var nameInput = document.getElementById('shNewTplName');
        if (nameInput && !nameInput.value && prompt) {
            nameInput.value = prompt.length > 48 ? prompt.slice(0, 45) + '…' : prompt;
        }
        Object.keys(data.title || {}).forEach(function (code) {
            var el = form.querySelector('.sh-tpl-text-input[data-field="title"][data-lang="' + code + '"]');
            if (el) el.value = data.title[code] || '';
        });
        Object.keys(data.subtitle || {}).forEach(function (code) {
            var el = form.querySelector('.sh-tpl-text-input[data-field="subtitle"][data-lang="' + code + '"]');
            if (el) el.value = data.subtitle[code] || '';
        });
        Object.keys(data.body || {}).forEach(function (code) {
            var ta = form.querySelector('.sh-tpl-body-input[data-lang="' + code + '"]');
            if (!ta) return;
            ta.value = data.body[code] || '';
            if (ta.nextSibling && ta.nextSibling.CodeMirror) {
                ta.nextSibling.CodeMirror.setValue(ta.value);
            }
        });
        updatePreview();
    }

    function bindPlacement(select, wrap) {
        if (!select || !wrap) return;
        function sync() {
            wrap.hidden = select.value !== 'page';
        }
        select.addEventListener('change', sync);
        sync();
    }

    bindPlacement(placementNew, pageWrapNew);
    form.querySelectorAll('.sh-tpl-placement-select').forEach(function (sel) {
        var idx = sel.getAttribute('data-idx');
        var wrap = form.querySelector('.sh-tpl-page-wrap[data-idx="' + idx + '"]');
        bindPlacement(sel, wrap);
    });

    form.querySelectorAll('.sh-tpl-text-input, .sh-tpl-body-input').forEach(function (el) {
        el.addEventListener('input', updatePreview);
    });

    if (genBtn) {
        genBtn.addEventListener('click', function () {
            var prompt = (promptInput && promptInput.value || '').trim();
            if (!prompt) {
                setStatus('Enter a prompt', 'error');
                return;
            }
            var url = form.getAttribute('data-ai-url') || '';
            genBtn.disabled = true;
            setStatus('Generating…', 'pending');
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: prompt })
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.ok || !res.data || !res.data.template) throw new Error(res.error || 'Failed');
                    fillNewTemplate(res.data.template, prompt);
                    setStatus(res.demo ? 'Demo template applied' : 'Generated', res.demo ? 'info' : 'success');
                })
                .catch(function (err) {
                    setStatus(err.message || 'Failed', 'error');
                })
                .finally(function () { genBtn.disabled = false; });
        });
    }

    form.addEventListener('submit', function () {
        form.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
            if (ta.nextSibling && ta.nextSibling.CodeMirror) {
                ta.value = ta.nextSibling.CodeMirror.getValue();
            }
        });
    });

    if (window.shAdminInitCodeMirror) {
        window.shAdminInitCodeMirror(form);
        setTimeout(function () {
            form.querySelectorAll('.sh-tpl-body-input').forEach(function (ta) {
                if (ta.nextSibling && ta.nextSibling.CodeMirror) {
                    ta.nextSibling.CodeMirror.on('change', updatePreview);
                }
            });
            updatePreview();
        }, 120);
    } else {
        updatePreview();
    }
})();