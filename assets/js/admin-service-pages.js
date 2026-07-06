(function () {
    function exec(cmd, value) {
        document.execCommand(cmd, false, value || null);
    }

    function syncEditor(wrap) {
        var surface = wrap.querySelector('.adm-rich-surface');
        var input = wrap.querySelector('.adm-rich-input');
        if (!surface || !input) return;
        input.value = surface.innerHTML.trim();
    }

    function initEditor(wrap) {
        var surface = wrap.querySelector('.adm-rich-surface');
        if (!surface || surface.dataset.bound === '1') return;
        surface.dataset.bound = '1';

        wrap.querySelectorAll('.adm-rich-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var format = btn.getAttribute('data-format');
                var value = btn.getAttribute('data-value');
                surface.focus();
                if (format === 'link') {
                    var url = window.prompt('URL', 'https://');
                    if (url) exec('createLink', url);
                } else if (format === 'header') {
                    exec('formatBlock', value === '2' ? 'h2' : 'p');
                } else if (format === 'clean') {
                    exec('removeFormat');
                    exec('unlink');
                } else if (format === 'list') {
                    exec(value === 'ordered' ? 'insertOrderedList' : 'insertUnorderedList');
                } else {
                    exec(format);
                }
                syncEditor(wrap);
            });
        });

        surface.addEventListener('input', function () { syncEditor(wrap); });
        surface.addEventListener('blur', function () { syncEditor(wrap); });
    }

    document.querySelectorAll('.adm-rich-editor').forEach(initEditor);

    var form = document.querySelector('#shServicePagesForm, .adm-settings-form');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.adm-rich-editor').forEach(syncEditor);
        });
    }

    var createBtn = document.getElementById('shServicePageCreateBtn');
    var createSlug = document.getElementById('shServicePageNewSlug');
    if (createBtn && createSlug) {
        createBtn.addEventListener('click', function () {
            var slug = (createSlug.value || '').trim().toLowerCase().replace(/\s+/g, '-');
            if (!/^[a-z][a-z0-9_-]{1,31}$/.test(slug)) {
                window.alert(createSlug.getAttribute('data-invalid') || 'Invalid slug');
                createSlug.focus();
                return;
            }
            window.location.href = createBtn.getAttribute('data-base-url') + '?page=' + encodeURIComponent(slug) + '&new=1';
        });
    }
})();