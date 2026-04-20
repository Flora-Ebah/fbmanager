@extends('layouts.app')
@section('title', 'Post #' . Str::limit($post->post_id, 15))

@section('content')
<div class="page-header">
    <h1><i class="fa-solid fa-newspaper"></i> Detail du Post</h1>
    <a href="/posts" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour aux posts</a>
</div>

<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        Post #{{ Str::limit($post->post_id, 30) }}
    </div>
    <div class="card-body">
        @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="Image du post" style="width:100%; max-height:400px; object-fit:cover; border-radius: var(--radius); margin-bottom: 16px;">
        @endif
        <p style="font-size: 16px; line-height: 1.7; margin-bottom: 16px;">{{ $post->message_post ?? 'Aucun contenu' }}</p>
        <div style="display:flex; gap:20px; flex-wrap:wrap; font-family:'VT323',monospace; font-size:18px; color:#888;">
            <span><i class="fa-regular fa-calendar"></i> {{ $post->temps_creer_post ?? '—' }}</span>
            @if($post->lien)
                <a href="{{ $post->lien }}" target="_blank" style="color: var(--bleu-france);"><i class="fa-solid fa-arrow-up-right-from-square"></i> Voir sur Facebook</a>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fa-solid fa-comments"></i> Commentaires ({{ $commentaires->total() }})
    </div>
    @if($commentaires->count() > 0)
        @foreach($commentaires as $c)
        @php $hasReplies = $c->aiReplies->count() > 0; @endphp
        <div class="comment-item">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <div style="flex:1; min-width:0;">
                    <div class="comment-author"><i class="fa-solid fa-user"></i> {{ $c->nom_auteur ?? 'Anonyme' }}</div>
                    <div class="comment-text">{{ $c->message_commentaire }}</div>
                    <div class="comment-date"><i class="fa-regular fa-clock"></i> {{ $c->temps_creer ?? '—' }}</div>
                </div>
                <div style="flex-shrink:0; display:flex; gap:6px; align-items:center;">
                    @if($hasReplies)
                        <span class="badge-ai-done" title="Reponse deja generee"><i class="fa-solid fa-check-circle"></i> {{ $c->aiReplies->count() }} reponse(s)</span>
                    @endif
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateReply('{{ $c->id_commentaire }}', this)">
                        <i class="fa-solid fa-robot"></i> {{ $hasReplies ? 'Regenerer' : 'Generer reponse IA' }}
                    </button>
                </div>
            </div>

            {{-- Historique des reponses deja generees --}}
            @if($hasReplies)
            @php
                $proReplies = $c->aiReplies->where('tone', 'professional');
                $friendlyReplies = $c->aiReplies->where('tone', 'friendly');
                $casualReplies = $c->aiReplies->where('tone', 'casual');
            @endphp
            <div class="ai-history" style="margin-top:10px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleHistory('{{ $c->id_commentaire }}')" style="font-size:14px;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Historique ({{ $c->aiReplies->count() }})
                    </button>
                    <div id="ai-history-tabs-{{ $c->id_commentaire }}" class="history-tabs" style="display:none;">
                        @if($proReplies->count() > 0)
                        <button type="button" class="history-tab history-tab-active" onclick="switchHistoryTab('{{ $c->id_commentaire }}', 'pro', this)">
                            <i class="fa-solid fa-briefcase"></i> Pro ({{ $proReplies->count() }})
                        </button>
                        @endif
                        @if($friendlyReplies->count() > 0)
                        <button type="button" class="history-tab" onclick="switchHistoryTab('{{ $c->id_commentaire }}', 'friendly', this)">
                            <i class="fa-solid fa-face-smile"></i> Amical ({{ $friendlyReplies->count() }})
                        </button>
                        @endif
                        @if($casualReplies->count() > 0)
                        <button type="button" class="history-tab" onclick="switchHistoryTab('{{ $c->id_commentaire }}', 'casual', this)">
                            <i class="fa-solid fa-comments"></i> Decontracte ({{ $casualReplies->count() }})
                        </button>
                        @endif
                    </div>
                </div>
                <div id="ai-history-{{ $c->id_commentaire }}" style="display:none; margin-top:8px;">
                    {{-- Onglet Pro --}}
                    @if($proReplies->count() > 0)
                    <div id="ai-history-{{ $c->id_commentaire }}-pro" class="history-panel">
                        @foreach($proReplies as $r)
                        <div class="ai-history-item">
                            <div class="ai-history-meta">
                                <span style="font-family:'VT323',monospace; font-size:15px; color:#888;">
                                    <i class="fa-solid fa-user"></i> {{ $r->user->username ?? '?' }} &middot; {{ $r->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="ai-history-text">{{ $r->reply }}</div>
                            <button type="button" class="btn btn-success btn-sm" onclick="copyText(this)" data-text="{{ addslashes($r->reply) }}" style="font-size:13px; margin-top:4px;">
                                <i class="fa-solid fa-copy"></i> Copier
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    {{-- Onglet Amical --}}
                    @if($friendlyReplies->count() > 0)
                    <div id="ai-history-{{ $c->id_commentaire }}-friendly" class="history-panel" style="display:none;">
                        @foreach($friendlyReplies as $r)
                        <div class="ai-history-item">
                            <div class="ai-history-meta">
                                <span style="font-family:'VT323',monospace; font-size:15px; color:#888;">
                                    <i class="fa-solid fa-user"></i> {{ $r->user->username ?? '?' }} &middot; {{ $r->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="ai-history-text">{{ $r->reply }}</div>
                            <button type="button" class="btn btn-success btn-sm" onclick="copyText(this)" data-text="{{ addslashes($r->reply) }}" style="font-size:13px; margin-top:4px;">
                                <i class="fa-solid fa-copy"></i> Copier
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    {{-- Onglet Decontracte --}}
                    @if($casualReplies->count() > 0)
                    <div id="ai-history-{{ $c->id_commentaire }}-casual" class="history-panel" style="display:none;">
                        @foreach($casualReplies as $r)
                        <div class="ai-history-item">
                            <div class="ai-history-meta">
                                <span style="font-family:'VT323',monospace; font-size:15px; color:#888;">
                                    <i class="fa-solid fa-user"></i> {{ $r->user->username ?? '?' }} &middot; {{ $r->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="ai-history-text">{{ $r->reply }}</div>
                            <button type="button" class="btn btn-success btn-sm" onclick="copyText(this)" data-text="{{ addslashes($r->reply) }}" style="font-size:13px; margin-top:4px;">
                                <i class="fa-solid fa-copy"></i> Copier
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Panel de nouvelle reponse IA --}}
            <div id="ai-panel-{{ $c->id_commentaire }}" style="display:none; margin-top:12px; padding:14px; background:#F0F4FF; border-radius:var(--radius); border-top:2px solid var(--bleu-ciel);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <span style="font-family:'VT323',monospace; font-size:20px; color:var(--bleu-france);"><i class="fa-solid fa-robot"></i> Reponse IA</span>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="tone-btn tone-active" onclick="changeTone('{{ $c->id_commentaire }}', 'professional', this)">Pro</button>
                        <button type="button" class="tone-btn" onclick="changeTone('{{ $c->id_commentaire }}', 'friendly', this)">Amical</button>
                        <button type="button" class="tone-btn" onclick="changeTone('{{ $c->id_commentaire }}', 'casual', this)">Decontracte</button>
                    </div>
                </div>
                <div id="ai-loading-{{ $c->id_commentaire }}" style="display:none; text-align:center; padding:20px; font-family:'VT323',monospace; font-size:20px; color:#888;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Generation en cours...
                </div>
                <div id="ai-result-{{ $c->id_commentaire }}" style="display:none;">
                    <div id="ai-text-{{ $c->id_commentaire }}" style="font-size:15px; line-height:1.6; padding:12px; background:var(--blanc); border-radius:4px; margin-bottom:10px;"></div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-success btn-sm" onclick="copyReply('{{ $c->id_commentaire }}')">
                            <i class="fa-solid fa-copy"></i> Copier
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="generateReply('{{ $c->id_commentaire }}', null)">
                            <i class="fa-solid fa-rotate"></i> Regenerer
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="closePanel('{{ $c->id_commentaire }}')">
                            <i class="fa-solid fa-xmark"></i> Fermer
                        </button>
                    </div>
                </div>
                <div id="ai-error-{{ $c->id_commentaire }}" style="display:none; color:var(--rouge-france); font-family:'VT323',monospace; font-size:18px;"></div>
            </div>
        </div>
        @endforeach

        {{ $commentaires->links() }}
    @else
        <div class="empty-state">Aucun commentaire pour ce post.</div>
    @endif
</div>

<style>
    .tone-btn {
        font-family: 'VT323', monospace;
        font-size: 16px;
        padding: 4px 12px;
        border: 1px solid var(--bleu-ciel);
        border-radius: 20px;
        background: var(--blanc);
        color: var(--bleu-france);
        cursor: pointer;
        transition: all 0.15s;
    }
    .tone-btn:hover { background: var(--bleu-ciel); color: var(--blanc); }
    .tone-active { background: var(--bleu-france) !important; color: var(--blanc) !important; border-color: var(--bleu-france) !important; }

    .badge-ai-done {
        font-family: 'VT323', monospace;
        font-size: 16px;
        color: var(--vert-ok);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .history-tabs {
        display: inline-flex;
        gap: 4px;
        align-items: center;
    }
    .history-tab {
        font-family: 'VT323', monospace;
        font-size: 15px;
        padding: 3px 10px;
        border: 1px solid var(--gris-moyen);
        border-radius: 4px;
        background: var(--blanc);
        color: #666;
        cursor: pointer;
        transition: all 0.15s;
    }
    .history-tab:hover { background: #f0f0f0; }
    .history-tab-active {
        background: var(--bleu-france) !important;
        color: var(--blanc) !important;
        border-color: var(--bleu-france) !important;
    }

    .ai-history-item {
        padding: 10px;
        margin-bottom: 8px;
        background: #FAFAFA;
        border-radius: 4px;
        border-left: 3px solid var(--bleu-ciel);
    }
    .ai-history-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .ai-history-text {
        font-size: 14px;
        line-height: 1.5;
        color: #555;
    }

    .copy-success {
        animation: copyFlash 0.6s ease;
    }
    @keyframes copyFlash {
        0% { background: var(--vert-doux); }
        100% { background: var(--blanc); }
    }
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
        .then(function(r) { return r.json(); })
        .then(function(data) {
            loading.style.display = 'none';
            if (data.success) {
                document.getElementById('ai-text-' + commentId).textContent = data.reply;
                result.style.display = 'block';
            } else {
                error.textContent = 'Erreur : ' + (data.message || 'Erreur inconnue');
                error.style.display = 'block';
            }
        })
        .catch(function(err) {
            loading.style.display = 'none';
            error.textContent = 'Erreur de connexion : ' + err.message;
            error.style.display = 'block';
        });
    }

    function changeTone(commentId, tone, btn) {
        currentTones[commentId] = tone;
        var btns = btn.parentElement.querySelectorAll('.tone-btn');
        btns.forEach(function(b) { b.classList.remove('tone-active'); });
        btn.classList.add('tone-active');
        generateReply(commentId, null);
    }

    function copyReply(commentId) {
        var text = document.getElementById('ai-text-' + commentId).textContent;
        navigator.clipboard.writeText(text).then(function() {
            var el = document.getElementById('ai-text-' + commentId);
            el.classList.add('copy-success');
            setTimeout(function() { el.classList.remove('copy-success'); }, 700);
        });
    }

    function copyText(btn) {
        var text = btn.getAttribute('data-text').replace(/\\'/g, "'");
        navigator.clipboard.writeText(text).then(function() {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copie !';
            setTimeout(function() { btn.innerHTML = orig; }, 1000);
        });
    }

    function closePanel(commentId) {
        document.getElementById('ai-panel-' + commentId).style.display = 'none';
    }

    function toggleHistory(commentId) {
        var el = document.getElementById('ai-history-' + commentId);
        var tabs = document.getElementById('ai-history-tabs-' + commentId);
        var isHidden = el.style.display === 'none';
        el.style.display = isHidden ? 'block' : 'none';
        if (tabs) tabs.style.display = isHidden ? 'inline-flex' : 'none';
    }

    function switchHistoryTab(commentId, tab, btn) {
        // Masquer tous les panels
        var panels = document.querySelectorAll('#ai-history-' + commentId + ' .history-panel');
        panels.forEach(function(p) { p.style.display = 'none'; });

        // Afficher le panel selectionne
        var target = document.getElementById('ai-history-' + commentId + '-' + tab);
        if (target) target.style.display = 'block';

        // Activer le bon onglet
        var allTabs = btn.parentElement.querySelectorAll('.history-tab');
        allTabs.forEach(function(t) { t.classList.remove('history-tab-active'); });
        btn.classList.add('history-tab-active');
    }
</script>
@endsection
