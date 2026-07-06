(function () {
    var list = document.getElementById('shHomeBlocksList');
    if (!list) return;

    var dragging = null;

    function reindexSort() {
        list.querySelectorAll('.adm-home-block-row').forEach(function (row, idx) {
            var sortInput = row.querySelector('.sh-home-block-sort');
            if (sortInput) sortInput.value = String(idx + 1);
        });
    }

    function bindRemove(row) {
        var btn = row.querySelector('.sh-home-block-remove');
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (!window.confirm(btn.getAttribute('title') || 'Remove?')) return;
            row.remove();
            reindexSort();
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
            reindexSort();
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
})();