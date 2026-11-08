@extends('layouts.app')

@section('title', 'Bergabung')
@section('description', 'Cara bergabung dengan komunitas Ruang Pulang, termasuk untuk teman-teman di luar Manado.')

@section('content')
    <x-page-hero>Cara <em>Bergabung</em> dengan Kami</x-page-hero>

    <section class="section">
        <div class="container">
            <div class="prose">
                <h2>Keanggotaan</h2>
                <p>Ruang Pulang terbuka bagi siapa saja, termasuk teman-teman yang berada di luar Manado. Kamu tetap bisa bergabung, berinteraksi, berbagi cerita, dan menemukan teman tanpa harus selalu hadir dalam kegiatan secara langsung.</p>
                <p>Bagi teman-teman yang ingin terlibat lebih jauh sebagai Anggota Inti, partisipasi dalam pertemuan dan kegiatan secara langsung tentunya sangat diharapkan. Hal ini menjadi salah satu bagian penting untuk membangun kedekatan, mengenal satu sama lain, serta menjalankan komunitas bersama-sama.</p>
                <p>Setiap orang memiliki kesempatan dan cara masing-masing untuk menjadi bagian dari Ruang Pulang. Tidak perlu khawatir jika belum bisa hadir secara langsung, karena jarak bukan penghalang untuk tetap terhubung.</p>
                <p><x-button :href="config('community.whatsapp_group')" target="_blank" rel="noopener">Gabung Grup WhatsApp</x-button></p>
            </div>
        </div>
    </section>
@endsection
