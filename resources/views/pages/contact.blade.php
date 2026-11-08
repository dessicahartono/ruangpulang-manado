@extends('layouts.app')

@section('title', 'Kontak')
@section('description', 'Hubungi Ruang Pulang melalui Instagram dan TikTok.')

@section('content')
    <x-page-hero>Kontak <em>Kami</em></x-page-hero>

    <section class="section">
        <div class="container container--narrow contact-page">
            <div class="contact__body">
                <x-social-links />
            </div>
            <x-crisis-help />
        </div>
    </section>
@endsection
