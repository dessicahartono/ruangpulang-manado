@props(['activity'])

{{-- Bingkai kotak bergaris tepi, sama seperti kartu Sponsorship. --}}
<article class="card portfolio-card activity-card">
    <div class="activity-card__media">
        <x-media-slider :media="$activity->media" />
    </div>
    <div class="card__body">
        <h3>{{ $activity->title }}</h3>
        <p>{{ $activity->description }}</p>
        @if ($activity->sponsors->isNotEmpty())
            <p class="activity-card__sponsors">Didukung oleh {{ $activity->sponsors->pluck('name')->join(', ', ' dan ') }}</p>
        @endif
        @if ($activity->link_url)
            <p><a class="text-link" href="{{ url($activity->link_url) }}" @if ($activity->linkIsExternal()) target="_blank" rel="noopener" @endif>{{ $activity->link_label }}</a></p>
        @endif
    </div>
</article>
