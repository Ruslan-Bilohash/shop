(function () {
    var form = document.getElementById('shCategoryForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var aiBtn = document.getElementById('shAiCategorySeo');
    var aiStatus = document.getElementById('shAiCategorySeoStatus');

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

    function getCategoryName() {
        var el = field('name_' + sourceLang) || field('name_en') || field('name_no');
        return el ? el.value.trim() : '';
    }

    if (aiBtn && apiUrl) {
        aiBtn.addEventListener('click', function () {
            var name = getCategoryName();
            var slugEl = field('slug');
            var slug = slugEl ? slugEl.value.trim() : '';
            if (!name) {
                setStatus(aiStatus, aiBtn.getAttribute('data-need-name') || 'Enter category name first.', 'error');
                (field('name_' + sourceLang) || field('name_en')) && (field('name_' + sourceLang) || field('name_en')).focus();
                return;
            }
            aiBtn.disabled = true;
            setStatus(aiStatus, aiBtn.getAttribute('data-loading') || 'Generating…', 'loading');
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ name: name, slug: slug, source_lang: sourceLang }),
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.ok || !res.data) throw new Error(res.error || 'AI failed');
                    fillSeo(res.data);
                    var msg = res.demo
                        ? (aiBtn.getAttribute('data-demo-ok') || 'Demo SEO templates applied.')
                        : (aiBtn.getAttribute('data-ok') || 'SEO generated.');
                    setStatus(aiStatus, msg, res.demo ? 'info' : 'success');
                })
                .catch(function (err) {
                    setStatus(aiStatus, err.message || 'Request failed', 'error');
                })
                .finally(function () { aiBtn.disabled = false; });
        });
    }
})();