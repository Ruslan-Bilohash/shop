(function () {
    var tpl = document.getElementById('shHeaderNavRowTemplate');
    var box = document.getElementById('shHeaderNavRows');
    var addBtn = document.getElementById('shHeaderNavAdd');
    if (!tpl || !box || !addBtn) return;

    function nextIndex() {
        var max = -1;
        box.querySelectorAll('.adm-header-nav-row').forEach(function (row) {
            var n = parseInt(row.getAttribute('data-row') || '0', 10);
            if (n > max) max = n;
        });
        return max + 1;
    }

    addBtn.addEventListener('click', function () {
        var idx = nextIndex();
        var html = tpl.innerHTML
            .replace(/__IDX__/g, String(idx))
            .replace(/__NUM__/g, String(idx + 1));
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        box.appendChild(wrap.firstElementChild);
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.sh-header-nav-remove');
        if (!btn) return;
        var row = btn.closest('.adm-header-nav-row');
        if (row) row.remove();
    });
})();