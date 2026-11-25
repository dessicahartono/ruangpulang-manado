@props(['activity'])

<article class="card portfolio-card">
    <div class="arch">
        <x-media-slider :media="$activity->media" />
    </div>
    <div class="card__body">
        <h3>{{ $activity->title }}</h3>
        <p>{{ $activity->description }}</p>
        @if ($activity->link_url)
            <p><a class="text-link" href="{{ url($activity->link_url) }}">{{ $activity->link_label }}</a></p>
        @endif
    </div>
</article>
