(function () {
    'use strict';

    var root = document.getElementById('shSecConsole');
    if (!root) return;

    var apiScan = root.getAttribute('data-api-scan') || '';
    var apiAi = root.getAttribute('data-api-ai') || '';
    var scanningLabel = root.getAttribute('data-scanning') || 'Scanning…';
    var aiScanLabel = root.getAttribute('data-ai-scanning') || 'AI analyzing…';

    var rescanBtn = document.getElementById('shSecRescanAjax');
    var aiBtn = document.getElementById('shSecAiScan');
    var hostInput = document.getElementById('shSecHost');
    var trendCanvas = document.getElementById('shSecTrendChart');
    var aiResult = document.getElementById('shSecAiResult');

    var trendRaw = root.getAttribute('data-trend') || '[]';
    var trend = [];
    try { trend = JSON.parse(trendRaw); } catch (e) { trend = []; }

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

    if (aiBtn && apiAi && aiResult) {
        aiBtn.addEventListener('click', function () {
            aiBtn.disabled = true;
            var old = aiBtn.innerHTML;
            aiBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + aiScanLabel;
            aiResult.hidden = false;
            aiResult.innerHTML = '<p class="adm-muted"><i class="fas fa-spinner fa-spin"></i> ' + aiScanLabel + '</p>';

            fetch(apiAi, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: '{}'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        aiResult.innerHTML = '<p class="adm-sec-ai-error">' + (data.error || 'Error') + '</p>';
                        return;
                    }
                    var html = '';
                    if (data.summary) {
                        html += '<p class="adm-sec-ai-summary">' + data.summary + '</p>';
                    }
                    if (data.demo) {
                        html += '<span class="adm-badge adm-badge-info">Demo</span>';
                    }
                    if (data.recommendations && data.recommendations.length) {
                        html += '<ul class="adm-sec-ai-recs">';
                        data.recommendations.forEach(function (r) {
                            html += '<li class="adm-sec-ai-rec adm-sec-ai-rec--' + (r.priority || 'medium') + '">';
                            html += '<strong>' + r.title + '</strong><p>' + r.detail + '</p></li>';
                        });
                        html += '</ul>';
                    }
                    aiResult.innerHTML = html;
                })
                .catch(function () {
                    aiResult.innerHTML = '<p class="adm-sec-ai-error">Request failed</p>';
                })
                .finally(function () {
                    aiBtn.disabled = false;
                    aiBtn.innerHTML = old;
                });
        });
    }
})();