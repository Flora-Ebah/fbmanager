@extends('layouts.app')
@section('title', $conversation->nom_expediteur ?? 'Conversation')

@section('main')
@include('messenger._list')

@php
    $initials = strtoupper(substr($conversation->nom_expediteur ?? '?', 0, 2));
    $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
    $color = $colors[abs(crc32($conversation->nom_expediteur ?? '?')) % count($colors)];
@endphp

<main class="content is-visible-mobile">
    {{-- ─── Header conversation ─── --}}
    <div class="top-bar">
        <div class="top-bar__title">
            <a href="/messenger" class="btn btn-ghost btn-icon btn-sm" style="margin-right:4px;"><i class="fa-solid fa-arrow-left"></i></a>
            <div class="chat-header-avatar" style="background:{{ $color }}">{{ $initials }}</div>
            <div>
                <div style="font-family:'Sora',sans-serif; font-weight:600; font-size:15px;">{{ $conversation->nom_expediteur ?? 'Utilisateur Facebook' }}</div>
                <div class="breadcrumb">{{ $conversation->nombre_messages }} message(s)</div>
            </div>
        </div>
        <div class="top-bar__actions">
            <span class="badge {{ $conversation->statut === 'non_lu' ? 'badge-nonlu' : 'badge-lu' }}">
                {{ $conversation->statut === 'non_lu' ? 'Non lu' : 'Lu' }}
            </span>
        </div>
    </div>

    {{-- ─── Chat body ─── --}}
    <div class="chat-body" id="chat-body" data-conversation-id="{{ $conversation->conversation_id }}">
        @if($hasMore)
            <div class="chat-load-more" id="load-more-trigger">
                <button id="load-more-btn" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-up"></i> Charger les messages plus anciens
                </button>
                <div id="loading-spinner" style="display:none; padding:10px; color:var(--text-muted);">
                    <i class="fa-solid fa-spinner fa-spin"></i> Chargement...
                </div>
            </div>
        @endif

        <div id="messages-container">
            @if($messages->count() > 0)
                @foreach($messages as $msg)
                    @include('messenger.partials.message', ['msg' => $msg, 'pageId' => $pageId])
                @endforeach
            @else
                <div class="chat-empty">
                    <i class="fa-regular fa-comment-dots"></i>
                    <p>Aucun message dans cette conversation.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Zone IA en bas ─── --}}
    <div class="chat-ai-bar">
        <div class="ai-actions-row">
            <button type="button" class="btn-ai" onclick="suggestAi('professional')">
                <i class="fa-solid fa-robot"></i> Pro
            </button>
            <button type="button" class="btn-ai" onclick="suggestAi('friendly')">
                <i class="fa-solid fa-face-smile"></i> Amical
            </button>
            <button type="button" class="btn-ai" onclick="suggestAi('casual')">
                <i class="fa-solid fa-comment"></i> Décontracté
            </button>
        </div>
        <div id="ai-result" class="ai-result" style="display:none;">
            <div class="ai-result-head">
                <span><i class="fa-solid fa-robot"></i> Suggestion <span id="ai-tone-label" class="badge badge-user" style="margin-left:4px;"></span></span>
                <button type="button" class="btn btn-ghost btn-sm" onclick="copyAiReply()" title="Copier">
                    <i class="fa-solid fa-copy"></i>
                </button>
            </div>
            <div id="ai-reply-text" class="ai-result-text"></div>
        </div>
        <div id="ai-loading" class="ai-result-loading" style="display:none;">
            <i class="fa-solid fa-spinner fa-spin"></i> Génération en cours...
        </div>
        <div id="ai-error" class="ai-result-error" style="display:none;"></div>
    </div>
</main>

