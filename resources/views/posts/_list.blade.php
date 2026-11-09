<aside class="list-col {{ isset($post) ? '' : 'is-visible-mobile' }}">
    <div class="list-col__head">
        <form method="GET" action="/posts" class="list-col__search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher un post..." />
        </form>
        @if($posts->hasPages())
        <div class="list-col__pagination">
            {{ $posts->appends(['search' => $search])->links('vendor.pagination.compact') }}
        </div>
        @endif
    </div>

    <div class="list-col__body">
        @forelse($posts as $p)
        @php
            $isActive = isset($post) && $post->post_id === $p->post_id;
            try { $timeAgo = \Carbon\Carbon::parse($p->temps_creer_post)->diffForHumans(['short' => true]); }
            catch (\Throwable $e) { $timeAgo = '—'; }
        @endphp
        <a href="/posts/{{ $p->post_id }}" class="post-item {{ $isActive ? 'is-active' : '' }}">
            @if($p->image_url)
                <img src="{{ $p->image_url }}" class="post-item__thumb" loading="lazy" onerror="this.outerHTML='<div class=\'post-item__thumb post-item__thumb--placeholder\'><i class=\'fa-regular fa-image\'></i></div>'" />
            @else
                <div class="post-item__thumb post-item__thumb--placeholder"><i class="fa-regular fa-image"></i></div>
            @endif
            <div class="post-item__content">
                <div class="post-item__text">{{ \Illuminate\Support\Str::limit($p->message_post ?? 'Sans texte', 85) }}</div>
                <div class="post-item__meta">
                    <span><i class="fa-regular fa-clock"></i> {{ $timeAgo }}</span>
                    @if($p->commentaires_count > 0)
                        <span class="post-item__count"><i class="fa-regular fa-comment"></i> {{ $p->commentaires_count }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:40px 20px;">
            <div class="empty-state__icon"><i class="fa-regular fa-folder-open"></i></div>
            <div class="empty-state__title">Aucun post</div>
            <div class="empty-state__text">Rien trouvé avec cette recherche.</div>
        </div>
        @endforelse

    </div>
</aside>

<style>
.list-col__pagination { margin-top: 10px; }
.pagination-compact {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--text-secondary);
    white-space: nowrap;
    flex-wrap: nowrap;
    overflow: hidden;
}
.pagination-compact .pg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--surface);
    color: var(--text-secondary);
    font-size: 10px;
    text-decoration: none;
    transition: all var(--duration) var(--ease);
    flex-shrink: 0;
}
.pagination-compact .pg-btn:hover { background: var(--bleu-france-soft); color: var(--bleu-france); border-color: var(--bleu-france); }
.pagination-compact .pg-btn.is-disabled { opacity: 0.4; cursor: not-allowed; }
.pagination-compact .pg-info {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-primary);
    padding: 0 4px;
    flex-shrink: 0;
}
.pagination-compact .pg-info strong { color: var(--bleu-france); font-weight: 600; }
.pagination-compact .pg-sep { margin: 0 2px; color: var(--text-muted); }
.pagination-compact .pg-range {
    font-size: 10.5px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}
.post-item {
    display: flex;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border);
    transition: all var(--duration) var(--ease);
    position: relative;
}
.post-item:hover { background: var(--surface-hover); }
.post-item.is-active {
    background: var(--bleu-france-soft);
}
.post-item.is-active::before {
    content: '';
    position: absolute;
    left: 0; top: 10px; bottom: 10px;
    width: 3px;
    background: var(--bleu-france);
    border-radius: 0 3px 3px 0;
}
.post-item__thumb {
    width: 52px;
    height: 52px;
    border-radius: var(--radius);
    object-fit: cover;
    background: var(--surface-subtle);
    flex-shrink: 0;
}
.post-item__thumb--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 20px;
    border: 1px solid var(--border);
}
.post-item__content { flex: 1; min-width: 0; }
.post-item__text {
    font-size: 13px;
    line-height: 1.4;
    color: var(--text-primary);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 6px;
}
.is-active .post-item__text { font-weight: 500; }
.post-item__meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    color: var(--text-muted);
}
.post-item__meta i { margin-right: 3px; }
.post-item__count {
    background: var(--bleu-france-soft);
    color: var(--bleu-france);
    padding: 2px 7px;
    border-radius: var(--radius-full);
    font-weight: 600;
}
</style>
