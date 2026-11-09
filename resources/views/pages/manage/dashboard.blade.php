@extends('layouts.app')

@section('title', 'Kelola Menu Utama')

@section('content')
    <x-page-hero>Kelola&nbsp;<em>Menu Utama</em></x-page-hero>

    <section class="section">
        <div class="container">
            <div class="grid grid--3">
                <a class="admin-menu__card" href="{{ route('manage.members.index') }}">
                    <h2>Kelola Profil Pengurus</h2>
                    <p>Lihat, tambah, ubah, dan hapus data pengurus per divisi.</p>
                    <p class="admin-menu__count">{{ $memberCount }} data pengurus</p>
                </a>
                <a class="admin-menu__card" href="{{ route('manage.blog.index') }}">
                    <h2>Kelola Blog</h2>
                    <p>Tinjau kiriman, serta tulis, ubah, dan hapus artikel.</p>
                    <p class="admin-menu__count">{{ $postCount }} artikel, {{ $pendingCount }} menunggu persetujuan</p>
                </a>
                {{-- Penanda saja. Fiturnya belum dibuat, jadi kartu ini bukan tautan. --}}
                <div class="admin-menu__card admin-menu__card--soon">
                    <h2>Kelola Kegiatan</h2>
                    <p>Lihat, tambah, ubah, dan hapus data kegiatan.</p>
                    <p class="admin-menu__status">On Progress</p>
                </div>
            </div>

            <form class="admin-section" method="POST" action="{{ route('manage.logout') }}">
                @csrf
                <x-button variant="danger" type="submit">Akhiri Sesi dan Keluar</x-button>
            </form>
        </div>
    </section>
@endsection
