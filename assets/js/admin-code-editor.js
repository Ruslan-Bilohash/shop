(function () {
    if (typeof CodeMirror === 'undefined') return;

    function syncMirrorValue(textarea) {
        if (!textarea) return;
        if (textarea.cmEditor) {
            textarea.value = textarea.cmEditor.getValue();
        }
    }

    function initMirror(textarea) {
        if (!textarea || textarea.dataset.cmInit === '1') return textarea.cmEditor || null;
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
})();