(function () {
    var colorInput = document.querySelector('input[name="chat_widget_color"]');
    var iconInput = document.getElementById('shChatIconInput');
    var previewBtn = document.getElementById('shChatColorPreview');
    var providerEl = document.querySelector('select[name="chat_provider"]');
    var modelSelect = document.getElementById('sh-chat-model-select');
    var modelCustom = document.getElementById('sh-chat-model-custom');
    var providers = window.SH_CHAT_PROVIDERS || {};

    function safeIcon(name) {
        return (name || 'comments').toLowerCase().replace(/[^a-z0-9-]/g, '') || 'comments';
    }

    function syncPreview() {
        var color = colorInput ? colorInput.value : '#2563eb';
        var icon = safeIcon(iconInput ? iconInput.value : 'comments');
        if (previewBtn) {
            previewBtn.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'dd)';
            previewBtn.innerHTML = '<i class="fas fa-' + icon + '"></i>';
        }
    }

    function syncChatModels() {
        if (!providerEl || !modelSelect) return;
        var key = providerEl.value;
        if (key === 'none') {
            modelSelect.innerHTML = '';
            return;
        }
        var preset = providers[key] || { models: [] };
        var current = modelCustom ? modelCustom.value.trim() : '';
        modelSelect.innerHTML = '';

        var emptyOpt = document.createElement('option');
        emptyOpt.value = '';
        emptyOpt.textContent = '— AI settings default —';
        if (current === '') {
            emptyOpt.selected = true;
        }
        modelSelect.appendChild(emptyOpt);

        (preset.models || []).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m;
            opt.textContent = m;
            if (current === m) {
                opt.selected = true;
            }
            modelSelect.appendChild(opt);
        });

        if (current && preset.models.indexOf(current) === -1) {
            var custom = document.createElement('option');
            custom.value = current;
            custom.textContent = current + ' (current)';
            custom.selected = true;
            modelSelect.appendChild(custom);
        }
    }

    if (colorInput) colorInput.addEventListener('input', syncPreview);
    document.addEventListener('iconpicker:change', function (e) {
        if (!e.detail || !e.detail.input || e.detail.input.id !== 'shChatIconInput') return;
        syncPreview();
    });

    if (providerEl) {
        providerEl.addEventListener('change', syncChatModels);
    }
    if (modelSelect) {
        modelSelect.addEventListener('change', function () {
            if (modelCustom) {
                modelCustom.value = modelSelect.value;
            }
        });
    }

    syncPreview();
    syncChatModels();
})();