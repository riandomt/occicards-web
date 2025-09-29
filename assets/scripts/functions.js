export function formatFileName() {
  document.addEventListener('input', (e) => {
    if (!e.target.matches('.name')) return;

    e.target.value = e.target.value
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9-]/g, '')
      .replace(/-+/g, '-')
      .replace(/^-+/, '')
      .replace(/^[0-9]+/, '');
  });
}
