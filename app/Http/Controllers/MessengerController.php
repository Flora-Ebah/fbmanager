<?php
namespace App\Http\Controllers;

use App\Models\MessengerConversation;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $conversations = MessengerConversation::withCount('messages')
            ->when($search, function ($q) use ($search) {
                $q->where('nom_expediteur', 'like', "%{$search}%")
                  ->orWhere('dernier_message', 'like', "%{$search}%");
            })
            ->orderByRaw('COALESCE(temps_dernier_message, updated_at) DESC')
            ->paginate(30);

        return view('messenger.index', compact('conversations', 'search'));
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

        return view('messenger.show', compact('conversation', 'messages', 'hasMore', 'pageId'));
    }
}
