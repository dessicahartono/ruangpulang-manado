@props(['member'])

<article class="founder-card">
    <div class="arch founder-card__portrait">
        @if ($member->photo)
            <img src="{{ asset($member->photo) }}" alt="Foto {{ $member->name }}" loading="lazy">
        @else
            <span aria-hidden="true">{{ Str::substr($member->name, 0, 1) }}</span>
        @endif
    </div>
    <h3>{{ $member->name }}</h3>
    @isset($member->role)
        <p class="founder-card__role">{{ $member->role }}</p>
    @endisset
    @isset($member->bio)
        <p>{{ $member->bio }}</p>
    @endisset
    @isset($member->bio_more)
        <details class="bio-more">
            <summary class="text-link"><span class="bio-more__more">Selengkapnya</span><span class="bio-more__less">Sembunyikan</span></summary>
            <p>{{ $member->bio_more }}</p>
        </details>
    @endisset
    @if ($member->instagram)
        <p><a class="text-link" href="{{ $member->instagram }}" target="_blank" rel="noopener">Instagram <span aria-hidden="true">&#8599;</span></a></p>
    @endif
</article>
