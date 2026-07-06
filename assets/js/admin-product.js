(function () {
    var form = document.getElementById('shProductForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var aiNameInput = document.getElementById('shAiProductName');
    var sourceNameField = form.querySelector('[name="name_' + sourceLang + '"]');

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function fillLangFields(prefix, data) {
        if (!data || typeof data !== 'object') return;
        Object.keys(data).forEach(function (code) {
            var el = field(prefix + code);
            if (el && data[code]) el.value = data[code];
        });
    }

    function syncAiNameToSource() {
        if (!aiNameInput || !sourceNameField) return;
        var val = aiNameInput.value.trim();
        if (val) sourceNameField.value = val;
    }

    if (aiNameInput) {
        if (!aiNameInput.value && sourceNameField && sourceNameField.value) {
            aiNameInput.value = sourceNameField.value.trim();
        }
        aiNameInput.addEventListener('input', syncAiNameToSource);
        aiNameInput.addEventListener('change', syncAiNameToSource);
    }

    function getProductName() {
        if (aiNameInput && aiNameInput.value.trim()) return aiNameInput.value.trim();
        var nameInput = field('name_' + sourceLang) || field('name_en') || field('name_no');
        return nameInput ? nameInput.value.trim() : '';
    }

    function applyAiData(res, statusEl, btn) {
        if (!res.ok || !res.data) throw new Error(res.error || 'AI failed');
        fillLangFields('name_', res.data.names);
        if (res.data.desc) fillLangFields('desc_', res.data.desc);
        if (res.data.seo) {
            fillLangFields('seo_meta_title_', res.data.seo.meta_title);
            fillLangFields('seo_meta_description_', res.data.seo.meta_description);
            fillLangFields('seo_meta_keywords_', res.data.seo.meta_keywords);
        }
        var brand = res.data.brand || (res.data.seo && res.data.seo.brand) || '';
        var brandField = field('seo_brand');
        if (brandField && brand) brandField.value = brand;
        if (aiNameInput && res.data.names && res.data.names[sourceLang]) {
            aiNameInput.value = res.data.names[sourceLang];
        }
        var msg = res.demo
            ? (btn.getAttribute('data-demo-ok') || 'Demo templates applied.')
            : (btn.getAttribute('data-ok') || 'Generated.');
        setStatus(statusEl, msg, res.demo ? 'info' : 'success');
    }

    function runAi(btn, statusEl, seoOnly) {
        var productName = getProductName();
        var categoryEl = field('category');
        var category = categoryEl ? categoryEl.value : '';

        if (!productName) {
            setStatus(statusEl, btn.getAttribute('data-need-name') || 'Enter product name first.', 'error');
            (aiNameInput || field('name_' + sourceLang)) && (aiNameInput || field('name_' + sourceLang)).focus();
            return;
        }

        syncAiNameToSource();
        btn.disabled = true;
        setStatus(statusEl, btn.getAttribute('data-generating') || 'Generating…', 'loading');

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                product_name: productName,
                category: category,
                source_lang: sourceLang
            })
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (seoOnly && res.ok && res.data) {
                    if (res.data.seo) {
                        fillLangFields('seo_meta_title_', res.data.seo.meta_title);
                        fillLangFields('seo_meta_description_', res.data.seo.meta_description);
                        fillLangFields('seo_meta_keywords_', res.data.seo.meta_keywords);
                    }
                    var brand = res.data.brand || (res.data.seo && res.data.seo.brand);
                    if (field('seo_brand') && brand) field('seo_brand').value = brand;
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
})();