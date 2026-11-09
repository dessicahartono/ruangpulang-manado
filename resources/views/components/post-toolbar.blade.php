@props(['action', 'search', 'sort', 'compactSort' => false])

@php($sorts = App\Repositories\CommunityRepository::POST_SORTS)

<form class="blog-toolbar" method="GET" action="{{ $action }}" role="search" data-post-toolbar>
    <label class="sr-only" for="cari">Cari Artikel</label>
    <div class="search-wrap">
        <input class="field field--search" id="cari" type="search" name="q" value="{{ $search }}" placeholder="Cari berdasarkan nama penulis, judul, atau isi">
        <button class="search-btn" type="submit" aria-label="Cari">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5L21 21"></path></svg>
        </button>
    </div>

    @if ($compactSort)
        <div class="sort-field only-wide">
            <label class="sr-only" for="urut">Urut Berdasarkan</label>
            <select class="select" id="urut" name="urut" data-autosubmit>
                <option value="" @selected(! request()->has('urut')) disabled hidden>Urut Berdasarkan</option>
                @foreach ($sorts as $value => $label)
                    <option value="{{ $value }}" @selected(request()->has('urut') && $sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <details class="sort-menu only-narrow">
            <summary class="sort-btn" aria-label="Urutkan Berdasarkan">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"></path></svg>
            </summary>
            <div class="sort-menu__list">
                <p class="sort-menu__title">Urutkan Berdasarkan</p>
                @foreach ($sorts as $value => $label)
                    <a @class(['is-current' => $sort === $value]) href="{{ request()->fullUrlWithQuery(['urut' => $value, 'page' => null]) }}" @if ($sort === $value) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </details>
    @else
        {{-- Urutan yang sedang dipilih ikut terkirim saat mencari. --}}
        @if (request()->has('urut'))
            <input type="hidden" name="urut" value="{{ $sort }}">
        @endif

        <details class="sort-menu">
            <summary class="sort-btn" aria-label="Urutkan Berdasarkan">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"></path></svg>
            </summary>
            <div class="sort-menu__list">
                <p class="sort-menu__title">Urutkan Berdasarkan</p>
                @foreach ($sorts as $value => $label)
                    @php($isCurrent = request()->has('urut') && $sort === $value)
                    <a @class(['is-current' => $isCurrent]) href="{{ request()->fullUrlWithQuery(['urut' => $value, 'page' => null]) }}" @if ($isCurrent) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </details>
    @endif
</form>
