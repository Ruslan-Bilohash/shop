(function () {
    'use strict';

    var header = document.getElementById('shsHeader');
    var menuBtn = document.getElementById('shsMenuBtn');
    var panel = document.getElementById('shsMobilePanel');
    var overlay = document.getElementById('shsOverlay');
    var langDetails = document.getElementById('shsLangDetails');

    function closeLangDetails() {
        if (langDetails) langDetails.removeAttribute('open');
    }

    function setNavOpen(open) {
        if (!header) return;
        header.classList.toggle('nav-open', open);
        document.body.classList.toggle('shs-nav-open', open);
        if (menuBtn) menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (panel) {
            panel.hidden = !open;
            panel.classList.toggle('is-open', open);
        }
        if (overlay) {
            if (open) {
                overlay.hidden = false;
                requestAnimationFrame(function () {
                    overlay.classList.add('is-open');
                });
            } else {
                overlay.classList.remove('is-open');
                overlay.hidden = true;
            }
        }
        if (open) closeLangDetails();
    }

    function closeNav() {
        if (panel) {
            panel.querySelectorAll('details[open]').forEach(function (d) {
                d.removeAttribute('open');
            });
        }
        setNavOpen(false);
    }

    function onMenuToggle(e) {
        e.preventDefault();
        e.stopPropagation();
        closeLangDetails();
        if (header && header.classList.contains('nav-open')) closeNav();
        else setNavOpen(true);
    }

    var menuClose = document.getElementById('shsMenuClose');

    if (menuBtn && header) {
        menuBtn.addEventListener('click', onMenuToggle);
    }
    if (menuClose) {
        menuClose.addEventListener('click', function (e) {
            e.preventDefault();
            closeNav();
        });
    }

    if (overlay) overlay.addEventListener('click', closeNav);

    if (panel) {
        panel.addEventListener('click', function (e) {
            e.stopPropagation();
        });
        panel.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', closeNav);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && header && header.classList.contains('nav-open')) {
            closeNav();
        }
    });

    if (langDetails) {
        langDetails.addEventListener('toggle', function () {
            if (langDetails.open) closeNav();
        });
        document.addEventListener('click', function (e) {
            if (!langDetails.open) return;
            if (!langDetails.contains(e.target)) closeLangDetails();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && langDetails.open) closeLangDetails();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNav();
    });

    (function initScreenshotLightbox() {
        var root = document.getElementById('shsLightbox');
        var dataEl = document.getElementById('shsLightboxData');
        if (!root || !dataEl) return;

        var items;
        try {
            items = JSON.parse(dataEl.textContent || '[]');
        } catch (e) {
            return;
        }
        if (!items.length) return;

        var img = document.getElementById('shsLightboxImg');
        var caption = document.getElementById('shsLightboxCaption');
        var counter = document.getElementById('shsLightboxCounter');
        var prevBtn = document.getElementById('shsLightboxPrev');
        var nextBtn = document.getElementById('shsLightboxNext');
        var counterFmt = (counter && counter.getAttribute('data-format')) || '%1$d / %2$d';
        var index = 0;
        var touchStartX = 0;
        var touchStartY = 0;

        function formatCounter(i) {
            return counterFmt
                .replace('%1$d', String(i + 1))
                .replace('%2$d', String(items.length))
                .replace('%d', String(i + 1));
        }

        function render() {
            var item = items[index];
            if (!item || !img) return;
            img.src = item.url;
            img.alt = item.alt || item.caption || '';
            if (caption) caption.textContent = item.caption || '';
            if (counter) counter.textContent = formatCounter(index);
            if (prevBtn) prevBtn.disabled = items.length <= 1;
            if (nextBtn) nextBtn.disabled = items.length <= 1;
        }

        function openAt(i) {
            index = ((i % items.length) + items.length) % items.length;
            render();
            root.hidden = false;
            root.setAttribute('aria-hidden', 'false');
            document.body.classList.add('shs-lightbox-open');
            if (prevBtn) prevBtn.focus();
        }

        function close() {
            root.hidden = true;
            root.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('shs-lightbox-open');
            if (img) img.removeAttribute('src');
        }

        function step(delta) {
            if (items.length <= 1) return;
            index = (index + delta + items.length) % items.length;
            render();
        }

        document.querySelectorAll('[data-shs-lightbox]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(btn.getAttribute('data-shs-lightbox'), 10);
                if (!isNaN(idx)) openAt(idx);
            });
        });

        root.querySelectorAll('[data-shs-lightbox-close]').forEach(function (el) {
            el.addEventListener('click', close);
        });

        if (prevBtn) prevBtn.addEventListener('click', function () { step(-1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { step(1); });

        root.addEventListener('touchstart', function (e) {
            if (!e.touches || !e.touches[0]) return;
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        root.addEventListener('touchend', function (e) {
            if (!e.changedTouches || !e.changedTouches[0]) return;
            var dx = e.changedTouches[0].clientX - touchStartX;
            var dy = e.changedTouches[0].clientY - touchStartY;
            if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
            step(dx < 0 ? 1 : -1);
        }, { passive: true });

        document.addEventListener('keydown', function (e) {
            if (root.hidden) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowLeft') step(-1);
            else if (e.key === 'ArrowRight') step(1);
        });
    })();

    document.querySelectorAll('[data-eco-more-btn]').forEach(function (btn) {
        var listId = btn.getAttribute('aria-controls');
        var list = listId ? document.getElementById(listId) : null;
        if (!list) return;
        btn.addEventListener('click', function () {
            var open = list.hidden;
            list.hidden = !open;
            list.setAttribute('aria-hidden', open ? 'false' : 'true');
            list.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.textContent = open
                ? (btn.getAttribute('data-label-less') || 'Show less')
                : (btn.getAttribute('data-label-more') || 'Show more');
        });
    });
})();