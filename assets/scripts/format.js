document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.querySelector('#folder_name');
    nameInput.addEventListener('input', () => {
        nameInput.value = nameInput.value
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+/, '')
            .replace(/^[0-9]+/, '')
    });
});
