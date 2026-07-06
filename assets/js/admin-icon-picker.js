(function () {
    'use strict';

    function initPicker(root) {
        var openBtn = root.querySelector('[data-icon-picker-open]');
        var modal = root.querySelector('[data-icon-picker-modal]');
        var grid = root.querySelector('[data-icon-picker-grid]');
        var search = root.querySelector('[data-icon-picker-search]');
        var input = root.querySelector('input[type="hidden"]');
        var preview = root.querySelector('[data-icon-picker-preview]');
        var pickLabel = openBtn ? openBtn.querySelector('.adm-icon-trigger-text') : null;
        if (!openBtn || !modal || !grid || !input) return;

        var iconsCache = null;
        var changeText = openBtn.getAttribute('data-change-label') || 'Change icon';
        var pickText = pickLabel ? pickLabel.textContent : 'Choose icon';

        function setPreview(icon) {
            if (preview) preview.innerHTML = '<i class="fas fa-' + icon + '" aria-hidden="true"></i>';
            if (pickLabel) pickLabel.textContent = changeText;
            root.dispatchEvent(new CustomEvent('iconpicker:change', {
                bubbles: true,
                detail: { icon: icon, root: root, input: input }
            }));
        }

        function closeModal() {
            modal.hidden = true;
            document.body.classList.remove('adm-modal-open');
        }

        function openModal() {
            modal.hidden = false;
            document.body.classList.add('adm-modal-open');
            if (search) {
                search.value = '';
                search.focus();
            }
            renderGrid(iconsCache || []);
            if (!iconsCache) loadIcons();
        }

        function loadIcons() {
            var url = openBtn.getAttribute('data-url');
            if (!url) return;
            grid.innerHTML = '<p class="adm-icon-modal-loading">Loading…</p>';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    iconsCache = data.ok && data.icons ? data.icons : [];
                    renderGrid(iconsCache);
                })
                .catch(function () {
                    grid.innerHTML = '<p class="adm-icon-modal-error">Failed to load icons.</p>';
                });
        }

        function renderGrid(icons) {
            var q = (search && search.value ? search.value : '').toLowerCase().trim();
            var filtered = icons.filter(function (name) {
                return q === '' || name.indexOf(q) !== -1;
            });
            if (!filtered.length) {
                grid.innerHTML = '<p class="adm-icon-modal-empty">No icons match.</p>';
                return;
            }
            var current = input.value || 'tag';
            grid.innerHTML = filtered.map(function (name) {
                var sel = name === current ? ' is-selected' : '';
                return '<button type="button" class="adm-icon-modal-item' + sel + '" data-icon="' + name + '" title="' + name + '">' +
                    '<i class="fas fa-' + name + '" aria-hidden="true"></i>' +
                    '<span>' + name + '</span></button>';
            }).join('');
        }

        openBtn.addEventListener('click', openModal);

        modal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close="icon-modal"]')) closeModal();
            var item = e.target.closest('.adm-icon-modal-item');
            if (!item) return;
            var icon = item.getAttribute('data-icon');
            if (!icon) return;
            input.value = icon;
            setPreview(icon);
            grid.querySelectorAll('.adm-icon-modal-item').forEach(function (el) {
                el.classList.toggle('is-selected', el.getAttribute('data-icon') === icon);
            });
            closeModal();
        });

        if (search) {
            search.addEventListener('input', function () {
                renderGrid(iconsCache || []);
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) closeModal();
        });

        if (input.value) {
            setPreview(input.value);
        } else if (pickLabel) {
            pickLabel.textContent = pickText;
        }
    }

    document.querySelectorAll('[data-icon-picker]').forEach(initPicker);
})();