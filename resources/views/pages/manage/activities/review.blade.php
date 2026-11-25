@extends('layouts.app')

@section('title', 'Tinjau Kegiatan')

@section('content')
    <x-page-hero>Tinjau&nbsp;<em>Kegiatan</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="member-review activity-review">
                <div class="activity-card__media activity-review__media">
                    <x-media-slider :media="$activity->media" />
                </div>

                <dl class="member-review__facts">
                    <div><dt>Judul Kegiatan</dt><dd>{{ $activity->title }}</dd></div>
                    <div><dt>Deskripsi</dt><dd>{{ $activity->description }}</dd></div>
                    <div><dt>Media</dt><dd>{{ $activity->mediaSummary() }}</dd></div>
                    <div><dt>Didukung oleh</dt><dd>{{ $activity->sponsors->pluck('name')->join(', ', ' dan ') ?: 'Tidak ada sponsor' }}</dd></div>
                    <div>
                        <dt>Link</dt>
                        <dd>
                            @if ($activity->link_url)
                                <a class="text-link" href="{{ url($activity->link_url) }}" @if ($activity->linkIsExternal()) target="_blank" rel="noopener" @endif>{{ $activity->link_label }}</a>
                                <small class="activity-review__url">{{ $activity->link_url }}</small>
                            @else
                                Tidak ada
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="article__nav">
                <a class="back-arrow" href="{{ route('manage.activities.index') }}" aria-label="Kembali ke Kelola Kegiatan">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
                </a>
            </div>

            <div class="admin-actions review-actions">
                <x-button variant="danger" href="#hapus">Hapus</x-button>
                <x-button variant="outline" :href="route('manage.activities.edit', $activity)">Ubah</x-button>
            </div>
        </div>
    </section>

    <div class="modal" id="hapus" role="dialog" aria-modal="true" aria-labelledby="hapus-judul">
        <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
        <div class="modal__box">
            <h2 id="hapus-judul">Hapus Kegiatan Ini?</h2>
            <p>{{ $activity->title }} beserta foto dan videonya akan hilang dari halaman Kegiatan. Data yang dihapus tidak bisa dikembalikan.</p>
            <form class="modal__actions" method="POST" action="{{ route('manage.activities.destroy', $activity) }}">
                @csrf
                @method('DELETE')
                <x-button variant="danger-solid" type="submit">Ya, Hapus</x-button>
                <x-button variant="outline" href="#">Batal</x-button>
            </form>
        </div>
    </div>
@endsection
