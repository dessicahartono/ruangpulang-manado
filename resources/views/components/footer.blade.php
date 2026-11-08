<footer class="footer">
    <div class="container footer__grid">
        <div>
            <a class="brand brand--light" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.png') }}" alt="" width="38" height="38">
                <span>{{ config('community.name') }}</span>
            </a>
            <p class="footer__tagline">{{ config('community.tagline') }}</p>
        </div>
        <nav aria-label="Menu footer">
            <ul class="footer__list">
                <li><a href="{{ route('about') }}">Tentang</a></li>
                <li><a href="{{ route('portfolio.index') }}">Kegiatan</a></li>
                <li><a href="{{ route('team') }}">Pengurus</a></li>
                <li><a href="{{ route('blog.index') }}">Blog</a></li>
                <li><label class="contact-open" for="hubungi-chk" role="button" tabindex="0">Kontak</label></li>
            </ul>
        </nav>
    </div>
    <div class="container footer__bottom">
        <span>&copy; {{ date('Y') }} {{ config('community.name') }} Social Community in {{ config('community.location') }}</span>
        <span>Designed and built by <a class="text-link" href="{{ config('community.credit.url') }}" target="_blank" rel="noopener">{{ config('community.credit.name') }}</a></span>
    </div>
</footer>
