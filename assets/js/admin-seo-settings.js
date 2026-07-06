(function () {
    var form = document.getElementById('shSeoSettingsForm');
    var aiBtn = document.getElementById('shAiSeoSiteBtn');
    var aiStatus = document.getElementById('shAiSeoSiteStatus');
    var brandInput = document.getElementById('shAiBrandName');
    var copyBtn = document.getElementById('shSitemapCopyBtn');

    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            var url = copyBtn.getAttribute('data-url') || '';
            if (!url || !navigator.clipboard) return;
            navigator.clipboard.writeText(url).then(function () {
                var orig = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i> ' + (copyBtn.getAttribute('data-copied') || 'Copied');
                window.setTimeout(function () { copyBtn.innerHTML = orig; }, 2000);
            }).catch(function () {});
        });
    }

    if (!form || !aiBtn || !brandInput) return;

    var apiUrl = form.getAttribute('data-ai-url') || '';

    function setStatus(msg, type) {
        if (!aiStatus) return;
        aiStatus.textContent = msg;
        aiStatus.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        aiStatus.hidden = !msg;
    }

    function setField(id, val) {
        var el = document.getElementById(id);
        if (el && val) el.value = val;
    }

    aiBtn.addEventListener('click', function () {
        var brand = brandInput.value.trim();
        if (!brand) {
            setStatus(aiBtn.getAttribute('data-need-brand') || 'Enter brand name.', 'error');
            brandInput.focus();
            return;
        }

        var countryEl = document.getElementById('seo_default_country_code');
        var country = countryEl ? countryEl.value.trim() : 'NO';

        aiBtn.disabled = true;
        setStatus(aiBtn.getAttribute('data-generating') || 'Generating…', 'loading');

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ brand_name: brand, country_code: country })
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok || !res.data) throw new Error(res.error || 'Failed');
                var d = res.data;
                setField('seo_site_name', d.seo_site_name);
                setField('seo_org_name', d.seo_org_name);
                setField('seo_geo_region', d.seo_geo_region);
                setField('seo_geo_placename', d.seo_geo_placename);
                setField('seo_default_country_code', d.seo_default_country_code);
                setField('seo_twitter_site', d.seo_twitter_site);
                var msg = res.demo
                    ? (aiBtn.getAttribute('data-demo-ok') || 'Demo templates applied.')
                    : (aiBtn.getAttribute('data-ok') || 'SEO fields filled.');
                setStatus(msg, res.demo ? 'info' : 'success');
            })
            .catch(function (err) {
                setStatus(err.message || (aiBtn.getAttribute('data-failed') || 'Failed'), 'error');
            })
            .finally(function () {
                aiBtn.disabled = false;
            });
    });
})();