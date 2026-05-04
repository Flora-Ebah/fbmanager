@extends('layouts.app')
@section('title', 'Publications')

@section('content')
<div class="page-header">
    <h1><i class="fa-brands fa-facebook"></i> Publications Facebook</h1>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <button type="button" id="btn-import-fb" class="btn btn-success btn-sm" onclick="triggerImport('facebook')">
            <i class="fa-solid fa-rotate"></i> Synchroniser
        </button>
        <form class="search-bar" method="GET" action="/posts" style="display:flex; gap:8px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher un post...">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
            @if($search)
                <a href="/posts" class="btn btn-secondary btn-sm">Effacer</a>
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

@if($posts->count() > 0)
    <div class="post-grid">
        @foreach($posts as $post)
        <a href="/posts/{{ $post->post_id }}" class="post-card" style="text-decoration:none; color:inherit;">
            @if($post->image_url)
                <img src="{{ $post->image_url }}" alt="Image du post" class="post-card-img" loading="lazy">
            @else
                <div class="post-card-img" style="display:flex; align-items:center; justify-content:center; font-family:'Outfit',sans-serif; font-size:24px; color:#999;">Pas d'image</div>
            @endif
            <div class="post-card-body">
                <h3>Post #{{ Str::limit($post->post_id, 20) }}</h3>
                <p>{{ $post->message_post ?? 'Aucun contenu' }}</p>
            </div>
            <div class="post-card-footer">
                <span class="meta">{{ $post->temps_creer_post ?? '—' }}</span>
                <span class="badge badge-count">{{ $post->commentaires_count }} com.</span>
            </div>
        </a>
        @endforeach
    </div>

    <div class="pagination-wrapper">
        {{ $posts->appends(['search' => $search])->links() }}
    </div>
@else
    <div class="empty-state">
        Aucune publication trouvee.
    </div>
@endif
@endsection
