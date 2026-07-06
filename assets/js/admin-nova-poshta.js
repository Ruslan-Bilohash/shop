(function () {
    'use strict';

    var form = document.getElementById('shNovaPoshtaForm');
    if (!form) return;

    var lookupUrl = form.getAttribute('data-lookup-url') || '';
    var testUrl = form.getAttribute('data-test-url') || '';
    var citySearch = document.getElementById('shNpCitySearch');
    var citySuggest = document.getElementById('shNpCitySuggest');
    var cityRef = document.getElementById('shNpCityRef');
    var cityName = document.getElementById('shNpCityName');
    var whSelect = document.getElementById('shNpWarehouseSelect');
    var whRef = document.getElementById('shNpWarehouseRef');
    var whName = document.getElementById('shNpWarehouseName');
    var testBtn = document.getElementById('shNpTestBtn');
    var testStatus = document.getElementById('shNpTestStatus');
    var debounceTimer = null;

    function setStatus(el, msg, type) {
        if (!el) return;
        el.textContent = msg;
        el.className = 'adm-ai-status' + (type ? ' adm-ai-status--' + type : '');
        el.hidden = !msg;
    }

    function fetchLookup(action, params) {
        var url = new URL(lookupUrl, window.location.origin);
        url.searchParams.set('action', action);
        Object.keys(params || {}).forEach(function (k) {
            url.searchParams.set(k, params[k]);
        });
        return fetch(url.toString(), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); });
    }

    function renderCities(cities) {
        if (!citySuggest) return;
        citySuggest.innerHTML = '';
        if (!cities || !cities.length) {
            citySuggest.hidden = true;
            return;
        }
        cities.forEach(function (c) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'adm-np-suggest-item';
            btn.textContent = c.name + (c.area ? ' — ' + c.area : '');
            btn.addEventListener('click', function () {
                if (cityRef) cityRef.value = c.ref || '';
                if (cityName) cityName.value = c.name || '';
                if (citySearch) citySearch.value = c.name || '';
                citySuggest.hidden = true;
                loadWarehouses(c.ref);
            });
            li.appendChild(btn);
            citySuggest.appendChild(li);
        });
        citySuggest.hidden = false;
    }

    function loadWarehouses(cityRefVal) {
        if (!whSelect) return;
        whSelect.innerHTML = '<option value="">…</option>';
        whSelect.disabled = true;
        if (!cityRefVal) return;
        fetchLookup('warehouses', { city_ref: cityRefVal })
            .then(function (res) {
                if (!res.ok) return;
                whSelect.innerHTML = '<option value="">—</option>';
                (res.warehouses || []).forEach(function (w) {
                    var opt = document.createElement('option');
                    opt.value = w.ref;
                    opt.textContent = w.name;
                    whSelect.appendChild(opt);
                });
                whSelect.disabled = false;
                var saved = form.getAttribute('data-warehouse-ref') || (whRef && whRef.value);
                if (saved) {
                    whSelect.value = saved;
                    syncWarehouse();
                }
            });
    }

    function syncWarehouse() {
        if (!whSelect || !whRef || !whName) return;
        var opt = whSelect.options[whSelect.selectedIndex];
        if (!opt || !opt.value) return;
        whRef.value = opt.value;
        whName.value = opt.textContent || '';
    }

    if (citySearch) {
        citySearch.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var q = citySearch.value.trim();
            if (q.length < 2) {
                if (citySuggest) citySuggest.hidden = true;
                return;
            }
            debounceTimer = setTimeout(function () {
                fetchLookup('cities', { q: q })
                    .then(function (res) {
                        if (res.ok) renderCities(res.cities);
                    });
            }, 280);
        });
        document.addEventListener('click', function (e) {
            if (!citySuggest || citySuggest.hidden) return;
            if (citySearch && citySearch.contains(e.target)) return;
            if (citySuggest.contains(e.target)) return;
            citySuggest.hidden = true;
        });
    }

    if (whSelect) {
        whSelect.addEventListener('change', syncWarehouse);
    }

    if (testBtn) {
        testBtn.addEventListener('click', function () {
            testBtn.disabled = true;
            setStatus(testStatus, '…', 'pending');
            fetch(testUrl, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    setStatus(testStatus, res.message || (res.ok ? 'OK' : 'Failed'), res.ok ? 'success' : 'error');
                })
                .catch(function (err) {
                    setStatus(testStatus, err.message || 'Failed', 'error');
                })
                .finally(function () { testBtn.disabled = false; });
        });
    }

    var initCityRef = form.getAttribute('data-city-ref') || (cityRef && cityRef.value);
    if (initCityRef) {
        loadWarehouses(initCityRef);
    }
})();