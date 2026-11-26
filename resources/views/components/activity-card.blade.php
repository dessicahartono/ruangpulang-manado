@props(['activity'])

{{-- Bingkai kotak bergaris tepi, sama seperti kartu Sponsorship. --}}
<article class="card portfolio-card activity-card">
    <div class="activity-card__media">
        <x-media-slider :media="$activity->media" />
    </div>
    <div class="card__body">
        <h3>{{ $activity->local('title') }}</h3>
        <p>{{ $activity->local('description') }}</p>
        @if ($activity->sponsors->isNotEmpty())
            <p class="activity-card__sponsors">{{ __('Didukung oleh') }} {{ $activity->sponsors->pluck('name')->join(', ', ' '.__('dan').' ') }}</p>
        @endif
        @if ($activity->link_url)
            <p><a class="text-link" href="{{ url($activity->link_url) }}" @if ($activity->linkIsExternal()) target="_blank" rel="noopener" @endif>{{ $activity->local('link_label') }}</a></p>
        @endif
    </div>
</article>
