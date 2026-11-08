@props(['portfolio'])

<article class="card portfolio-card">
    <div class="arch">
        <img src="{{ asset($portfolio->image) }}" alt="{{ $portfolio->alt }}" loading="lazy"
             @isset($portfolio->position) style="object-position: {{ $portfolio->position }}" @endisset>
    </div>
    <div class="card__body">
        <h3>{{ $portfolio->title }}</h3>
        <p>{{ $portfolio->text }}</p>
        @isset($portfolio->link_route)
            <p><a class="text-link" href="{{ route($portfolio->link_route) }}">{{ $portfolio->link_label }}</a></p>
        @endisset
    </div>
</article>
