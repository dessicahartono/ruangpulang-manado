@props(['post'])

<article class="result">
    <div class="result__main">
        <p class="result__url">
            <img src="{{ asset('images/logo.png') }}" alt="" width="20" height="20">
            <span>{{ config('community.name') }}<small>blog &rsaquo; {{ $post->slug }}</small></span>
        </p>
        <h3 class="result__title"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
        <p class="result__snippet">
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
            {{ $post->excerpt }}
        </p>
    </div>
</article>
