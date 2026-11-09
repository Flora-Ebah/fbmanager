{{-- Live updates (poll AJAX, style WhatsApp Web) --}}
<script>
(function() {
    const path = window.location.pathname;
    const isMessengerPage = path.startsWith('/messenger');
    const isPostsPage = path.startsWith('/posts');
    if (!isMessengerPage && !isPostsPage) return;

    const LIST_POLL_MS = 15000;    // Liste: 15s
    const MESSAGES_POLL_MS = 10000; // Messages dans une conversation: 10s

    // ─── Utilitaires ─────────────────────────────────────────────
    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function hashCode(str) { let h=0; for(let i=0;i<str.length;i++){h=((h<<5)-h)+str.charCodeAt(i);h|=0;} return h; }
    function formatTimeShort(iso) {
        if (!iso) return '—';
        const d = new Date(iso.replace(' ', 'T'));
        const s = Math.floor((Date.now() - d.getTime()) / 1000);
        if (s < 60) return s + 's';
        if (s < 3600) return Math.floor(s/60) + 'min';
        if (s < 86400) return Math.floor(s/3600) + 'h';
        return Math.floor(s/86400) + 'j';
    }

    // ─── LISTE MESSENGER: mise à jour live ───────────────────────
    async function updateMessengerList() {
        const container = document.getElementById('convo-list');
        if (!container) return;
        try {
            const res = await fetch('/messenger/live-list', { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!data.conversations) return;

            const existing = new Map();
            container.querySelectorAll('.convo-item').forEach(el => existing.set(el.dataset.convId, el));
            const activeId = container.querySelector('.convo-item.is-active')?.dataset.convId;

            const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
            const fragment = document.createDocumentFragment();

            data.conversations.forEach(c => {
                let el = existing.get(c.id);
                if (el) {
                    // Mettre à jour heure et aperçu
                    const timeEl = el.querySelector('.convo-item__time');
                    if (timeEl) timeEl.textContent = formatTimeShort(c.time);
                    const prevEl = el.querySelector('.convo-item__preview > span:first-child');
                    if (prevEl && c.preview) prevEl.textContent = c.preview;
                    el.dataset.timeIso = c.time;
                } else {
                    // Nouvelle conversation: créer l'élément
                    const initials = (c.name || '?').substring(0,2).toUpperCase();
                    const color = colors[Math.abs(hashCode(c.name || '?')) % colors.length];
                    el = document.createElement('a');
                    el.href = '/messenger/' + c.id;
                    el.className = 'convo-item live-new';
                    el.dataset.convId = c.id;
                    el.dataset.timeIso = c.time;
                    el.innerHTML = `
                        <div class="convo-item__avatar" style="background:${color}">${escapeHtml(initials)}</div>
                        <div class="convo-item__content">
                            <div class="convo-item__head">
                                <span class="convo-item__name">${escapeHtml(c.name || 'Utilisateur Facebook')}</span>
                                <span class="convo-item__time">${formatTimeShort(c.time)}</span>
                            </div>
                            <div class="convo-item__preview">
                                <span>${escapeHtml(c.preview || '—')}</span>
                            </div>
                        </div>`;
                    setTimeout(() => el.classList.remove('live-new'), 2000);
                }
                if (c.id === activeId) el.classList.add('is-active');
                fragment.appendChild(el);
            });

            // Remplacer le contenu dans le bon ordre (pas de clignotement si rien ne change)
            container.innerHTML = '';
            container.appendChild(fragment);
        } catch (e) {}
    }

    // ─── CONVERSATION OUVERTE: append nouveaux messages ──────────
    async function updateOpenConversation() {
        const chatBody = document.getElementById('chat-body');
        const messagesContainer = document.getElementById('messages-container');
        if (!chatBody || !messagesContainer) return;
        const convId = chatBody.dataset.conversationId;
        if (!convId) return;

        const lastMsgEl = messagesContainer.querySelector('[data-msg-id]:last-of-type');
        const afterId = lastMsgEl ? lastMsgEl.dataset.msgId : 0;

        try {
            const res = await fetch(`/messenger/${convId}/live-messages?after=${afterId}`, {
                headers: { Accept: 'application/json' }
            });
            const data = await res.json();
            if (!data.messages || data.messages.length === 0) return;

            const atBottom = (chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight) < 100;
            const pageId = data.page_id;
            const colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'];

            data.messages.forEach(msg => {
                const isPage = msg.nom_expediteur && (msg.nom_expediteur.toLowerCase().includes('sodeci') || msg.statut === 'lu');
                const cls = isPage ? 'is-page' : 'is-user';
                const time = msg.temps_envoi ? new Date(msg.temps_envoi.replace(' ', 'T')).toLocaleString('fr-FR', {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
                const initials = (msg.nom_expediteur || '?').substring(0,2).toUpperCase();
                const c = colors[Math.abs(hashCode(msg.nom_expediteur || '?')) % colors.length];
                const avatar = !isPage ? `<div class="msg-mini-avatar" style="background:${c}">${escapeHtml(initials)}</div>` : '';
                const html = `<div class="msg-row ${cls} live-new" data-msg-id="${msg.id}">
                    ${avatar}
                    <div class="msg-content">
                        <div class="msg-bubble">${escapeHtml(msg.message)}</div>
                        <div class="msg-meta">${time}</div>
                    </div>
                </div>`;
                messagesContainer.insertAdjacentHTML('beforeend', html);
            });

            // Auto-scroll si l'utilisateur était déjà en bas
            if (atBottom) chatBody.scrollTop = chatBody.scrollHeight;

            setTimeout(() => {
                messagesContainer.querySelectorAll('.live-new').forEach(el => el.classList.remove('live-new'));
            }, 2000);
        } catch (e) {}
    }

    // ─── LISTE POSTS: mise à jour live ───────────────────────────
    async function updatePostsList() {
        const container = document.getElementById('post-list');
        if (!container) return;
        try {
            const res = await fetch('/posts/live-list', { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (!data.posts) return;

            const existing = new Map();
            container.querySelectorAll('.post-item').forEach(el => existing.set(el.dataset.postId, el));
            const activeId = container.querySelector('.post-item.is-active')?.dataset.postId;

            const fragment = document.createDocumentFragment();

            data.posts.forEach(p => {
                let el = existing.get(p.id);
                if (el) {
                    // Mettre à jour heure et compteur de commentaires
                    const timeEl = el.querySelector('.post-item__meta span:first-child');
                    if (timeEl) timeEl.innerHTML = '<i class="ph ph-clock"></i> ' + formatTimeShort(p.time);
                    const countEl = el.querySelector('.post-item__count');
                    if (p.comments_count > 0) {
                        if (countEl) {
                            countEl.innerHTML = '<i class="ph ph-chat-circle"></i> ' + p.comments_count;
                            if (p.comments_count > parseInt(el.dataset.commentsCount || 0)) {
                                el.classList.add('live-new');
                                setTimeout(() => el.classList.remove('live-new'), 2000);
                            }
                        } else {
                            const metaEl = el.querySelector('.post-item__meta');
                            if (metaEl) metaEl.insertAdjacentHTML('beforeend', `<span class="post-item__count"><i class="ph ph-chat-circle"></i> ${p.comments_count}</span>`);
                        }
                    }
                    el.dataset.commentsCount = p.comments_count;
                    el.dataset.timeIso = p.time;
                } else {
                    // Nouveau post
                    el = document.createElement('a');
                    el.href = '/posts/' + p.id;
                    el.className = 'post-item live-new';
                    el.dataset.postId = p.id;
                    el.dataset.timeIso = p.time;
                    el.dataset.commentsCount = p.comments_count;
                    el.innerHTML = `
                        <div class="post-item__thumb post-item__thumb--placeholder"><i class="ph ph-image"></i></div>
                        <div class="post-item__content">
                            <div class="post-item__text">${escapeHtml(p.preview || 'Sans texte')}</div>
                            <div class="post-item__meta">
                                <span><i class="ph ph-clock"></i> ${formatTimeShort(p.time)}</span>
                                ${p.comments_count > 0 ? `<span class="post-item__count"><i class="ph ph-chat-circle"></i> ${p.comments_count}</span>` : ''}
                            </div>
                        </div>`;
                    setTimeout(() => el.classList.remove('live-new'), 2000);
                }
                if (p.id === activeId) el.classList.add('is-active');
                fragment.appendChild(el);
            });

            container.innerHTML = '';
            container.appendChild(fragment);
        } catch (e) {}
    }

    // ─── Lancement des polls ─────────────────────────────────────
    if (isMessengerPage) {
        updateMessengerList();
        setInterval(updateMessengerList, LIST_POLL_MS);
        // Si on est sur une conversation ouverte, poll aussi les nouveaux messages
        if (document.getElementById('chat-body')) {
            setInterval(updateOpenConversation, MESSAGES_POLL_MS);
        }
    }
    if (isPostsPage) {
        updatePostsList();
        setInterval(updatePostsList, LIST_POLL_MS);
    }

    // Pause quand l'onglet est caché, reprise quand il revient
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            if (isMessengerPage) {
                updateMessengerList();
                if (document.getElementById('chat-body')) updateOpenConversation();
            }
            if (isPostsPage) updatePostsList();
        }
    });
})();
</script>

<style>
/* Animation subtile pour les nouveautés */
.convo-item.live-new,
.post-item.live-new {
    animation: liveFadeIn 0.4s ease-out, liveHighlight 2s ease-out;
}
.msg-row.live-new {
    animation: liveSlideIn 0.35s ease-out;
}
@keyframes liveFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes liveHighlight {
    0%, 100% { background-color: transparent; }
    20% { background-color: #FEF3C7; }
}
@keyframes liveSlideIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
