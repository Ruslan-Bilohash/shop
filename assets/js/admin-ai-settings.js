(function () {
    var providers = window.SH_AI_PROVIDERS || {};
    var providerEl = document.getElementById('sh-ai-provider');
    var modelSelect = document.getElementById('sh-ai-model-select');
    var modelCustom = document.getElementById('sh-ai-model-custom');
    var apiBase = document.getElementById('sh-ai-api-base');
    if (!providerEl || !modelSelect) return;

    function syncModels() {
        var key = providerEl.value;
        var preset = providers[key] || { models: [], api_base: '' };
        modelSelect.innerHTML = '';
        (preset.models || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            modelSelect.appendChild(opt);
        });
        if (modelCustom && modelCustom.value && preset.models.indexOf(modelCustom.value) === -1) {
            var custom = document.createElement('option');
            custom.value = modelCustom.value;
            custom.textContent = modelCustom.value + ' (current)';
            custom.selected = true;
            modelSelect.appendChild(custom);
        }
        if (apiBase && (!apiBase.value || apiBase.dataset.auto === '1')) {
            apiBase.value = preset.api_base || '';
            apiBase.dataset.auto = '1';
        }
    }

    providerEl.addEventListener('change', function () {
        if (apiBase) apiBase.dataset.auto = '1';
        syncModels();
    });
    if (apiBase) {
        apiBase.addEventListener('input', function () { apiBase.dataset.auto = '0'; });
    }
    modelSelect.addEventListener('change', function () {
        if (modelCustom) modelCustom.value = modelSelect.value;
    });
    syncModels();
})();