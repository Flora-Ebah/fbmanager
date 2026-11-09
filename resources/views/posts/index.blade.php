@extends('layouts.app')
@section('title', 'Publications')

@section('main')
@include('posts._list')

<main class="content">
    <div class="top-bar">
        <div class="top-bar__title">
            <i class="ph ph-newspaper" style="color:var(--bleu-france);"></i>
            Publications
            <span class="breadcrumb">· {{ $posts->total() }} au total</span>
        </div>
        <div class="top-bar__actions">
            <div class="fresh-pill" id="fresh-pill">
                <span class="fresh-pill__dot"></span>
                <span class="fresh-pill__text" id="fresh-pill-text">Chargement...</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="triggerImport('facebook')">
                <i class="ph ph-arrows-clockwise"></i> Synchroniser
            </button>
        </div>
    </div>

    <div class="empty-state" style="flex:1;">
        <div class="empty-state__icon"><i class="ph ph-hand-pointing"></i></div>
        <div class="empty-state__title">Sélectionnez une publication</div>
        <div class="empty-state__text">Choisissez une publication dans la liste de gauche pour voir ses commentaires et répondre avec l'aide de l'IA.</div>
    </div>
</main>

@include('partials.import-modal')
@include('partials.freshness-indicator')
@include('partials.live-updates')
@endsection
