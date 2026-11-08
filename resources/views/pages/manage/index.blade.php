@extends('layouts.app')

@section('title', 'Kelola Blog')

@section('content')
    <x-page-hero>Kelola <em>Blog</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="admin-head">
                <p class="blog-count admin-count">Total {{ $pendingPosts->total() + $publishedPosts->total() }} artikel</p>
                <div class="admin-head__actions">
                    <x-button variant="accent" :href="route('manage.create')">Tulis Artikel</x-button>
                    <form method="POST" action="{{ route('manage.logout') }}">
                        @csrf
                        <x-button variant="outline" type="submit">Keluar</x-button>
                    </form>
                </div>
            </div>

            <x-post-toolbar :action="route('manage.index')" :search="$search" :sort="$sort" />

            <x-admin-post-list
                title="Menunggu Persetujuan"
                :posts="$pendingPosts"
                date-label="Tanggal Dikirim"
                date-column="created_at"
                :empty="$search !== '' ? 'Tidak ada yang cocok dengan pencarian ini.' : 'Tidak ada artikel yang menunggu persetujuan.'"
            />

            <x-admin-post-list
                title="Sudah Diterbitkan"
                :posts="$publishedPosts"
                date-label="Tanggal Publikasi"
                date-column="published_at"
                :empty="$search !== '' ? 'Tidak ada yang cocok dengan pencarian ini.' : 'Belum ada artikel yang diterbitkan.'"
            />
        </div>
    </section>
@endsection
