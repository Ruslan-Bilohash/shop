(function () {
    'use strict';

    var root = document.getElementById('shProductReviews');
    if (!root) return;

    var form = document.getElementById('shProductReviewForm');
    var msg = document.getElementById('shProductReviewMsg');
    var ratingInput = document.getElementById('shReviewRating');
    var starWrap = root.querySelector('[data-review-stars]');
    var api = root.getAttribute('data-api') || '';

    function showMsg(text, ok) {
        if (!msg) return;
        msg.textContent = text;
        msg.hidden = false;
        msg.classList.toggle('is-ok', !!ok);
        msg.classList.toggle('is-err', !ok);
    }

    function paintStars(value) {
        if (!starWrap) return;
        starWrap.querySelectorAll('.sh-review-star-btn').forEach(function (btn) {
            var v = parseInt(btn.getAttribute('data-value') || '0', 10);
            var icon = btn.querySelector('i');
            if (!icon) return;
            var on = v <= value;
            icon.className = on ? 'fas fa-star' : 'far fa-star';
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    if (starWrap) {
        starWrap.querySelectorAll('.sh-review-star-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = parseInt(btn.getAttribute('data-value') || '0', 10);
                if (ratingInput) ratingInput.value = String(v);
                paintStars(v);
            });
            btn.addEventListener('mouseenter', function () {
                paintStars(parseInt(btn.getAttribute('data-value') || '0', 10));
            });
        });
        starWrap.addEventListener('mouseleave', function () {
            paintStars(parseInt(ratingInput && ratingInput.value || '0', 10));
        });
    }

    if (!form || !api) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var rating = parseInt(ratingInput && ratingInput.value || '0', 10);
        var author = (form.author && form.author.value || '').trim();
        var body = (form.body && form.body.value || '').trim();

        if (rating < 1 || rating > 5) {
            showMsg(root.getAttribute('data-err-rating') || 'Select rating', false);
            return;
        }
        if (author.length < 2) {
            showMsg(root.getAttribute('data-err-author') || 'Enter name', false);
            return;
        }
        if (body.length < 10) {
            showMsg(root.getAttribute('data-err-body') || 'Write more text', false);
            return;
        }

        var fd = new FormData(form);
        fd.append('product_id', root.getAttribute('data-product-id') || '');
        fd.append('lang', root.getAttribute('data-lang') || 'en');
        if (typeof grecaptcha !== 'undefined' && document.querySelector('.g-recaptcha')) {
            try {
                var token = grecaptcha.getResponse();
                if (token) fd.set('g-recaptcha-response', token);
            } catch (err) { /* widget not ready */ }
        }

        var btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
            .then(function (res) {
                if (btn) btn.disabled = false;
                if (res.json && res.json.ok) {
                    showMsg(root.getAttribute('data-ok') || 'Thank you!', true);
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                    return;
                }
                var err = (res.json && res.json.error) || '';
                var map = {
                    recaptcha: root.getAttribute('data-err-recaptcha'),
                    rate_limit: root.getAttribute('data-rate-limit'),
                };
                showMsg(map[err] || root.getAttribute('data-err-generic') || 'Error', false);
                if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
            })
            .catch(function () {
                if (btn) btn.disabled = false;
                showMsg(root.getAttribute('data-err-generic') || 'Error', false);
            });
    });
})();