@props(['title', 'count', 'posts', 'dateLabel', 'dateColumn', 'empty', 'preview' => null])

@php
    $visible = $preview ? $posts->take($preview) : $posts;
    $hidden = $preview ? $posts->slice($preview) : collect();
    $toggleId = 'lihat-'.Str::slug($title);
@endphp

<section class="admin-section">
    <h2 class="h3-title">{{ $title }} ({{ $count }})</h2>

    @if ($posts->isEmpty())
        <p class="admin-empty">{{ $empty }}</p>
    @else
        <div class="admin-list">
            <div class="admin-row admin-row--head"><span>Judul dan Slug</span><span>Nama Penulis</span><span>{{ $dateLabel }}</span><span>Aksi</span></div>
            @foreach ($visible as $post)
                <x-admin-post-row :post="$post" :date-column="$dateColumn" />
            @endforeach
        </div>

        {{-- Memakai pola checkbox seperti menu dan popup, jadi tetap jalan tanpa JavaScript. --}}
        @if ($hidden->isNotEmpty())
            <input class="admin-more__check sr-only" type="checkbox" id="{{ $toggleId }}" aria-label="Tampilkan semua artikel {{ $title }}">
            <div class="admin-list admin-more__rows">
                @foreach ($hidden as $post)
                    <x-admin-post-row :post="$post" :date-column="$dateColumn" />
                @endforeach
            </div>
            <label class="text-link admin-more__toggle" for="{{ $toggleId }}">
                <span class="admin-more__more">Lihat Selengkapnya ({{ $hidden->count() }})</span>
                <span class="admin-more__less">Sembunyikan</span>
            </label>
        @endif
    @endif

    {{ $slot }}
</section>
