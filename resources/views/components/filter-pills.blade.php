@props(['items', 'active' => null])

<div class="filters">
    <a @class(['filters__item', 'is-active' => $active === null]) href="{{ request()->fullUrlWithQuery(['kategori' => null, 'page' => null]) }}">Semua</a>
    @foreach ($items as $item)
        <a @class(['filters__item', 'is-active' => $active === $item]) href="{{ request()->fullUrlWithQuery(['kategori' => $item, 'page' => null]) }}">{{ $item }}</a>
    @endforeach
</div>
