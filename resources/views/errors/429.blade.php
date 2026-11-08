@extends('layouts.app')

@section('title', 'Terlalu Banyak Percobaan')

@section('content')
    <x-page-hero>Pelan-pelan <em>Dulu</em></x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>Terlalu banyak percobaan dari perangkat ini. Coba lagi beberapa saat lagi.</p>
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </section>
@endsection
