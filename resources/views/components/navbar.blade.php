@php
    $isHome = request()->routeIs('home');
    $links = [
        ['route' => 'home', 'label' => 'Beranda', 'active' => ['home']],
        ['route' => 'about', 'label' => 'Tentang', 'active' => ['about']],
        ['route' => 'activity', 'label' => 'Kegiatan', 'active' => ['activity']],
        ['route' => 'portofolioteam', 'label' => 'Pengurus', 'active' => ['portofolioteam']],
        ['route' => 'blog.index', 'label' => 'Blog', 'active' => ['blog.*', 'manage.*']],
    ];
@endphp

<header class="navbar">
    <div class="container navbar__inner">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" width="{{ $isHome ? 64 : 38 }}" height="{{ $isHome ? 64 : 38 }}">
            <span>{{ config('community.name') }}</span>
        </a>

        <input class="nav__check" type="checkbox" id="nav-chk" aria-label="Buka menu">
        <label class="navbar__toggle" for="nav-chk">
            <span class="navbar__toggle-bars" aria-hidden="true"></span>
            <span class="sr-only">Buka menu</span>
        </label>

        <nav class="navbar__menu" id="menu-utama" aria-label="Menu utama">
            {{-- Di desktop, beranda memakai menu ringkas dengan dropdown. Halaman lain dan tampilan HP memakai daftar lengkap. --}}
            @if ($isHome)
                <a class="navbar__link nav-wide" href="{{ route('portofolioteam') }}">Pengurus</a>
                <details class="navdrop nav-wide" data-navdrop>
                    <summary class="navbar__link">Tentang Kami</summary>
                    <div class="navdrop__list">
                        <a href="{{ route('about') }}">Tentang</a>
                        <a href="{{ route('activity') }}">Kegiatan</a>
                        <label class="contact-open" for="hubungi-chk" role="button" tabindex="0">Kontak</label>
                    </div>
                </details>
            @endif

            @foreach ($links as $link)
                @php($active = request()->routeIs(...$link['active']))
                <a @class(['navbar__link', 'is-active' => $active, 'nav-narrow' => $isHome])
                   href="{{ route($link['route']) }}"
                   @if ($active) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
            <label @class(['navbar__link', 'contact-open', 'nav-narrow' => $isHome]) for="hubungi-chk" role="button" tabindex="0">Kontak</label>
        </nav>
    </div>
</header>
