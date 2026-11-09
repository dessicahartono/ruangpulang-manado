@props(['divisions', 'search', 'divisionId', 'sort'])

<form class="blog-toolbar blog-toolbar--members" method="GET" action="{{ route('manage.members.index') }}" role="search">
    {{-- Urutan yang sedang dipilih ikut terkirim saat mencari atau memilih divisi. --}}
    @if ($sort !== App\Repositories\CommunityRepository::DEFAULT_MEMBER_SORT)
        <input type="hidden" name="urut" value="{{ $sort }}">
    @endif

    <label class="sr-only" for="cari">Cari Pengurus</label>
    <div class="search-wrap">
        <input class="field field--search" id="cari" type="search" name="q" value="{{ $search }}" placeholder="Cari berdasarkan nama atau divisi">
        <button class="search-btn" type="submit" aria-label="Cari">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5L21 21"></path></svg>
        </button>
    </div>

    <details class="sort-menu">
        <summary class="sort-btn" aria-label="Urutkan Berdasarkan">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"></path></svg>
        </summary>
        <div class="sort-menu__list">
            <p class="sort-menu__title">Urutkan Berdasarkan</p>
            @foreach (App\Repositories\CommunityRepository::MEMBER_SORTS as $value => $label)
                <a @class(['is-current' => $sort === $value]) href="{{ request()->fullUrlWithQuery(['urut' => $value, 'page' => null]) }}" @if ($sort === $value) aria-current="true" @endif>{{ $label }}</a>
            @endforeach
        </div>
    </details>

    <div class="sort-field">
        <label class="sr-only" for="divisi">Lihat Per Divisi</label>
        <select class="select" id="divisi" name="divisi" data-autosubmit>
            <option value="">Semua Divisi</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected($divisionId === $division->id)>{{ $division->name }}</option>
            @endforeach
        </select>
    </div>
</form>
