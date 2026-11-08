@props(['title', 'posts', 'dateLabel', 'dateColumn', 'empty'])

<section class="admin-section">
    <h2 class="h3-title">{{ $title }} ({{ $posts->total() }})</h2>

    @if ($posts->isEmpty())
        <p class="admin-empty">{{ $empty }}</p>
    @else
        <div class="admin-list">
            <div class="admin-row admin-row--head"><span>Judul dan Slug</span><span>Nama Penulis</span><span>{{ $dateLabel }}</span><span>Aksi</span></div>
            @foreach ($posts as $post)
                <div class="admin-row">
                    <div>
                        <h3>{{ $post->title }}</h3>
                        <small>/blog/{{ $post->slug }}</small>
                    </div>
                    <div class="admin-meta">{{ $post->author }}</div>
                    <div class="admin-meta">
                        <time datetime="{{ $post->{$dateColumn}->toDateString() }}">{{ $post->{$dateColumn}->translatedFormat('j F Y') }}</time>
                    </div>
                    <div class="admin-actions">
                        <x-button variant="outline" class="btn--sm" :href="route('manage.show', $post->slug)">Tinjau Artikel</x-button>
                    </div>
                </div>
            @endforeach
        </div>

        <x-pager :paginator="$posts" />
    @endif
</section>
