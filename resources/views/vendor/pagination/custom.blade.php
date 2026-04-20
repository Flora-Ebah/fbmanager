@if ($paginator->hasPages())
<nav style="display:flex; align-items:center; justify-content:center; gap:6px; padding:20px 0; flex-wrap:wrap;">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="pg-btn pg-disabled"><i class="fa-solid fa-chevron-left"></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="pg-btn"><i class="fa-solid fa-chevron-left"></i></a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pg-btn pg-disabled">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pg-btn pg-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="pg-btn"><i class="fa-solid fa-chevron-right"></i></a>
    @else
        <span class="pg-btn pg-disabled"><i class="fa-solid fa-chevron-right"></i></span>
    @endif

    <span style="margin-left:12px; font-family:'VT323',monospace; font-size:16px; color:#888;">
        {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
    </span>
</nav>

<style>
    .pg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        font-family: 'VT323', monospace;
        font-size: 18px;
        text-decoration: none;
        color: var(--bleu-france);
        border: 1px solid var(--gris-moyen);
        border-radius: 4px;
        background: var(--blanc);
        cursor: pointer;
        transition: all 0.15s;
    }
    .pg-btn:hover { background: var(--bleu-france); color: var(--blanc); }
    .pg-active { background: var(--bleu-france) !important; color: var(--blanc) !important; border-color: var(--bleu-france) !important; }
    .pg-disabled { color: var(--gris-moyen) !important; cursor: default; pointer-events: none; }
</style>
@endif
