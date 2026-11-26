@props(['sponsor'])

<article class="card sponsor-card">
    <div class="sponsor-card__logo">
        @if ($sponsor->photo)
            <img src="{{ asset($sponsor->photo) }}" alt="{{ __('Logo :name', ['name' => $sponsor->name]) }}" loading="lazy">
        @else
            <span aria-hidden="true">{{ Str::substr($sponsor->name, 0, 1) }}</span>
        @endif
    </div>
    <div class="card__body">
        <h3>{{ $sponsor->name }}</h3>
        <p>{{ $sponsor->local('description') }}</p>
        @if ($sponsor->activities->isNotEmpty())
            <p class="sponsor-card__activities">{{ __('Mendukung kegiatan') }} {{ $sponsor->activities->map->local('title')->join(', ', ' '.__('dan').' ') }}</p>
        @endif
        @if ($sponsor->link)
            <p><a class="text-link" href="{{ $sponsor->link }}" target="_blank" rel="noopener">{{ __('Kunjungi :name', ['name' => $sponsor->name]) }}</a></p>
        @endif
    </div>
</article>
