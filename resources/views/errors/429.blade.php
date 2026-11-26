@extends('layouts.app')

@section('title', __('Terlalu Banyak Percobaan'))

@section('content')
    <x-page-hero>{!! __('Pelan-pelan <em>Dulu</em>') !!}</x-page-hero>

    <section class="section not-found">
        <div class="container">
            <p>{{ __('Terlalu banyak percobaan dari perangkat ini. Coba lagi beberapa saat lagi.') }}</p>
            <x-button :href="route('home')">{{ __('Kembali ke Beranda') }}</x-button>
        </div>
    </section>
@endsection
