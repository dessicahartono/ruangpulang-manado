@extends('layouts.app')

@section('title', __('Bergabung'))
@section('description', __('Cara bergabung dengan komunitas Ruang Pulang, termasuk untuk teman-teman di luar Manado.'))

@section('content')
    <x-page-hero>{!! __('Cara <em>Bergabung</em> dengan Kami') !!}</x-page-hero>

    <section class="section">
        <div class="container">
            <h2 class="join-page__title">{{ __('Keanggotaan') }}</h2>

            {{-- Dua kolom di desktop, menjadi satu kolom di layar kecil. --}}
            <div class="split split--vision join-page__columns">
                <div class="prose">
                    <p>{{ __('Ruang Pulang terbuka bagi siapa saja, termasuk teman-teman yang berada di luar Manado. Kamu tetap bisa bergabung, berinteraksi, berbagi cerita, dan menemukan teman tanpa harus selalu hadir dalam kegiatan secara langsung.') }}</p>
                    <p>{{ __('Bagi teman-teman yang ingin terlibat lebih jauh sebagai Anggota Inti, partisipasi dalam pertemuan dan kegiatan secara langsung tentunya sangat diharapkan. Hal ini menjadi salah satu bagian penting untuk membangun kedekatan, mengenal satu sama lain, serta menjalankan komunitas bersama-sama.') }}</p>
                </div>
                <div class="prose">
                    <p>{{ __('Setiap orang memiliki kesempatan dan cara masing-masing untuk menjadi bagian dari Ruang Pulang. Tidak perlu khawatir jika belum bisa hadir secara langsung, karena jarak bukan penghalang untuk tetap terhubung.') }}</p>
                    <p><x-button :href="config('community.whatsapp_group')" target="_blank" rel="noopener">{{ __('Gabung Grup WhatsApp') }}</x-button></p>
                </div>
            </div>
        </div>
    </section>
@endsection
