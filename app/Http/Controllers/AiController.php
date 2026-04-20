<?php

namespace App\Http\Controllers;

use App\Models\AiReply;
use App\Models\Commentaire;
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
}
