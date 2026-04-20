@extends('layouts.app')
@section('title', 'Publications')

@section('content')
<div class="page-header">
    <h1><i class="fa-brands fa-facebook"></i> Publications Facebook</h1>
    <form class="search-bar" method="GET" action="/posts">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher un post...">
        <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
        @if($search)
            <a href="/posts" class="btn btn-secondary btn-sm">Effacer</a>
        @endif
    </form>
</div>

@if($posts->count() > 0)
    <div class="post-grid">
        @foreach($posts as $post)
        <a href="/posts/{{ $post->post_id }}" class="post-card" style="text-decoration:none; color:inherit;">
            @if($post->image_url)
                <img src="{{ $post->image_url }}" alt="Image du post" class="post-card-img" loading="lazy">
            @else
                <div class="post-card-img" style="display:flex; align-items:center; justify-content:center; font-family:'VT323',monospace; font-size:24px; color:#999;">Pas d'image</div>
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
