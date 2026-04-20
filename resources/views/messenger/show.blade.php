@extends('layouts.app')
@section('title', 'Conversation avec ' . ($conversation->nom_expediteur ?? 'Inconnu'))

@section('content')
<div class="page-header">
    <h1><i class="fa-brands fa-facebook-messenger"></i> {{ $conversation->nom_expediteur ?? 'Inconnu' }}</h1>
    <a href="/messenger" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour aux conversations</a>
</div>

<div class="card">
    <div class="card-header">
        <span>Conversation #{{ Str::limit($conversation->conversation_id, 20) }}</span>
        <span class="badge {{ $conversation->statut === 'non_lu' ? 'badge-nonlu' : 'badge-lu' }}">
            {{ $conversation->statut === 'non_lu' ? 'NON LU' : 'LU' }}
        </span>
    </div>
    <div class="chat-container">
        @if($messages->count() > 0)
            @foreach($messages as $msg)
            <div class="chat-msg {{ $msg->nom_expediteur === 'Page' ? 'sent' : 'received' }}">
                <div>
                    @if($msg->nom_expediteur !== 'Page')
                        <div class="chat-sender">{{ $msg->nom_expediteur }}</div>
                    @endif
                    <div class="chat-bubble">
                        {{ $msg->message }}
                    </div>
                    <div class="chat-time">{{ $msg->temps_envoi ?? '—' }}</div>
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state">Aucun message dans cette conversation.</div>
        @endif
    </div>

    @if($messages->hasPages())
    <div class="pagination-wrapper">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection
