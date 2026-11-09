@extends('layouts.app')

@section('title', 'File Terlalu Besar')

@section('content')
    <x-page-hero>File Terlalu <em>Besar</em></x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>Ukuran file yang dikirim terlalu besar. Foto pengurus maksimal 2 MB. Kembali ke halaman sebelumnya, lalu coba lagi dengan file yang lebih kecil.</p>
            <x-button :href="route('home')">Kembali ke Beranda</x-button>
        </div>
    </section>
@endsection
