/**
 * BILOHASH unified chat widget — adaptive, XSS-safe, GDPR-aware
 * Configure via window.BH_CHAT_CONFIG before this script loads.
 */
(function () {
    'use strict';

    if (window.__bhChatWidgetInit) return;
    window.__bhChatWidgetInit = true;

    var ICON_CHAT = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';
    var ICON_SEND = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3.4 20.4 22 12 3.4 3.6 3 11l8 1-8 1z"/></svg>';
    var ICON_REFRESH = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>';
    var ICON_CLOSE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';

    var STRINGS = {
        ua: {
            placeholder: 'Напишіть повідомлення…',
            online: 'онлайн',
            refresh: 'Оновити чат',
            close: 'Закрити',
            open: 'Відкрити чат BILOHASH',
            send: 'Надіслати',
            error: 'Помилка з\'єднання. Спробуйте ще раз.',
            powered: 'Працює на',
            crm: 'CRM AI Consultant',
            title: 'BILOHASH',
            subtitle: 'AI Consultant • PHP Developer'
        },
        en: {
            placeholder: 'Type your message…',
            online: 'online',
            refresh: 'Refresh chat',
            close: 'Close',
            open: 'Open BILOHASH chat',
            send: 'Send',
            error: 'Connection error. Please try again.',
            powered: 'Powered by',
            crm: 'CRM AI Consultant',
            title: 'BILOHASH',
            subtitle: 'AI Consultant • PHP Developer'
        },
        no: {
            placeholder: 'Skriv meldingen din…',
            online: 'pålogget',
            refresh: 'Oppdater chat',
            close: 'Lukk',
            open: 'Åpne BILOHASH-chat',
            send: 'Send',
            error: 'Tilkoblingsfeil. Prøv igjen.',
            powered: 'Drevet av',
            crm: 'CRM AI Consultant',
            title: 'BILOHASH',
            subtitle: 'AI-konsulent • PHP-utvikler'
        },
        ru: {
            placeholder: 'Напишите сообщение…',
            online: 'онлайн',
            refresh: 'Обновить чат',
            close: 'Закрыть',
            open: 'Открыть чат BILOHASH',
            send: 'Отправить',
            error: 'Ошибка соединения. Попробуйте снова.',
            powered: 'Работает на',
            crm: 'CRM AI Consultant',
            title: 'BILOHASH',
            subtitle: 'AI Consultant • PHP Developer'
        }
    };

    var PRESETS = {
        root: {
            apiUrl: '/bot.php',
            historyUrl: '/get-messages.php',
            sessionKey: 'bilohash_chat_session'
        },
        ai: {
            apiUrl: '/ai/bot.php',
            historyUrl: '/ai/get-messages.php',
            sessionKey: 'grok_ai_consultant_session'
        },
        website: {
            apiUrl: '/website/ai/bot.php',
            historyUrl: '/website/ai/get-messages.php',
            sessionKey: 'bilohash_ai_consultant_session',
            autoOpen: true,
            autoOpenDelay: 5000,
            requireConsent: true
        }
    };

    function detectPreset() {
        var p = (location.pathname || '').toLowerCase();
        if (p.indexOf('/website/') === 0) return 'website';
        if (p.indexOf('/ai/') === 0) return 'ai';
        return 'root';
    }

    function detectLang() {
        var cfg = window.BH_CHAT_CONFIG || {};
        if (cfg.lang) return normalizeLang(cfg.lang);
        var params = new URLSearchParams(location.search);
        var q = params.get('lang');
        if (q) return normalizeLang(q);
        var htmlLang = (document.documentElement.lang || '').toLowerCase();
        if (htmlLang) return normalizeLang(htmlLang);
        if ((location.pathname || '').toLowerCase().indexOf('/website/') === 0) return 'no';
        return 'en';
    }

    function normalizeLang(l) {
        l = (l || 'en').toLowerCase();
        if (l === 'uk') return 'ua';
        if (l === 'sv') return 'no';
        if (STRINGS[l]) return l;
        return 'en';
    }

    var preset = detectPreset();
    var userCfg = window.BH_CHAT_CONFIG || {};
    var presetCfg = PRESETS[preset] || PRESETS.root;
    var cfg = Object.assign({
        title: null,
        subtitle: null,
        showFooter: true,
        crmUrl: 'https://bilohash.com/ai/crm/',
        requireConsent: preset !== 'root',
        autoOpen: false,
        autoOpenDelay: 0,
        welcomeAuto: false
    }, presetCfg, userCfg);

    var lang = detectLang();
    var t = STRINGS[lang] || STRINGS.en;
    if (!cfg.title) cfg.title = t.title;
    if (!cfg.subtitle) cfg.subtitle = t.subtitle;

    function consentOk() {
        if (!cfg.requireConsent) return true;
        try {
            var keys = ['bilohash_gdpr_consent', 'ai_gdpr_consent'];
            for (var i = 0; i < keys.length; i++) {
                var v = localStorage.getItem(keys[i]);
                if (v === 'accepted' || v === 'custom') return true;
            }
            var raw = localStorage.getItem('bh_cookie_consent');
            if (raw) {
                var d = JSON.parse(raw);
                if (d.v === 2 && d.functional) return true;
            }
            if (localStorage.getItem('gdpr_consent_status') === 'accepted') return true;
        } catch (e) {}
        return false;
    }

    var session = localStorage.getItem(cfg.sessionKey);
    if (!session) {
        session = 's_' + Date.now() + '_' + Math.random().toString(36).substring(2, 14);
        localStorage.setItem(cfg.sessionKey, session);
    }

    var root, toggle, panel, messagesEl, inputEl, typingEl;

    function el(tag, cls, html) {
        var node = document.createElement(tag);
        if (cls) node.className = cls;
        if (html != null) node.innerHTML = html;
        return node;
    }

    function buildUi() {
        root = el('div', 'bh-chat-root');
        root.setAttribute('data-bh-chat', '1');

        toggle = el('button', 'bh-chat-toggle');
        toggle.type = 'button';
        toggle.setAttribute('aria-label', t.open);
        if (cfg.toggleIcon) {
            var safeIcon = String(cfg.toggleIcon).replace(/[^a-z0-9-]/gi, '');
            toggle.innerHTML = safeIcon ? '<i class="fas fa-' + safeIcon + '" aria-hidden="true"></i>' : ICON_CHAT;
        } else {
            toggle.innerHTML = ICON_CHAT;
        }
        if (cfg.accentColor && /^#[0-9a-fA-F]{3,8}$/.test(cfg.accentColor)) {
            root.style.setProperty('--bh-chat-cyan', cfg.accentColor);
            root.style.setProperty('--bh-chat-border', cfg.accentColor + '47');
            toggle.style.background = 'linear-gradient(135deg, ' + cfg.accentColor + ', ' + cfg.accentColor + 'dd)';
            toggle.style.color = '#fff';
        }

        panel = el('div', 'bh-chat-panel');
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-label', cfg.title);

        var header = el('div', 'bh-chat-header');
        var brand = el('div', 'bh-chat-brand');
        brand.appendChild(el('div', 'bh-chat-avatar', 'BH'));
        var meta = el('div', 'bh-chat-meta');
        meta.appendChild(el('div', 'bh-chat-title', escapeHtml(cfg.title)));
        var sub = el('div', 'bh-chat-subtitle');
        sub.innerHTML = '<span class="bh-chat-dot" aria-hidden="true"></span><span>' + escapeHtml(cfg.subtitle) + ' • ' + escapeHtml(t.online) + '</span>';
        meta.appendChild(sub);
        brand.appendChild(meta);

        var actions = el('div', 'bh-chat-actions');
        var refreshBtn = el('button', 'bh-chat-icon-btn', ICON_REFRESH);
        refreshBtn.type = 'button';
        refreshBtn.title = t.refresh;
        refreshBtn.setAttribute('aria-label', t.refresh);
        var closeBtn = el('button', 'bh-chat-icon-btn', ICON_CLOSE);
        closeBtn.type = 'button';
        closeBtn.title = t.close;
        closeBtn.setAttribute('aria-label', t.close);
        actions.append(refreshBtn, closeBtn);

        header.append(brand, actions);

        messagesEl = el('div', 'bh-chat-messages');
        messagesEl.setAttribute('aria-live', 'polite');

        var compose = el('div', 'bh-chat-compose');
        inputEl = el('input', 'bh-chat-input');
        inputEl.type = 'text';
        inputEl.placeholder = t.placeholder;
        inputEl.autocomplete = 'off';
        inputEl.maxLength = 4000;
        var sendBtn = el('button', 'bh-chat-send', ICON_SEND);
        sendBtn.type = 'button';
        sendBtn.setAttribute('aria-label', t.send);
        compose.append(inputEl, sendBtn);

        panel.append(header, messagesEl, compose);

        if (cfg.showFooter) {
            var footer = el('div', 'bh-chat-footer');
            footer.innerHTML = escapeHtml(t.powered) + ' <a href="' + escapeAttr(cfg.crmUrl) + '" target="_blank" rel="noopener">' + escapeHtml(t.crm) + '</a>';
            panel.appendChild(footer);
        }

        root.append(toggle, panel);
        document.body.appendChild(root);

        toggle.addEventListener('click', openChat);
        closeBtn.addEventListener('click', closeChat);
        refreshBtn.addEventListener('click', loadHistory);
        sendBtn.addEventListener('click', sendMessage);
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) closeChat();
        });
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escapeAttr(s) {
        return escapeHtml(s).replace(/'/g, '&#39;');
    }

    function addMsg(text, from) {
        var div = el('div', 'bh-chat-msg bh-chat-msg--' + (from === 'client' ? 'user' : 'bot'));
        div.textContent = text;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showTyping() {
        hideTyping();
        typingEl = el('div', 'bh-chat-msg--typing');
        typingEl.innerHTML = '<span></span><span></span><span></span>';
        messagesEl.appendChild(typingEl);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideTyping() {
        if (typingEl && typingEl.parentNode) typingEl.parentNode.removeChild(typingEl);
        typingEl = null;
    }

    async function loadHistory() {
        try {
            var r = await fetch(cfg.historyUrl + '?session=' + encodeURIComponent(session));
            if (!r.ok) return;
            var history = await r.json();
            if (!Array.isArray(history)) return;
            messagesEl.innerHTML = '';
            history.forEach(function (m) {
                if (m && m.content) addMsg(m.content, m.sender === 'client' ? 'client' : 'bot');
            });
        } catch (e) {}
    }

    async function sendMessage() {
        var text = (inputEl.value || '').trim();
        if (!text) return;
        addMsg(text, 'client');
        inputEl.value = '';
        showTyping();
        try {
            var r = await fetch(cfg.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session: session, message: text })
            });
            var data = await r.json();
            hideTyping();
            if (data && data.reply) {
                addMsg(data.reply, 'bot');
                setTimeout(loadHistory, 600);
            }
        } catch (e) {
            hideTyping();
            addMsg(t.error, 'bot');
        }
    }

    function openChat() {
        panel.classList.add('is-open');
        toggle.classList.add('is-hidden');
        loadHistory();
        setTimeout(function () { inputEl.focus(); }, 120);
    }

    function closeChat() {
        panel.classList.remove('is-open');
        toggle.classList.remove('is-hidden');
    }

    function start() {
        buildUi();
        if (cfg.autoOpen && cfg.autoOpenDelay > 0) {
            setTimeout(function () {
                if (!panel.classList.contains('is-open')) openChat();
            }, cfg.autoOpenDelay);
        }
    }

    function waitForConsent() {
        if (consentOk()) {
            start();
            return;
        }
        function onUpdate() {
            if (consentOk()) {
                start();
                document.removeEventListener('bhConsentUpdated', onUpdate);
                window.removeEventListener('storage', onStorage);
            }
        }
        document.addEventListener('bhConsentUpdated', onUpdate);
        window.addEventListener('storage', function onStorage(e) {
            if (e.key && (e.key.indexOf('gdpr') !== -1 || e.key === 'bh_cookie_consent')) onUpdate();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForConsent);
    } else {
        waitForConsent();
    }
})();