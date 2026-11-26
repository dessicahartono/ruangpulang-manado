@extends('layouts.app')

@section('title', 'Sponsorship')
@section('description', __('Para sponsor yang mendukung kegiatan komunitas Ruang Pulang.'))

@section('content')
    <x-page-hero>{!! __('Sponsorship <em>Ruang Pulang</em>') !!}</x-page-hero>

    <section class="section section--sand">
        <div class="container">
            <p class="sponsor-intro">{{ __('Terima kasih kepada para sponsor yang sudah mendukung kegiatan Ruang Pulang.') }}</p>

            @if ($sponsors->isEmpty())
                <p class="sponsor-intro">{{ __('Belum ada sponsor yang ditampilkan.') }}</p>
            @else
                <div class="grid grid--3">
                    @foreach ($sponsors as $sponsor)
                        <x-sponsor-card :sponsor="$sponsor" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
