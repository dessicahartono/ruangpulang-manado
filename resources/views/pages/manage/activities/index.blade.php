@extends('layouts.app')

@section('title', 'Kelola Kegiatan')

@section('content')
    <x-page-hero>Kelola&nbsp;<em>Kegiatan</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="admin-head">
                <p class="blog-count admin-count">Total {{ $activities->count() }} kegiatan</p>
                <div class="admin-head__actions">
                    <x-button variant="accent" :href="route('manage.activities.create')">Tambah Kegiatan</x-button>
                    <a class="text-link admin-back-link" href="{{ route('manage.dashboard') }}">Kembali ke menu utama</a>
                </div>
            </div>

            @if ($activities->isEmpty())
                <p class="admin-empty">Belum ada data kegiatan.</p>
            @else
                <div class="admin-list admin-list--simple">
                    <div class="admin-row admin-row--head"><span>Judul Kegiatan</span><span>Media</span><span>Aksi</span></div>
                    @foreach ($activities as $activity)
                        <div class="admin-row">
                            <div>
                                <h3>{{ $activity->title }}</h3>
                                <small>{{ Str::limit($activity->description, 80) }}</small>
                            </div>
                            <div class="admin-meta">{{ $activity->mediaSummary() }}</div>
                            <div class="admin-actions">
                                <x-button variant="outline" class="btn--sm" :href="route('manage.activities.show', $activity)">Tinjau</x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
