(function () {
    var form = document.getElementById('shCategoryForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var aiSeoBtn = document.getElementById('shAiCategorySeo');
    var aiSeoStatus = document.getElementById('shAiCategorySeoStatus');
    var aiNamesBtn = document.getElementById('shAiCategoryNames');
    var aiNamesStatus = document.getElementById('shAiCategoryNamesStatus');

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function fillSeo(data) {
        if (!data || !data.seo) return;
        var seo = data.seo;
        Object.keys(seo.meta_title || {}).forEach(function (code) {
            var el = field('seo_meta_title_' + code);
            if (el && seo.meta_title[code]) el.value = seo.meta_title[code];
        });
        Object.keys(seo.meta_description || {}).forEach(function (code) {
            var el = field('seo_meta_description_' + code);
            if (el && seo.meta_description[code]) el.value = seo.meta_description[code];
        });
        Object.keys(seo.meta_keywords || {}).forEach(function (code) {
            var el = field('seo_meta_keywords_' + code);
            if (el && seo.meta_keywords[code]) el.value = seo.meta_keywords[code];
        });
        Object.keys(seo.intro || {}).forEach(function (code) {
            var el = field('seo_intro_' + code);
            if (el && seo.intro[code]) el.value = seo.intro[code];
        });
    }

    function fillNames(data) {
        if (!data || !data.names) return;
        Object.keys(data.names).forEach(function (code) {
            var el = field('name_' + code);
            if (el && data.names[code]) {
                el.value = data.names[code];
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    function getCategoryName() {
        var el = field('name_' + sourceLang) || field('name_en') || field('name_no');
        return el ? el.value.trim() : '';
    }

    function requestAi(action, btn, statusEl) {
        if (!apiUrl) {
            setStatus(statusEl, 'AI endpoint not configured.', 'error');
            return;
        }
        var name = getCategoryName();
        var slugEl = field('slug');
        var slug = slugEl ? slugEl.value.trim() : '';
        if (!name) {
            setStatus(statusEl, btn.getAttribute('data-need-name') || 'Enter category name first.', 'error');
            (field('name_' + sourceLang) || field('name_en')).focus();
            return;
        }
        btn.disabled = true;
        setStatus(statusEl, btn.getAttribute('data-loading') || 'Working…', 'loading');
        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ name: name, slug: slug, source_lang: sourceLang, action: action }),
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok || !res.data) throw new Error(res.error || 'AI failed');
                if (action === 'names') {
                    fillNames(res.data);
                } else {
                    fillSeo(res.data);
                }
                var msg = res.demo
                    ? (btn.getAttribute('data-demo-ok') || 'Demo templates applied.')
                    : (btn.getAttribute('data-ok') || 'Done.');
                setStatus(statusEl, msg, res.demo ? 'info' : 'success');
            })
            .catch(function (err) {
                setStatus(statusEl, err.message || 'Request failed', 'error');
            })
            .finally(function () { btn.disabled = false; });
    }

    if (aiSeoBtn && apiUrl) {
        aiSeoBtn.addEventListener('click', function () {
            requestAi('seo', aiSeoBtn, aiSeoStatus);
        });
    }

    if (aiNamesBtn && apiUrl) {
        aiNamesBtn.addEventListener('click', function () {
            requestAi('names', aiNamesBtn, aiNamesStatus);
        });
    }
})();