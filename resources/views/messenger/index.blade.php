@extends('layouts.app')
@section('title', 'Messenger')

@section('content')
<div class="page-header">
    <h1><i class="fa-brands fa-facebook-messenger"></i> Conversations Messenger</h1>
    <form class="search-bar" method="GET" action="/messenger">
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher une conversation...">
        <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
        @if($search)
            <a href="/messenger" class="btn btn-secondary btn-sm">Effacer</a>
        @endif
    </form>
</div>

<div class="card">
    @if($conversations->count() > 0)
        <ul class="convo-list">
            @foreach($conversations as $convo)
            <a href="/messenger/{{ $convo->conversation_id }}" class="convo-item {{ $convo->statut === 'non_lu' ? 'non-lu' : '' }}">
                <div class="convo-avatar">
                    {{ strtoupper(substr($convo->nom_expediteur ?? '?', 0, 2)) }}
                </div>
                <div class="convo-info">
                    <div class="convo-name">
                        {{ $convo->nom_expediteur ?? 'Inconnu' }}
                        @if($convo->statut === 'non_lu')
                            <span class="badge badge-nonlu" style="font-size:13px; margin-left:8px;">NON LU</span>
                        @endif
                    </div>
                    <div class="convo-preview">{{ $convo->dernier_message ?? '—' }}</div>
                </div>
                <div class="convo-meta">
                    <div class="convo-date">{{ $convo->temps_dernier_message ?? '—' }}</div>
                    <div class="badge badge-count" style="margin-top:6px;">{{ $convo->messages_count ?? $convo->nombre_messages }}</div>
                </div>
            </a>
            @endforeach
        </ul>

        <div class="pagination-wrapper">
            {{ $conversations->appends(['search' => $search])->links() }}
        </div>
    @else
        <div class="empty-state">Aucune conversation trouvee.</div>
    @endif
</div>
@endsection
