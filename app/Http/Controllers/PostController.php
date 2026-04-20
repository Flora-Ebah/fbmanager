<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $posts = Post::withCount(['commentaires' => function ($q) {
                $q->whereNotIn('nom_auteur', ['SODECI', 'Sara Conseiller Client Sodeci']);
            }])
            ->when($search, function ($q) use ($search) {
                $q->where('message_post', 'like', "%{$search}%")
                  ->orWhere('post_id', 'like', "%{$search}%");
            })
            ->orderBy('temps_creer_post', 'desc')
            ->paginate(20);

        return view('posts.index', compact('posts', 'search'));
    }

    public function show($postId)
    {
        $post = Post::where('post_id', $postId)->firstOrFail();

        $commentaires = $post->commentaires()
            ->with(['aiReplies' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }, 'aiReplies.user'])
            ->whereNotIn('nom_auteur', ['SODECI', 'Sara Conseiller Client Sodeci'])
            ->orderBy('temps_creer', 'desc')
            ->paginate(20);

        return view('posts.show', compact('post', 'commentaires'));
    }
}