<style>
    .chat-header-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
    }

    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px;
        background:
            radial-gradient(circle at 20% 20%, rgba(0, 35, 149, 0.025) 0%, transparent 60%),
            radial-gradient(circle at 80% 80%, rgba(237, 41, 57, 0.02) 0%, transparent 60%),
            var(--canvas);
    }
    .chat-body::-webkit-scrollbar { width: 6px; }
    .chat-body::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 3px; }

    .chat-load-more { text-align: center; margin-bottom: 16px; }
    .chat-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    .chat-empty i { font-size: 48px; margin-bottom: 12px; display: block; }

    /* Messages bubbles */
    .msg-row {
        display: flex;
        margin-bottom: 10px;
        align-items: flex-end;
        gap: 8px;
    }
    .msg-row.is-page { justify-content: flex-end; }
    .msg-row.is-user { justify-content: flex-start; }
    .msg-mini-avatar {
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 11px;
        flex-shrink: 0;
    }
    .msg-content { max-width: 65%; }
    .msg-bubble {
        padding: 10px 14px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.45;
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    .is-user .msg-bubble {
        background: var(--surface);
        color: var(--text-primary);
        border: 1px solid var(--border);
        border-bottom-left-radius: 4px;
    }
    .is-page .msg-bubble {
        background: linear-gradient(135deg, var(--bleu-france), var(--bleu-france-dark));
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-meta {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 4px;
        padding: 0 6px;
    }
    .is-page .msg-meta { text-align: right; }

    /* AI bar */
    .chat-ai-bar {
        border-top: 1px solid var(--border);
        padding: 14px 20px;
        background: var(--surface);
    }
    .ai-actions-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-ai {
        background: var(--surface-subtle);
        border: 1px solid var(--border);
        color: var(--text-primary);
        padding: 8px 16px;
        border-radius: var(--radius);
        font-family: inherit;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all var(--duration) var(--ease);
    }
    .btn-ai:hover {
        background: var(--bleu-france);
        color: #fff;
        border-color: var(--bleu-france);
    }
    .btn-ai:disabled { opacity: 0.5; cursor: not-allowed; }

    .ai-result {
        margin-top: 12px;
        background: linear-gradient(135deg, #EFF6FF, #F5F3FF);
        border: 1px solid #C7D2FE;
        border-radius: var(--radius);
        padding: 12px 14px;
    }
    .ai-result-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--bleu-france);
    }
    .ai-result-text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--text-primary);
        white-space: pre-wrap;
    }
    .ai-result-loading {
        margin-top: 10px;
        padding: 10px;
        text-align: center;
        color: var(--text-muted);
        font-size: 13px;
    }
    .ai-result-error {
        margin-top: 10px;
        padding: 10px 14px;
        background: var(--rouge-doux);
        border: 1px solid #FECACA;
        color: #991B1B;
        border-radius: var(--radius);
        font-size: 13px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBody = document.getElementById('chat-body');
        const messagesContainer = document.getElementById('messages-container');
        const loadMoreBtn = document.getElementById('load-more-btn');
        const loadingSpinner = document.getElementById('loading-spinner');
        const conversationId = chatBody.dataset.conversationId;

        chatBody.scrollTop = chatBody.scrollHeight;

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', async function() {
                const firstMsg = messagesContainer.querySelector('[data-msg-id]');
                if (!firstMsg) return;
                const beforeId = firstMsg.dataset.msgId;
                loadMoreBtn.style.display = 'none';
                loadingSpinner.style.display = 'block';
                const oldHeight = chatBody.scrollHeight;

                try {
                    const res = await fetch(`/messenger/${conversationId}?before_id=${beforeId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.messages && data.messages.length > 0) {
                        const html = data.messages.map(msg => renderMessage(msg, data.page_id)).join('');
                        messagesContainer.insertAdjacentHTML('afterbegin', html);
                        chatBody.scrollTop = chatBody.scrollHeight - oldHeight;
                        loadMoreBtn.style.display = data.messages.length === 30 ? 'inline-flex' : 'none';
                    } else {
                        document.getElementById('load-more-trigger').remove();
                    }
                } catch (e) {
                    loadMoreBtn.style.display = 'inline-flex';
                } finally {
                    loadingSpinner.style.display = 'none';
                }
            });
        }

        function renderMessage(msg, pageId) {
            const isPage = msg.nom_expediteur && (msg.nom_expediteur.toLowerCase().includes('sodeci') || msg.statut === 'lu');
            const cls = isPage ? 'is-page' : 'is-user';
            const time = msg.temps_envoi ? new Date(msg.temps_envoi).toLocaleString('fr-FR', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
            const initials = (msg.nom_expediteur || '?').substring(0,2).toUpperCase();
            const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'];
            const c = colors[Math.abs(hashCode(msg.nom_expediteur || '?')) % colors.length];
            const avatar = !isPage ? `<div class="msg-mini-avatar" style="background:${c}">${escapeHtml(initials)}</div>` : '';
            return `<div class="msg-row ${cls}" data-msg-id="${msg.id}">
                ${avatar}
                <div class="msg-content">
                    <div class="msg-bubble">${escapeHtml(msg.message)}</div>
                    <div class="msg-meta">${time}</div>
                </div>
            </div>`;
        }

        function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
        function hashCode(str) { let h=0; for(let i=0;i<str.length;i++){h=((h<<5)-h)+str.charCodeAt(i);h|=0;} return h; }
    });

    async function suggestAi(tone) {
        const conversationId = document.getElementById('chat-body').dataset.conversationId;
        const buttons = document.querySelectorAll('.btn-ai');
        const result = document.getElementById('ai-result');
        const loading = document.getElementById('ai-loading');
        const error = document.getElementById('ai-error');

        buttons.forEach(b => b.disabled = true);
        result.style.display = 'none';
        error.style.display = 'none';
        loading.style.display = 'block';

        try {
            const res = await fetch('/ai/suggest-messenger', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ conversation_id: conversationId, tone: tone }),
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('ai-tone-label').textContent = tone;
                document.getElementById('ai-reply-text').textContent = data.reply;
                result.style.display = 'block';
            } else {
                error.textContent = data.error || 'Erreur lors de la génération.';
                error.style.display = 'block';
            }
        } catch (e) {
            error.textContent = 'Erreur réseau : ' + e.message;
            error.style.display = 'block';
        } finally {
            loading.style.display = 'none';
            buttons.forEach(b => b.disabled = false);
        }
    }

    function copyAiReply() {
        const text = document.getElementById('ai-reply-text').textContent;
        navigator.clipboard.writeText(text);
    }
</script>

@include('partials.freshness-indicator')
@endsection
