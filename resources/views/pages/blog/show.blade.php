@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->excerpt)

@section('content')
    <x-page-hero>
        {{ $post->title }}
        <x-slot:after>
            <p class="article-meta">
                <span>{{ $post->author }}</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
            </p>
        </x-slot:after>
    </x-page-hero>

    <section class="section">
        <div class="container">
            <article class="article">
                <div class="prose">
                    @foreach ($post->content as $block)
                        @if (str_starts_with($block, '## '))
                            <h2>{{ substr($block, 3) }}</h2>
                        @else
                            <p>{{ $block }}</p>
                        @endif
                    @endforeach
                </div>
                <div class="article__nav">
                    <a class="back-arrow" href="{{ route('blog.index') }}" aria-label="Kembali ke Blog">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"></path><path d="M11 6l-6 6 6 6"></path></svg>
                    </a>
                    <a class="text-link" href="{{ route('blog.index') }}">Baca Artikel Lainnya <span aria-hidden="true">&#8599;</span></a>
                </div>
            </article>
        </div>
    </section>
@endsection
