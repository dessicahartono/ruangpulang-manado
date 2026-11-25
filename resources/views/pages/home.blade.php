@extends('layouts.app')

@section('content')
    <section class="hero">
        <div class="container hero__content">
            <h1>Semua Orang Butuh Tempat untuk <em>Pulang</em></h1>
            <p class="hero__lead">Komunitas di Manado yang menjadi ruang aman untuk berbagi cerita, saling mendukung, dan membangun relasi yang positif, sekaligus wadah untuk mengembangkan diri dan tumbuh bersama.</p>
            <div class="hero__actions">
                <x-button variant="outline-light" class="only-narrow" :href="route('about')">Tentang Kami</x-button>
                <x-button variant="outline-light" class="btn--icon" :href="route('blog.index')">
                    <img src="{{ asset('images/blog-icon.png') }}" alt="" width="22" height="22">Blog
                </x-button>
                <x-button variant="outline-light" :href="route('portofolioteam')">Orang-Orang di Balik Ruang Pulang</x-button>
            </div>
        </div>
        <x-sky />
    </section>
@endsection
