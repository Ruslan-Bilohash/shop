(function () {
    'use strict';

    var root = document.getElementById('shChangelogConsole');
    if (!root) return;

    var api = root.getAttribute('data-update-api') || '';
    var btn = document.getElementById('shUpdateCheckBtn');
    if (!btn || !api) return;

    var msgEl = document.getElementById('shUpdateMsg');
    var metaEl = document.getElementById('shUpdateMeta');
    var latestEl = document.getElementById('shUpdateLatestVer');
    var cachedBadge = document.getElementById('shUpdateCachedBadge');
    var releaseLink = document.getElementById('shUpdateReleaseLink');

    var labels = {
        checking: root.getAttribute('data-label-checking') || 'Checking for updates…',
        upToDate: root.getAttribute('data-label-up-to-date') || 'You are on the latest version',
        available: root.getAttribute('data-label-available') || 'Update available: v{version}',
        error: root.getAttribute('data-label-error') || 'Could not check for updates',
        checked: root.getAttribute('data-label-checked') || 'Checked: {time}'
    };

    function setMsg(type, text) {
        if (!msgEl) return;
        msgEl.className = 'adm-cl-update-msg' + (type ? ' adm-cl-update-msg--' + type : '');
        msgEl.innerHTML = text;
    }

    function setCached(show) {
        if (!cachedBadge) return;
        cachedBadge.style.display = show ? '' : 'none';
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        setMsg('', '<i class="fas fa-spinner fa-spin"></i> ' + labels.checking);

        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ refresh: true })
        }).then(function (r) {
            return r.json().then(function (j) { return { status: r.status, body: j }; });
        }).then(function (res) {
            var data = res.body || {};
            if (data.blocked || res.status === 403) {
                window.location.href = root.getAttribute('data-license-url') || 'license.php';
                return;
            }
            if (!data.ok) {
                setMsg('err', '<i class="fas fa-circle-xmark"></i> ' + (data.error || labels.error));
                setCached(false);
                return;
            }
            if (data.update_available) {
                setMsg('warn', '<i class="fas fa-arrow-circle-up"></i> '
                    + labels.available.replace('{version}', data.latest_version || ''));
            } else {
                setMsg('ok', '<i class="fas fa-check-circle"></i> ' + labels.upToDate);
            }
            if (latestEl && data.latest_version) {
                latestEl.textContent = 'v' + data.latest_version;
            }
            setCached(!!data.cached);
            if (metaEl && data.checked_at) {
                metaEl.textContent = labels.checked.replace('{time}', data.checked_at);
            }
            if (releaseLink) {
                if (data.release_url) {
                    releaseLink.href = data.release_url;
                    releaseLink.style.display = '';
                } else {
                    releaseLink.style.display = 'none';
                }
            }
        }).catch(function () {
            setMsg('err', '<i class="fas fa-circle-xmark"></i> ' + labels.error);
        }).finally(function () {
            btn.disabled = false;
        });
    });
})();