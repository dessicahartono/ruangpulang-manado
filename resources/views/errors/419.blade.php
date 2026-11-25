@extends('layouts.app')

@section('title', 'Sesi Berakhir')

@section('content')
    <x-page-hero>Sesinya Sudah <em>Berakhir</em></x-page-hero>

    {{-- Muncul kalau formulir dibiarkan terbuka terlalu lama, sehingga token keamanannya kedaluwarsa. --}}
    <section class="section not-found">
        <div class="container">
            <p>Halaman ini terlalu lama dibiarkan terbuka, jadi demi keamanan kirimannya tidak bisa diproses. Kembali ke halaman sebelumnya, muat ulang, lalu kirim lagi. Kalau kamu sedang menulis panjang, salin dulu tulisanmu sebelum memuat ulang.</p>
            <x-button :href="url()->previous()">Kembali ke Halaman Sebelumnya</x-button>
        </div>
    </section>
@endsection
