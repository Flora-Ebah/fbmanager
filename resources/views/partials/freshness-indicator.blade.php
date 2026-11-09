{{-- Indicateur discret de fraicheur des donnees + notification de nouveautes --}}
<div id="freshness-indicator" style="position:fixed; bottom:20px; right:20px; z-index:999; display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
    <div id="fresh-badge" style="background:#fff; border:1px solid #E5E7EB; padding:8px 14px; border-radius:20px; font-size:12px; color:#6B7280; box-shadow:0 2px 8px rgba(0,0,0,0.06); display:flex; align-items:center; gap:8px; transition:all 0.3s;">
        <span id="fresh-dot" style="width:8px; height:8px; border-radius:50%; background:#10B981;"></span>
        <span id="fresh-text">Synchronisé</span>
    </div>
    <div id="new-items-toast" style="display:none; background:linear-gradient(135deg, #002395, #0041d6); color:#fff; padding:12px 18px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); font-size:14px; cursor:pointer; animation:slideUp 0.3s;" onclick="window.location.reload()">
        <i class="fa-solid fa-bell"></i>
        <span id="new-items-text">Nouveaux éléments disponibles</span>
        <span style="margin-left:8px; opacity:0.8; font-size:12px;">Cliquer pour voir</span>
    </div>
</div>

<style>
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
#fresh-badge:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.fresh-syncing #fresh-dot { background: #F59E0B !important; animation: pulse 1s infinite; }
.fresh-stale #fresh-dot { background: #EF4444 !important; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
</style>

<script>
(function() {
    // Type de page (facebook ou messenger)
    const pageType = window.location.pathname.startsWith('/messenger') ? 'messenger' : 'facebook';
    const pageOpenedAt = new Date().toISOString().replace('T', ' ').substring(0, 19);
    let pollTimer = null;
    let shownNewCount = 0;

    function formatAgo(dateStr) {
        if (!dateStr) return 'Jamais';
        const date = new Date(dateStr.replace(' ', 'T'));
        const sec = Math.floor((Date.now() - date.getTime()) / 1000);
        if (sec < 10) return 'à l\'instant';
        if (sec < 60) return `il y a ${sec} sec`;
        if (sec < 3600) return `il y a ${Math.floor(sec/60)} min`;
        if (sec < 86400) return `il y a ${Math.floor(sec/3600)}h`;
        return `il y a ${Math.floor(sec/86400)}j`;
    }

    async function checkFreshness() {
        try {
            const res = await fetch(`/import/freshness/${pageType}?since=${encodeURIComponent(pageOpenedAt)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const badge = document.getElementById('fresh-badge');
            const text = document.getElementById('fresh-text');

            badge.classList.remove('fresh-syncing', 'fresh-stale');

            if (data.running) {
                badge.classList.add('fresh-syncing');
                text.textContent = 'Synchronisation en cours...';
            } else if (data.last_sync) {
                const sec = Math.floor((Date.now() - new Date(data.last_sync.replace(' ', 'T')).getTime()) / 1000);
                if (sec > 300) badge.classList.add('fresh-stale');
                text.textContent = 'Mis à jour ' + formatAgo(data.last_sync);
            } else {
                text.textContent = 'En attente de sync';
            }

            // Notifier les nouveautes
            if (data.new_count > shownNewCount) {
                const toast = document.getElementById('new-items-toast');
                const toastText = document.getElementById('new-items-text');
                toastText.textContent = `${data.new_count} nouveau${data.new_count > 1 ? 'x' : ''} ${pageType === 'facebook' ? 'commentaire' : 'message'}${data.new_count > 1 ? 's' : ''}`;
                toast.style.display = 'block';
                shownNewCount = data.new_count;
            }
        } catch (e) {
            // silencieux
        }
    }

    // Verifier a chaque chargement
    checkFreshness();
    // Puis toutes les 30 secondes
    pollTimer = setInterval(checkFreshness, 30000);

    // Si la page revient au premier plan, check immediatement
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) checkFreshness();
    });
})();
</script>
