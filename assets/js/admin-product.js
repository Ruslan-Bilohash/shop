(function () {
    var form = document.getElementById('shProductForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var langCodes = (window.SH_CHECKLIST_LANGS || []).map(function (c) { return c.code; });
    if (langCodes.length === 0) {
        langCodes = ['no', 'en', 'uk', 'ru', 'sv'];
    }
    var aiNameInput = document.getElementById('shAiProductName');
    var aiBriefInput = document.getElementById('shAiProductBrief');
    var sourceNameField = form.querySelector('[name="name_' + sourceLang + '"]');
    var sourceDescField = form.querySelector('[name="desc_' + sourceLang + '"]');

    var META_DESC_MIN = 120;
    var META_DESC_MAX = 160;
    var META_DESC_PAD = [' Free delivery available.', ' Order securely online today.', ' Quality guaranteed.'];

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        var base = el.classList.contains('adm-ai-status--inline')
            ? 'adm-ai-status adm-ai-status--inline'
            : 'adm-ai-status adm-ai-status--block';
        el.className = base + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function setLoading(btn, loading) {
        if (!btn) return;
        btn.classList.toggle('is-loading', loading);
        btn.disabled = loading;
        var icon = btn.querySelector('.adm-ai-btn-icon');
        var label = btn.querySelector('.adm-ai-btn-label');
        if (icon) {
            icon.className = loading
                ? 'fas fa-spinner adm-ai-btn-icon'
                : 'fas fa-wand-magic-sparkles adm-ai-btn-icon';
        }
        if (label && loading) {
            label.textContent = btn.getAttribute('data-generating') || 'Generating…';
        } else if (label) {
            label.textContent = btn.getAttribute('data-label-default') || label.textContent;
        }
    }

    function setFieldValue(el, value) {
        if (!el || value == null) return;
        el.value = String(value);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function fitMetaDescription(text) {
        text = String(text || '').replace(/\s+/g, ' ').trim();
        if (!text) return '';
        if (text.length > META_DESC_MAX) {
            text = text.slice(0, META_DESC_MAX);
            var cut = text.lastIndexOf(' ');
            if (cut > META_DESC_MAX - 40) {
                text = text.slice(0, cut);
            }
            text = text.replace(/[.,;:!?—-–]+$/, '');
        }
        var i = 0;
        while (text.length < META_DESC_MIN && i < META_DESC_PAD.length) {
            if (text.length + META_DESC_PAD[i].length <= META_DESC_MAX) {
                text += META_DESC_PAD[i];
            }
            i++;
        }
        if (text.length > META_DESC_MAX) {
            text = text.slice(0, META_DESC_MAX).replace(/[.,;:!?—-–]+$/, '');
        }
        return text;
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

    function fillMetaDescriptions(data) {
        if (!data || typeof data !== 'object') return;
        Object.keys(data).forEach(function (code) {
            var el = field('seo_meta_description_' + code);
            if (!el || data[code] == null) return;
            var fitted = fitMetaDescription(data[code]);
            if (fitted) setFieldValue(el, fitted);
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

    function buildClientDemoData(productName, category, brief) {
        var langs = langCodes.length ? langCodes : ['no', 'en', 'uk', 'ru', 'sv'];
        var names = {};
        var desc = {};
        var metaTitle = {};
        var metaDesc = {};
        var metaKw = {};
        var briefText = brief ? ' ' + brief : '';
        langs.forEach(function (code) {
            names[code] = productName;
            desc[code] = productName + ' — demo product.' + briefText + (category ? ' Category: ' + category + '.' : '');
            metaTitle[code] = (productName + ' | ' + (category || 'Shop')).slice(0, 60);
            metaDesc[code] = fitMetaDescription(
                'Buy ' + productName + ' online — secure checkout, multilingual storefront.' + briefText
            );
            metaKw[code] = productName.toLowerCase().replace(/\s+/g, ', ') + ', shop, demo';
        });
        return {
            names: names,
            desc: desc,
            seo: { meta_title: metaTitle, meta_description: metaDesc, meta_keywords: metaKw },
            brand: productName
        };
    }

    function applyAiData(res, statusEl, btn) {
        if (!res || !res.data || typeof res.data !== 'object') {
            throw new Error((res && res.error) || 'AI failed');
        }
        if (res.ok === false) {
            throw new Error(res.error || 'AI failed');
        }
        var data = res.data;
        fillLangFields('name_', data.names);
        if (data.desc) fillLangFields('desc_', data.desc);
        if (data.seo) {
            fillLangFields('seo_meta_title_', data.seo.meta_title);
            fillMetaDescriptions(data.seo.meta_description);
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
        var namesPanel = document.getElementById('product-section-names');
        if (namesPanel) {
            namesPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        var msg = res.demo
            ? (btn.getAttribute('data-demo-ok') || 'Demo templates applied.')
            : (btn.getAttribute('data-ok') || 'Generated.');
        if (res.demo && res.error) {
            msg += ' (' + res.error + ')';
        }
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

    function requestAi(productName, category, brief) {
        var payload = {
            product_name: productName,
            category: category,
            source_lang: sourceLang,
            brief_description: brief
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

    function runAi(btn, statusEl) {
        if (!apiUrl) {
            setStatus(statusEl, btn.getAttribute('data-failed') || 'AI endpoint not configured.', 'error');
            return;
        }
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
        setLoading(btn, true);
        setStatus(statusEl, btn.getAttribute('data-generating') || 'Generating…', 'loading');

        requestAi(productName, category, brief)
            .then(function (res) {
                applyAiData(res, statusEl, btn);
            })
            .catch(function (err) {
                try {
                    applyAiData({
                        ok: true,
                        demo: true,
                        data: buildClientDemoData(productName, category, brief),
                        error: err.message || ''
                    }, statusEl, btn);
                } catch (e2) {
                    setStatus(statusEl, err.message || (btn.getAttribute('data-failed') || 'Failed'), 'error');
                }
            })
            .finally(function () {
                setLoading(btn, false);
            });
    }

    var btnGenerate = document.getElementById('shAiGenerateBtn');
    var statusEl = document.getElementById('shAiStatus');
    if (btnGenerate) {
        var defaultLabel = btnGenerate.querySelector('.adm-ai-btn-label');
        if (defaultLabel) {
            btnGenerate.setAttribute('data-label-default', defaultLabel.textContent);
        }
        btnGenerate.addEventListener('click', function () {
            runAi(btnGenerate, statusEl);
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