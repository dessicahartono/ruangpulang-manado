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
                    <x-button variant="accent" :href="route('manage.create')">Tambah Artikel</x-button>
                    <form method="POST" action="{{ route('manage.logout') }}">
                        @csrf
                        <x-button variant="outline" type="submit">Keluar</x-button>
                    </form>
                </div>
            </div>

            <x-post-toolbar :action="route('manage.index')" :search="$search" :sort="$sort" :category="$activeCategory" />
            <x-filter-pills :items="$categories" :active="$activeCategory" />

            <div class="admin-list">
                <div class="admin-row admin-row--head"><span>Judul dan Slug</span><span>Kategori</span><span>Tanggal Publikasi</span><span>Aksi</span></div>
                @forelse ($posts as $post)
                    <div class="admin-row">
                        <div>
                            <h3>{{ $post->title }}</h3>
                            <small>/blog/{{ $post->slug }}</small>
                        </div>
                        <div class="admin-meta">
                            @if ($post->category)
                                <span class="tag">{{ $post->category }}</span>
                            @endif
                        </div>
                        <div class="admin-meta">
                            @if ($post->isPending())
                                <strong>Menunggu Persetujuan</strong>
                            @else
                                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                            @endif
                        </div>
                        <div class="admin-actions">
                            @if ($post->isPending())
                                <form method="POST" action="{{ route('manage.approve', $post->slug) }}">
                                    @csrf
                                    <x-button variant="accent" class="btn--sm" type="submit">Setujui</x-button>
                                </form>
                            @else
                                <x-button variant="outline" class="btn--sm" :href="route('blog.show', $post->slug)">Lihat</x-button>
                            @endif
                            <x-button variant="outline" class="btn--sm" :href="route('manage.edit', $post->slug)">Ubah</x-button>
                            <x-button variant="danger" class="btn--sm" href="#hapus-{{ $post->slug }}">Hapus</x-button>
                        </div>
                    </div>
                @empty
                    <p>Belum ada artikel yang cocok.</p>
                @endforelse
            </div>

            <x-pager :paginator="$posts" />
        </div>
    </section>

    @foreach ($posts as $post)
        <div class="modal" id="hapus-{{ $post->slug }}" role="dialog" aria-modal="true" aria-labelledby="hapus-judul-{{ $post->slug }}">
            <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
            <div class="modal__box">
                <h2 id="hapus-judul-{{ $post->slug }}">Hapus Artikel Ini?</h2>
                <p><strong>{{ $post->title }}</strong></p>
                <p>Artikel yang dihapus tidak bisa dikembalikan, dan akan hilang dari halaman Blog.</p>
                <form class="modal__actions" method="POST" action="{{ route('manage.destroy', $post->slug) }}">
                    @csrf
                    @method('DELETE')
                    <x-button variant="danger-solid" type="submit">Ya, Hapus</x-button>
                    <x-button variant="outline" href="#">Batal</x-button>
                </form>
            </div>
        </div>
    @endforeach
@endsection
