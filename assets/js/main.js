(function () {
    'use strict';

    var header = document.getElementById('shHeader');
    var menuBtn = document.getElementById('shMenuBtn');
    var menuClose = document.getElementById('shMenuClose');
    var overlay = document.getElementById('shOverlay');
    var panel = document.getElementById('shHeaderPanel');

    function closeNav() {
        if (!header) return;
        header.classList.remove('nav-open');
        document.body.classList.remove('sh-nav-open');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
        if (panel) panel.setAttribute('aria-hidden', 'true');
        if (overlay) {
            overlay.classList.remove('is-open');
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
        }
    }

    function openNav() {
        if (!header) return;
        header.classList.add('nav-open');
        document.body.classList.add('sh-nav-open');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
        if (panel) panel.setAttribute('aria-hidden', 'false');
        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(function () { overlay.classList.add('is-open'); });
        }
    }

    if (menuBtn && header) {
        menuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (header.classList.contains('nav-open')) closeNav();
            else openNav();
        });
    }
    if (menuClose) menuClose.addEventListener('click', closeNav);
    if (overlay) overlay.addEventListener('click', closeNav);
    if (panel) {
        panel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeNav);
        });
    }

    function closeAllLangDropdowns() {
        document.querySelectorAll('.sh-lang-dropdown.is-open').forEach(function (dd) {
            dd.classList.remove('is-open');
            var btn = dd.querySelector('.sh-lang-dropdown-btn');
            var menu = dd.querySelector('.sh-lang-dropdown-menu');
            if (btn) btn.setAttribute('aria-expanded', 'false');
            if (menu) menu.hidden = true;
        });
    }

    document.querySelectorAll('.sh-lang-dropdown').forEach(function (langDropdown) {
        var langBtn = langDropdown.querySelector('.sh-lang-dropdown-btn');
        var langMenu = langDropdown.querySelector('.sh-lang-dropdown-menu');
        if (!langBtn || !langMenu) return;
        langBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var wasOpen = langDropdown.classList.contains('is-open');
            closeAllLangDropdowns();
            if (!wasOpen) {
                langDropdown.classList.add('is-open');
                langMenu.hidden = false;
                langBtn.setAttribute('aria-expanded', 'true');
            }
        });
        langMenu.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    document.addEventListener('click', closeAllLangDropdowns);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeNav();
            closeAllLangDropdowns();
        }
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            closeNav();
            closeAllLangDropdowns();
        }
    });

    // Cart badge — update from data attribute if present
    var badge = document.getElementById('shCartBadge');
    if (badge) {
        var count = parseInt(badge.textContent, 10) || 0;
        if (count <= 0) badge.hidden = true;
    }

    // Product detail tabs
    document.querySelectorAll('.sh-tabs').forEach(function (tablist) {
        var tabs = tablist.querySelectorAll('.sh-tab[data-tab]');
        if (!tabs.length) return;
        var container = tablist.parentElement;

        function activate(tabId) {
            tabs.forEach(function (btn) {
                var on = btn.getAttribute('data-tab') === tabId;
                btn.classList.toggle('active', on);
                btn.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            container.querySelectorAll('.sh-tab-panel').forEach(function (p) {
                var match = p.id === 'tab-' + tabId;
                p.classList.toggle('active', match);
                p.hidden = !match;
            });
        }

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activate(btn.getAttribute('data-tab'));
            });
        });
    });

    var quickPhone = document.getElementById('shQuickPhone');
    var quickBtn = document.getElementById('shQuickBuyBtn');
    var quickBox = document.getElementById('shQuickBuy');
    var quickMsg = document.getElementById('shQuickBuyMsg');
    if (quickPhone && quickBtn && quickBox) {
        function phoneOk(v) {
            return (v.replace(/\D/g, '').length >= 8);
        }
        quickPhone.addEventListener('input', function () {
            var ok = phoneOk(quickPhone.value);
            quickBtn.hidden = !ok;
            if (quickMsg) quickMsg.hidden = true;
        });
        quickBtn.addEventListener('click', function () {
            var api = quickBox.getAttribute('data-api');
            if (!api || !phoneOk(quickPhone.value)) return;
            quickBtn.disabled = true;
            fetch(api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    phone: quickPhone.value,
                    product_id: quickBox.getAttribute('data-product-id'),
                    product_name: quickBox.getAttribute('data-product-name'),
                }),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (quickMsg) {
                        quickMsg.hidden = false;
                        quickMsg.textContent = data.ok
                            ? (quickMsg.dataset.ok || 'Request sent! We will call you back.')
                            : (data.error || 'Could not submit');
                        quickMsg.className = 'sh-quick-buy-msg ' + (data.ok ? 'is-ok' : 'is-err');
                    }
                    if (data.ok) {
                        quickPhone.value = '';
                        quickBtn.hidden = true;
                    }
                })
                .catch(function () {
                    if (quickMsg) {
                        quickMsg.hidden = false;
                        quickMsg.textContent = 'Network error';
                        quickMsg.className = 'sh-quick-buy-msg is-err';
                    }
                })
                .finally(function () { quickBtn.disabled = false; });
        });
    }

    function bindShowMore(btn, list, openClass) {
        if (!btn || !list) return;
        btn.addEventListener('click', function () {
            var open = list.hasAttribute('hidden');
            if (open) {
                list.removeAttribute('hidden');
                list.style.removeProperty('display');
            } else {
                list.setAttribute('hidden', '');
                list.style.display = 'none';
            }
            list.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (openClass) list.classList.toggle(openClass, open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.textContent = open
                ? (btn.getAttribute('data-label-less') || 'Show less')
                : (btn.getAttribute('data-label-more') || 'Show more');
        });
    }

    document.querySelectorAll('[data-eco-more-btn]').forEach(function (btn) {
        var listId = btn.getAttribute('aria-controls');
        bindShowMore(btn, listId ? document.getElementById(listId) : null, 'is-open');
    });

    document.querySelectorAll('[data-cat-more-btn]').forEach(function (btn) {
        var listId = btn.getAttribute('aria-controls');
        bindShowMore(btn, listId ? document.getElementById(listId) : null, null);
    });
})();