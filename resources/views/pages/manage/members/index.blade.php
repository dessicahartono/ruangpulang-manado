@extends('layouts.app')

@section('title', 'Kelola Profil Pengurus')

@section('content')
    <x-page-hero>Kelola Profil&nbsp;<em>Pengurus</em></x-page-hero>

    <section class="section">
        <div class="container">
            @if (session('notice'))
                <x-notice>{{ session('notice') }}</x-notice>
            @endif

            <div class="admin-head">
                <p class="blog-count admin-count">Total {{ $members->total() }} data pengurus</p>
                <div class="admin-head__actions">
                    <x-button variant="accent" :href="route('manage.members.create')">Tambah Pengurus</x-button>
                    <a class="text-link admin-back-link" href="{{ route('manage.dashboard') }}">Kembali ke menu utama</a>
                </div>
            </div>

            <x-member-toolbar :divisions="$divisions" :search="$search" :division-id="$divisionId" :sort="$sort" />

            @if ($members->isEmpty())
                <p class="admin-empty">
                    {{ $search !== '' || $divisionId !== null ? 'Tidak ada pengurus yang cocok dengan pencarian ini.' : 'Belum ada data pengurus.' }}
                </p>
            @else
                <div class="admin-list">
                    <div class="admin-row admin-row--head"><span>Nama</span><span>Divisi</span><span>Kelengkapan Data</span><span>Aksi</span></div>
                    @foreach ($members as $member)
                        <x-admin-member-row :member="$member" />
                    @endforeach
                </div>

                <x-pager :paginator="$members" />
            @endif
        </div>
    </section>
@endsection
