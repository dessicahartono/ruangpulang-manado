@props(['sponsor'])

<article class="card sponsor-card">
    <div class="sponsor-card__logo">
        @if ($sponsor->photo)
            <img src="{{ asset($sponsor->photo) }}" alt="Logo {{ $sponsor->name }}" loading="lazy">
        @else
            <span aria-hidden="true">{{ Str::substr($sponsor->name, 0, 1) }}</span>
        @endif
    </div>
    <div class="card__body">
        <h3>{{ $sponsor->name }}</h3>
        <p>{{ $sponsor->description }}</p>
        @if ($sponsor->activities->isNotEmpty())
            <p class="sponsor-card__activities">Mendukung kegiatan {{ $sponsor->activities->pluck('title')->join(', ', ' dan ') }}</p>
        @endif
        @if ($sponsor->link)
            <p><a class="text-link" href="{{ $sponsor->link }}" target="_blank" rel="noopener">Kunjungi {{ $sponsor->name }}</a></p>
        @endif
    </div>
</article>
