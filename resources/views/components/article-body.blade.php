@props(['post'])

@if ($post->isHtml())
    {{-- Artikel dari TinyMCE. HTML-nya sudah dibersihkan, hanya tag format teks yang aman yang tersisa. --}}
    <div class="prose prose--rich">{!! $post->safeHtml() !!}</div>
@else
    <div class="prose">
        @foreach ($post->content as $block)
            @if (str_starts_with($block, '## '))
                <h2>{{ substr($block, 3) }}</h2>
            @else
                <p>{{ $block }}</p>
            @endif
        @endforeach
    </div>
@endif
