(function () {
    'use strict';

    var root = document.getElementById('shSecConsole');
    if (!root) return;

    var apiScan = root.getAttribute('data-api-scan') || '';
    var apiAi = root.getAttribute('data-api-ai') || '';
    var scanningLabel = root.getAttribute('data-scanning') || 'Scanning…';
    var aiScanLabel = root.getAttribute('data-ai-scanning') || 'AI analyzing…';
    var aiLabels = {
        thinking: root.getAttribute('data-ai-thinking') || 'Agent thinking…',
        init: root.getAttribute('data-ai-console-init') || 'Initializing security agent…',
        checks: root.getAttribute('data-ai-console-checks') || 'Loading vulnerability checks…',
        analyze: root.getAttribute('data-ai-console-analyze') || 'Analyzing open issues…',
        done: root.getAttribute('data-ai-console-done') || 'Scan complete',
        error: root.getAttribute('data-ai-console-error') || 'Scan failed',
        demo: root.getAttribute('data-ai-demo-badge') || 'Demo mode'
    };

    var rescanBtn = document.getElementById('shSecRescanAjax');
    var aiBtn = document.getElementById('shSecAiScan');
    var hostInput = document.getElementById('shSecHost');
    var trendCanvas = document.getElementById('shSecTrendChart');
    var aiConsole = document.getElementById('shSecAiConsole');
    var aiResult = document.getElementById('shSecAiResult');

    var trendRaw = root.getAttribute('data-trend') || '[]';
    var trend = [];
    try { trend = JSON.parse(trendRaw); } catch (e) { trend = []; }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function delay(ms) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, ms);
        });
    }

    function drawTrend() {
        if (!trendCanvas || !trendCanvas.getContext || trend.length < 2) return;
        var ctx = trendCanvas.getContext('2d');
        var dpr = window.devicePixelRatio || 1;
        var w = trendCanvas.clientWidth || 400;
        var h = 140;
        trendCanvas.width = w * dpr;
        trendCanvas.height = h * dpr;
        ctx.scale(dpr, dpr);

        var pad = { top: 12, right: 8, bottom: 24, left: 32 };
        var plotW = w - pad.left - pad.right;
        var plotH = h - pad.top - pad.bottom;

        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = '#fef2f2';
        ctx.fillRect(pad.left, pad.top, plotW, plotH);

        ctx.strokeStyle = '#dc2626';
        ctx.lineWidth = 2;
        ctx.beginPath();
        trend.forEach(function (row, i) {
            var x = pad.left + (plotW * i / (trend.length - 1));
            var val = Math.max(0, Math.min(100, parseInt(row.security || row.score || 0, 10)));
            var y = pad.top + plotH * (1 - val / 100);
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.stroke();

        ctx.fillStyle = '#64748b';
        ctx.font = '10px system-ui,sans-serif';
        trend.forEach(function (row, i) {
            if (i % Math.ceil(trend.length / 5) !== 0 && i !== trend.length - 1) return;
            var x = pad.left + (plotW * i / (trend.length - 1));
            ctx.fillText((row.date || '').slice(5), x - 12, h - 6);
        });
    }

    drawTrend();
    window.addEventListener('resize', function () {
        window.requestAnimationFrame(drawTrend);
    });

    if (rescanBtn && apiScan) {
        rescanBtn.addEventListener('click', function () {
            var host = hostInput ? hostInput.value.trim() : '127.0.0.1';
            rescanBtn.disabled = true;
            var old = rescanBtn.innerHTML;
            rescanBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + scanningLabel;

            fetch(apiScan + '?host=' + encodeURIComponent(host), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok || !data.scan) return;
                    var tbody = document.querySelector('.adm-sec-port-table tbody');
                    if (!tbody) return;
                    var rows = data.scan.ports || [];
                    tbody.innerHTML = rows.map(function (row) {
                        return '<tr class="adm-sec-port-row is-' + (row.status || '') + '">' +
                            '<td><code>' + row.port + '</code></td>' +
                            '<td>' + (row.service || '') + '</td>' +
                            '<td>' + (row.group || '') + '</td>' +
                            '<td><span class="adm-sec-pill adm-sec-pill--' + (row.status || '') + '">' + (row.status || '') + '</span></td>' +
                            '<td><span class="adm-sec-risk adm-sec-risk--' + (row.risk || '') + '">' + (row.risk || '') + '</span></td>' +
                            '</tr>';
                    }).join('');
                })
                .finally(function () {
                    rescanBtn.disabled = false;
                    rescanBtn.innerHTML = old;
                });
        });
    }

    function appendConsoleLine(line) {
        if (!aiConsole) return;
        var type = line.type || 'info';
        var prefixes = { info: '›', ok: '✓', warn: '⚠', err: '✗', demo: '◆', think: '…' };
        var row = document.createElement('div');
        row.className = 'adm-sec-ai-line adm-sec-ai-line--' + type;
        row.innerHTML = '<span class="adm-sec-ai-line-prefix">' + (prefixes[type] || '›') + '</span>'
            + '<span class="adm-sec-ai-line-text">' + escapeHtml(line.text || '') + '</span>';
        aiConsole.appendChild(row);
        aiConsole.scrollTop = aiConsole.scrollHeight;
    }

    function showThinkingLine() {
        if (!aiConsole) return null;
        var row = document.createElement('div');
        row.className = 'adm-sec-ai-line adm-sec-ai-line--think is-pending';
        row.innerHTML = '<span class="adm-sec-ai-line-prefix">…</span>'
            + '<span class="adm-sec-ai-line-text">' + escapeHtml(aiLabels.thinking)
            + ' <span class="adm-ai-thinking-dots" aria-hidden="true"><span></span><span></span><span></span></span></span>';
        aiConsole.appendChild(row);
        aiConsole.scrollTop = aiConsole.scrollHeight;
        return row;
    }

    function renderRecommendations(data) {
        if (!aiResult) return;
        var html = '';
        if (data.summary) {
            html += '<p class="adm-sec-ai-summary">' + escapeHtml(data.summary) + '</p>';
        }
        if (data.demo) {
            html += '<span class="adm-badge adm-badge-info">' + escapeHtml(aiLabels.demo) + '</span>';
        }
        if (data.recommendations && data.recommendations.length) {
            html += '<ul class="adm-sec-ai-recs">';
            data.recommendations.forEach(function (r) {
                html += '<li class="adm-sec-ai-rec adm-sec-ai-rec--' + escapeHtml(r.priority || 'medium') + '">';
                html += '<strong>' + escapeHtml(r.title || '') + '</strong>';
                html += '<p>' + escapeHtml(r.detail || '') + '</p></li>';
            });
            html += '</ul>';
        }
        aiResult.innerHTML = html;
        aiResult.hidden = false;
    }

    async function streamConsoleLines(lines) {
        if (!lines || !lines.length) return;
        for (var i = 0; i < lines.length; i++) {
            await delay(i === 0 ? 80 : 140);
            appendConsoleLine(lines[i]);
        }
    }

    if (aiBtn && apiAi && aiConsole) {
        aiBtn.addEventListener('click', function () {
            if (aiBtn.disabled) return;
            aiBtn.disabled = true;
            var oldBtn = aiBtn.innerHTML;
            aiBtn.innerHTML = '<i class="fas fa-brain"></i> ' + aiScanLabel;
            aiBtn.classList.add('is-loading');

            aiConsole.hidden = false;
            aiConsole.innerHTML = '';
            if (aiResult) {
                aiResult.hidden = true;
                aiResult.innerHTML = '';
            }

            var thinkingRow = null;
            var fetchPromise = fetch(apiAi, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: '{}'
            }).then(function (r) { return r.json(); });

            (async function () {
                try {
                    appendConsoleLine({ type: 'info', text: aiLabels.init });
                    await delay(320);
                    appendConsoleLine({ type: 'info', text: aiLabels.checks });
                    await delay(380);
                    appendConsoleLine({ type: 'info', text: aiLabels.analyze });
                    thinkingRow = showThinkingLine();

                    var data = await fetchPromise;
                    if (thinkingRow && thinkingRow.parentNode) {
                        thinkingRow.parentNode.removeChild(thinkingRow);
                    }

                    if (!data.ok) {
                        appendConsoleLine({ type: 'err', text: data.error || aiLabels.error });
                        if (aiResult) {
                            aiResult.innerHTML = '<p class="adm-sec-ai-error">' + escapeHtml(data.error || aiLabels.error) + '</p>';
                            aiResult.hidden = false;
                        }
                        return;
                    }

                    var lines = data.console || [];
                    if (lines.length) {
                        await streamConsoleLines(lines);
                    } else {
                        appendConsoleLine({ type: 'ok', text: aiLabels.done });
                    }
                    renderRecommendations(data);
                } catch (e) {
                    if (thinkingRow && thinkingRow.parentNode) {
                        thinkingRow.parentNode.removeChild(thinkingRow);
                    }
                    appendConsoleLine({ type: 'err', text: aiLabels.error });
                    if (aiResult) {
                        aiResult.innerHTML = '<p class="adm-sec-ai-error">' + escapeHtml(aiLabels.error) + '</p>';
                        aiResult.hidden = false;
                    }
                } finally {
                    aiBtn.disabled = false;
                    aiBtn.classList.remove('is-loading');
                    aiBtn.innerHTML = oldBtn;
                }
            })();
        });
    }
})();