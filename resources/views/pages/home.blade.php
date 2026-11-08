@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="container hero__content">
            <h1>Semua Orang Butuh Tempat untuk <em>Pulang</em></h1>
            <p class="hero__lead">Komunitas kecil untuk bercerita, merawat diri, dan menemukan teman baru. Terbuka untuk siapa saja di Manado, termasuk kamu yang datang sendirian.</p>
            <div class="hero__actions">
                <x-button variant="outline-light" class="only-narrow" :href="route('about')">Tentang Kami</x-button>
                <x-button variant="outline-light" class="btn--icon" :href="route('blog.index')">
                    <img src="{{ asset('images/blog-icon.png') }}" alt="" width="22" height="22">Blog
                </x-button>
                <x-button variant="outline-light" :href="route('team')">Orang-Orang di Balik Ruang Pulang</x-button>
            </div>
        </div>
        <x-sky />
    </section>

    <section class="join" id="kontak">
        <div class="container join__inner">
            <h2>Pintu Kami Selalu Terbuka untukmu</h2>
            <div class="hero__actions">
                <a class="join__link" href="{{ route('join') }}">Lihat Ketentuan untuk Bergabung</a>
            </div>
        </div>
    </section>
@endsection
