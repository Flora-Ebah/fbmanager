@extends('layouts.app')
@section('title', 'Conversation avec ' . ($conversation->nom_expediteur ?? 'Utilisateur Facebook'))

@section('content')
@php
    $initials = strtoupper(substr($conversation->nom_expediteur ?? '?', 0, 2));
    $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
    $color = $colors[crc32($conversation->nom_expediteur ?? '?') % count($colors)];
@endphp

<div class="chat-wrapper">
    {{-- Header de la conversation --}}
    <div class="chat-header">
        <a href="/messenger" class="chat-back" title="Retour"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="chat-avatar" style="background:{{ $color }}">{{ $initials }}</div>
        <div class="chat-header-info">
            <div class="chat-header-name">{{ $conversation->nom_expediteur ?? 'Utilisateur Facebook' }}</div>
            <div class="chat-header-status">
                <span class="badge {{ $conversation->statut === 'non_lu' ? 'badge-nonlu' : 'badge-lu' }}">
                    {{ $conversation->statut === 'non_lu' ? 'NON LU' : 'LU' }}
                </span>
                <span style="margin-left:8px; color:#9CA3AF; font-size:12px;">{{ $conversation->nombre_messages }} message(s)</span>
            </div>
        </div>
    </div>

    {{-- Zone des messages avec scroll --}}
    <div class="chat-body" id="chat-body" data-conversation-id="{{ $conversation->conversation_id }}">
        @if($hasMore)
            <div class="chat-load-more" id="load-more-trigger">
                <button id="load-more-btn" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-up"></i> Charger les messages plus anciens
                </button>
                <div id="loading-spinner" style="display:none; padding:10px; color:#9CA3AF;">
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

    {{-- Zone IA --}}
    <div class="chat-ai-bar">
        <div class="ai-actions">
            <button type="button" class="btn-ai" data-tone="professional" onclick="suggestAi('professional')">
                <i class="fa-solid fa-robot"></i> Réponse pro
            </button>
            <button type="button" class="btn-ai" data-tone="friendly" onclick="suggestAi('friendly')">
                <i class="fa-solid fa-face-smile"></i> Amicale
            </button>
            <button type="button" class="btn-ai" data-tone="casual" onclick="suggestAi('casual')">
                <i class="fa-solid fa-comment"></i> Décontractée
            </button>
        </div>
        <div id="ai-result" class="ai-result" style="display:none;">
            <div class="ai-result-header">
                <span><i class="fa-solid fa-robot"></i> Suggestion IA <span id="ai-tone-label" class="ai-tone-pill"></span></span>
                <button type="button" class="ai-copy-btn" onclick="copyAiReply()" title="Copier">
                    <i class="fa-solid fa-copy"></i>
                </button>
            </div>
            <div id="ai-reply-text" class="ai-reply-text"></div>
        </div>
        <div id="ai-loading" class="ai-loading" style="display:none;">
            <i class="fa-solid fa-spinner fa-spin"></i> Génération en cours...
        </div>
        <div id="ai-error" class="ai-error" style="display:none;"></div>
    </div>
</div>

