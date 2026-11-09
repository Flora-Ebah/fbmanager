@extends('layouts.app')
@section('title', 'Post #' . Str::limit($post->post_id, 15))

@section('main')
@include('posts._list')

<main class="content is-visible-mobile">
    <div class="top-bar">
        <div class="top-bar__title">
            <a href="/posts" class="btn btn-ghost btn-icon btn-sm" style="margin-right:4px;" title="Retour"><i class="ph ph-arrow-left"></i></a>
            <i class="ph ph-newspaper" style="color:var(--bleu-france);"></i>
            Publication
            <span class="breadcrumb">· {{ $commentaires->total() }} commentaire(s)</span>
        </div>
        <div class="top-bar__actions">
            <div class="fresh-pill" id="fresh-pill">
                <span class="fresh-pill__dot"></span>
                <span class="fresh-pill__text" id="fresh-pill-text">Chargement...</span>
            </div>
            @if($post->lien)
                <a href="{{ $post->lien }}" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="ph ph-arrow-up-right-from-square"></i> Voir sur Facebook
                </a>
            @endif
        </div>
    </div>

    <div class="content-full">
        <div class="content-full__inner">
            {{-- ─── POST ─── --}}
            <article class="post-detail">
                @if($post->image_url)
                    <img src="{{ $post->image_url }}" alt="" class="post-detail__image" onerror="this.style.display='none'">
                @endif
                <div class="post-detail__body">
                    <p class="post-detail__text">{{ $post->message_post ?? 'Aucun contenu' }}</p>
                    <div class="post-detail__meta">
                        @php
                            try { $t = \Carbon\Carbon::parse($post->temps_creer_post)->format('d M Y, H:i'); } catch(\Throwable $e) { $t = '—'; }
                        @endphp
                        <span><i class="ph ph-calendar"></i> {{ $t }}</span>
                    </div>
                </div>
            </article>

            {{-- ─── COMMENTAIRES ─── --}}
            <h2 class="section-title">
                <i class="ph ph-chat-circle"></i> Commentaires
                <span class="section-title__count">{{ $commentaires->total() }}</span>
            </h2>

            @if($commentaires->count() > 0)
                <div class="comments-list">
                    @foreach($commentaires as $c)
                    @php
                        $hasReplies = $c->aiReplies->count() > 0;
                        $initials = strtoupper(substr($c->nom_auteur ?? '?', 0, 2));
                        $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
                        $color = $colors[abs(crc32($c->nom_auteur ?? '?')) % count($colors)];
                        try { $time = \Carbon\Carbon::parse($c->temps_creer)->format('d M Y, H:i'); } catch(\Throwable $e) { $time = $c->temps_creer ?? '—'; }
                    @endphp
                    <div class="comment">
                        <div class="comment__avatar" style="background:{{ $color }}">{{ $initials }}</div>
                        <div class="comment__body">
                            <div class="comment__head">
                                <div>
                                    <span class="comment__author">{{ $c->nom_auteur ?? 'Utilisateur Facebook' }}</span>
                                    <span class="comment__time">· {{ $time }}</span>
                                </div>
                                <div class="comment__actions">
                                    @if($hasReplies)
                                        <button type="button" class="btn btn-ghost btn-sm" onclick="toggleHistory('{{ $c->id_commentaire }}')">
                                            <i class="ph ph-clock-rotate-left"></i> Historique ({{ $c->aiReplies->count() }})
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-primary btn-sm" onclick="generateReply('{{ $c->id_commentaire }}', this)">
                                        <i class="ph ph-robot"></i> {{ $hasReplies ? 'Régénérer' : 'Répondre avec IA' }}
                                    </button>
                                </div>
                            </div>
                            <div class="comment__text">{{ $c->message_commentaire }}</div>

                            {{-- Panel réponse IA --}}
                            <div id="ai-panel-{{ $c->id_commentaire }}" class="ai-panel" style="display:none;">
                                <div class="ai-panel__head">
                                    <div class="ai-panel__title"><i class="ph ph-robot"></i> Suggestion IA</div>
                                    <div class="tone-selector">
                                        <button type="button" class="tone-btn tone-active" onclick="changeTone('{{ $c->id_commentaire }}', 'professional', this)">Pro</button>
                                        <button type="button" class="tone-btn" onclick="changeTone('{{ $c->id_commentaire }}', 'friendly', this)">Amical</button>
                                        <button type="button" class="tone-btn" onclick="changeTone('{{ $c->id_commentaire }}', 'casual', this)">Décontracté</button>
                                    </div>
                                </div>
                                <div id="ai-loading-{{ $c->id_commentaire }}" class="ai-loading" style="display:none;">
                                    <i class="ph ph-spinner ph-spin"></i> Génération en cours...
                                </div>
                                <div id="ai-result-{{ $c->id_commentaire }}" style="display:none;">
                                    <div id="ai-text-{{ $c->id_commentaire }}" class="ai-text"></div>
                                    <div class="ai-actions">
                                        <button type="button" class="btn btn-success btn-sm" onclick="copyReply('{{ $c->id_commentaire }}')">
                                            <i class="ph ph-copy"></i> Copier
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="generateReply('{{ $c->id_commentaire }}', null)">
                                            <i class="ph ph-arrows-clockwise"></i> Régénérer
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-sm" onclick="closePanel('{{ $c->id_commentaire }}')">
                                            <i class="ph ph-x"></i> Fermer
                                        </button>
                                    </div>
                                </div>
                                <div id="ai-error-{{ $c->id_commentaire }}" class="ai-error" style="display:none;"></div>
                            </div>

                            {{-- Historique --}}
                            @if($hasReplies)
                            <div id="ai-history-{{ $c->id_commentaire }}" class="ai-history" style="display:none;">
                                @foreach($c->aiReplies as $r)
                                <div class="ai-history__item">
                                    <div class="ai-history__meta">
                                        <span class="badge badge-user">{{ strtoupper($r->tone) }}</span>
                                        <span>{{ $r->user->username ?? '?' }} · {{ $r->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="ai-history__text">{{ $r->reply }}</div>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="copyText(this)" data-text="{{ addslashes($r->reply) }}">
                                        <i class="ph ph-copy"></i> Copier
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pagination-wrapper" style="margin-top:20px; border-top:none;">
                    {{ $commentaires->links() }}
                </div>
            @else
                <div class="empty-state" style="padding:40px;">
                    <div class="empty-state__icon"><i class="ph ph-chat-circle"></i></div>
                    <div class="empty-state__title">Aucun commentaire</div>
                    <div class="empty-state__text">Ce post n'a pas encore de commentaires.</div>
                </div>
            @endif
        </div>
    </div>
</main>

<style>
    .post-detail {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }
    .post-detail__image {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        display: block;
    }
    .post-detail__body { padding: 20px 24px; }
    .post-detail__text {
        font-size: 15px;
        line-height: 1.65;
        color: var(--text-primary);
        white-space: pre-wrap;
        margin-bottom: 16px;
    }
    .post-detail__meta {
        display: flex;
        gap: 16px;
        font-size: 12px;
        color: var(--text-muted);
    }

    .section-title {
        font-family: 'Sora', sans-serif;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        color: var(--text-primary);
    }
    .section-title__count {
        background: var(--bleu-france-soft);
        color: var(--bleu-france);
        padding: 2px 10px;
        border-radius: var(--radius-full);
        font-size: 12px;
        font-weight: 600;
    }

    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .comment {
        display: flex;
        gap: 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 14px 16px;
        transition: all var(--duration) var(--ease);
    }
    .comment:hover { border-color: var(--border-strong); }
    .comment__avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
        flex-shrink: 0;
    }
    .comment__body { flex: 1; min-width: 0; }
    .comment__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }
    .comment__author { font-weight: 600; font-size: 14px; color: var(--text-primary); }
    .comment__time { font-size: 12px; color: var(--text-muted); margin-left: 4px; }
    .comment__actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .comment__text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--text-primary);
        white-space: pre-wrap;
    }

    /* AI panel */
    .ai-panel {
        margin-top: 12px;
        background: linear-gradient(135deg, #EFF6FF 0%, #F5F3FF 100%);
        border: 1px solid #C7D2FE;
        border-radius: var(--radius);
        padding: 14px;
    }
    .ai-panel__head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .ai-panel__title {
        font-size: 13px;
        font-weight: 600;
        color: var(--bleu-france);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tone-selector { display: flex; gap: 4px; }
    .tone-btn {
        font-size: 11px;
        font-weight: 500;
        padding: 4px 10px;
        border: 1px solid #C7D2FE;
        border-radius: var(--radius-full);
        background: #fff;
        color: var(--bleu-france);
        cursor: pointer;
        transition: all var(--duration) var(--ease);
    }
    .tone-btn:hover { background: var(--bleu-france-soft); }
    .tone-active { background: var(--bleu-france) !important; color: #fff !important; border-color: var(--bleu-france) !important; }
    .ai-loading { text-align: center; padding: 16px; color: var(--text-secondary); font-size: 13px; }
    .ai-text {
        font-size: 14px;
        line-height: 1.55;
        padding: 12px 14px;
        background: #fff;
        border-radius: var(--radius-sm);
        margin-bottom: 10px;
        white-space: pre-wrap;
    }
    .ai-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .ai-error {
        background: var(--rouge-doux);
        color: #991B1B;
        padding: 10px 12px;
        border-radius: var(--radius-sm);
        font-size: 13px;
    }

    /* Historique */
    .ai-history {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed var(--border);
    }
    .ai-history__item {
        padding: 10px 12px;
        background: var(--surface-subtle);
        border-radius: var(--radius-sm);
        border-left: 3px solid var(--bleu-france);
        margin-bottom: 8px;
    }
    .ai-history__meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        font-size: 11px;
        color: var(--text-muted);
    }
    .ai-history__text {
        font-size: 13px;
        line-height: 1.5;
        color: var(--text-primary);
        margin-bottom: 8px;
        white-space: pre-wrap;
    }

    .copy-flash { animation: copyFlash 0.6s ease; }
    @keyframes copyFlash { 0% { background: var(--vert-doux); } 100% { background: #fff; } }
</style>

<script>
    var currentTones = {};

    function generateReply(commentId, btn) {
        var panel = document.getElementById('ai-panel-' + commentId);
        var loading = document.getElementById('ai-loading-' + commentId);
        var result = document.getElementById('ai-result-' + commentId);
        var error = document.getElementById('ai-error-' + commentId);
        var tone = currentTones[commentId] || 'professional';

        panel.style.display = 'block';
        loading.style.display = 'block';
        result.style.display = 'none';
        error.style.display = 'none';

        fetch('/ai/suggest-reply', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ comment_id: commentId, tone: tone })
        })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            if (data.success) {
                document.getElementById('ai-text-' + commentId).textContent = data.reply;
                result.style.display = 'block';
            } else {
                error.textContent = 'Erreur : ' + (data.message || 'Erreur inconnue');
                error.style.display = 'block';
            }
        })
        .catch(err => {
            loading.style.display = 'none';
            error.textContent = 'Erreur réseau : ' + err.message;
            error.style.display = 'block';
        });
    }

    function changeTone(commentId, tone, btn) {
        currentTones[commentId] = tone;
        btn.parentElement.querySelectorAll('.tone-btn').forEach(b => b.classList.remove('tone-active'));
        btn.classList.add('tone-active');
        generateReply(commentId, null);
    }

    function copyReply(commentId) {
        var text = document.getElementById('ai-text-' + commentId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            var el = document.getElementById('ai-text-' + commentId);
            el.classList.add('copy-flash');
            setTimeout(() => el.classList.remove('copy-flash'), 700);
        });
    }

    function copyText(btn) {
        var text = btn.getAttribute('data-text').replace(/\\'/g, "'");
        navigator.clipboard.writeText(text).then(() => {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="ph ph-check"></i> Copié';
            setTimeout(() => btn.innerHTML = orig, 1000);
        });
    }

    function closePanel(commentId) {
        document.getElementById('ai-panel-' + commentId).style.display = 'none';
    }

    function toggleHistory(commentId) {
        var el = document.getElementById('ai-history-' + commentId);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
</script>

@include('partials.freshness-indicator')
@endsection
