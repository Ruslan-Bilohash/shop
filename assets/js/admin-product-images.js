(function () {
    var root = document.getElementById('shProductImages');
    var form = document.getElementById('shProductForm');
    if (!root || !form) return;

    var uploadUrl = root.getAttribute('data-upload-url') || '';
    var listEl = root.querySelector('.adm-img-gallery-list');
    var dropzone = root.querySelector('.adm-img-dropzone');
    var fileInput = root.querySelector('.adm-img-file-input');
    var jsonInput = document.getElementById('shProductImagesJson');
    var urlInput = document.getElementById('shProductImageUrl');
    var statusEl = root.querySelector('.adm-img-status');
    var dragSrc = null;

    function images() {
        var items = listEl ? listEl.querySelectorAll('.adm-img-gallery-item') : [];
        return Array.prototype.map.call(items, function (el) {
            return (el.getAttribute('data-url') || '').trim();
        }).filter(Boolean);
    }

    function notifyChecklist() {
        document.dispatchEvent(new CustomEvent('shProductImagesChanged'));
    }

    function syncHidden() {
        var urls = images();
        if (jsonInput) {
            jsonInput.value = JSON.stringify(urls);
        }
        if (urlInput) {
            urlInput.value = urls[0] || '';
        }
        notifyChecklist();
    }

    function setStatus(msg, type) {
        if (!statusEl) return;
        statusEl.textContent = msg || '';
        statusEl.className = 'adm-img-status' + (type ? ' adm-img-status--' + type : '');
        statusEl.hidden = !msg;
    }

    function createItem(url) {
        var li = document.createElement('li');
        li.className = 'adm-img-gallery-item';
        li.setAttribute('data-url', url);
        li.setAttribute('draggable', 'true');
        li.innerHTML =
            '<div class="adm-img-gallery-thumb">' +
            '<img src="' + url.replace(/"/g, '&quot;') + '" alt="" loading="lazy">' +
            '<span class="adm-img-gallery-drag" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>' +
            '</div>' +
            '<button type="button" class="adm-img-gallery-remove" aria-label="Remove"><i class="fas fa-trash"></i></button>';
        bindItem(li);
        return li;
    }

    function addImage(url) {
        if (!url || !listEl) return;
        if (images().indexOf(url) !== -1) return;
        listEl.appendChild(createItem(url));
        syncHidden();
    }

    function removeItem(li) {
        var url = li.getAttribute('data-url') || '';
        if (url.indexOf('/uploads/') !== -1 && uploadUrl) {
            var fd = new FormData();
            fd.append('action', 'delete');
            fd.append('url', url);
            fetch(uploadUrl, { method: 'POST', body: fd, credentials: 'same-origin' }).catch(function () {});
        }
        li.remove();
        syncHidden();
    }

    function bindItem(li) {
        var removeBtn = li.querySelector('.adm-img-gallery-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                removeItem(li);
            });
        }
        li.addEventListener('dragstart', function (e) {
            dragSrc = li;
            li.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        li.addEventListener('dragend', function () {
            li.classList.remove('is-dragging');
            dragSrc = null;
            syncHidden();
        });
        li.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (!dragSrc || dragSrc === li) return;
            var rect = li.getBoundingClientRect();
            var after = e.clientY > rect.top + rect.height / 2;
            listEl.insertBefore(dragSrc, after ? li.nextSibling : li);
        });
    }

    if (listEl) {
        listEl.querySelectorAll('.adm-img-gallery-item').forEach(bindItem);
        syncHidden();
    }

    function uploadFiles(files) {
        if (!files || !files.length || !uploadUrl) return;
        var pending = files.length;
        setStatus(root.getAttribute('data-uploading') || 'Uploading…', 'loading');

        Array.prototype.forEach.call(files, function (file) {
            if (!file.type || file.type.indexOf('image/') !== 0) {
                pending--;
                return;
            }
            var fd = new FormData();
            fd.append('image', file);
            fetch(uploadUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.ok && res.url) {
                        addImage(res.url);
                    } else {
                        throw new Error(res.error || 'Upload failed');
                    }
                })
                .catch(function (err) {
                    setStatus(err.message || 'Upload failed', 'error');
                })
                .finally(function () {
                    pending--;
                    if (pending <= 0) {
                        setStatus(root.getAttribute('data-upload-ok') || 'Images added.', 'success');
                        window.setTimeout(function () { setStatus('', ''); }, 2500);
                    }
                });
        });
    }

    if (dropzone) {
        dropzone.addEventListener('click', function () {
            if (fileInput) fileInput.click();
        });
        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('is-dragover');
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
            uploadFiles(e.dataTransfer.files);
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            uploadFiles(fileInput.files);
            fileInput.value = '';
        });
    }

    if (urlInput) {
        urlInput.addEventListener('change', function () {
            var url = urlInput.value.trim();
            if (url && images().length === 0) {
                addImage(url);
            } else if (url) {
                var first = listEl && listEl.querySelector('.adm-img-gallery-item');
                if (first) {
                    first.setAttribute('data-url', url);
                    var img = first.querySelector('img');
                    if (img) img.src = url;
                    syncHidden();
                }
            }
        });
    }

    form.addEventListener('submit', syncHidden);
})();