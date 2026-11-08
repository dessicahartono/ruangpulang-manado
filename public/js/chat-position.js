// Tombol chat bisa digeser lalu menempel ke sisi layar terdekat.
const fab = document.querySelector('.fab');

if (fab) {
    const button = fab.querySelector('.fab__btn');
    const EDGE = 12;
    const DRAG_THRESHOLD = 6;
    const clamp = (value, max) => Math.max(EDGE, Math.min(max, value));

    let drag = null;
    let suppressClick = false;

    const moveTo = (left, top) => {
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';
        fab.style.left = `${clamp(left, window.innerWidth - fab.offsetWidth - EDGE)}px`;
        fab.style.top = `${clamp(top, window.innerHeight - fab.offsetHeight - EDGE)}px`;
    };

    // Panel dibuka ke arah yang ruangnya lebih lega dan tingginya mengikuti ruang itu.
    const fitPanel = () => {
        const rect = fab.getBoundingClientRect();
        const above = rect.top - 92;
        const below = window.innerHeight - rect.bottom - 28;
        fab.classList.toggle('fab--top', below > above);
        fab.classList.toggle('fab--left', rect.left + rect.width / 2 < window.innerWidth / 2);
        fab.style.setProperty('--fab-panel-max', `${Math.max(above, below)}px`);
    };

    button.addEventListener('pointerdown', (event) => {
        const rect = fab.getBoundingClientRect();
        drag = { x: event.clientX, y: event.clientY, left: rect.left, top: rect.top, moved: false };
        button.setPointerCapture(event.pointerId);
    });

    button.addEventListener('pointermove', (event) => {
        if (!drag) {
            return;
        }
        const dx = event.clientX - drag.x;
        const dy = event.clientY - drag.y;
        if (!drag.moved && Math.abs(dx) + Math.abs(dy) < DRAG_THRESHOLD) {
            return;
        }
        drag.moved = true;
        moveTo(drag.left + dx, drag.top + dy);
    });

    const finishDrag = () => {
        if (!drag) {
            return;
        }
        if (drag.moved) {
            const rect = fab.getBoundingClientRect();
            const toLeft = rect.left + rect.width / 2 < window.innerWidth / 2;
            moveTo(toLeft ? EDGE : window.innerWidth, rect.top);
            fitPanel();
            suppressClick = true;
        }
        drag = null;
    };

    button.addEventListener('pointerup', finishDrag);
    button.addEventListener('pointercancel', finishDrag);

    // Selesai menggeser tidak boleh ikut membuka atau menutup panel.
    button.addEventListener('click', (event) => {
        if (suppressClick) {
            event.preventDefault();
            suppressClick = false;
        }
    });

    window.addEventListener('resize', () => {
        ['left', 'top', 'right', 'bottom'].forEach((side) => fab.style.removeProperty(side));
        fab.style.removeProperty('--fab-panel-max');
        fab.classList.remove('fab--top', 'fab--left');
    });
}
