@props(['post'])

<div class="prose">
    @foreach ($post->content as $block)
        @if (str_starts_with($block, '## '))
            <h2>{{ substr($block, 3) }}</h2>
        @else
            <p>{{ $block }}</p>
        @endif
    @endforeach
</div>
