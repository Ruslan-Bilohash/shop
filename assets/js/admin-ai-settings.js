(function () {
    var providers = window.SH_AI_PROVIDERS || {};
    var providerEl = document.getElementById('sh-ai-provider');
    var apiBase = document.getElementById('sh-ai-api-base');
    if (!providerEl) return;

    function syncModelField(select, custom, preset, isDefault) {
        if (!select) return;
        var current = custom ? custom.value.trim() : '';
        select.innerHTML = '';

        if (!isDefault) {
            var emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '— ' + (window.SH_AI_LABELS && SH_AI_LABELS.use_default ? SH_AI_LABELS.use_default : 'use default') + ' —';
            if (current === '') {
                emptyOpt.selected = true;
            }
            select.appendChild(emptyOpt);
        }

        (preset.models || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            if (current === m) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });

        if (current && preset.models.indexOf(current) === -1) {
            var customOpt = document.createElement('option');
            customOpt.value = current;
            customOpt.textContent = current + ' (current)';
            customOpt.selected = true;
            select.appendChild(customOpt);
        }
    }

    function syncAllModels() {
        var key = providerEl.value;
        var preset = providers[key] || { models: [], api_base: '' };
        document.querySelectorAll('.sh-ai-model-field').forEach(function (wrap) {
            var ctx = wrap.getAttribute('data-context') || 'default';
            var select = wrap.querySelector('.sh-ai-model-select');
            var custom = wrap.querySelector('.sh-ai-model-custom');
            syncModelField(select, custom, preset, ctx === 'default');
        });
        if (apiBase && (!apiBase.value || apiBase.dataset.auto === '1')) {
            apiBase.value = preset.api_base || '';
            apiBase.dataset.auto = '1';
        }
    }

    providerEl.addEventListener('change', function () {
        if (apiBase) apiBase.dataset.auto = '1';
        syncAllModels();
    });

    if (apiBase) {
        apiBase.addEventListener('input', function () { apiBase.dataset.auto = '0'; });
    }

    document.querySelectorAll('.sh-ai-model-select').forEach(function (select) {
        select.addEventListener('change', function () {
            var wrap = select.closest('.sh-ai-model-field');
            var custom = wrap ? wrap.querySelector('.sh-ai-model-custom') : null;
            if (custom) {
                custom.value = select.value;
            }
        });
    });

    syncAllModels();
})();