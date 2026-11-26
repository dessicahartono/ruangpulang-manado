@extends('layouts.app')

@section('title', __('Halaman Tidak Ditemukan'))

@section('content')
    <x-page-hero>{!! __('Sepertinya Kamu <em>Salah Belok</em>') !!}</x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>{{ __('Halaman ini tidak ada, tapi jalan pulangnya masih ada.') }}</p>
            <x-button :href="route('home')">{{ __('Kembali ke Beranda') }}</x-button>
        </div>
    </section>
@endsection
