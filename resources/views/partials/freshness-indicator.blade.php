{{-- Pill de synchronisation (dans le top-bar) + notification nouveautés (dans la sidebar) --}}
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
        if (sec < 15) return "à l'instant";
        if (sec < 60) return 'il y a ' + sec + ' sec';
        if (sec < 3600) return 'il y a ' + Math.floor(sec/60) + ' min';
        if (sec < 86400) return 'il y a ' + Math.floor(sec/3600) + ' h';
        return 'il y a ' + Math.floor(sec/86400) + ' j';
    }

    async function checkFreshness() {
        try {
            const res = await fetch('/import/freshness/' + pageType + '?since=' + encodeURIComponent(pageOpenedAt), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            // Pill dans le top-bar
            const pill = document.getElementById('fresh-pill');
            const pillText = document.getElementById('fresh-pill-text');
            if (pill && pillText) {
                pill.classList.remove('is-syncing', 'is-stale');
                if (data.running) {
                    pill.classList.add('is-syncing');
                    pillText.innerHTML = '<strong>Synchronisation en cours...</strong>';
                } else if (data.last_sync) {
                    const sec = Math.floor((Date.now() - new Date(data.last_sync.replace(' ', 'T')).getTime()) / 1000);
                    if (sec > 300) pill.classList.add('is-stale');
                    pillText.innerHTML = 'Synchronisation automatique <strong>' + formatAgo(data.last_sync) + '</strong>';
                } else {
                    pillText.innerHTML = 'En attente de synchronisation...';
                }
            }

            // Nouveautés dans la sidebar
            const newBtn = document.getElementById('sidebar-new-items');
            const newBtnText = document.getElementById('sidebar-new-items-text');
            if (newBtn && newBtnText) {
                if (data.new_count > 0) {
                    const label = pageType === 'facebook' ? 'commentaire' : 'message';
                    newBtnText.textContent = data.new_count + ' nouveau' + (data.new_count > 1 ? 'x ' : ' ') + label + (data.new_count > 1 ? 's' : '');
                    if (data.new_count > shownNewCount) {
                        newBtn.classList.add('is-visible');
                        shownNewCount = data.new_count;
                    }
                } else {
                    newBtn.classList.remove('is-visible');
                }
            }
        } catch (e) {}
    }

    checkFreshness();
    pollTimer = setInterval(checkFreshness, 30000);
    document.addEventListener('visibilitychange', function() { if (!document.hidden) checkFreshness(); });
})();
</script>
