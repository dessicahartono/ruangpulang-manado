@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="pager" aria-label="Halaman">
        <p class="pager__brand">
            {{ config('community.name') }}
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"></path></svg>
        </p>
        <ol class="pager__nums">
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                <li>
                    <a @class(['pager__n', 'is-current' => $page === $paginator->currentPage()]) href="{{ $url }}"
                       @if ($page === $paginator->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                </li>
            @endforeach
            @if ($paginator->hasMorePages())
                <li><a class="pager__next" href="{{ $paginator->nextPageUrl() }}">Berikutnya</a></li>
            @endif
        </ol>
    </nav>
@endif
