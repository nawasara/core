js modal:
window.dispatchEvent(new CustomEvent('modal-open', {
detail: {
title: 'Judul Modal',
content: '<p>Isi modal bisa HTML, Livewire, dsb</p>',
size: 'md', // optional: sm, md, lg, xl
confirm: true, // optional: show confirm button
onConfirm: () => { /_ aksi _/ }
}
}));
