(function () {
    var tpl = document.getElementById('shFooterRowTemplate');
    if (!tpl) return;

    function nextIndex(col) {
        var box = document.getElementById('footer-rows-' + col);
        if (!box) return 0;
        var max = -1;
        box.querySelectorAll('.adm-footer-link-row').forEach(function (row) {
            var n = parseInt(row.getAttribute('data-row') || '0', 10);
            if (n > max) max = n;
        });
        return max + 1;
    }

    document.querySelectorAll('.sh-footer-add').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var col = btn.getAttribute('data-col');
            var box = document.getElementById('footer-rows-' + col);
            if (!box) return;
            var idx = nextIndex(col);
            var html = tpl.innerHTML
                .replace(/__COL__/g, col)
                .replace(/__IDX__/g, String(idx))
                .replace(/__NUM__/g, String(idx + 1));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            box.appendChild(wrap.firstElementChild);
        });
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.sh-footer-remove');
        if (!btn) return;
        var row = btn.closest('.adm-footer-link-row');
        if (row) row.remove();
    });
})();