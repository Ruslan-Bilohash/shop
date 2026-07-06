(function () {
    var list = document.getElementById('shHomeBlocksList');
    var form = document.getElementById('shHomepageForm');
    if (!list) return;

    var dragging = null;

    function reindexRows() {
        list.querySelectorAll('.adm-home-block-row').forEach(function (row, idx) {
            row.setAttribute('data-idx', String(idx));
            var sortInput = row.querySelector('.sh-home-block-sort');
            if (sortInput) {
                sortInput.value = String(idx + 1);
            }
            row.querySelectorAll('[name]').forEach(function (el) {
                var name = el.getAttribute('name');
                if (!name) return;
                el.setAttribute('name', name.replace(/_(\d+)$/, '_' + idx));
            });
        });
    }

    function bindRemove(row) {
        var btn = row.querySelector('.sh-home-block-remove');
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (!window.confirm(btn.getAttribute('title') || 'Remove?')) return;
            row.remove();
            reindexRows();
        });
    }

    list.querySelectorAll('.adm-home-block-row').forEach(function (row) {
        row.setAttribute('draggable', 'true');
        row.addEventListener('dragstart', function (e) {
            dragging = row;
            row.classList.add('is-dragging');
            if (e.dataTransfer) e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', function () {
            row.classList.remove('is-dragging');
            dragging = null;
            reindexRows();
        });
        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragging || dragging === row) return;
            var rect = row.getBoundingClientRect();
            var before = (e.clientY - rect.top) < rect.height / 2;
            list.insertBefore(dragging, before ? row : row.nextSibling);
        });
        bindRemove(row);
    });

    if (form) {
        form.addEventListener('submit', function () {
            reindexRows();
            form.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
                if (ta.cmEditor) {
                    ta.value = ta.cmEditor.getValue();
                }
                if (!ta.name || ta.name.indexOf('home_block_body_') === -1) {
                    return;
                }
                var val = ta.value || '';
                if (val !== '' && val.indexOf('b64:') !== 0) {
                    try {
                        ta.value = 'b64:' + btoa(unescape(encodeURIComponent(val)));
                    } catch (e) { /* keep plain */ }
                }
            });
        });
    }
})();