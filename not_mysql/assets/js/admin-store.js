(function () {
    var form = document.getElementById('shStoreForm');
    var preset = document.getElementById('shCurrencyPreset');
    var codeInput = document.getElementById('shSiteCurrency');
    var symbolInput = document.getElementById('shCurrencySymbol');
    var decimalsSelect = document.getElementById('shCurrencyDecimals');
    var shopToggle = document.getElementById('shShopOpenToggle');
    var statusBadge = document.querySelector('.adm-store-status-badge');
    var statusText = document.querySelector('.adm-store-status-text');

    if (preset && codeInput && symbolInput && decimalsSelect) {
        preset.addEventListener('change', function () {
            var opt = preset.options[preset.selectedIndex];
            if (!opt || opt.value === 'custom') {
                return;
            }
            codeInput.value = opt.value;
            symbolInput.value = opt.getAttribute('data-symbol') || '';
            decimalsSelect.value = opt.getAttribute('data-decimals') || '0';
        });

        function syncPresetFromFields() {
            var code = (codeInput.value || '').toUpperCase();
            var found = false;
            for (var i = 0; i < preset.options.length; i++) {
                if (preset.options[i].value === code) {
                    preset.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                for (var j = 0; j < preset.options.length; j++) {
                    if (preset.options[j].value === 'custom') {
                        preset.selectedIndex = j;
                        break;
                    }
                }
            }
        }

        codeInput.addEventListener('input', syncPresetFromFields);
        codeInput.addEventListener('change', syncPresetFromFields);
    }

    if (shopToggle && statusBadge) {
        shopToggle.addEventListener('change', function () {
            var open = shopToggle.checked;
            statusBadge.classList.toggle('is-open', open);
            statusBadge.classList.toggle('is-closed', !open);
            var icon = statusBadge.querySelector('i');
            if (icon) {
                icon.className = 'fas ' + (open ? 'fa-store' : 'fa-hard-hat');
            }
            var label = statusBadge.querySelector('.adm-store-status-label');
            if (label) {
                label.textContent = open
                    ? (statusBadge.getAttribute('data-open-label') || label.textContent)
                    : (statusBadge.getAttribute('data-closed-label') || label.textContent);
            }
            if (statusText) {
                statusText.textContent = open
                    ? (statusText.getAttribute('data-open') || statusText.textContent)
                    : (statusText.getAttribute('data-closed') || statusText.textContent);
            }
        });
    }
})();