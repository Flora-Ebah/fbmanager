@php
    // Détecter si c'est un message de la page (SODECI) ou de l'utilisateur
    $isPage = false;
    if (!empty($msg->nom_expediteur)) {
        $isPage = stripos($msg->nom_expediteur, 'sodeci') !== false;
    }
    if (!$isPage && $msg->statut === 'lu') {
        // Heuristique : les messages "lu" sont souvent ceux envoyés par la page
    }

    $initials = strtoupper(substr($msg->nom_expediteur ?? '?', 0, 2));
    $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316'];
    $color = $colors[abs(crc32($msg->nom_expediteur ?? '?')) % count($colors)];

    try {
        $time = $msg->temps_envoi ? \Carbon\Carbon::parse($msg->temps_envoi)->format('d M H:i') : '';
    } catch (\Exception $e) { $time = $msg->temps_envoi ?? ''; }
@endphp

<div class="msg-row {{ $isPage ? 'is-page' : 'is-user' }}" data-msg-id="{{ $msg->id }}">
    @if(!$isPage)
        <div class="msg-mini-avatar" style="background:{{ $color }}">{{ $initials }}</div>
    @endif
    <div class="msg-content">
        <div class="msg-bubble">{{ $msg->message }}</div>
        <div class="msg-meta">{{ $time }}</div>
    </div>
</div>
