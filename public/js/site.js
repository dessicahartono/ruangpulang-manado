// Menu HP dan kontak memakai checkbox sehingga tetap jalan tanpa JavaScript.
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
        ['nav-chk', 'hubungi-chk'].forEach((id) => {
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

// Slider media kegiatan: panah dan penghitung. Tanpa JavaScript, media tetap bisa digeser.
document.querySelectorAll('[data-media-slider]').forEach((slider) => {
    const track = slider.querySelector('[data-media-track]');
    const prev = slider.querySelector('[data-media-prev]');
    const next = slider.querySelector('[data-media-next]');
    const count = slider.querySelector('[data-media-count]');
    const total = track.children.length;

    if (total < 2) {
        return;
    }

    const current = () => Math.round(track.scrollLeft / track.clientWidth);

    const update = () => {
        const index = current();
        prev.hidden = index === 0;
        next.hidden = index === total - 1;
        count.textContent = `${index + 1}/${total}`;

        // Video yang sudah digeser keluar dihentikan.
        [...track.children].forEach((item, i) => {
            const video = item.querySelector('video');
            if (video && i !== index) {
                video.pause();
            }
        });
    };

    prev.addEventListener('click', () => track.scrollBy({ left: -track.clientWidth }));
    next.addEventListener('click', () => track.scrollBy({ left: track.clientWidth }));
    track.addEventListener('scroll', () => window.requestAnimationFrame(update), { passive: true });
    update();
});

// Kelola Kegiatan: tolak video yang lebih panjang dari batas sebelum dikirim.
document.querySelectorAll('[data-max-video-seconds]').forEach((input) => {
    const maxSeconds = Number(input.dataset.maxVideoSeconds);
    const error = input.parentElement.querySelector('[data-media-error]');

    const durationOf = (file) => new Promise((resolve) => {
        const video = document.createElement('video');
        const url = URL.createObjectURL(file);
        video.preload = 'metadata';
        video.onloadedmetadata = () => { URL.revokeObjectURL(url); resolve(video.duration); };
        video.onerror = () => { URL.revokeObjectURL(url); resolve(0); };
        video.src = url;
    });

    input.addEventListener('change', async () => {
        const tooLong = [];

        for (const file of input.files) {
            // Toleransi setengah detik, karena durasi hasil rekam sering sedikit lebih dari angka bulatnya.
            if (file.type.startsWith('video/') && (await durationOf(file)) > maxSeconds + 0.5) {
                tooLong.push(file.name);
            }
        }

        const message = tooLong.length
            ? `Video maksimal ${maxSeconds} detik. Potong dulu: ${tooLong.join(', ')}.`
            : '';

        input.setCustomValidity(message);
        error.textContent = message;
        error.hidden = !message;
    });
});
