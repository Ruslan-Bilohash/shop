(function () {
    'use strict';

    var modal = document.getElementById('shSubscriberEmailModal');
    if (!modal) return;

    var form = document.getElementById('shSubscriberEmailForm');
    var statusEl = document.getElementById('shSubscriberEmailStatus');
    var apiUrl = modal.getAttribute('data-api') || '';
    var sendBtn = document.getElementById('shSubscriberEmailSend');

    function setStatus(msg, type) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.className = 'adm-ai-status adm-ai-status--block' + (type ? ' adm-ai-status--' + type : '');
        statusEl.hidden = !msg;
    }

    function openModal(btn) {
        if (!form) return;
        form.reset();
        setStatus('', '');
        var idInput = form.querySelector('[name="subscriber_id"]');
        var toInput = form.querySelector('[name="to"]');
        if (idInput) idInput.value = btn.getAttribute('data-id') || '';
        if (toInput) toInput.value = btn.getAttribute('data-email') || '';
        modal.hidden = false;
        document.body.classList.add('adm-modal-open');
        if (toInput) toInput.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('adm-modal-open');
        setStatus('', '');
    }

    document.querySelectorAll('[data-subscriber-email]').forEach(function (btn) {
        btn.addEventListener('click', function () { openModal(btn); });
    });

    modal.querySelectorAll('[data-close="subscriber-modal"]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });

    if (!form || !apiUrl) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!sendBtn) return;

        var payload = {
            subscriber_id: (form.querySelector('[name="subscriber_id"]') || {}).value || '',
            to: (form.querySelector('[name="to"]') || {}).value || '',
            subject: (form.querySelector('[name="subject"]') || {}).value || '',
            body: (form.querySelector('[name="body"]') || {}).value || ''
        };

        sendBtn.disabled = true;
        setStatus(sendBtn.getAttribute('data-sending') || 'Sending…', 'loading');

        fetch(apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(function (r) {
                return r.json().then(function (res) {
                    if (!r.ok || !res.ok) {
                        throw new Error((res && res.error) || ('HTTP ' + r.status));
                    }
                    return res;
                });
            })
            .then(function (res) {
                setStatus(res.message || (sendBtn.getAttribute('data-sent') || 'Sent'), 'success');
                window.setTimeout(closeModal, 1200);
            })
            .catch(function (err) {
                setStatus(err.message || (sendBtn.getAttribute('data-failed') || 'Failed'), 'error');
            })
            .finally(function () {
                sendBtn.disabled = false;
            });
    });
})();