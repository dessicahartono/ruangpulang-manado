@extends('layouts.app')

@section('title', 'Terjadi Kesalahan')

@section('content')
    <x-page-hero>Ada yang <em>Tidak Beres</em></x-page-hero>

    {{-- Detail error tidak ditampilkan ke pengunjung. Penyebabnya tercatat di storage/logs/laravel.log. --}}
    <section class="section not-found">
        <div class="container">
            <p>Maaf, terjadi kesalahan di server kami. Ini bukan salahmu. Coba lagi beberapa saat lagi, atau kembali ke beranda.</p>
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </section>
@endsection
