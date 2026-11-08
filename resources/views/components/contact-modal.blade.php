<input class="modal__check" type="checkbox" id="hubungi-chk" aria-label="Buka atau tutup kontak">
<div class="modal modal--blur" id="hubungi" role="dialog" aria-modal="true" aria-labelledby="hubungi-judul">
    <label class="modal__backdrop" for="hubungi-chk" aria-label="Tutup"></label>
    <div class="modal__box modal__box--contact">
        <div class="contact__head">
            <span class="contact__handle" aria-hidden="true"></span>
            <img class="contact__logo" src="{{ asset('images/logo.png') }}" alt="{{ config('community.name') }}" width="40" height="40">
            <h2 id="hubungi-judul">Media Sosial Kami</h2>
            <label class="modal__close" for="hubungi-chk" aria-label="Tutup">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
            </label>
            <svg class="contact__wave" viewBox="0 0 400 24" preserveAspectRatio="none" aria-hidden="true"><path d="M0 8 C80 24 160 0 240 8 S360 20 400 6 V24 H0Z" fill="#fff"></path></svg>
        </div>
        <div class="contact__body">
            <x-social-links />
        </div>
    </div>
</div>
