@extends('layouts.app')

@section('title', 'Kelola Sponsorship')

@section('content')
    <x-page-hero>Kelola&nbsp;<em>Sponsorship</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="admin-head">
                <p class="blog-count admin-count">Total {{ $sponsors->count() }} sponsor</p>
                <div class="admin-head__actions">
                    <x-button variant="accent" :href="route('manage.sponsors.create')">Tambah Sponsor</x-button>
                    <a class="text-link admin-back-link" href="{{ route('manage.dashboard') }}">Kembali ke menu utama</a>
                </div>
            </div>

            @if ($sponsors->isEmpty())
                <p class="admin-empty">Belum ada data sponsor.</p>
            @else
                <div class="admin-list admin-list--sponsors">
                    <div class="admin-row admin-row--head"><span>Nama Sponsor</span><span>Kelengkapan Data</span><span>Aksi</span></div>
                    @foreach ($sponsors as $sponsor)
                        <div class="admin-row">
                            <div>
                                <h3>{{ $sponsor->name }}</h3>
                                <small>{{ Str::limit($sponsor->description, 80) }}</small>
                            </div>
                            <div class="admin-meta">
                                @if ($sponsor->missingData())
                                    <span class="member-status--missing">Belum ada {{ implode(' dan ', $sponsor->missingData()) }}</span>
                                @else
                                    Lengkap
                                @endif
                            </div>
                            <div class="admin-actions">
                                <x-button variant="outline" class="btn--sm" :href="route('manage.sponsors.show', $sponsor)">Tinjau</x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
