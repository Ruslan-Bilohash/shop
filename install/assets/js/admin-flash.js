(function () {
    var stack = document.getElementById('admFlashStack');
    if (!stack) return;

    var toast = stack.querySelector('[data-adm-flash-toast]');
    if (!toast) return;

    function dismiss() {
        toast.classList.add('is-hiding');
        window.setTimeout(function () {
            stack.remove();
        }, 280);
    }

    var closeBtn = toast.querySelector('[data-adm-flash-dismiss]');
    if (closeBtn) {
        closeBtn.addEventListener('click', dismiss);
    }

    window.requestAnimationFrame(function () {
        toast.classList.add('is-visible');
    });

    window.setTimeout(dismiss, 6000);
})();