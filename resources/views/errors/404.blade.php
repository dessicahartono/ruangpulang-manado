@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <x-page-hero>Sepertinya Kamu <em>Salah Belok</em></x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>Halaman ini tidak ada, tapi jalan pulangnya masih ada.</p>
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </section>
@endsection
