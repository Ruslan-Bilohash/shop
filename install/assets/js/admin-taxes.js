(function () {
    'use strict';

    var form = document.getElementById('shTaxForm');
    if (!form) return;

    var country = document.getElementById('shTaxCountry');
    var rate = document.getElementById('shTaxRate');
    var mode = document.getElementById('shTaxMode');
    var preview = document.getElementById('shTaxPreviewText');
    var example = 10000;

    function fmt(n) {
        return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function recalc() {
        if (!preview || !rate || !mode) return;
        var r = parseFloat(rate.value) || 0;
        var m = mode.value;
        var net, tax, total;
        if (m === 'exclusive') {
            net = example;
            tax = Math.round(example * r / 100);
            total = net + tax;
        } else {
            total = example;
            tax = Math.round(example * r / (100 + r));
            net = total - tax;
        }
        preview.textContent = fmt(net) + ' + ' + fmt(tax) + ' = ' + fmt(total);
    }

    if (country && rate) {
        country.addEventListener('change', function () {
            var opt = country.options[country.selectedIndex];
            if (opt && opt.dataset.rate) {
                rate.value = opt.dataset.rate;
            }
            recalc();
        });
    }
    if (rate) rate.addEventListener('input', recalc);
    if (mode) mode.addEventListener('change', recalc);
})();