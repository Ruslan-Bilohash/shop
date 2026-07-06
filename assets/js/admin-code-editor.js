(function () {
    if (typeof CodeMirror === 'undefined') return;

    function initMirror(textarea) {
        if (!textarea || textarea.dataset.cmInit === '1') return null;
        var mode = textarea.getAttribute('data-mode') || 'htmlmixed';
        var editor = CodeMirror.fromTextArea(textarea, {
            mode: mode,
            theme: 'material-darker',
            lineNumbers: true,
            lineWrapping: true,
            indentUnit: 2,
            tabSize: 2,
            indentWithTabs: false,
            viewportMargin: Infinity
        });
        textarea.dataset.cmInit = '1';
        editor.setSize('100%', Math.max(180, textarea.rows * 22));
        return editor;
    }

    function bindForm(form) {
        if (!form) return;
        form.addEventListener('submit', function () {
            form.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
                if (ta.nextSibling && ta.nextSibling.CodeMirror) {
                    ta.value = ta.nextSibling.CodeMirror.getValue();
                }
            });
        });
    }

    document.querySelectorAll('.adm-code-mirror').forEach(initMirror);
    bindForm(document.getElementById('shCodeEditorForm'));
    bindForm(document.getElementById('shHomepageForm'));

    window.shAdminInitCodeMirror = function (root) {
        var scope = root || document;
        scope.querySelectorAll('.adm-code-mirror').forEach(function (ta) {
            if (ta.dataset.cmInit !== '1') initMirror(ta);
        });
    };
})();