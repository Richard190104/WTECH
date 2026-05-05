(function () {
    function fileKey(file) {
        return [file.name, file.size, file.lastModified].join(':');
    }

    function formatSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex += 1;
        }

        return `${size.toFixed(size >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
    }

    function createEmptyState() {
        const column = document.createElement('div');
        column.className = 'col-12';

        column.innerHTML = `
            <div class="border rounded-3 bg-light p-4 text-center text-muted">
                <i class="fas fa-images mb-2"></i>
                <div>No images selected yet.</div>
            </div>
        `;

        return column;
    }

    function createPreviewCard(file, index, removeHandler, objectUrls) {
        const column = document.createElement('div');
        column.className = 'col-md-4';

        const url = URL.createObjectURL(file);
        objectUrls.push(url);

        column.innerHTML = `
            <div class="card h-100">
                <img src="${url}" class="card-img-top" alt="${file.name}" style="height: 160px; object-fit: cover;">
                <div class="card-body d-flex flex-column gap-2">
                    <div>
                        <div class="fw-semibold text-truncate" title="${file.name}">${file.name}</div>
                        <div class="text-muted small">${formatSize(file.size)}</div>
                    </div>
                    <button type="button" class="admin-btn admin-btn-secondary mt-auto js-admin-remove-image" data-index="${index}">
                        Remove
                    </button>
                </div>
            </div>
        `;

        column.querySelector('.js-admin-remove-image').addEventListener('click', removeHandler);
        return column;
    }

    function setupUploader(form) {
        const input = form.querySelector('[data-admin-image-input]');
        const preview = form.querySelector('[data-admin-image-preview]');

        if (!input || !preview) {
            return;
        }

        const maxFiles = Number(input.dataset.adminMaxImages || 5);
        let selectedFiles = [];
        let objectUrls = [];

        function syncInput() {
            const transfer = new DataTransfer();
            selectedFiles.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
        }

        function render() {
            objectUrls.forEach((url) => URL.revokeObjectURL(url));
            objectUrls = [];
            preview.innerHTML = '';

            if (!selectedFiles.length) {
                preview.appendChild(createEmptyState());
                return;
            }

            selectedFiles.forEach((file, index) => {
                preview.appendChild(
                    createPreviewCard(file, index, () => {
                        selectedFiles = selectedFiles.filter((_, currentIndex) => currentIndex !== index);
                        syncInput();
                        render();
                    }, objectUrls)
                );
            });
        }

        input.addEventListener('change', () => {
            const incomingFiles = Array.from(input.files || []);
            const nextFiles = selectedFiles.slice();

            incomingFiles.forEach((file) => {
                if (nextFiles.length >= maxFiles) {
                    return;
                }

                const key = fileKey(file);
                const alreadyAdded = nextFiles.some((existingFile) => fileKey(existingFile) === key);

                if (!alreadyAdded) {
                    nextFiles.push(file);
                }
            });

            selectedFiles = nextFiles;
            syncInput();
            render();
        });

        render();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-admin-image-uploader]').forEach(setupUploader);
    });
}());