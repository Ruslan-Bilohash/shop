(function () {
    'use strict';

    function initBlockBuilderDelete() {
        var form = document.getElementById('shBlockBuilderForm');
        if (!form || form.dataset.tplDeleteBound === '1') {
            return;
        }
        form.dataset.tplDeleteBound = '1';

        var deleteBucket = document.getElementById('shTplDeleteIds');

        function markDeleted(tplId, idx) {
            if (!tplId) return;
            if (deleteBucket) {
                var exists = deleteBucket.querySelector('input[data-tpl-id="' + tplId + '"]');
                if (!exists) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_tpl_ids[]';
                    input.value = tplId;
                    input.setAttribute('data-tpl-id', tplId);
                    deleteBucket.appendChild(input);
                }
            }
            if (idx !== null && idx !== undefined && idx !== '') {
                var check = form.querySelector('.sh-tpl-delete-check[data-tpl-id="' + tplId + '"]')
                    || form.querySelector('input[name="tpl_delete_' + idx + '"]');
                if (check) check.checked = true;
            }
        }

        form.querySelectorAll('.sh-tpl-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var tplId = btn.getAttribute('data-tpl-id') || '';
                var idx = btn.getAttribute('data-idx') || '';
                var msg = btn.getAttribute('data-confirm') || 'Delete this template?';
                if (!tplId || !window.confirm(msg)) return;
                markDeleted(tplId, idx);
                var row = btn.closest('.adm-block-builder-saved-row');
                if (row) row.remove();
            });
        });

        form.querySelectorAll('.sh-tpl-delete-check').forEach(function (check) {
            check.addEventListener('change', function () {
                if (!check.checked) return;
                markDeleted(check.getAttribute('data-tpl-id') || '', null);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBlockBuilderDelete);
    } else {
        initBlockBuilderDelete();
    }
})();