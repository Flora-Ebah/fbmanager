<aside class="list-col {{ isset($conversation) ? '' : 'is-visible-mobile' }}">
    <div class="list-col__head">
        <form method="GET" action="/messenger" class="list-col__search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher une conversation..." />
        </form>
        @if($conversations->hasPages())
        <div class="list-col__pagination">
            {{ $conversations->appends(['search' => $search])->onEachSide(1)->links() }}
        </div>
        @endif
    </div>

    <div class="list-col__body">
        @forelse($conversations as $c)
        @php
            $isActive = isset($conversation) && $conversation->conversation_id === $c->conversation_id;
            $initials = strtoupper(substr($c->nom_expediteur ?? '?', 0, 2));
            $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
            $color = $colors[abs(crc32($c->nom_expediteur ?? '?')) % count($colors)];
            try { $time = $c->temps_dernier_message ? \Carbon\Carbon::parse($c->temps_dernier_message)->diffForHumans(['short' => true]) : '—'; }
            catch (\Throwable $e) { $time = '—'; }
            $isUnread = $c->statut === 'non_lu';
        @endphp
        <a href="/messenger/{{ $c->conversation_id }}" class="convo-item {{ $isActive ? 'is-active' : '' }} {{ $isUnread ? 'is-unread' : '' }}">
            <div class="convo-item__avatar" style="background:{{ $color }}">{{ $initials }}</div>
            <div class="convo-item__content">
                <div class="convo-item__head">
                    <span class="convo-item__name">{{ $c->nom_expediteur ?? 'Utilisateur Facebook' }}</span>
                    <span class="convo-item__time">{{ $time }}</span>
                </div>
                <div class="convo-item__preview">
                    <span>{{ \Illuminate\Support\Str::limit($c->dernier_message ?? '—', 60) }}</span>
                    @if($isUnread)<span class="convo-item__dot"></span>@endif
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:40px 20px;">
            <div class="empty-state__icon"><i class="fa-regular fa-comment-dots"></i></div>
            <div class="empty-state__title">Aucune conversation</div>
        </div>
        @endforelse

    </div>
</aside>

<style>
.list-col__pagination {
    margin-top: 12px;
    display: flex;
    justify-content: center;
}
.list-col__pagination nav { width: 100%; }
.list-col__pagination .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2px;
    font-size: 12px;
    flex-wrap: wrap;
}
.list-col__pagination .pagination a,
.list-col__pagination .pagination span {
    padding: 4px 8px;
    min-width: 26px;
    font-size: 12px;
}
.convo-item {
    display: flex;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border);
    transition: all var(--duration) var(--ease);
    position: relative;
    align-items: center;
}
.convo-item:hover { background: var(--surface-hover); }
.convo-item.is-active { background: var(--bleu-france-soft); }
.convo-item.is-active::before {
    content: '';
    position: absolute;
    left: 0; top: 10px; bottom: 10px;
    width: 3px;
    background: var(--bleu-france);
    border-radius: 0 3px 3px 0;
}
.convo-item.is-unread { background: #F0F6FF; }
.convo-item__avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}
.convo-item__content { flex: 1; min-width: 0; }
.convo-item__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3px;
    gap: 8px;
}
.convo-item__name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.is-unread .convo-item__name { font-weight: 700; }
.convo-item__time {
    font-size: 11px;
    color: var(--text-muted);
    flex-shrink: 0;
}
.is-unread .convo-item__time { color: var(--bleu-france); font-weight: 600; }
.convo-item__preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: var(--text-secondary);
    gap: 8px;
}
.convo-item__preview > span:first-child {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
}
.is-unread .convo-item__preview { color: var(--text-primary); font-weight: 500; }
.convo-item__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--bleu-france);
    flex-shrink: 0;
}
</style>
