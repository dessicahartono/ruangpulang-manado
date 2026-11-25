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

// Video kegiatan diam saat halaman dimuat. Disorot kursor: berputar tanpa suara, berhenti saat kursor pergi.
// Kalau pengunjung memutarnya sendiri lewat klik, video tidak dihentikan saat kursor pergi.
const canHover = window.matchMedia('(hover: hover)').matches;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.querySelectorAll('[data-hover-video]').forEach((video) => {
    let preview = false;

    video.addEventListener('play', () => {
        if (!preview) {
            video.dataset.userPlayed = '1';
        }
    });
    video.addEventListener('pause', () => {
        delete video.dataset.userPlayed;
    });

    if (!canHover || reduceMotion) {
        return;
    }

    video.addEventListener('mouseenter', () => {
        if (video.paused) {
            preview = true;
            video.play().catch(() => {}).finally(() => { preview = false; });
        }
    });
    video.addEventListener('mouseleave', () => {
        if (!video.dataset.userPlayed) {
            video.pause();
        }
    });
});

// Foto dan video selalu muncul seperti tirai yang turun, termasuk yang sudah ada di cache.
// Kilau loading hanya muncul kalau media masih menunggu jaringan lebih dari sekejap.
const revealTargets = '.arch img, .sponsor-card__logo img, .media-slider__item img, .media-slider__item video';
const LOADING_DELAY = 150;

document.querySelectorAll(revealTargets).forEach((media) => {
    const frame = media.parentElement;
    let finished = false;

    const loadingTimer = window.setTimeout(() => {
        if (!finished) {
            frame.classList.add('is-loading');
        }
    }, LOADING_DELAY);

    // Dua frame menunggu supaya keadaan tertutup sempat tergambar, lalu transisi tirainya berjalan.
    const done = () => {
        finished = true;
        window.clearTimeout(loadingTimer);
        frame.classList.remove('is-loading');
        window.requestAnimationFrame(() => window.requestAnimationFrame(() => media.classList.add('is-revealed')));
    };

    const ready = media.tagName === 'VIDEO' ? media.readyState >= 2 : media.complete && media.naturalWidth > 0;
    if (ready) {
        done();
        return;
    }

    media.addEventListener(media.tagName === 'VIDEO' ? 'loadeddata' : 'load', done, { once: true });
    // Kalau gagal dimuat, tetap ditampilkan supaya bingkainya tidak kosong selamanya.
    media.addEventListener('error', done, { once: true });
});

// Akordion (misalnya Pengurus di HP dan iPad): setiap kali dibuka lagi, foto yang sudah tampil dianimasikan ulang.
// Saat ditutup, foto menutup dulu seperti tirai naik, baru akordionnya tertutup.
const CLOSE_DURATION = 300;

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('details').forEach((details) => {
        const summary = details.querySelector(':scope > summary');

        summary?.addEventListener('click', (event) => {
            const shown = [...details.querySelectorAll(revealTargets)].filter((media) => media.classList.contains('is-revealed'));

            if (!details.open || shown.length === 0) {
                return;
            }

            event.preventDefault();
            if (details.dataset.closing) {
                return;
            }

            details.dataset.closing = '1';
            shown.forEach((media) => media.classList.add('is-closing'));

            window.setTimeout(() => {
                details.open = false;
                shown.forEach((media) => media.classList.remove('is-closing'));
                delete details.dataset.closing;
            }, CLOSE_DURATION);
        });

        details.addEventListener('toggle', () => {
            if (!details.open) {
                return;
            }

            details.querySelectorAll(revealTargets).forEach((media) => {
                if (!media.classList.contains('is-revealed')) {
                    return; // Belum dimuat. Animasinya berjalan sendiri setelah selesai dimuat.
                }

                // Transisi CSS dilewati browser karena isi akordion yang tertutup tidak digambar,
                // jadi animasinya diputar langsung: tirai 0,3 detik dan zoom 1,1 detik, sama dengan CSS.
                media.animate(
                    [{ clipPath: 'inset(0 0 99.9% 0)' }, { clipPath: 'inset(0 0 0 0)' }],
                    { duration: 300, easing: 'cubic-bezier(.65, 0, .35, 1)' },
                );
                media.animate(
                    [{ transform: 'scale(1.04)' }, { transform: 'none' }],
                    { duration: 1100, easing: 'cubic-bezier(.2, .8, .2, 1)' },
                );
            });
        });
    });
}
