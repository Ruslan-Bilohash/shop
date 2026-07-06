(function () {
    'use strict';

    function boot() {
        var form = document.getElementById('shBlockBuilderForm');
        if (!form) return;

        if (typeof window.shAdminInitCodeMirror === 'function') {
            window.shAdminInitCodeMirror(form);
        }

        var getCmValue = window.shAdminGetCmValue || function (ta) { return ta ? (ta.value || '') : ''; };
        var setCmValue = window.shAdminSetCmValue || function (ta, v) { if (ta) ta.value = v || ''; };

        var previewLang = form.getAttribute('data-preview-lang') || 'en';
        var preview = document.getElementById('shTplPreview');
        var promptInput = document.getElementById('shTplPrompt');
        var genBtn = document.getElementById('shTplGenerateBtn');
        var genStatus = document.getElementById('shTplGenerateStatus');
        var newPromptHidden = document.getElementById('shNewTplPrompt');
        var placementNew = document.getElementById('shNewTplPlacement');
        var pageWrapNew = document.getElementById('shNewTplPageWrap');
        var colorPicker = document.getElementById('shBlockColorPicker');
        var presets = [];
        try {
            presets = JSON.parse(form.getAttribute('data-presets') || '[]');
        } catch (e) {
            presets = [];
        }
        var activeColor = (colorPicker && colorPicker.value) || '#2563eb';

        function setStatus(msg, type) {
            if (!genStatus) return;
            genStatus.textContent = msg;
            genStatus.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
            genStatus.hidden = !msg;
        }

        function getBodyValue(lang) {
            var ta = form.querySelector('.sh-tpl-body-input[data-lang="' + lang + '"]');
            return getCmValue(ta);
        }

        function updatePreview() {
            if (!preview) return;
            var titleEl = form.querySelector('.sh-tpl-text-input[data-field="title"][data-lang="' + previewLang + '"]');
            var subtitleEl = form.querySelector('.sh-tpl-text-input[data-field="subtitle"][data-lang="' + previewLang + '"]');
            var title = titleEl ? titleEl.value : '';
            var subtitle = subtitleEl ? subtitleEl.value : '';
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

        function applyColorToHtml(html, color) {
            if (!html || !color) return html;
            var re = /#(?:2563eb|059669|7c3aed|ea580c|0d9488|dc2626)\b/gi;
            return html.replace(re, color);
        }

        function fillNewTemplate(data, prompt, color) {
            if (newPromptHidden) newPromptHidden.value = prompt || '';
            var nameInput = document.getElementById('shNewTplName');
            if (nameInput && prompt && (!nameInput.value || nameInput.dataset.auto === '1')) {
                nameInput.value = prompt.length > 48 ? prompt.slice(0, 45) + '…' : prompt;
                nameInput.dataset.auto = '1';
            }
            var useColor = color || activeColor;
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
                var body = applyColorToHtml(data.body[code] || '', useColor);
                if (ta) setCmValue(ta, body);
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

        form.querySelectorAll('.sh-tpl-text-input').forEach(function (el) {
            el.addEventListener('input', updatePreview);
        });

        form.querySelectorAll('.sh-tpl-body-input').forEach(function (ta) {
            if (ta.cmEditor) {
                ta.cmEditor.on('change', updatePreview);
            } else {
                ta.addEventListener('input', updatePreview);
            }
        });

        function encodeBodiesForSave() {
            form.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
                if (ta.cmEditor) {
                    ta.value = ta.cmEditor.getValue();
                }
                if (ta.name && (ta.name.indexOf('_body_') !== -1 || ta.name.indexOf('tpl_body_') !== -1 || ta.name.indexOf('new_tpl_body_') !== -1)) {
                    var val = ta.value || '';
                    if (val !== '' && val.indexOf('b64:') !== 0) {
                        try {
                            ta.value = 'b64:' + btoa(unescape(encodeURIComponent(val)));
                        } catch (e) { /* keep plain */ }
                    }
                }
            });
        }

        form.addEventListener('submit', encodeBodiesForSave);

        function parseApiResponse(r) {
            return r.text().then(function (text) {
                if (r.status === 403) {
                    throw new Error('HTTP 403 Forbidden');
                }
                var data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    if (text.indexOf('Forbidden') !== -1) {
                        throw new Error('HTTP 403 Forbidden');
                    }
                    if (text.indexOf('<') !== -1) {
                        throw new Error(form.getAttribute('data-err-server') || 'Server error — check admin API');
                    }
                    throw new Error(text || ('HTTP ' + r.status));
                }
                if (!r.ok) {
                    throw new Error((data && data.error) || ('HTTP ' + r.status));
                }
                return data;
            });
        }

        function requestAiGenerate(prompt) {
            var url = form.getAttribute('data-ai-url') || '';
            return fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ prompt: prompt })
            }).then(parseApiResponse).catch(function (err) {
                if (err.message.indexOf('403') === -1 && err.message.indexOf('Forbidden') === -1) {
                    throw err;
                }
                var body = new URLSearchParams();
                body.set('prompt', prompt);
                return fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    body: body
                }).then(parseApiResponse);
            });
        }

        if (genBtn) {
            genBtn.addEventListener('click', function () {
                var prompt = (promptInput && promptInput.value || '').trim();
                if (!prompt) {
                    setStatus(form.getAttribute('data-err-prompt') || 'Enter a prompt', 'error');
                    return;
                }
                genBtn.disabled = true;
                setStatus(form.getAttribute('data-status-generating') || 'Generating…', 'pending');
                requestAiGenerate(prompt)
                    .then(function (res) {
                        if (!res.ok || !res.data || !res.data.template) throw new Error(res.error || 'Failed');
                        fillNewTemplate(res.data.template, prompt, activeColor);
                        setStatus(
                            res.demo
                                ? (form.getAttribute('data-status-demo') || 'Demo template applied')
                                : (form.getAttribute('data-status-ok') || 'Generated'),
                            res.demo ? 'info' : 'success'
                        );
                        var editor = document.getElementById('block-builder-new');
                        if (editor) editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    })
                    .catch(function (err) {
                        setStatus(err.message || 'Failed', 'error');
                    })
                    .finally(function () { genBtn.disabled = false; });
            });
        }

        function setActiveColor(color) {
            if (!/^#[0-9a-fA-F]{6}$/.test(color)) return;
            activeColor = color;
            if (colorPicker) colorPicker.value = color;
            form.querySelectorAll('.adm-block-color-swatch').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-color') === color);
            });
        }

        if (colorPicker) {
            colorPicker.addEventListener('input', function () {
                setActiveColor(colorPicker.value);
                var body = getBodyValue(previewLang);
                if (body) {
                    var ta = form.querySelector('.sh-tpl-body-input[data-lang="' + previewLang + '"]');
                    if (ta) setCmValue(ta, applyColorToHtml(body, activeColor));
                    updatePreview();
                }
            });
        }

        form.querySelectorAll('.adm-block-color-swatch').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setActiveColor(btn.getAttribute('data-color') || '#2563eb');
                if (colorPicker) colorPicker.dispatchEvent(new Event('input'));
            });
        });

        form.querySelectorAll('.adm-block-preset-card').forEach(function (card) {
            card.addEventListener('click', function () {
                var id = card.getAttribute('data-preset-id');
                var preset = presets.find(function (p) { return p.id === id; });
                if (!preset || !preset.template) return;
                var color = card.style.getPropertyValue('--preset-color') || preset.color || activeColor;
                setActiveColor(color.trim() || activeColor);
                if (promptInput) promptInput.value = preset.prompt || '';
                fillNewTemplate(preset.template, preset.prompt || preset.name || '', activeColor);
                setStatus(form.getAttribute('data-status-preset') || 'Preset applied', 'success');
                form.querySelectorAll('.adm-block-preset-card').forEach(function (c) {
                    c.classList.toggle('is-active', c === card);
                });
                var editor = document.getElementById('block-builder-new');
                if (editor) editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        var nameInput = document.getElementById('shNewTplName');
        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (nameInput.value) nameInput.removeAttribute('data-auto');
            });
        }

        if (window.location.hash) {
            var target = document.querySelector(window.location.hash);
            if (target) {
                window.setTimeout(function () {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 120);
            }
        }

        setActiveColor(activeColor);
        updatePreview();
    }

    function start() {
        if (typeof CodeMirror === 'undefined' || typeof window.shAdminInitCodeMirror !== 'function') {
            window.setTimeout(start, 50);
            return;
        }
        boot();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();