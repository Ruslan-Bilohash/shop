(function () {
    var dropdown = document.getElementById('admLangDropdown');
    var btn = document.getElementById('admLangBtn');
    var menu = document.getElementById('admLangMenu');
    if (!dropdown || !btn || !menu) return;

    function closeMenu() {
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
        dropdown.classList.remove('is-open');
    }

    function openMenu() {
        menu.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        dropdown.classList.add('is-open');
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (menu.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });
})();