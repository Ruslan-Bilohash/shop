(function () {
    'use strict';

    var root = document.getElementById('shHealthConsole');
    if (!root) return;

    var trendRaw = root.getAttribute('data-trend') || '[]';
    var trend = [];
    try { trend = JSON.parse(trendRaw); } catch (e) { trend = []; }

    function drawTrend() {
        var canvas = document.getElementById('shHealthTrendChart');
        if (!canvas || !canvas.getContext || trend.length < 2) return;

        var ctx = canvas.getContext('2d');
        var dpr = window.devicePixelRatio || 1;
        var w = canvas.clientWidth || 600;
        var h = 200;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        ctx.scale(dpr, dpr);

        var pad = { top: 16, right: 12, bottom: 28, left: 36 };
        var plotW = w - pad.left - pad.right;
        var plotH = h - pad.top - pad.bottom;

        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(pad.left, pad.top, plotW, plotH);

        for (var g = 0; g <= 4; g++) {
            var gy = pad.top + (plotH * g / 4);
            ctx.strokeStyle = '#e2e8f0';
            ctx.beginPath();
            ctx.moveTo(pad.left, gy);
            ctx.lineTo(pad.left + plotW, gy);
            ctx.stroke();
            ctx.fillStyle = '#94a3b8';
            ctx.font = '10px system-ui,sans-serif';
            ctx.fillText(String(100 - g * 25), 4, gy + 4);
        }

        var series = [
            { key: 'overall', color: '#2563eb' },
            { key: 'seo', color: '#7c3aed' },
            { key: 'security', color: '#dc2626' },
            { key: 'content', color: '#059669' },
            { key: 'conversion', color: '#d97706' }
        ];

        series.forEach(function (s) {
            ctx.strokeStyle = s.color;
            ctx.lineWidth = s.key === 'overall' ? 2.5 : 1.5;
            ctx.globalAlpha = s.key === 'overall' ? 1 : 0.65;
            ctx.beginPath();
            trend.forEach(function (row, i) {
                var x = pad.left + (plotW * i / (trend.length - 1));
                var val = Math.max(0, Math.min(100, parseInt(row[s.key] || 0, 10)));
                var y = pad.top + plotH * (1 - val / 100);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.stroke();
            ctx.globalAlpha = 1;
        });

        ctx.fillStyle = '#64748b';
        ctx.font = '10px system-ui,sans-serif';
        var step = Math.max(1, Math.floor(trend.length / 7));
        trend.forEach(function (row, i) {
            if (i % step !== 0 && i !== trend.length - 1) return;
            var x = pad.left + (plotW * i / (trend.length - 1));
            var label = (row.date || '').slice(5);
            ctx.fillText(label, x - 14, h - 8);
        });
    }

    function drawRadar() {
        var canvas = document.getElementById('shHealthRadarChart');
        if (!canvas || !canvas.getContext || trend.length === 0) return;

        var last = trend[trend.length - 1];
        var values = [
            parseInt(last.seo || 0, 10),
            parseInt(last.security || 0, 10),
            parseInt(last.content || 0, 10),
            parseInt(last.conversion || 0, 10)
        ];
        var labels = ['SEO', 'Security', 'Content', 'Conversion'];
        var ctx = canvas.getContext('2d');
        var dpr = window.devicePixelRatio || 1;
        var size = Math.min(canvas.clientWidth || 280, 280);
        canvas.width = size * dpr;
        canvas.height = size * dpr;
        ctx.scale(dpr, dpr);

        var cx = size / 2;
        var cy = size / 2;
        var maxR = size * 0.36;
        var n = values.length;

        ctx.clearRect(0, 0, size, size);
        for (var ring = 1; ring <= 4; ring++) {
            ctx.strokeStyle = '#e2e8f0';
            ctx.beginPath();
            for (var i = 0; i < n; i++) {
                var a = (Math.PI * 2 * i / n) - Math.PI / 2;
                var r = maxR * ring / 4;
                var x = cx + Math.cos(a) * r;
                var y = cy + Math.sin(a) * r;
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.closePath();
            ctx.stroke();
        }

        ctx.fillStyle = 'rgba(37, 99, 235, 0.2)';
        ctx.strokeStyle = '#2563eb';
        ctx.lineWidth = 2;
        ctx.beginPath();
        values.forEach(function (v, i) {
            var a = (Math.PI * 2 * i / n) - Math.PI / 2;
            var r = maxR * Math.max(0, Math.min(100, v)) / 100;
            var x = cx + Math.cos(a) * r;
            var y = cy + Math.sin(a) * r;
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = '#475569';
        ctx.font = '11px system-ui,sans-serif';
        labels.forEach(function (lbl, i) {
            var a = (Math.PI * 2 * i / n) - Math.PI / 2;
            var x = cx + Math.cos(a) * (maxR + 18);
            var y = cy + Math.sin(a) * (maxR + 18);
            ctx.textAlign = 'center';
            ctx.fillText(lbl, x, y + 4);
        });
    }

    function redraw() {
        drawTrend();
        drawRadar();
    }

    redraw();
    window.addEventListener('resize', function () {
        window.requestAnimationFrame(redraw);
    });
})();