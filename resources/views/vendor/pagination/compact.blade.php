@if ($paginator->hasPages())
<nav class="pagination-compact">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="pg-btn is-disabled"><i class="fa-solid fa-chevron-left"></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pg-btn" rel="prev" aria-label="Précédent"><i class="fa-solid fa-chevron-left"></i></a>
    @endif

    {{-- Current / Total --}}
    <span class="pg-info">
        <strong>{{ $paginator->currentPage() }}</strong><span class="pg-sep">/</span>{{ $paginator->lastPage() }}
    </span>

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pg-btn" rel="next" aria-label="Suivant"><i class="fa-solid fa-chevron-right"></i></a>
    @else
        <span class="pg-btn is-disabled"><i class="fa-solid fa-chevron-right"></i></span>
    @endif

    <span class="pg-range">· {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}</span>
</nav>
@endif
