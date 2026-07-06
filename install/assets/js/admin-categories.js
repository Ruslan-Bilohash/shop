(function () {
    var tbody = document.getElementById('shCatSortable');
    if (!tbody) return;

    var status = document.getElementById('shCatSortStatus');
    var sortUrl = tbody.getAttribute('data-sort-url') || '';
    var dragging = null;
    var lastSaved = '';

    function setStatus(msg, type) {
        if (!status) return;
        status.textContent = msg;
        status.hidden = !msg;
        status.className = 'adm-cat-sort-status' + (type ? ' is-' + type : '');
    }

    function currentOrder() {
        return Array.prototype.map.call(
            tbody.querySelectorAll('tr[data-slug]'),
            function (row) { return row.getAttribute('data-slug') || ''; }
        ).filter(Boolean);
    }

    function saveOrder() {
        if (!sortUrl) return;
        var order = currentOrder();
        if (!order.length) return;
        setStatus(tbody.getAttribute('data-saving') || 'Saving order…', 'pending');
        fetch(sortUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: JSON.stringify({ order: order })
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (res.data && res.data.ok) {
                    lastSaved = order.join(',');
                    setStatus(tbody.getAttribute('data-saved') || 'Order saved.', 'ok');
                    window.setTimeout(function () { setStatus('', ''); }, 2200);
                } else {
                    setStatus((res.data && res.data.error) || tbody.getAttribute('data-error') || 'Could not save order.', 'error');
                }
            })
            .catch(function () {
                setStatus(tbody.getAttribute('data-error') || 'Could not save order.', 'error');
            });
    }

    function clearDropTargets() {
        tbody.querySelectorAll('.is-drop-target').forEach(function (row) {
            row.classList.remove('is-drop-target');
        });
    }

    lastSaved = currentOrder().join(',');

    tbody.querySelectorAll('tr[data-slug]').forEach(function (row) {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', function (e) {
            dragging = row;
            row.classList.add('is-dragging');
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.getAttribute('data-slug') || '');
            }
        });

        row.addEventListener('dragend', function () {
            row.classList.remove('is-dragging');
            clearDropTargets();
            dragging = null;
            var now = currentOrder().join(',');
            if (now !== lastSaved) {
                saveOrder();
            }
        });

        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragging || dragging === row) return;
            clearDropTargets();
            row.classList.add('is-drop-target');
            var rect = row.getBoundingClientRect();
            var before = (e.clientY - rect.top) < rect.height / 2;
            tbody.insertBefore(dragging, before ? row : row.nextSibling);
        });

        row.addEventListener('drop', function (e) {
            e.preventDefault();
            clearDropTargets();
        });
    });
})();