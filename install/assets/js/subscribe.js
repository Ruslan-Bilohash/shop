(function () {
    'use strict';

    var forms = document.querySelectorAll('[data-sh-subscribe]');
    if (!forms.length) return;

    var apiUrl = '';
    var first = forms[0];
    if (first && first.getAttribute('action')) {
        apiUrl = first.getAttribute('action');
    } else {
        var base = document.querySelector('link[rel="canonical"]');
        var root = base ? base.href.replace(/\/[^/]*$/, '/') : '/';
        apiUrl = root + 'api/subscribe.php';
    }

    var lang = document.documentElement.lang || 'en';

    function msg(el, text, ok) {
        var box = el.querySelector('.sh-newsletter-msg');
        if (!box) return;
        box.textContent = text;
        box.hidden = !text;
        box.classList.toggle('is-error', !ok);
        box.classList.toggle('is-success', !!ok);
    }

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('input[type="email"]');
            var btn = form.querySelector('button[type="submit"]');
            var email = input ? input.value.trim() : '';
            if (!email) return;

            if (btn) btn.disabled = true;
            msg(form, '', true);

            var url = form.getAttribute('action') || apiUrl;
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, lang: lang })
            })
                .then(function (r) {
                    return r.json().then(function (data) {
                        return { ok: r.ok, data: data };
                    });
                })
                .then(function (res) {
                    if (res.ok && res.data && res.data.ok) {
                        msg(form, form.getAttribute('data-success') || 'Subscribed! Thank you.', true);
                        if (input) input.value = '';
                    } else {
                        var err = (res.data && res.data.error) || 'error';
                        var map = {
                            already_subscribed: form.getAttribute('data-already') || 'Already subscribed.',
                            invalid_email: form.getAttribute('data-invalid') || 'Invalid email.',
                            newsletter_disabled: form.getAttribute('data-disabled') || 'Newsletter disabled.'
                        };
                        msg(form, map[err] || err, false);
                    }
                })
                .catch(function () {
                    msg(form, form.getAttribute('data-failed') || 'Could not subscribe. Try again.', false);
                })
                .finally(function () {
                    if (btn) btn.disabled = false;
                });
        });
    });
})();