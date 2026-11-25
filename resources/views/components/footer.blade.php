<footer class="footer">
    <div class="container footer__grid">
        <div>
            <a class="brand brand--light" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="" width="38" height="38">
                <span>{{ config('community.name') }}</span>
            </a>
            <p class="footer__tagline">{{ config('community.tagline') }}</p>
        </div>
        <div class="footer__crisis">
            <img src="{{ asset('images/bird-hand.png') }}" alt="" width="44" height="57">
            <p>Kalau kamu sedang berpikir untuk mengakhiri hidup atau merasa tidak aman, segera hubungi <a href="{{ config('community.crisis.phone_href') }}">{{ config('community.crisis.phone_label') }}</a>. Kamu tidak harus menghadapinya sendirian.</p>
        </div>
    </div>
    <div class="container footer__bottom">
        <span>&copy; {{ date('Y') }} {{ config('community.name') }} Social Community in {{ config('community.location') }}</span>
        <span>Designed and built by <a class="text-link" href="{{ config('community.credit.url') }}" target="_blank" rel="noopener">{{ config('community.credit.name') }}</a></span>
    </div>
</footer>
