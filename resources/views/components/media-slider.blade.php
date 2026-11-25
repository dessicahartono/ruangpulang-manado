@props(['media'])

{{-- Geser ke samping untuk melihat media berikutnya. Tombol panah ditambahkan site.js. --}}
<div class="media-slider" data-media-slider>
    <div class="media-slider__track" data-media-track tabindex="0" aria-label="Media kegiatan">
        @foreach ($media as $item)
            <div class="media-slider__item">
                @if ($item->isVideo())
                    <video src="{{ asset($item->path) }}" controls playsinline preload="metadata" aria-label="{{ $item->alt }}"></video>
                @else
                    <img src="{{ asset($item->path) }}" alt="{{ $item->alt }}" loading="lazy"
                         @if ($item->focus) style="object-position: {{ $item->focus }}" @endif>
                @endif
            </div>
        @endforeach
    </div>

    @if ($media->count() > 1)
        <button class="media-slider__nav media-slider__nav--prev" type="button" data-media-prev aria-label="Media sebelumnya" hidden>
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"></path></svg>
        </button>
        <button class="media-slider__nav media-slider__nav--next" type="button" data-media-next aria-label="Media berikutnya" hidden>
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"></path></svg>
        </button>
        <span class="media-slider__count" data-media-count aria-hidden="true">1/{{ $media->count() }}</span>
    @endif
</div>
