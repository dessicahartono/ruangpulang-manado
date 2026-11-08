// Menu HP, kontak, dan chat memakai checkbox sehingga tetap jalan tanpa JavaScript.
// Skrip ini hanya menambahkan kemudahan: tombol keyboard dan penutupan otomatis.

const dropdowns = document.querySelectorAll('[data-navdrop]');

document.addEventListener('click', (event) => {
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        ['nav-chk', 'hubungi-chk', 'rp-chat-toggle'].forEach((id) => {
            document.getElementById(id).checked = false;
        });
        dropdowns.forEach((dropdown) => {
            dropdown.open = false;
        });
    }

    // Label dengan role="button" tidak merespons keyboard secara bawaan.
    if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('label.contact-open')) {
        event.preventDefault();
        event.target.click();
    }
});

document.querySelectorAll('[data-autosubmit]').forEach((select) => {
    select.addEventListener('change', () => select.form.submit());
});
