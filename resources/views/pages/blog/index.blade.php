@extends('layouts.app')

@section('title', 'Blog')
@section('description', 'Tulisan seputar bercerita, merawat diri, dan kebersamaan dari Ruang Pulang.')

@section('content')
    <x-page-hero>Blog&nbsp;<em>Ruang Pulang</em></x-page-hero>

    <section class="section">
        <div class="container">
            <div class="tool-link tool-link--center">
                <x-button variant="accent" :href="route('articles.create')">Tulis Artikelmu di Sini</x-button>
            </div>

            <x-post-toolbar :action="route('blog.index')" :search="$search" :sort="$sort" compact-sort />

            <p class="blog-count">Sekitar {{ $posts->total() }} artikel ditemukan</p>

            <div class="results">
                @forelse ($posts as $post)
                    <x-post-result :post="$post" />
                @empty
                    <p>Belum ada artikel yang cocok. Coba kata lain.</p>
                @endforelse
            </div>

            <x-pager :paginator="$posts" />
        </div>
    </section>
@endsection
