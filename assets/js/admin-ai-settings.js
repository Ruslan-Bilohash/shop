(function () {
    var providers = window.SH_AI_PROVIDERS || {};
    var labels = window.SH_AI_LABELS || {};
    var modelMeta = window.SH_AI_MODEL_META || {};
    var providerEl = document.getElementById('sh-ai-provider');
    var apiBase = document.getElementById('sh-ai-api-base');
    var CUSTOM = '__custom__';
    if (!providerEl) return;

    function useDefaultLabel() {
        return labels.use_default || 'Use default model';
    }

    function customOptionLabel() {
        return labels.custom_option || 'Custom model…';
    }

    function recommendedLabel() {
        return labels.recommended_for || 'Recommended';
    }

    function getPreset(key) {
        return providers[key] || { models: [], api_base: '' };
    }

    function modelOptionLabel(modelId, context) {
        var meta = modelMeta[modelId];
        if (!meta || !meta.hint) {
            return modelId;
        }
        var suffix = meta.hint;
        if (context && context !== 'default' && meta.recommended && meta.recommended.indexOf(context) !== -1) {
            suffix = '★ ' + suffix;
        }
        return modelId + ' — ' + suffix;
    }

    function syncModelHint(wrap, modelId) {
        if (!wrap) return;
        var hintEl = wrap.querySelector('.sh-ai-model-hint');
        if (!hintEl) return;
        var meta = modelMeta[modelId];
        if (!meta || !meta.hint) {
            hintEl.textContent = '';
            hintEl.hidden = true;
            return;
        }
        var ctx = wrap.getAttribute('data-context') || 'default';
        var text = meta.hint;
        if (ctx !== 'default' && meta.recommended && meta.recommended.indexOf(ctx) !== -1) {
            text = recommendedLabel() + ': ' + text;
        }
        hintEl.textContent = text;
        hintEl.hidden = false;
    }

    function syncModelField(wrap, preset) {
        if (!wrap) return;
        var ctx = wrap.getAttribute('data-context') || 'default';
        var isDefault = ctx === 'default';
        var select = wrap.querySelector('.sh-ai-model-select');
        var custom = wrap.querySelector('.sh-ai-model-custom');
        var valueInput = wrap.querySelector('.sh-ai-model-value');
        if (!select || !valueInput) return;

        var current = valueInput.value.trim();
        select.innerHTML = '';

        if (!isDefault) {
            var emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '— ' + useDefaultLabel() + ' —';
            select.appendChild(emptyOpt);
        }

        (preset.models || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = modelOptionLabel(m, ctx);
            select.appendChild(opt);
        });

        var customOpt = document.createElement('option');
        customOpt.value = CUSTOM;
        customOpt.textContent = customOptionLabel();
        select.appendChild(customOpt);

        var matched = false;
        if (!isDefault && current === '') {
            select.value = '';
            matched = true;
            if (custom) {
                custom.hidden = true;
                custom.value = '';
            }
            syncModelHint(wrap, '');
        } else if (current && (preset.models || []).indexOf(current) !== -1) {
            select.value = current;
            matched = true;
            if (custom) {
                custom.hidden = true;
                custom.value = current;
            }
            syncModelHint(wrap, current);
        } else if (current) {
            select.value = CUSTOM;
            matched = true;
            if (custom) {
                custom.hidden = false;
                custom.value = current;
            }
            syncModelHint(wrap, current);
        } else if (isDefault && (preset.models || []).length) {
            select.value = preset.models[0];
            valueInput.value = preset.models[0];
            matched = true;
            if (custom) {
                custom.hidden = true;
                custom.value = preset.models[0];
            }
            syncModelHint(wrap, preset.models[0]);
        }

        if (!matched && select.options.length) {
            select.selectedIndex = 0;
            applySelectValue(wrap);
        }
    }

    function applySelectValue(wrap) {
        var select = wrap.querySelector('.sh-ai-model-select');
        var custom = wrap.querySelector('.sh-ai-model-custom');
        var valueInput = wrap.querySelector('.sh-ai-model-value');
        if (!select || !valueInput) return;

        var val = select.value;
        if (val === CUSTOM) {
            if (custom) {
                custom.hidden = false;
                if (!custom.value.trim() && valueInput.value.trim()) {
                    custom.value = valueInput.value.trim();
                }
                valueInput.value = custom.value.trim();
                custom.focus();
            }
            syncModelHint(wrap, valueInput.value.trim());
            return;
        }

        if (custom) {
            custom.hidden = true;
            if (val !== '') {
                custom.value = val;
            }
        }
        valueInput.value = val;
        syncModelHint(wrap, val);
    }

    function syncAllModels() {
        var preset = getPreset(providerEl.value);
        document.querySelectorAll('.sh-ai-model-field').forEach(function (wrap) {
            syncModelField(wrap, preset);
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

    document.querySelectorAll('.sh-ai-model-field').forEach(function (wrap) {
        var select = wrap.querySelector('.sh-ai-model-select');
        var custom = wrap.querySelector('.sh-ai-model-custom');
        if (select) {
            select.addEventListener('change', function () {
                applySelectValue(wrap);
            });
        }
        if (custom) {
            custom.addEventListener('input', function () {
                var valueInput = wrap.querySelector('.sh-ai-model-value');
                if (valueInput && select && select.value === CUSTOM) {
                    valueInput.value = custom.value.trim();
                    syncModelHint(wrap, valueInput.value.trim());
                }
            });
        }
    });

    var form = document.getElementById('sh-ai-settings-form');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.sh-ai-model-field').forEach(function (wrap) {
                applySelectValue(wrap);
            });
        });
    }

    syncAllModels();
})();