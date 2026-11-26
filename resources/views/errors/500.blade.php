@extends('layouts.app')

@section('title', __('Terjadi Kesalahan'))

@section('content')
    <x-page-hero>{!! __('Ada yang <em>Tidak Beres</em>') !!}</x-page-hero>

    {{-- Detail error tidak ditampilkan ke pengunjung. Penyebabnya tercatat di storage/logs/laravel.log. --}}
    <section class="section not-found">
        <div class="container">
            <p>{{ __('Maaf, terjadi kesalahan di server kami. Ini bukan salahmu. Coba lagi beberapa saat lagi, atau kembali ke beranda.') }}</p>
            <x-button :href="route('home')">{{ __('Kembali ke Beranda') }}</x-button>
        </div>
    </section>
@endsection
