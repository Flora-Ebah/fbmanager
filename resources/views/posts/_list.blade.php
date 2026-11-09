<aside class="list-col {{ isset($post) ? '' : 'is-visible-mobile' }}">
    <div class="list-col__head">
        <form method="GET" action="/posts" class="list-col__search">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher un post..." />
        </form>
        @if($posts->hasPages())
        <div class="list-col__pagination">
            {{ $posts->appends(['search' => $search])->links('vendor.pagination.compact') }}
        </div>
        @endif
    </div>

    <div class="list-col__body" id="post-list" data-live-updates="posts">
        @forelse($posts as $p)
        @php
            $isActive = isset($post) && $post->post_id === $p->post_id;
            try { $timeAgo = \Carbon\Carbon::parse($p->temps_creer_post)->diffForHumans(['short' => true]); }
            catch (\Throwable $e) { $timeAgo = '—'; }
        @endphp
        <a href="/posts/{{ $p->post_id }}" class="post-item {{ $isActive ? 'is-active' : '' }}"
           data-post-id="{{ $p->post_id }}"
           data-time-iso="{{ $p->temps_creer_post }}"
           data-comments-count="{{ $p->commentaires_count }}">
            @if($p->image_url)
                <img src="{{ $p->image_url }}" class="post-item__thumb" loading="lazy" onerror="this.outerHTML='<div class=\'post-item__thumb post-item__thumb--placeholder\'><i class=\'ph ph-image\'></i></div>'" />
            @else
                <div class="post-item__thumb post-item__thumb--placeholder"><i class="ph ph-image"></i></div>
            @endif
            <div class="post-item__content">
                <div class="post-item__text">{{ \Illuminate\Support\Str::limit($p->message_post ?? 'Sans texte', 85) }}</div>
                <div class="post-item__meta">
                    <span><i class="ph ph-clock"></i> {{ $timeAgo }}</span>
                    @if($p->commentaires_count > 0)
                        <span class="post-item__count"><i class="ph ph-chat-circle"></i> {{ $p->commentaires_count }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:40px 20px;">
            <div class="empty-state__icon"><i class="ph ph-folder-open"></i></div>
            <div class="empty-state__title">Aucun post</div>
            <div class="empty-state__text">Rien trouvé avec cette recherche.</div>
        </div>
        @endforelse

    </div>
</aside>

<style>
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
