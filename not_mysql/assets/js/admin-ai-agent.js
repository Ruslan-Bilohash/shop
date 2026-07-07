(function () {
    'use strict';

    var consoleRoot = document.getElementById('shAiAgentConsole');
    var widgetRoot = document.getElementById('shAiAgentWidget');
    var roots = [consoleRoot, widgetRoot].filter(Boolean);

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function initAgent(root) {
        if (!root || root.getAttribute('data-bound') === '1') return;
        root.setAttribute('data-bound', '1');

        var apiUrl = root.getAttribute('data-api') || '';
        var lang = root.getAttribute('data-lang') || 'en';
        var thinking = root.getAttribute('data-thinking') || 'Thinking…';
        var errorLabel = root.getAttribute('data-error') || 'Request failed';
        var demoLabel = root.getAttribute('data-demo') || 'Demo mode';

        var messagesEl = root.querySelector('.adm-ai-messages, #shAiWidgetMessages');
        var form = root.querySelector('.adm-ai-compose, #shAiWidgetForm');
        var input = root.querySelector('textarea');
        var tipsEl = root.querySelector('#shAiAgentTips, #shAiWidgetTips');
        var history = [];

        function appendMsg(role, text, isDemo) {
            if (!messagesEl) return;
            var wrap = document.createElement('div');
            wrap.className = 'adm-ai-msg adm-ai-msg--' + (role === 'user' ? 'user' : 'bot');
            var avatar = role === 'bot' ? '<div class="adm-ai-msg-avatar"><i class="fas fa-robot"></i></div>' : '';
            var demo = isDemo ? ' <span class="adm-badge adm-badge-info">' + escapeHtml(demoLabel) + '</span>' : '';
            wrap.innerHTML = avatar + '<div class="adm-ai-msg-bubble">' + escapeHtml(text) + demo + '</div>';
            messagesEl.appendChild(wrap);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function renderTips(tips) {
            if (!tipsEl || !tips || !tips.length) return;
            var html = '<ul class="adm-ai-tips-list">';
            tips.forEach(function (t) {
                html += '<li><strong>' + escapeHtml(t.title || '') + '</strong><p>' + escapeHtml(t.detail || '') + '</p></li>';
            });
            html += '</ul>';
            tipsEl.innerHTML = html;
        }

        function sendMessage(text) {
            text = (text || '').trim();
            if (!text || !apiUrl) return;

            appendMsg('user', text);
            history.push({ role: 'user', content: text });

            var btn = form ? form.querySelector('button[type="submit"]') : null;
            if (btn) btn.disabled = true;
            if (input) input.disabled = true;

            var pending = document.createElement('div');
            pending.className = 'adm-ai-msg adm-ai-msg--bot adm-ai-msg--pending';
            pending.innerHTML = '<div class="adm-ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="adm-ai-msg-bubble">'
                + escapeHtml(thinking)
                + ' <span class="adm-ai-thinking-dots" aria-hidden="true"><span></span><span></span><span></span></span></div>';
            if (messagesEl) {
                messagesEl.appendChild(pending);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text, lang: lang, history: history })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (pending.parentNode) pending.parentNode.removeChild(pending);
                    if (!data.ok) {
                        appendMsg('bot', data.error || errorLabel, false);
                        return;
                    }
                    appendMsg('bot', data.reply || '—', !!data.demo);
                    history.push({ role: 'assistant', content: data.reply || '' });
                    renderTips(data.tips);
                })
                .catch(function () {
                    if (pending.parentNode) pending.parentNode.removeChild(pending);
                    appendMsg('bot', errorLabel, false);
                })
                .finally(function () {
                    if (btn) btn.disabled = false;
                    if (input) {
                        input.disabled = false;
                        input.value = '';
                        input.focus();
                    }
                });
        }

        if (form && input) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                sendMessage(input.value);
            });
        }

        root.querySelectorAll('.sh-ai-starter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                sendMessage(btn.getAttribute('data-text') || '');
            });
        });
    }

    roots.forEach(initAgent);

    var fab = document.getElementById('shAiAgentFab');
    var topbarBtn = document.getElementById('shAiAgentTopbarBtn');
    var panel = document.getElementById('shAiAgentWidget');

    function setWidgetOpen(open) {
        if (!panel) return;
        panel.hidden = !open;
        if (fab) fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (topbarBtn) topbarBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) initAgent(panel);
    }

    function toggleWidget() {
        setWidgetOpen(panel.hidden);
    }

    if (panel) {
        if (fab) fab.addEventListener('click', toggleWidget);
        if (topbarBtn) topbarBtn.addEventListener('click', toggleWidget);
        var closeBtn = panel.querySelector('[data-sh-ai-widget-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                setWidgetOpen(false);
            });
        }
    }
})();