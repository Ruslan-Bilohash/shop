(function () {
    'use strict';

    var root = document.getElementById('shLicensePanel');
    if (!root) return;

    var api = root.getAttribute('data-api') || '';
    var btn = document.getElementById('shLicenseVerifyBtn');
    if (!btn || !api) return;

    var statusBadge = document.getElementById('shLicenseStatusBadge');
    var sitesCountEl = document.getElementById('shLicenseSitesCount');
    var expEl = document.getElementById('shLicenseExp');
    var daysEl = document.getElementById('shLicenseDaysLeft');
    var renewBadge = document.getElementById('shLicenseRenewSoon');
    var sitesList = document.getElementById('shLicenseSitesList');
    var msgEl = document.getElementById('shLicenseMsg');

    var labels = {
        verifying: root.getAttribute('data-label-verifying') || 'Verifying license…',
        verified: root.getAttribute('data-label-verified') || 'License verified',
        error: root.getAttribute('data-label-error') || 'Could not verify license',
        licensed: root.getAttribute('data-label-status-licensed') || 'Licensed',
        trial: root.getAttribute('data-label-status-trial') || 'Trial: %d days left',
        expired: root.getAttribute('data-label-status-expired') || 'License expired',
        renewSoon: root.getAttribute('data-label-renew-soon') || 'Renew soon',
        trialExpires: root.getAttribute('data-label-trial-expires') || 'Trial period',
        expiredLabel: root.getAttribute('data-label-expired') || 'Expired',
        current: root.getAttribute('data-label-current') || 'This site'
    };

    function setMsg(text, isError) {
        if (!msgEl) return;
        msgEl.textContent = text || '';
        msgEl.className = 'adm-muted adm-license-msg' + (isError ? ' adm-license-msg--err' : '');
    }

    function setStatusBadge(status, trialDays) {
        if (!statusBadge) return;
        var cls = 'adm-badge adm-badge-info';
        var text = labels.trial.replace('%d', String(trialDays || 0));
        if (status === 'licensed') {
            cls = 'adm-badge adm-badge-green';
            text = labels.licensed;
        } else if (status === 'expired') {
            cls = 'adm-badge adm-badge-warn';
            text = labels.expired;
        }
        statusBadge.className = cls;
        statusBadge.textContent = text;
        root.setAttribute('data-status', status || 'trial');
    }

    function renderSites(sites) {
        if (!sitesList) return;
        sitesList.innerHTML = '';
        if (!Array.isArray(sites) || !sites.length) return;
        sites.forEach(function (site) {
            if (!site || !site.domain) return;
            var li = document.createElement('li');
            li.className = 'adm-license-site' + (site.current ? ' is-current' : '');
            var icon = document.createElement('i');
            icon.className = 'fas fa-globe';
            icon.setAttribute('aria-hidden', 'true');
            li.appendChild(icon);
            var span = document.createElement('span');
            span.textContent = site.domain;
            li.appendChild(span);
            if (site.current) {
                var badge = document.createElement('span');
                badge.className = 'adm-badge adm-badge-info adm-badge-sm';
                badge.textContent = labels.current;
                li.appendChild(badge);
            }
            sitesList.appendChild(li);
        });
    }

    function applyData(data) {
        var status = data.license_status || root.getAttribute('data-status') || 'trial';
        var trialDays = data.trial_days_left || 0;
        setStatusBadge(status, trialDays);

        if (sitesCountEl) {
            sitesCountEl.textContent = String(data.sites_count != null ? data.sites_count : 0);
        }
        if (daysEl) {
            daysEl.textContent = String(data.days_left != null ? data.days_left : trialDays);
        }
        if (expEl) {
            if (status === 'trial') {
                expEl.textContent = labels.trialExpires;
            } else if (data.exp_label) {
                expEl.textContent = data.exp_label;
            } else if (status === 'expired') {
                expEl.textContent = labels.expiredLabel;
            }
        }
        if (renewBadge) {
            renewBadge.style.display = data.renew_soon ? '' : 'none';
        }
        renderSites(data.sites || []);
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        setMsg(labels.verifying, false);

        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ refresh: true })
        }).then(function (r) {
            return r.json().then(function (j) { return { status: r.status, body: j }; });
        }).then(function (res) {
            var data = res.body || {};
            if (res.status === 403) {
                setMsg(labels.error, true);
                return;
            }
            if (!data.ok) {
                setMsg(data.error || labels.error, true);
                if (data.license_status) {
                    setStatusBadge(data.license_status, data.trial_days_left || 0);
                }
                return;
            }
            applyData(data);
            setMsg(labels.verified, false);
        }).catch(function () {
            setMsg(labels.error, true);
        }).finally(function () {
            btn.disabled = false;
        });
    });
})();