@props(['activity'])

<article class="card portfolio-card">
    <div class="arch">
        <img src="{{ asset($activity->image) }}" alt="{{ $activity->alt }}" loading="lazy"
             @isset($activity->position) style="object-position: {{ $activity->position }}" @endisset>
    </div>
    <div class="card__body">
        <h3>{{ $activity->title }}</h3>
        <p>{{ $activity->text }}</p>
        @isset($activity->link_route)
            <p><a class="text-link" href="{{ route($activity->link_route) }}">{{ $activity->link_label }}</a></p>
        @endisset
    </div>
</article>
