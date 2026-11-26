@php
    $isHome = request()->routeIs('home');
    $links = [
        ['route' => 'home', 'label' => __('Beranda'), 'active' => ['home']],
        ['route' => 'about', 'label' => __('Tentang Kami'), 'active' => ['about']],
        ['route' => 'activity', 'label' => __('Kegiatan'), 'active' => ['activity']],
        ['route' => 'portofolioteam', 'label' => __('Pengurus'), 'active' => ['portofolioteam', 'manage.members.*']],
        ['route' => 'blog.index', 'label' => 'Blog', 'active' => ['blog.*', 'manage.blog.*']],
        ['route' => 'sponsorship', 'label' => 'Sponsorship', 'active' => ['sponsorship', 'manage.sponsors.*']],
        ['route' => 'join', 'label' => __('Keanggotaan'), 'active' => ['join']],
    ];
@endphp

<header class="navbar">
    <div class="container navbar__inner">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" width="{{ $isHome ? 64 : 38 }}" height="{{ $isHome ? 64 : 38 }}">
            <span>{{ config('community.name') }}</span>
        </a>

        <input class="nav__check" type="checkbox" id="nav-chk" aria-label="{{ __('Buka menu') }}">
        <label class="navbar__toggle" for="nav-chk">
            <span class="navbar__toggle-bars" aria-hidden="true"></span>
            <span class="sr-only">{{ __('Buka menu') }}</span>
        </label>

        <nav class="navbar__menu" id="menu-utama" aria-label="{{ __('Menu utama') }}">
            {{-- Di desktop, beranda memakai menu ringkas dengan dropdown. Halaman lain dan tampilan HP memakai daftar lengkap. --}}
            @if ($isHome)
                <a class="navbar__link nav-wide" href="{{ route('sponsorship') }}">Sponsorship</a>
                <details class="navdrop nav-wide" data-navdrop>
                    <summary class="navbar__link">{{ __('Tentang Kami') }}</summary>
                    <div class="navdrop__list">
                        <a href="{{ route('about') }}">{{ __('Tentang Kami') }}</a>
                        <a href="{{ route('activity') }}">{{ __('Kegiatan') }}</a>
                        <a href="{{ route('portofolioteam') }}">{{ __('Pengurus') }}</a>
                        <a href="{{ route('blog.index') }}">Blog</a>
                        <a href="{{ route('join') }}">{{ __('Keanggotaan') }}</a>
                        <label class="contact-open" for="hubungi-chk" role="button" tabindex="0">{{ __('Kontak') }}</label>
                    </div>
                </details>
            @endif

            @foreach ($links as $link)
                @php($active = request()->routeIs(...$link['active']))
                <a @class(['navbar__link', 'is-active' => $active, 'nav-narrow' => $isHome])
                   href="{{ route($link['route']) }}"
                   @if ($active) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
            <label @class(['navbar__link', 'contact-open', 'nav-narrow' => $isHome]) for="hubungi-chk" role="button" tabindex="0">{{ __('Kontak') }}</label>
        </nav>

        {{-- Pilihan bahasa, dropdown yang sama seperti Tentang Kami. Ada di luar menu supaya di HP dan iPad tetap terlihat
             di samping tombol menu. Halaman Kelola selalu berbahasa Indonesia, jadi tidak ditampilkan di sana. --}}
        @unless (request()->routeIs('manage.*'))
            @php($languages = ['id' => 'Bahasa Indonesia', 'en' => 'English'])
            <details class="navdrop lang-menu" data-navdrop>
                <summary class="navbar__link">
                    <x-flag :code="app()->getLocale()" /><span class="lang-menu__code">{{ strtoupper(app()->getLocale()) }}</span><span class="sr-only">, {{ __('Pilih bahasa') }}</span>
                </summary>
                <div class="navdrop__list">
                    @foreach ($languages as $code => $name)
                        <a @class(['lang-menu__option', 'is-current' => app()->isLocale($code)]) href="{{ route('locale', $code) }}" hreflang="{{ $code }}" lang="{{ $code }}" @if (app()->isLocale($code)) aria-current="true" @endif>
                            <x-flag :code="$code" />{{ $name }}
                        </a>
                    @endforeach
                </div>
            </details>
        @endunless
    </div>
</header>
