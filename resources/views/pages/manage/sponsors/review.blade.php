@extends('layouts.app')

@section('title', 'Tinjau Sponsor')

@section('content')
    <x-page-hero>Tinjau&nbsp;<em>Sponsor</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="member-review">
                <div class="sponsor-card__logo">
                    @if ($sponsor->photo)
                        <img src="{{ asset($sponsor->photo) }}" alt="Logo {{ $sponsor->name }}">
                    @else
                        <span aria-hidden="true">{{ Str::substr($sponsor->name, 0, 1) }}</span>
                    @endif
                </div>

                <dl class="member-review__facts">
                    <div><dt>Nama Sponsor</dt><dd>{{ $sponsor->name }}</dd></div>
                    <div><dt>Deskripsi</dt><dd>{{ $sponsor->description }}</dd></div>
                    <div><dt>Kegiatan yang Didukung</dt><dd>{{ $sponsor->activities->pluck('title')->join(', ', ' dan ') ?: 'Belum ada. Atur lewat Kelola Kegiatan.' }}</dd></div>
                    <div>
                        <dt>Link</dt>
                        <dd>
                            @if ($sponsor->link)
                                <a class="text-link" href="{{ $sponsor->link }}" target="_blank" rel="noopener">{{ $sponsor->link }}</a>
                            @else
                                <span class="member-status--missing">Belum ada</span>
                            @endif
                        </dd>
                    </div>
                    @if (! $sponsor->photo)
                        <div><dt>Foto</dt><dd><span class="member-status--missing">Belum ada</span></dd></div>
                    @endif
                </dl>
            </div>

            <div class="article__nav">
                <a class="back-arrow" href="{{ route('manage.sponsors.index') }}" aria-label="Kembali ke Kelola Sponsorship">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
                </a>
            </div>

            <div class="admin-actions review-actions">
                <x-button variant="danger" href="#hapus">Hapus</x-button>
                <x-button variant="outline" :href="route('manage.sponsors.edit', $sponsor)">Ubah</x-button>
            </div>
        </div>
    </section>

    <div class="modal" id="hapus" role="dialog" aria-modal="true" aria-labelledby="hapus-judul">
        <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
        <div class="modal__box">
            <h2 id="hapus-judul">Hapus Sponsor Ini?</h2>
            <p>{{ $sponsor->name }} akan hilang dari halaman Sponsorship. Data yang dihapus tidak bisa dikembalikan.</p>
            <form class="modal__actions" method="POST" action="{{ route('manage.sponsors.destroy', $sponsor) }}">
                @csrf
                @method('DELETE')
                <x-button variant="danger-solid" type="submit">Ya, Hapus</x-button>
                <x-button variant="outline" href="#">Batal</x-button>
            </form>
        </div>
    </div>
@endsection
