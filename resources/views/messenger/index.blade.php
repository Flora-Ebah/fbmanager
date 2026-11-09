@extends('layouts.app')
@section('title', 'Messenger')

@section('main')
@include('messenger._list')

<main class="content">
    <div class="top-bar">
        <div class="top-bar__title">
            <i class="ph ph-messenger-logo" style="color:var(--bleu-france);"></i>
            Messenger
            <span class="breadcrumb">· {{ $conversations->total() }} conversation(s)</span>
        </div>
        <div class="top-bar__actions">
            <div class="fresh-pill" id="fresh-pill">
                <span class="fresh-pill__dot"></span>
                <span class="fresh-pill__text" id="fresh-pill-text">Chargement...</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="triggerImport('messenger')">
                <i class="ph ph-arrows-clockwise"></i> Synchroniser
            </button>
        </div>
    </div>

    <div class="empty-state" style="flex:1;">
        <div class="empty-state__icon"><i class="ph ph-chat-circle-dots"></i></div>
        <div class="empty-state__title">Sélectionnez une conversation</div>
        <div class="empty-state__text">Choisissez une conversation à gauche pour lire l'historique et générer des réponses avec l'IA.</div>
    </div>
</main>

@include('partials.import-modal')
@include('partials.freshness-indicator')
@include('partials.live-updates')
@endsection
