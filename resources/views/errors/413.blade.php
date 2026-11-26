@extends('layouts.app')

@section('title', __('File Terlalu Besar'))

@section('content')
    <x-page-hero>{!! __('File Terlalu <em>Besar</em>') !!}</x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>{{ __('Ukuran file yang dikirim terlalu besar. Foto maksimal 2 MB dan video kegiatan maksimal 15 detik dan 20 MB. Kembali ke halaman sebelumnya, lalu coba lagi dengan file yang lebih kecil.') }}</p>
            <x-button :href="route('home')">{{ __('Kembali ke Beranda') }}</x-button>
        </div>
    </section>
@endsection
