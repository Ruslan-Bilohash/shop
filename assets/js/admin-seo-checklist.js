(function () {
    var form = document.getElementById('shProductForm');
    if (!form) return;

    var sourceLang = form.getAttribute('data-ai-source-lang') || 'en';
    var langCodes = (window.SH_CHECKLIST_LANGS || []).map(function (c) { return c.code; });
    if (langCodes.length === 0) {
        langCodes = ['no', 'en', 'uk', 'ru', 'sv'];
    }

    function field(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function val(name) {
        var el = field(name);
        return el ? String(el.value || '').trim() : '';
    }

    function checked(name) {
        var el = field(name);
        return el ? !!el.checked : false;
    }

    function imageCount() {
        var json = field('images_json');
        if (!json || !json.value) return val('image') ? 1 : 0;
        try {
            var arr = JSON.parse(json.value);
            return Array.isArray(arr) ? arr.filter(Boolean).length : 0;
        } catch (e) {
            return 0;
        }
    }

    function rateLength(len, goodMin, goodMax, warnMin) {
        if (len === 0) return 'bad';
        if (len >= goodMin && len <= goodMax) return 'good';
        if (len >= warnMin) return 'warn';
        return 'bad';
    }

    function scoreItems(items) {
        if (!items.length) return 0;
        var total = 0;
        var wSum = 0;
        items.forEach(function (it) {
            var w = it.weight || 1;
            var pts = it.status === 'good' ? 100 : (it.status === 'warn' ? 55 : 0);
            total += pts * w;
            wSum += w;
        });
        return Math.round(total / Math.max(1, wSum));
    }

    function grade(score, labels) {
        if (score >= 90) return { key: 'excellent', label: labels.grade_excellent || 'Excellent' };
        if (score >= 75) return { key: 'good', label: labels.grade_good || 'Good' };
        if (score >= 50) return { key: 'fair', label: labels.grade_fair || 'Needs work' };
        return { key: 'poor', label: labels.grade_poor || 'Poor' };
    }

    function statusIcon(st) {
        if (st === 'good') return 'check-circle';
        if (st === 'warn') return 'triangle-exclamation';
        return 'circle-xmark';
    }

    function buildContentItems(L) {
        var items = [];
        var cat = val('category');
        items.push({ key: 'category', status: cat ? 'good' : 'bad', weight: 2 });
        var price = parseInt(val('price'), 10) || 0;
        items.push({ key: 'price', status: price > 0 ? 'good' : 'bad', weight: 2 });
        var stock = parseInt(val('stock'), 10) || 0;
        items.push({ key: 'stock', status: stock > 0 ? 'good' : (stock === 0 ? 'warn' : 'bad'), weight: 1 });
        items.push({ key: 'sku', status: val('sku') ? 'good' : 'warn', weight: 1 });
        var imgs = imageCount();
        items.push({ key: 'images', status: imgs >= 2 ? 'good' : (imgs === 1 ? 'warn' : 'bad'), weight: 3 });
        var sale = parseInt(val('sale_price'), 10) || 0;
        if (sale > 0 && price > 0) {
            items.push({ key: 'sale', status: sale < price ? 'good' : 'warn', weight: 1 });
        }
        langCodes.forEach(function (code) {
            var w = code === sourceLang ? 3 : 2;
            items.push({ key: 'name_' + code, status: rateLength(val('name_' + code).length, 12, 120, 4), weight: w });
            items.push({ key: 'desc_' + code, status: rateLength(val('desc_' + code).length, 80, 600, 25), weight: w });
        });
        return items;
    }

    function buildSeoItems() {
        var items = [];
        langCodes.forEach(function (code) {
            var w = code === sourceLang ? 3 : 2;
            items.push({ key: 'meta_title_' + code, status: rateLength(val('seo_meta_title_' + code).length, 30, 60, 12), weight: w });
            items.push({ key: 'meta_desc_' + code, status: rateLength(val('seo_meta_description_' + code).length, 120, 160, 40), weight: w });
            items.push({ key: 'meta_kw_' + code, status: val('seo_meta_keywords_' + code) ? 'good' : 'warn', weight: 1 });
        });
        var og = val('seo_og_image');
        var hasImg = imageCount() > 0;
        items.push({ key: 'og_image', status: og ? 'good' : (hasImg ? 'warn' : 'bad'), weight: 2 });
        items.push({ key: 'brand', status: val('seo_brand') ? 'good' : 'warn', weight: 2 });
        items.push({
            key: 'identifiers',
            status: (val('seo_gtin') || val('seo_mpn')) ? 'good' : 'warn',
            weight: 1
        });
        var schemaOk = checked('seo_schema_product') && checked('seo_schema_offer');
        items.push({ key: 'schema', status: schemaOk ? 'good' : 'bad', weight: 3 });
        return items;
    }

    function updatePanel(panelId, items, labels) {
        var panel = document.getElementById(panelId);
        if (!panel) return;
        var score = scoreItems(items);
        var g = grade(score, labels);
        var scoreEl = panel.querySelector('[data-checklist-score]');
        var scoreVal = panel.querySelector('[data-checklist-score-val]');
        var gradeEl = panel.querySelector('[data-checklist-grade]');
        if (scoreEl) {
            scoreEl.className = 'adm-checklist-score adm-checklist-score--' + g.key;
        }
        if (scoreVal) scoreVal.textContent = String(score);
        if (gradeEl) {
            gradeEl.textContent = g.label;
            gradeEl.className = 'adm-checklist-grade adm-checklist-grade--' + g.key;
        }
        items.forEach(function (it) {
            var row = panel.querySelector('[data-check-key="' + it.key + '"]');
            if (!row) return;
            row.className = 'adm-checklist-item adm-checklist-item--' + it.status;
            row.setAttribute('data-check-status', it.status);
            var icon = row.querySelector('.adm-checklist-item-icon i');
            if (icon) icon.className = 'fas fa-' + statusIcon(it.status);
        });
    }

    function refresh() {
        var contentLabels = window.SH_CONTENT_CHECKLIST_LABELS || {};
        var seoLabels = window.SH_SEO_CHECKLIST_LABELS || {};
        updatePanel('shContentChecklist', buildContentItems(contentLabels), contentLabels);
        updatePanel('shSeoChecklist', buildSeoItems(), seoLabels);
    }

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    form.addEventListener('shProductAiFilled', refresh);
    document.addEventListener('shProductImagesChanged', refresh);

    document.querySelectorAll('[data-checklist-panel] .adm-checklist-item').forEach(function (row) {
        if (!row.querySelector('.adm-checklist-item-hint')) return;
        function toggleHint(e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
            if (e.type === 'keydown') e.preventDefault();
            row.classList.toggle('is-hint-open');
        }
        row.addEventListener('click', toggleHint);
        row.addEventListener('keydown', toggleHint);
    });

    refresh();
})();