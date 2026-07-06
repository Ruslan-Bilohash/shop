(function () {
    var tpl = document.getElementById('shLangRowTpl');
    var box = document.getElementById('shLangRows');
    var addBtn = document.getElementById('shLangAdd');

    function nextIdx() {
        var max = -1;
        if (!box) return 0;
        box.querySelectorAll('.adm-lang-row').forEach(function (row) {
            var n = parseInt(row.getAttribute('data-row') || '0', 10);
            if (n > max) max = n;
        });
        return max + 1;
    }

    var addHint = document.getElementById('shLangAddHint');

    if (addBtn && tpl && box) {
        addBtn.addEventListener('click', function () {
            var idx = nextIdx();
            var html = tpl.innerHTML.replace(/__IDX__/g, String(idx));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            box.appendChild(wrap.firstElementChild);
            if (addHint) addHint.hidden = false;
            var newRow = box.lastElementChild;
            if (newRow) {
                var codeInput = newRow.querySelector('input[name^="lang_code_"]');
                if (codeInput) codeInput.focus();
            }
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.sh-lang-remove');
        if (!btn) return;
        var row = btn.closest('.adm-lang-row');
        if (row && box && box.querySelectorAll('.adm-lang-row').length > 1) {
            row.remove();
        }
    });

    var aiBtn = document.getElementById('shAiTranslateLangs');
    var aiStatus = document.getElementById('shAiTranslateStatus');
    var targetSel = document.getElementById('shAiTranslateTarget');
    var addActive = document.getElementById('shAiTranslateAddActive');

    if (aiBtn) {
        aiBtn.addEventListener('click', function () {
            var url = aiBtn.getAttribute('data-url');
            var target = targetSel ? targetSel.value : '';
            if (!url || !target) {
                if (aiStatus) {
                    aiStatus.hidden = false;
                    aiStatus.textContent = 'Select a target language.';
                    aiStatus.className = 'adm-ai-status adm-ai-status--error';
                }
                if (targetSel) targetSel.focus();
                return;
            }
            var label = targetSel.options[targetSel.selectedIndex];
            var targetName = label ? label.textContent.trim() : target;
            aiBtn.disabled = true;
            if (aiStatus) {
                aiStatus.hidden = false;
                aiStatus.textContent = 'Translating ' + targetName + ' from English…';
                aiStatus.className = 'adm-ai-status adm-ai-status--loading';
            }
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    target: target,
                    add_active: addActive ? addActive.checked : false,
                }),
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (aiStatus) {
                        if (data.ok) {
                            var msg = data.demo
                                ? 'Demo: lang/' + data.target + '.php created from English template.'
                                : 'Translated to ' + (data.target_name || data.target) + ' → lang/' + data.target + '.php';
                            if (data.lang_added) msg += ' Added to active languages — save settings or reload.';
                            aiStatus.textContent = msg;
                            aiStatus.className = 'adm-ai-status adm-ai-status--success';
                        } else {
                            aiStatus.textContent = data.error || 'Failed';
                            aiStatus.className = 'adm-ai-status adm-ai-status--error';
                        }
                    }
                })
                .catch(function () {
                    if (aiStatus) {
                        aiStatus.textContent = 'Request failed';
                        aiStatus.className = 'adm-ai-status adm-ai-status--error';
                    }
                })
                .finally(function () { aiBtn.disabled = false; });
        });
    }
})();