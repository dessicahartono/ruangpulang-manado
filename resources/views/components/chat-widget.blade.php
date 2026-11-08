<div class="fab" data-chat data-url="{{ route('chat.send') }}" data-max-history="{{ config('chatbot.max_history') }}">
    <input class="fab__check" type="checkbox" id="rp-chat-toggle" aria-label="Buka atau tutup Teman Cerita">
    <label class="fab__btn" for="rp-chat-toggle">
        <img class="fab__ico fab__ico--chat" src="{{ asset('images/ikon-chat.png') }}" alt="">
        <svg class="fab__ico fab__ico--close" viewBox="0 0 48 48" aria-hidden="true"><path d="M12 12l24 24M36 12L12 36"></path></svg>
        <span class="sr-only">Teman Cerita</span>
    </label>

    <section class="fab__panel" aria-label="Teman Cerita">
        <div class="chat__header">
            <img src="{{ asset('images/logo.png') }}" alt="" width="40" height="40">
            <div>
                <strong>Teman Bicara AI</strong>
                <span data-chat-mode hidden></span>
            </div>
            <button class="chat__reset" type="button" data-chat-clear aria-label="Hapus Riwayat" hidden>Hapus</button>
        </div>

        <div class="chat__messages" data-chat-messages role="log" aria-live="polite" aria-label="Percakapan" tabindex="0">
            {{-- Wajib tampil: pesan dikirim ke Gemini versi gratis yang ketentuannya berbeda dari layanan berbayar. --}}
            <p class="chat__privacy">Riwayat chat hanya tersimpan di browser perangkat ini, bukan di server kami, dan bisa kamu hapus kapan saja. Pesanmu tetap diproses Google Gemini versi gratis dan bisa dibaca peninjau manusia. Jangan tulis data pribadi. Untuk usia 18 tahun ke atas.</p>
            <p class="bubble" data-chat-greeting>Hai, aku Teman Pulang. Apa yang lagi kamu rasakan hari ini? Boleh cerita sedikit atau banyak, sesukamu.</p>
        </div>

        <form class="chat__form" data-chat-form>
            <label class="sr-only" for="fab-input">Tulis pesanmu</label>
            <textarea id="fab-input" rows="1" maxlength="{{ config('chatbot.max_message_length') }}" placeholder="Tulis pesanmu..." data-chat-input required></textarea>
            <button class="btn btn--accent" type="submit" data-chat-send>Kirim</button>
        </form>

        <div class="fab__note fab__note--logo">
            <img src="{{ asset('images/bird-hand.png') }}" alt="" width="40" height="52">
            <p>Kalau kamu sedang berpikir untuk mengakhiri hidup atau merasa tidak aman, segera hubungi <a href="{{ config('community.crisis.phone_href') }}">{{ config('community.crisis.phone_label') }}</a>. Kamu tidak harus menghadapinya sendirian.</p>
        </div>
    </section>
</div>
