<?php
namespace App\Http\Controllers;

use App\Models\MessengerConversation;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    public function index(Request $request)
    {
        $conversations = $this->getConversationsList($request);
        return view('messenger.index', compact('conversations') + ['search' => $request->get('search')]);
    }

    public function show(Request $request, $conversationId)
    {
        $conversation = MessengerConversation::where('conversation_id', $conversationId)->firstOrFail();

        // AJAX : charger plus de messages anciens (infinite scroll vers le haut)
        if ($request->ajax() && $request->has('before_id')) {
            $messages = $conversation->messages()
                ->where('id', '<', $request->before_id)
                ->orderBy('temps_envoi', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();

            return response()->json([
                'messages' => $messages,
                'page_id' => config('fbmanager.facebook_page_id'),
            ]);
        }

        // Charger les 30 derniers messages (les plus recents)
        $messages = $conversation->messages()
            ->orderBy('temps_envoi', 'desc')
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        $hasMore = $conversation->messages()->count() > 30;
        $pageId = config('fbmanager.facebook_page_id');

        // Liste des conversations pour la colonne de gauche
        $conversations = $this->getConversationsList($request);

        return view('messenger.show', compact('conversation', 'messages', 'hasMore', 'pageId', 'conversations') + ['search' => $request->get('search')]);
    }

    protected function getConversationsList(Request $request)
    {
        $search = $request->get('search');

        return MessengerConversation::withCount('messages')
            ->when($search, function ($q) use ($search) {
                $q->where('nom_expediteur', 'like', "%{$search}%")
                  ->orWhere('dernier_message', 'like', "%{$search}%");
            })
            ->orderByRaw('COALESCE(temps_dernier_message, updated_at) DESC')
            ->paginate(50);
    }

    // Endpoint live: liste JSON des conversations (poll toutes les 15s)
    public function liveList(Request $request)
    {
        $search = $request->get('search');
        $conversations = MessengerConversation::withCount('messages')
            ->when($search, function ($q) use ($search) {
                $q->where('nom_expediteur', 'like', "%{$search}%")
                  ->orWhere('dernier_message', 'like', "%{$search}%");
            })
            ->orderByRaw('COALESCE(temps_dernier_message, updated_at) DESC')
            ->limit(50)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->conversation_id,
                    'name' => $c->nom_expediteur,
                    'preview' => mb_substr((string) $c->dernier_message, 0, 60),
                    'time' => $c->temps_dernier_message ?? $c->updated_at,
                    'messages_count' => $c->messages_count,
                ];
            });

        return response()->json(['conversations' => $conversations]);
    }

    // Endpoint live: nouveaux messages dans une conversation (poll toutes les 10s)
    public function liveMessages(Request $request, $conversationId)
    {
        $conversation = MessengerConversation::where('conversation_id', $conversationId)->firstOrFail();
        $afterId = (int) $request->get('after', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
            'page_id' => config('fbmanager.facebook_page_id'),
        ]);
    }
}
