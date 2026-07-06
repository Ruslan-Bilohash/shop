(function () {
    var colorInput = document.querySelector('input[name="chat_widget_color"]');
    var iconInput = document.getElementById('shChatIconInput');
    var previewBtn = document.getElementById('shChatColorPreview');

    function safeIcon(name) {
        return (name || 'comments').toLowerCase().replace(/[^a-z0-9-]/g, '') || 'comments';
    }

    function sync() {
        var color = colorInput ? colorInput.value : '#2563eb';
        var icon = safeIcon(iconInput ? iconInput.value : 'comments');
        if (previewBtn) {
            previewBtn.style.background = 'linear-gradient(135deg,' + color + ',' + color + 'dd)';
            previewBtn.innerHTML = '<i class="fas fa-' + icon + '"></i>';
        }
    }

    if (colorInput) colorInput.addEventListener('input', sync);
    document.addEventListener('iconpicker:change', function (e) {
        if (!e.detail || !e.detail.input || e.detail.input.id !== 'shChatIconInput') return;
        sync();
    });
    sync();
})();