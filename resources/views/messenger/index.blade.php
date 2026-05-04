@extends('layouts.app')
@section('title', 'Messenger')

@section('content')
<div class="page-header">
    <h1><i class="fa-brands fa-facebook-messenger"></i> Conversations Messenger</h1>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button type="button" id="btn-import-msg" class="btn btn-success btn-sm" onclick="triggerImport('messenger')">
            <i class="fa-solid fa-rotate"></i> Synchroniser
        </button>
        <form class="search-bar" method="GET" action="/messenger" style="display:flex; gap:8px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher une conversation...">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
            @if($search)
                <a href="/messenger" class="btn btn-secondary btn-sm">Effacer</a>
            @endif
        </form>
    </div>
</div>

<div id="import-toast" style="display:none; position:fixed; top:80px; right:20px; background:var(--bleu-france); color:#fff; padding:12px 20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:1000; font-size:14px;"></div>

<script>
async function triggerImport(type) {
    const btn = document.getElementById('btn-import-' + (type === 'facebook' ? 'fb' : 'msg'));
    const toast = document.getElementById('import-toast');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Lancement...';

    try {
        const res = await fetch('/import/' + type, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        toast.textContent = data.message || 'Import lancé !';
        toast.style.display = 'block';
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Lancé !';
        setTimeout(() => {
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            toast.style.display = 'none';
        }, 4000);
    } catch (e) {
        toast.style.background = '#ED2939';
        toast.textContent = 'Erreur : ' + e.message;
        toast.style.display = 'block';
        btn.innerHTML = oldHtml;
        btn.disabled = false;
    }
}
</script>

<div class="msg-list-wrapper">
    @if($conversations->count() > 0)
        <ul class="msg-convo-list">
            @foreach($conversations as $convo)
            @php
                $initials = strtoupper(substr($convo->nom_expediteur ?? '?', 0, 2));
                $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
                $color = $colors[crc32($convo->nom_expediteur ?? '?') % count($colors)];
                $time = $convo->temps_dernier_message;
                try {
                    $time = $time ? \Carbon\Carbon::parse($time) : null;
                } catch (\Exception $e) { $time = null; }
            @endphp
            <a href="/messenger/{{ $convo->conversation_id }}" class="msg-convo-item {{ $convo->statut === 'non_lu' ? 'is-unread' : '' }}">
                <div class="msg-avatar" style="background:{{ $color }}">{{ $initials }}</div>
                <div class="msg-convo-content">
                    <div class="msg-convo-top">
                        <span class="msg-convo-name">{{ $convo->nom_expediteur ?? 'Utilisateur Facebook' }}</span>
                        <span class="msg-convo-time">
                            @if($time)
                                {{ $time->diffForHumans(['short' => true]) }}
                            @else — @endif
                        </span>
                    </div>
                    <div class="msg-convo-bottom">
                        <span class="msg-convo-preview">{{ \Illuminate\Support\Str::limit($convo->dernier_message ?? '—', 80) }}</span>
                        @if($convo->statut === 'non_lu')
                            <span class="msg-unread-dot"></span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </ul>

        <div class="pagination-wrapper">
            {{ $conversations->appends(['search' => $search])->links() }}
        </div>
    @else
        <div class="empty-state" style="padding:60px 20px; text-align:center; color:#9CA3AF;">
            <i class="fa-regular fa-comment-dots" style="font-size:48px; margin-bottom:12px; display:block;"></i>
            Aucune conversation trouvée.
        </div>
    @endif
</div>

<style>
    .msg-list-wrapper {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .msg-convo-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .msg-convo-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid #F3F4F6;
        text-decoration: none;
        color: var(--gris-fonce);
        transition: background 0.15s;
    }
    .msg-convo-item:hover { background: #F9FAFB; }
    .msg-convo-item:last-child { border-bottom: none; }
    .msg-convo-item.is-unread { background: #EFF6FF; }
    .msg-convo-item.is-unread:hover { background: #DBEAFE; }

    .msg-avatar {
        width: 48px; height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 16px;
        flex-shrink: 0;
        font-family: 'Outfit', sans-serif;
    }
    .msg-convo-content { flex: 1; min-width: 0; }
    .msg-convo-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .msg-convo-name {
        font-weight: 600;
        font-size: 15px;
        color: var(--gris-fonce);
    }
    .is-unread .msg-convo-name { font-weight: 700; }
    .msg-convo-time {
        font-size: 12px;
        color: #9CA3AF;
        flex-shrink: 0;
        margin-left: 10px;
    }
    .is-unread .msg-convo-time { color: var(--bleu-france); font-weight: 600; }
    .msg-convo-bottom {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .msg-convo-preview {
        flex: 1;
        font-size: 13px;
        color: #6B7280;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .is-unread .msg-convo-preview { color: var(--gris-fonce); font-weight: 500; }
    .msg-unread-dot {
        width: 10px; height: 10px;
        background: var(--bleu-france);
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
@endsection
