<div id="import-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:14px; padding:28px; max-width:480px; width:90%; box-shadow:0 20px 50px rgba(0,0,0,0.25);">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px;">
            <div id="import-modal-icon" style="width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg, var(--bleu-france), #0041d6); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px;">
                <i class="fa-solid fa-rotate fa-spin"></i>
            </div>
            <div style="flex:1;">
                <div id="import-modal-title" style="font-size:17px; font-weight:600; color:var(--gris-fonce);">Synchronisation en cours</div>
                <div id="import-modal-subtitle" style="font-size:13px; color:#6B7280; margin-top:2px;">Démarrage...</div>
            </div>
        </div>

        <div style="background:#F3F4F6; border-radius:10px; height:10px; overflow:hidden; margin-bottom:8px;">
            <div id="import-progress-bar" style="height:100%; width:0%; background:linear-gradient(90deg, var(--bleu-france), #4F46E5); border-radius:10px; transition:width 0.3s;"></div>
        </div>
        <div id="import-progress-text" style="font-size:12px; color:#6B7280; text-align:right; margin-bottom:18px;">0 / 0</div>

        <div id="import-stats" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:10px; margin-bottom:18px;">
            <div style="background:#F9FAFB; padding:10px 12px; border-radius:8px;">
                <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:600;">Nouveaux</div>
                <div id="stat-new" style="font-size:20px; font-weight:700; color:var(--vert-ok); margin-top:2px;">0</div>
            </div>
            <div style="background:#F9FAFB; padding:10px 12px; border-radius:8px;">
                <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:600;">Mis à jour</div>
                <div id="stat-updated" style="font-size:20px; font-weight:700; color:var(--bleu-france); margin-top:2px;">0</div>
            </div>
            <div style="background:#F9FAFB; padding:10px 12px; border-radius:8px;">
                <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:600;">Détails</div>
                <div id="stat-details" style="font-size:14px; font-weight:600; color:var(--gris-fonce); margin-top:4px;">—</div>
            </div>
            <div style="background:#F9FAFB; padding:10px 12px; border-radius:8px;">
                <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:600;">Erreurs</div>
                <div id="stat-errors" style="font-size:20px; font-weight:700; color:#9CA3AF; margin-top:2px;">0</div>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" id="import-modal-close" onclick="closeImportModal()" class="btn btn-secondary btn-sm" style="display:none;">Fermer</button>
            <button type="button" onclick="hideImportModal()" class="btn btn-secondary btn-sm">Masquer</button>
        </div>
    </div>
</div>

<script>
let importPollTimer = null;
let importType = null;

async function triggerImport(type) {
    importType = type;
    const btn = document.getElementById('btn-import-' + (type === 'facebook' ? 'fb' : 'msg'));
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Lancement...';

    // Reset modal
    document.getElementById('import-modal-title').textContent =
        type === 'facebook' ? 'Synchronisation des publications' : 'Synchronisation Messenger';
    document.getElementById('import-modal-subtitle').textContent = 'Démarrage...';
    document.getElementById('import-progress-bar').style.width = '0%';
    document.getElementById('import-progress-text').textContent = '0 / 0';
    document.getElementById('stat-new').textContent = '0';
    document.getElementById('stat-updated').textContent = '0';
    document.getElementById('stat-details').textContent = '—';
    document.getElementById('stat-errors').textContent = '0';
    document.getElementById('import-modal-close').style.display = 'none';
    document.getElementById('import-modal-icon').innerHTML = '<i class="fa-solid fa-rotate fa-spin"></i>';
    document.getElementById('import-modal').style.display = 'flex';

    try {
        await fetch('/import/' + type, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        // Démarrer le polling
        startPolling(type);
    } catch (e) {
        document.getElementById('import-modal-subtitle').textContent = 'Erreur : ' + e.message;
        document.getElementById('import-modal-icon').innerHTML = '<i class="fa-solid fa-xmark"></i>';
        document.getElementById('import-modal-close').style.display = 'inline-flex';
    } finally {
        btn.innerHTML = oldHtml;
        btn.disabled = false;
    }
}

function startPolling(type) {
    if (importPollTimer) clearInterval(importPollTimer);
    importPollTimer = setInterval(() => pollStatus(type), 2000);
    pollStatus(type); // Premier appel immédiat
}

async function pollStatus(type) {
    try {
        const res = await fetch('/import/status/' + type, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        const p = data.progress;

        if (p) {
            const percent = p.total > 0 ? Math.round((p.current / p.total) * 100) : 0;
            document.getElementById('import-progress-bar').style.width = percent + '%';
            document.getElementById('import-progress-text').textContent = `${p.current} / ${p.total} (${percent}%)`;
            document.getElementById('import-modal-subtitle').textContent = p.current_message;

            if (type === 'facebook') {
                document.getElementById('stat-new').textContent = p.posts_created || 0;
                document.getElementById('stat-updated').textContent = p.posts_updated || 0;
                document.getElementById('stat-details').textContent = `${p.comments_created || 0} comm. (${p.comments_total || 0})`;
            } else {
                document.getElementById('stat-new').textContent = p.convos_created || 0;
                document.getElementById('stat-updated').textContent = p.convos_updated || 0;
                document.getElementById('stat-details').textContent = `${p.messages_created || 0} msg (${p.messages_total || 0})`;
            }
            document.getElementById('stat-errors').textContent = p.errors || 0;

            if (p.finished) {
                clearInterval(importPollTimer);
                importPollTimer = null;
                document.getElementById('import-modal-icon').innerHTML = '<i class="fa-solid fa-check"></i>';
                document.getElementById('import-modal-icon').style.background = 'linear-gradient(135deg, #10B981, #059669)';
                document.getElementById('import-modal-subtitle').textContent = 'Import terminé avec succès !';
                document.getElementById('import-modal-close').style.display = 'inline-flex';
            }
        } else if (!data.running) {
            // Pas encore commencé ou cache vidé
            document.getElementById('import-modal-subtitle').textContent = 'En attente du démarrage...';
        }
    } catch (e) {
        console.error('Erreur polling:', e);
    }
}

function hideImportModal() {
    document.getElementById('import-modal').style.display = 'none';
    // Continue à poller en arrière-plan, le modal réapparaîtra à la fin si finished
}

function closeImportModal() {
    document.getElementById('import-modal').style.display = 'none';
    if (importPollTimer) {
        clearInterval(importPollTimer);
        importPollTimer = null;
    }
}

// Au chargement de la page, vérifier s'il y a un import en cours
document.addEventListener('DOMContentLoaded', async function() {
    for (const type of ['facebook', 'messenger']) {
        try {
            const res = await fetch('/import/status/' + type);
            const data = await res.json();
            if (data.running && data.progress && !data.progress.finished) {
                importType = type;
                document.getElementById('import-modal-title').textContent =
                    type === 'facebook' ? 'Synchronisation des publications' : 'Synchronisation Messenger';
                document.getElementById('import-modal').style.display = 'flex';
                startPolling(type);
                break;
            }
        } catch (e) {}
    }
});
</script>
