/**
 * documentos-create.js
 * Auto-fills the title input from the selected file name.
 */
document.addEventListener('DOMContentLoaded', () => {
    const fileInput  = document.getElementById('file');
    const titleInput = document.getElementById('title');

    if (!fileInput || !titleInput) return;

    fileInput.addEventListener('change', function () {
        if (titleInput.value.trim() || !this.files[0]) return;

        const raw  = this.files[0].name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ').trim();
        titleInput.value = raw.charAt(0).toUpperCase() + raw.slice(1);
    });
});
