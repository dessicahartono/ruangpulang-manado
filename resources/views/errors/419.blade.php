@extends('layouts.app')

@section('title', __('Kiriman Tidak Bisa Diproses'))

@section('content')
    <x-page-hero>{!! __('Kiriman Tidak <em>Bisa Diproses</em>') !!}</x-page-hero>

    {{--
        Muncul kalau kiriman formulir tidak bisa dipastikan berasal dari situs ini: tidak ada token CSRF yang cocok
        dan browser tidak menandainya sebagai kiriman dari situs yang sama (header Sec-Fetch-Site). Contohnya formulir
        yang dikirim dari situs lain, atau dari browser lama setelah sesinya kedaluwarsa.
    --}}
    <section class="section not-found">
        <div class="container">
            <p>{{ __('Demi keamanan, kiriman ini tidak kami proses karena tidak bisa dipastikan berasal dari situs Ruang Pulang. Kembali ke halaman sebelumnya, muat ulang, lalu kirim lagi. Kalau kamu sedang menulis panjang, salin dulu tulisanmu sebelum memuat ulang.') }}</p>
            <x-button :href="url()->previous()">{{ __('Kembali ke Halaman Sebelumnya') }}</x-button>
        </div>
    </section>
@endsection
