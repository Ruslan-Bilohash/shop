(function () {
    var form = document.getElementById('shSmsAuthForm');
    if (!form) return;

    var apiUrl = form.getAttribute('data-sms-url') || '';
    var phoneStep = document.getElementById('shSmsPhoneStep');
    var codeStep = document.getElementById('shSmsCodeStep');
    var phoneInput = document.getElementById('shAuthPhone');
    var codeInput = document.getElementById('shAuthSmsCode');
    var statusEl = document.getElementById('shSmsAuthStatus');
    var sendBtn = document.getElementById('shSmsSendBtn');
    var verifyBtn = document.getElementById('shSmsVerifyBtn');
    var backBtn = document.getElementById('shSmsBackBtn');

    function setStatus(msg, type) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.className = 'sh-auth-status' + (type ? ' sh-auth-status--' + type : '');
        statusEl.hidden = !msg;
    }

    function showCodeStep(phone) {
        if (phoneStep) phoneStep.hidden = true;
        if (codeStep) codeStep.hidden = false;
        if (codeInput) {
            codeInput.value = '';
            codeInput.focus();
        }
        if (form.querySelector('[name="action"]')) {
            form.querySelector('[name="action"]').value = 'verify_otp';
        }
        if (form.querySelector('[name="phone"]')) {
            form.querySelector('[name="phone"]').value = phone;
        }
    }

    function showPhoneStep() {
        if (phoneStep) phoneStep.hidden = false;
        if (codeStep) codeStep.hidden = true;
        if (form.querySelector('[name="action"]')) {
            form.querySelector('[name="action"]').value = 'send_otp';
        }
        setStatus('', '');
    }

    if (sendBtn && apiUrl) {
        sendBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var phone = phoneInput ? phoneInput.value.trim() : '';
            if (!phone) {
                setStatus(sendBtn.getAttribute('data-need-phone') || 'Enter phone number', 'error');
                return;
            }
            sendBtn.disabled = true;
            setStatus(sendBtn.getAttribute('data-sending') || 'Sending…', 'loading');
            fetch(apiUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ phone: phone })
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error(res.error || 'send_failed');
                    }
                    var msg = sendBtn.getAttribute('data-sent') || 'Code sent.';
                    if (res.demo && res.demo_code) {
                        msg = (sendBtn.getAttribute('data-demo-sent') || 'Demo code: %s').replace('%s', res.demo_code);
                    }
                    setStatus(msg, res.demo ? 'info' : 'success');
                    showCodeStep(phone);
                })
                .catch(function (err) {
                    setStatus(err.message || 'Failed', 'error');
                })
                .finally(function () {
                    sendBtn.disabled = false;
                });
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            showPhoneStep();
        });
    }
})();