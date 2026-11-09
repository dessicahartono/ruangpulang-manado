@extends('layouts.app')

@section('title', 'Tinjau Pengurus')

@section('content')
    <x-page-hero>Tinjau&nbsp;<em>Pengurus</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="member-review">
                <div class="arch founder-card__portrait">
                    @if ($member->photo)
                        <img src="{{ asset($member->photo) }}" alt="Foto {{ $member->display_name }}">
                    @else
                        <span aria-hidden="true">{{ Str::substr($member->full_name, 0, 1) }}</span>
                    @endif
                </div>

                <dl class="member-review__facts">
                    <div><dt>Nama Lengkap</dt><dd>{{ $member->full_name }}</dd></div>
                    <div><dt>Nama Panggilan</dt><dd>{{ $member->nickname }}</dd></div>
                    <div><dt>Divisi</dt><dd>{{ $member->division->name }}</dd></div>
                    @if ($member->role)
                        <div><dt>Jabatan</dt><dd>{{ $member->role }}</dd></div>
                    @endif
                    <div>
                        <dt>Instagram</dt>
                        <dd>
                            @if ($member->instagram)
                                <a class="text-link" href="{{ $member->instagram }}" target="_blank" rel="noopener">{{ $member->instagram }}</a>
                            @else
                                <span class="member-status--missing">Belum ada</span>
                            @endif
                        </dd>
                    </div>
                    @if (! $member->photo)
                        <div><dt>Foto</dt><dd><span class="member-status--missing">Belum ada</span></dd></div>
                    @endif
                    @if ($member->bio)
                        <div><dt>Bio</dt><dd>{{ $member->bio }}@if ($member->bio_more) {{ $member->bio_more }}@endif</dd></div>
                    @endif
                </dl>
            </div>

            <div class="article__nav">
                <a class="back-arrow" href="{{ route('manage.members.index') }}" aria-label="Kembali ke Kelola Profil Pengurus">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
                </a>
            </div>

            <div class="admin-actions review-actions">
                <x-button variant="danger" href="#hapus">Hapus</x-button>
                <x-button variant="outline" :href="route('manage.members.edit', $member)">Ubah</x-button>
            </div>
        </div>
    </section>

    <div class="modal" id="hapus" role="dialog" aria-modal="true" aria-labelledby="hapus-judul">
        <a class="modal__backdrop" href="#" aria-label="Tutup"></a>
        <div class="modal__box">
            <h2 id="hapus-judul">Hapus Data Pengurus Ini?</h2>
            <p>{{ $member->display_name }} akan hilang dari divisi {{ $member->division->name }} di halaman Pengurus. Data yang dihapus tidak bisa dikembalikan.</p>
            <form class="modal__actions" method="POST" action="{{ route('manage.members.destroy', $member) }}">
                @csrf
                @method('DELETE')
                <x-button variant="danger-solid" type="submit">Ya, Hapus</x-button>
                <x-button variant="outline" href="#">Batal</x-button>
            </form>
        </div>
    </div>
@endsection
