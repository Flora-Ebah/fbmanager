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
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('messenger.index', compact('conversations', 'search'));
    }

    public function show($conversationId)
    {
        $conversation = MessengerConversation::where('conversation_id', $conversationId)->firstOrFail();
        $messages = $conversation->messages()->orderBy('temps_envoi', 'asc')->paginate(50);

        return view('messenger.show', compact('conversation', 'messages'));
    }
}
