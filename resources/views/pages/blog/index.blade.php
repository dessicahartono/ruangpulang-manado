@extends('layouts.app')

@section('title', 'Blog')
@section('description', __('Tulisan seputar bercerita, merawat diri, dan kebersamaan dari Ruang Pulang.'))

@section('content')
    <x-page-hero>{!! __('Blog&nbsp;<em>Ruang Pulang</em>') !!}</x-page-hero>

    <section class="section">
        {{-- Tombol tulis, pencarian, dan hasil berada di satu kolom supaya tepinya lurus. --}}
        <div class="container blog-column">
            <div class="tool-link tool-link--center">
                <x-button variant="accent" :href="route('articles.create')">{{ __('Tulis Artikelmu di Sini') }}</x-button>
            </div>

            <x-post-toolbar :action="route('blog.index')" :search="$search" :sort="$sort" compact-sort />

            <p class="blog-count">{{ __('Sekitar :count artikel ditemukan', ['count' => $posts->total()]) }}</p>

            <div class="results">
                @forelse ($posts as $post)
                    <x-post-result :post="$post" />
                @empty
                    <p>{{ __('Belum ada artikel yang cocok. Coba kata lain.') }}</p>
                @endforelse
            </div>

            <x-pager :paginator="$posts" />
        </div>
    </section>
@endsection
