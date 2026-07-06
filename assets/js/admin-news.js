(function () {
    var form = document.getElementById('shNewsForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';
    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var aiBtn = document.getElementById('shAiNewsGenerate');
    var aiStatus = document.getElementById('shAiNewsStatus');
    var aiTitle = document.getElementById('shAiNewsTitle');
    var aiBrief = document.getElementById('shAiNewsBrief');

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function setRichBody(code, html) {
        var input = field('body_' + code);
        var surface = document.getElementById('shNewsBody' + code);
        if (surface) {
            surface.innerHTML = html || '';
        }
        if (input) {
            input.value = html || '';
        }
    }

    function fillArticle(data) {
        if (!data) return;

        Object.keys(data.name || {}).forEach(function (code) {
            var el = field('name_' + code);
            if (el && data.name[code]) el.value = data.name[code];
        });
        Object.keys(data.excerpt || {}).forEach(function (code) {
            var el = field('excerpt_' + code);
            if (el && data.excerpt[code]) el.value = data.excerpt[code];
        });
        Object.keys(data.body || {}).forEach(function (code) {
            if (data.body[code]) setRichBody(code, data.body[code]);
        });

        if (data.seo) {
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
        }
    }

    function getTitle() {
        if (aiTitle && aiTitle.value.trim()) {
            return aiTitle.value.trim();
        }
        var el = field('name_' + sourceLang) || field('name_en') || field('name_no');
        return el ? el.value.trim() : '';
    }

    if (aiBtn && apiUrl) {
        aiBtn.addEventListener('click', function () {
            var title = getTitle();
            var slugEl = field('slug');
            var slug = slugEl ? slugEl.value.trim() : '';
            var brief = aiBrief ? aiBrief.value.trim() : '';
            if (!title) {
                setStatus(aiStatus, aiBtn.getAttribute('data-need-title') || 'Enter article title first.', 'error');
                (aiTitle || field('name_' + sourceLang) || field('name_en')) && (aiTitle || field('name_' + sourceLang) || field('name_en')).focus();
                return;
            }
            aiBtn.disabled = true;
            aiBtn.classList.add('is-loading');
            var spinner = aiBtn.querySelector('.adm-btn-ai-spinner');
            if (spinner) spinner.hidden = false;
            setStatus(aiStatus, aiBtn.getAttribute('data-loading') || 'Generating…', 'loading');
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ title: title, slug: slug, brief: brief, source_lang: sourceLang }),
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.ok || !res.data) throw new Error(res.error || 'AI failed');
                    fillArticle(res.data);
                    var msg = res.demo
                        ? (aiBtn.getAttribute('data-demo-ok') || 'Demo templates applied.')
                        : (aiBtn.getAttribute('data-ok') || 'Article generated.');
                    setStatus(aiStatus, msg, res.demo ? 'info' : 'success');
                })
                .catch(function (err) {
                    setStatus(aiStatus, err.message || 'Request failed', 'error');
                })
                .finally(function () {
                    aiBtn.disabled = false;
                    aiBtn.classList.remove('is-loading');
                    var sp = aiBtn.querySelector('.adm-btn-ai-spinner');
                    if (sp) sp.hidden = true;
                });
        });
    }
})();