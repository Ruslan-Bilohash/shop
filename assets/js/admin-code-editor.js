(function () {
    if (typeof CodeMirror === 'undefined') return;

    function syncMirrorValue(textarea) {
        if (!textarea) return;
        if (textarea.cmEditor) {
            textarea.value = textarea.cmEditor.getValue();
        }
    }

    function cmTheme() {
        return document.body.classList.contains('adm-body--code-editor') ? 'nord' : 'material-darker';
    }

    function initMirror(textarea) {
        if (!textarea || textarea.dataset.cmInit === '1') return textarea.cmEditor || null;
        var mode = textarea.getAttribute('data-mode') || 'htmlmixed';
        var editor = CodeMirror.fromTextArea(textarea, {
            mode: mode,
            theme: cmTheme(),
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 2,
            tabSize: 2,
            indentWithTabs: false,
            viewportMargin: Infinity
        });
        textarea.dataset.cmInit = '1';
        textarea.cmEditor = editor;
        editor.setSize('100%', Math.max(180, textarea.rows * 22));
        return editor;
    }

    function bindForm(form) {
        if (!form) return;
        form.addEventListener('submit', function () {
            form.querySelectorAll('.adm-code-mirror').forEach(syncMirrorValue);
        });
    }

    window.shAdminGetCmValue = function (textarea) {
        if (!textarea) return '';
        if (textarea.cmEditor) return textarea.cmEditor.getValue();
        return textarea.value || '';
    };

    window.shAdminSetCmValue = function (textarea, value) {
        if (!textarea) return;
        var next = value || '';
        textarea.value = next;
        if (textarea.cmEditor) {
            textarea.cmEditor.setValue(next);
        }
    };

    window.shAdminInitCodeMirror = function (root) {
        var scope = root || document;
        scope.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
            if (ta.dataset.cmInit !== '1') initMirror(ta);
        });
    };

    document.querySelectorAll('.adm-code-mirror').forEach(initMirror);
    bindForm(document.getElementById('shCodeEditorForm'));
    bindForm(document.getElementById('shHomepageForm'));
    bindForm(document.getElementById('shBlockBuilderForm'));

    document.querySelectorAll('.adm-secret-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-target');
            var input = id ? document.getElementById(id) : null;
            if (!input) return;
            var hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = hidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            }
        });
    });

    function scrollToSettingsSection(hash, smooth) {
        if (!hash || hash.charAt(0) !== '#') return false;
        var target = document.querySelector(hash);
        if (!target) return false;
        target.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        document.querySelectorAll('.adm-settings-toc-link').forEach(function (a) {
            a.classList.toggle('is-active', (a.getAttribute('href') || '') === hash);
        });
        return true;
    }

    document.querySelectorAll('.adm-settings-toc-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var href = link.getAttribute('href') || '';
            if (!href.startsWith('#')) return;
            if (!scrollToSettingsSection(href, true)) return;
            e.preventDefault();
            if (history.replaceState) {
                history.replaceState(null, '', href);
            }
        });
    });

    function scrollToHashOnLoad() {
        var hash = window.location.hash;
        if (!hash) return;
        scrollToSettingsSection(hash, false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.requestAnimationFrame(scrollToHashOnLoad);
        });
    } else {
        window.requestAnimationFrame(scrollToHashOnLoad);
    }
    window.addEventListener('hashchange', function () {
        scrollToSettingsSection(window.location.hash, true);
    });
})();