<style>
    .main-content { padding: 20px 40px !important; }

    .chat-wrapper {
        background: #fff;
        border: none;
        border-radius: 0;
        overflow: hidden;
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--gris-moyen);
        background: #fff;
    }
    .chat-back {
        width: 36px; height: 36px;
        background: #F3F4F6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: var(--gris-fonce);
        transition: background 0.15s;
    }
    .chat-back:hover { background: #E5E7EB; }
    .chat-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 15px;
        flex-shrink: 0;
        font-family: 'Outfit', sans-serif;
    }
    .chat-header-info { flex: 1; }
    .chat-header-name { font-weight: 600; font-size: 16px; color: var(--gris-fonce); }
    .chat-header-status { margin-top: 3px; display: flex; align-items: center; }

    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #F9FAFB;
        background-image:
            radial-gradient(circle at 25% 25%, rgba(59,130,246,0.04) 0%, transparent 50%),
            radial-gradient(circle at 75% 75%, rgba(139,92,246,0.04) 0%, transparent 50%);
    }

    .chat-load-more {
        text-align: center;
        margin-bottom: 16px;
    }

    .chat-empty {
        text-align: center;
        padding: 60px 20px;
        color: #9CA3AF;
    }
    .chat-empty i { font-size: 48px; margin-bottom: 12px; display: block; }

    /* ── Messages bubbles ── */
    .msg-row {
        display: flex;
        margin-bottom: 12px;
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
        font-family: 'Outfit', sans-serif;
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
        background: #fff;
        color: var(--gris-fonce);
        border: 1px solid #E5E7EB;
        border-bottom-left-radius: 4px;
    }
    .is-page .msg-bubble {
        background: linear-gradient(135deg, #002395 0%, #0041d6 100%);
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .msg-meta {
        font-size: 11px;
        color: #9CA3AF;
        margin-top: 4px;
        padding: 0 6px;
    }
    .is-page .msg-meta { text-align: right; }

    /* ── Date separator ── */
    .msg-date-sep {
        text-align: center;
        margin: 20px 0 14px;
        position: relative;
    }
    .msg-date-sep span {
        background: #F9FAFB;
        padding: 2px 14px;
        font-size: 12px;
        color: #6B7280;
        font-weight: 500;
    }

    /* ── AI bar ── */
    .chat-ai-bar {
        border-top: 1px solid var(--gris-moyen);
        padding: 14px 20px;
        background: #fff;
    }
    .ai-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-ai {
        background: #F3F4F6;
        border: 1px solid #E5E7EB;
        color: var(--gris-fonce);
        padding: 8px 14px;
        border-radius: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
    }
    .btn-ai:hover {
        background: var(--bleu-france);
        color: #fff;
        border-color: var(--bleu-france);
    }
    .btn-ai:disabled { opacity: 0.5; cursor: not-allowed; }

    .ai-result {
        margin-top: 12px;
        background: linear-gradient(135deg, #EFF6FF 0%, #F5F3FF 100%);
        border: 1px solid #C7D2FE;
        border-radius: 10px;
        padding: 12px 14px;
    }
    .ai-result-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--bleu-france);
    }
    .ai-tone-pill {
        background: var(--bleu-france);
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        margin-left: 6px;
        text-transform: uppercase;
    }
    .ai-copy-btn {
        background: none;
        border: 1px solid #C7D2FE;
        color: var(--bleu-france);
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        transition: all 0.15s;
    }
    .ai-copy-btn:hover { background: var(--bleu-france); color: #fff; }
    .ai-reply-text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--gris-fonce);
        white-space: pre-wrap;
    }
    .ai-loading {
        margin-top: 10px;
        padding: 10px;
        text-align: center;
        color: #6B7280;
        font-size: 13px;
    }
    .ai-error {
        margin-top: 10px;
        padding: 10px 14px;
        background: #FEE2E2;
        border: 1px solid #FECACA;
        color: #991B1B;
        border-radius: 8px;
        font-size: 13px;
    }

    /* Custom scrollbar */
    .chat-body::-webkit-scrollbar { width: 6px; }
    .chat-body::-webkit-scrollbar-track { background: transparent; }
    .chat-body::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
    .chat-body::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }

    @media (max-width: 768px) {
        .main-content { padding: 0 !important; max-width: 100% !important; }
        .chat-wrapper { height: calc(100vh - 64px); border-radius: 0; border: none; }
        .msg-content { max-width: 80%; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatBody = document.getElementById('chat-body');
        const messagesContainer = document.getElementById('messages-container');
        const loadMoreBtn = document.getElementById('load-more-btn');
        const loadingSpinner = document.getElementById('loading-spinner');
        const conversationId = chatBody.dataset.conversationId;

        // Auto-scroll en bas a l'ouverture
        chatBody.scrollTop = chatBody.scrollHeight;

        // Charger plus d'anciens messages
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
                        // Maintenir la position de scroll
                        chatBody.scrollTop = chatBody.scrollHeight - oldHeight;
                        loadMoreBtn.style.display = data.messages.length === 30 ? 'inline-flex' : 'none';
                    } else {
                        document.getElementById('load-more-trigger').remove();
                    }
                } catch (e) {
                    console.error('Erreur chargement:', e);
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

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }
        function hashCode(str) { let h=0; for(let i=0;i<str.length;i++){h=((h<<5)-h)+str.charCodeAt(i);h|=0;} return h; }
    });

    // ─── Suggestion IA ───
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
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('.ai-copy-btn');
            const old = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            setTimeout(() => { btn.innerHTML = old; }, 1500);
        });
    }
</script>
@endsection
