@props(['post', 'dateColumn'])

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
        <x-button variant="outline" class="btn--sm" :href="route('manage.blog.show', $post->slug)">Tinjau Artikel</x-button>
    </div>
</div>
