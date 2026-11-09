{{-- Indicateur discret de fraicheur des donnees + notification de nouveautes (toast en bas à droite uniquement si nouveautés) --}}
<div id="new-items-toast" style="display:none; position:fixed; bottom:20px; right:20px; z-index:999; background:linear-gradient(135deg, var(--bleu-france), #0041d6); color:#fff; padding:12px 18px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.2); font-size:14px; cursor:pointer; animation:slideUp 0.3s; display:flex; align-items:center; gap:10px;" onclick="window.location.reload()">
    <i class="fa-solid fa-bell"></i>
    <span id="new-items-text">Nouveaux éléments disponibles</span>
    <span style="opacity:0.8; font-size:12px;">· Cliquer</span>
</div>

<style>
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<script>
(function() {
    const pageType = window.location.pathname.startsWith('/messenger') ? 'messenger' : 'facebook';
    const pageOpenedAt = new Date().toISOString().replace('T', ' ').substring(0, 19);
    let pollTimer = null;
    let shownNewCount = 0;

    function formatAgo(dateStr) {
        if (!dateStr) return 'Jamais';
        const date = new Date(dateStr.replace(' ', 'T'));
        const sec = Math.floor((Date.now() - date.getTime()) / 1000);
        if (sec < 10) return 'à l\'instant';
        if (sec < 60) return 'il y a ' + sec + ' sec';
        if (sec < 3600) return 'il y a ' + Math.floor(sec/60) + ' min';
        if (sec < 86400) return 'il y a ' + Math.floor(sec/3600) + 'h';
        return 'il y a ' + Math.floor(sec/86400) + 'j';
    }

    async function checkFreshness() {
        try {
            const res = await fetch('/import/freshness/' + pageType + '?since=' + encodeURIComponent(pageOpenedAt), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const pill = document.getElementById('sidebar-fresh');
            const label = document.getElementById('sidebar-fresh-label');
            if (!pill || !label) return;

            pill.classList.remove('is-syncing', 'is-stale');

            if (data.running) {
                pill.classList.add('is-syncing');
                label.textContent = 'Synchronisation...';
            } else if (data.last_sync) {
                const sec = Math.floor((Date.now() - new Date(data.last_sync.replace(' ', 'T')).getTime()) / 1000);
                if (sec > 300) pill.classList.add('is-stale');
                label.textContent = formatAgo(data.last_sync);
            } else {
                label.textContent = 'En attente...';
            }

            if (data.new_count > shownNewCount) {
                const toast = document.getElementById('new-items-toast');
                const toastText = document.getElementById('new-items-text');
                toastText.textContent = data.new_count + ' nouveau' + (data.new_count > 1 ? 'x' : '') + ' ' + (pageType === 'facebook' ? 'commentaire' : 'message') + (data.new_count > 1 ? 's' : '');
                toast.style.display = 'flex';
                shownNewCount = data.new_count;
            }
        } catch (e) {}
    }

    checkFreshness();
    pollTimer = setInterval(checkFreshness, 30000);
    document.addEventListener('visibilitychange', function() { if (!document.hidden) checkFreshness(); });
})();
</script>
