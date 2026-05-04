<?php

namespace App\Http\Controllers;

use App\Models\AiReply;
use App\Models\Commentaire;
use App\Models\MessengerConversation;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function suggestReply(Request $request, AiService $ai)
    {
        $request->validate([
            'comment_id' => 'required',
            'tone' => 'nullable|in:professional,friendly,casual',
        ]);

        $comment = Commentaire::where('id_commentaire', $request->comment_id)->firstOrFail();
        $post = $comment->post;
        $tone = $request->tone ?? 'professional';

        $reply = $ai->suggestReply(
            $post->message_post ?? '',
            $comment->message_commentaire ?? '',
            $comment->nom_auteur ?? 'Client',
            $tone
        );

        // Sauvegarder la reponse en base
        $saved = AiReply::create([
            'id_commentaire' => $request->comment_id,
            'tone' => $tone,
            'reply' => $reply,
            'generated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'tone' => $tone,
            'saved_id' => $saved->id,
            'generated_at' => $saved->created_at->format('d/m/Y H:i'),
            'generated_by' => auth()->user()->username,
        ]);
    }

    public function suggestMultipleReplies(Request $request, AiService $ai)
    {
        $request->validate([
            'comment_id' => 'required',
        ]);

        $comment = Commentaire::where('id_commentaire', $request->comment_id)->firstOrFail();
        $post = $comment->post;

        $replies = $ai->suggestMultipleReplies(
            $post->message_post ?? '',
            $comment->message_commentaire ?? '',
            $comment->nom_auteur ?? 'Client'
        );

        // Sauvegarder chaque reponse
        foreach ($replies as $tone => $reply) {
            AiReply::create([
                'id_commentaire' => $request->comment_id,
                'tone' => $tone,
                'reply' => $reply,
                'generated_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'replies' => $replies,
        ]);
    }

    /**
     * Suggerer une reponse a un message Messenger en tenant compte du contexte
     */
    public function suggestMessengerReply(Request $request, AiService $ai)
    {
        $request->validate([
            'conversation_id' => 'required',
            'tone' => 'nullable|in:professional,friendly,casual',
        ]);

        $conv = MessengerConversation::where('conversation_id', $request->conversation_id)->firstOrFail();
        $pageId = config('fbmanager.facebook_page_id');

        // Recuperer les 20 derniers messages pour le contexte
        $messages = $conv->messages()
            ->orderBy('temps_envoi', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        if ($messages->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Aucun message dans cette conversation.'], 400);
        }

        // Detecter le dernier message du client (non-page)
        $lastClientMsg = null;
        foreach ($messages->reverse() as $m) {
            $isPage = stripos($m->nom_expediteur ?? '', 'sodeci') !== false;
            if (!$isPage) {
                $lastClientMsg = $m;
                break;
            }
        }

        if (!$lastClientMsg) {
            return response()->json(['success' => false, 'error' => 'Aucun message du client a repondre.'], 400);
        }

        // Construire le contexte
        $contextMessages = $messages->map(function ($m) {
            return [
                'is_page' => stripos($m->nom_expediteur ?? '', 'sodeci') !== false,
                'message' => $m->message,
            ];
        })->toArray();

        $tone = $request->tone ?? 'professional';

        $reply = $ai->suggestMessengerReply(
            $contextMessages,
            $lastClientMsg->message,
            $conv->nom_expediteur ?? 'Client',
            $tone
        );

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'tone' => $tone,
            'generated_at' => now()->format('d/m/Y H:i'),
            'generated_by' => auth()->user()->username,
        ]);
    }
}
