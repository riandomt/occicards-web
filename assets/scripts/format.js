function formatToLowerAndHyphen(){
    const name = document.getElementById('name');

    if (!name) return;

    name.addEventListener('input', () => {
        name.value = name.value.toLowerCase()
            .replace(' ', '-')
            .replace(/[^a-z0-9-]/g, '');
    })
}
formatToLowerAndHyphen()