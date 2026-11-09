@extends('layouts.app')

@section('title', 'Tinjau Artikel')

@section('content')
    <x-page-hero>Tinjau&nbsp;<em>Artikel</em></x-page-hero>

    <section class="section">
        <div class="container">
            <article class="article">
                @if (session('notice'))
                    <x-notice>{{ session('notice') }}</x-notice>
                @endif

                <h2 class="article__title">{{ $post->title }}</h2>
                <p class="article-meta article-meta--dark">
                    <span>{{ $post->author }}</span>
                    @if ($post->isPending())
                        <strong>Menunggu Persetujuan</strong>
                    @else
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                    @endif
                </p>

                <x-article-body :post="$post" />

                <div class="article__nav">
                    <a class="back-arrow" href="{{ route('manage.blog.index') }}" aria-label="Kembali ke Kelola Blog">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
                    </a>
                </div>

                <div class="admin-actions review-actions">
                    {{-- Artikel yang sudah terbit tidak perlu disetujui lagi. --}}
                    @if ($post->isPending())
                        <x-button variant="accent" href="#setujui">Setujui Artikel</x-button>
                    @endif
                    <x-button variant="danger" href="#hapus">Hapus</x-button>
                    <x-button variant="outline" :href="route('manage.blog.edit', $post->slug)">Ubah</x-button>
                </div>
            </article>
        </div>
    </section>

    @if ($post->isPending())
        <div class="modal" id="setujui" role="dialog" aria-modal="true" aria-labelledby="setujui-judul">
            <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
            <div class="modal__box">
                <h2 id="setujui-judul">Setujui Artikel Ini?</h2>
                <p>Artikel akan diterbitkan dan tampil di halaman Blog.</p>
                <form class="modal__actions" method="POST" action="{{ route('manage.blog.approve', $post->slug) }}">
                    @csrf
                    <x-button variant="accent" type="submit">Ya, Setujui</x-button>
                    <x-button variant="outline" href="#">Batal</x-button>
                </form>
            </div>
        </div>
    @endif

    @if (session('published'))
        <div class="modal" id="terbit" role="dialog" aria-modal="true" aria-labelledby="terbit-judul">
            <a class="modal__backdrop" href="{{ route('manage.blog.index') }}" aria-label="Tutup"></a>
            <div class="modal__box">
                <h2 id="terbit-judul">Artikel Telah Diterbitkan</h2>
                <p>Artikel ini sudah tampil di halaman Blog.</p>
                <div class="modal__actions">
                    <a class="text-link" href="{{ route('manage.blog.index') }}">Kembali ke Kelola Blog</a>
                </div>
            </div>
        </div>
    @endif

    <div class="modal" id="hapus" role="dialog" aria-modal="true" aria-labelledby="hapus-judul">
        <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
        <div class="modal__box">
            <h2 id="hapus-judul">Hapus Artikel Ini?</h2>
            <p>Artikel yang dihapus tidak bisa dikembalikan, dan akan hilang dari halaman Blog.</p>
            <form class="modal__actions" method="POST" action="{{ route('manage.blog.destroy', $post->slug) }}">
                @csrf
                @method('DELETE')
                <x-button variant="danger-solid" type="submit">Ya, Hapus</x-button>
                <x-button variant="outline" href="#">Batal</x-button>
            </form>
        </div>
    </div>
@endsection
