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
                <p class="blog-count admin-count">
                    Total {{ $posts->total() }} artikel
                    @if ($pendingCount)
                        <strong>({{ $pendingCount }} Menunggu Persetujuan)</strong>
                    @endif
                </p>
                <div class="admin-head__actions">
                    <x-button variant="accent" :href="route('manage.create')">Tulis Artikel</x-button>
                    <form method="POST" action="{{ route('manage.logout') }}">
                        @csrf
                        <x-button variant="outline" type="submit">Keluar</x-button>
                    </form>
                </div>
            </div>

            <x-post-toolbar :action="route('manage.index')" :search="$search" :sort="$sort" />

            <div class="admin-list">
                <div class="admin-row admin-row--head"><span>Judul dan Slug</span><span>Nama Penulis</span><span>Tanggal Publikasi</span><span>Aksi</span></div>
                @forelse ($posts as $post)
                    <div class="admin-row">
                        <div>
                            <h3>{{ $post->title }}</h3>
                            <small>/blog/{{ $post->slug }}</small>
                        </div>
                        <div class="admin-meta">{{ $post->author }}</div>
                        <div class="admin-meta">
                            @if ($post->isPending())
                                <strong>Menunggu Persetujuan</strong>
                            @else
                                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                            @endif
                        </div>
                        <div class="admin-actions">
                            <x-button variant="outline" class="btn--sm" :href="route('manage.show', $post->slug)">Tinjau Artikel</x-button>
                        </div>
                    </div>
                @empty
                    <p>Belum ada artikel yang cocok.</p>
                @endforelse
            </div>

            <x-pager :paginator="$posts" />
        </div>
    </section>
@endsection
