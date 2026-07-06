(function () {
    var form = document.getElementById('shProductForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var aiNameInput = document.getElementById('shAiProductName');
    var aiBriefInput = document.getElementById('shAiProductBrief');
    var sourceNameField = form.querySelector('[name="name_' + sourceLang + '"]');
    var sourceDescField = form.querySelector('[name="desc_' + sourceLang + '"]');

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function setFieldValue(el, value) {
        if (!el || value == null) return;
        el.value = String(value);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function fillLangFields(prefix, data) {
        if (!data || typeof data !== 'object') return;
        Object.keys(data).forEach(function (code) {
            var el = field(prefix + code);
            if (el && data[code] != null && String(data[code]).trim() !== '') {
                setFieldValue(el, data[code]);
            }
        });
    }

    function syncAiNameToSource() {
        if (!aiNameInput || !sourceNameField) return;
        var val = aiNameInput.value.trim();
        if (val) setFieldValue(sourceNameField, val);
    }

    function syncAiBriefToSource() {
        if (!aiBriefInput || !sourceDescField) return;
        var val = aiBriefInput.value.trim();
        if (val) setFieldValue(sourceDescField, val);
    }

    if (aiNameInput) {
        if (!aiNameInput.value && sourceNameField && sourceNameField.value) {
            aiNameInput.value = sourceNameField.value.trim();
        }
        aiNameInput.addEventListener('input', syncAiNameToSource);
        aiNameInput.addEventListener('change', syncAiNameToSource);
    }

    if (aiBriefInput) {
        if (!aiBriefInput.value && sourceDescField && sourceDescField.value) {
            aiBriefInput.value = sourceDescField.value.trim();
        }
        aiBriefInput.addEventListener('input', syncAiBriefToSource);
        aiBriefInput.addEventListener('change', syncAiBriefToSource);
    }

    function getProductName() {
        if (aiNameInput && aiNameInput.value.trim()) return aiNameInput.value.trim();
        var nameInput = field('name_' + sourceLang) || field('name_en') || field('name_no');
        return nameInput ? nameInput.value.trim() : '';
    }

    function getProductBrief() {
        if (aiBriefInput && aiBriefInput.value.trim()) return aiBriefInput.value.trim();
        var descInput = field('desc_' + sourceLang) || field('desc_en');
        return descInput ? descInput.value.trim() : '';
    }

    function openLangSpoilers() {
        form.querySelectorAll('[data-panel="names"] details.adm-spoiler').forEach(function (d) {
            d.setAttribute('open', '');
        });
        form.querySelectorAll('[data-panel="seo"] details.adm-spoiler').forEach(function (d) {
            d.setAttribute('open', '');
        });
    }

    function applyAiData(res, statusEl, btn) {
        if (!res.ok || !res.data) throw new Error(res.error || 'AI failed');
        var data = res.data;
        fillLangFields('name_', data.names);
        if (data.desc) fillLangFields('desc_', data.desc);
        if (data.seo) {
            fillLangFields('seo_meta_title_', data.seo.meta_title);
            fillLangFields('seo_meta_description_', data.seo.meta_description);
            fillLangFields('seo_meta_keywords_', data.seo.meta_keywords);
        }
        var brand = data.brand || (data.seo && data.seo.brand) || '';
        var brandField = field('seo_brand');
        if (brandField && brand) setFieldValue(brandField, brand);
        if (aiNameInput && data.names && data.names[sourceLang]) {
            aiNameInput.value = data.names[sourceLang];
        }
        if (aiBriefInput && data.desc && data.desc[sourceLang]) {
            aiBriefInput.value = data.desc[sourceLang];
        }
        openLangSpoilers();
        form.dispatchEvent(new CustomEvent('shProductAiFilled', { bubbles: true }));
        var msg = res.demo
            ? (btn.getAttribute('data-demo-ok') || 'Demo templates applied.')
            : (btn.getAttribute('data-ok') || 'Generated.');
        setStatus(statusEl, msg, res.demo ? 'info' : 'success');
    }

    function parseApiResponse(r) {
        return r.text().then(function (text) {
            if (r.status === 403 || text.indexOf('Forbidden') !== -1) {
                throw new Error('HTTP 403 Forbidden');
            }
            var res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                if (text.indexOf('<') !== -1) {
                    throw new Error('Server error — reload or check Settings → AI');
                }
                throw new Error(text || ('HTTP ' + r.status));
            }
            if (!r.ok) {
                throw new Error((res && res.error) || ('HTTP ' + r.status));
            }
            return res;
        });
    }

    function requestAi(productName, category, brief, seoOnly) {
        var payload = {
            product_name: productName,
            category: category,
            source_lang: sourceLang,
            brief_description: brief,
            seo_only: seoOnly ? 1 : 0
        };
        return fetch(apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(parseApiResponse)
            .catch(function (err) {
                if (err.message.indexOf('403') === -1 && err.message.indexOf('Forbidden') === -1) {
                    throw err;
                }
                var body = new URLSearchParams();
                Object.keys(payload).forEach(function (k) {
                    body.set(k, String(payload[k]));
                });
                return fetch(apiUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    body: body
                }).then(parseApiResponse);
            });
    }

    function runAi(btn, statusEl, seoOnly) {
        var productName = getProductName();
        var categoryEl = field('category');
        var category = categoryEl ? categoryEl.value : '';
        var brief = getProductBrief();

        if (!productName) {
            setStatus(statusEl, btn.getAttribute('data-need-name') || 'Enter product name first.', 'error');
            (aiNameInput || field('name_' + sourceLang)).focus();
            return;
        }

        syncAiNameToSource();
        syncAiBriefToSource();
        btn.disabled = true;
        setStatus(statusEl, btn.getAttribute('data-generating') || 'Generating…', 'loading');

        requestAi(productName, category, brief, seoOnly)
            .then(function (res) {
                if (seoOnly && res.ok && res.data) {
                    if (res.data.seo) {
                        fillLangFields('seo_meta_title_', res.data.seo.meta_title);
                        fillLangFields('seo_meta_description_', res.data.seo.meta_description);
                        fillLangFields('seo_meta_keywords_', res.data.seo.meta_keywords);
                    }
                    var brand = res.data.brand || (res.data.seo && res.data.seo.brand);
                    if (field('seo_brand') && brand) setFieldValue(field('seo_brand'), brand);
                    openLangSpoilers();
                    form.dispatchEvent(new CustomEvent('shProductAiFilled', { bubbles: true }));
                    var msg = res.demo
                        ? (btn.getAttribute('data-demo-ok') || 'Demo templates.')
                        : (btn.getAttribute('data-ok') || 'SEO generated.');
                    setStatus(statusEl, msg, res.demo ? 'info' : 'success');
                } else {
                    applyAiData(res, statusEl, btn);
                }
            })
            .catch(function (err) {
                setStatus(statusEl, err.message || (btn.getAttribute('data-failed') || 'Failed'), 'error');
            })
            .finally(function () {
                btn.disabled = false;
            });
    }

    var btnNames = document.getElementById('shAiGenerateBtn');
    var statusNames = document.getElementById('shAiStatus');
    if (btnNames) {
        btnNames.addEventListener('click', function () {
            runAi(btnNames, statusNames, false);
        });
    }

    var btnSeo = document.getElementById('shAiSeoGenerateBtn');
    var statusSeo = document.getElementById('shAiSeoStatus');
    if (btnSeo) {
        btnSeo.addEventListener('click', function () {
            runAi(btnSeo, statusSeo, true);
        });
    }

    var aside = document.getElementById('shProductChecklistAside');
    var asideToggle = document.getElementById('shChecklistAsideToggle');
    if (aside && asideToggle) {
        var hideLabel = asideToggle.getAttribute('title') || 'Hide checklist';
        var showLabel = asideToggle.getAttribute('data-show-label') || 'Show checklist';
        asideToggle.addEventListener('click', function () {
            var collapsed = aside.classList.toggle('is-collapsed');
            asideToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            asideToggle.setAttribute('title', collapsed ? showLabel : hideLabel);
        });
    }
})();