(function () {
    'use strict';
    var root = document.getElementById('shBillingDemo');
    if (!root) return;

    var api = root.dataset.api || '';
    var lang = root.dataset.lang || 'en';
    var isDemoStaff = root.dataset.demoStaff === '1';
    var msgEl = document.getElementById('shBillingMsg');
    var statusEl = document.getElementById('shBillingStatus');

    function showMsg(text, ok) {
        if (!msgEl) return;
        msgEl.hidden = false;
        msgEl.textContent = text;
        msgEl.className = 'adm-bd-msg ' + (ok ? 'is-ok' : 'is-err');
    }

    function post(action, extra) {
        var body = Object.assign({ action: action, lang: lang }, extra || {});
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        }).then(function (r) { return r.json(); });
    }

    function updateUi(state, ref) {
        if (!state) return;
        var plan = state.plan || '';
        var used = state.api_requests_used || 0;
        var limit = state.api_requests_limit || 0;
        var pct = limit > 0 ? Math.min(100, Math.round(used / limit * 100)) : 0;
        var hasApi = isDemoStaff || (plan !== '' && limit > 0);

        document.querySelectorAll('.adm-bd-plan').forEach(function (el) {
            el.classList.toggle('is-active', el.querySelector('[data-plan="' + plan + '"]') !== null && plan !== '');
        });
        document.querySelectorAll('.adm-bd-pay').forEach(function (btn) {
            btn.disabled = plan !== '';
        });

        if (statusEl) statusEl.hidden = !isDemoStaff && plan === '';
        var planLabel = document.getElementById('shBillingPlanLabel');
        if (planLabel && plan) {
            planLabel.textContent = plan === 'yearly' ? 'Active: Yearly' : 'Active: Monthly';
        }
        var refEl = document.getElementById('shBillingRef');
        if (refEl && ref) refEl.textContent = 'Ref: ' + ref;
        var apiBlock = document.getElementById('shBillingApiBlock');
        if (apiBlock) apiBlock.hidden = !hasApi;

        var countEl = document.getElementById('shBillingApiCount');
        if (countEl) countEl.textContent = used + ' / ' + limit;
        var barEl = document.getElementById('shBillingApiBar');
        if (barEl) barEl.style.width = pct + '%';
    }

    root.querySelectorAll('.adm-bd-pay').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var plan = btn.dataset.plan || '';
            btn.disabled = true;
            post('subscribe', { plan: plan }).then(function (res) {
                if (res.ok) {
                    updateUi(res.state, res.ref);
                    showMsg('Demo payment successful — ref ' + (res.ref || ''), true);
                } else {
                    showMsg(res.error || 'Payment failed', false);
                    btn.disabled = false;
                }
            }).catch(function () {
                showMsg('Network error', false);
                btn.disabled = false;
            });
        });
    });

    var cancelBtn = document.getElementById('shBillingCancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            post('cancel').then(function (res) {
                if (res.ok) {
                    updateUi(res.state, '');
                    showMsg('Demo subscription cancelled', true);
                } else {
                    showMsg(res.error || 'Cancel failed', false);
                }
            });
        });
    }

    var testBtn = document.getElementById('shBillingApiTest');
    if (testBtn) {
        testBtn.addEventListener('click', function () {
            post('api_request').then(function (res) {
                if (res.ok) {
                    updateUi(res.state, '');
                    showMsg('API request OK — ' + (res.remaining || 0) + ' left', true);
                } else {
                    if (res.state) updateUi(res.state, '');
                    showMsg(res.error || 'API limit reached', false);
                }
            });
        });
    }
})();