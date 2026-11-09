<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = $this->getPostsList($request);
        return view('posts.index', compact('posts') + ['search' => $request->get('search')]);
    }

    public function show(Request $request, $postId)
    {
        $post = Post::where('post_id', $postId)->firstOrFail();

        $commentaires = $post->commentaires()
            ->with(['aiReplies' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }, 'aiReplies.user'])
            ->whereNotIn('nom_auteur', ['SODECI', 'Sara Conseiller Client Sodeci'])
            ->orderBy('temps_creer', 'desc')
            ->paginate(20);

        // Liste des posts pour la colonne de gauche
        $posts = $this->getPostsList($request);

        return view('posts.show', compact('post', 'commentaires', 'posts') + ['search' => $request->get('search')]);
    }

    protected function getPostsList(Request $request)
    {
        $search = $request->get('search');

        return Post::withCount(['commentaires' => function ($q) {
                $q->whereNotIn('nom_auteur', ['SODECI', 'Sara Conseiller Client Sodeci']);
            }])
            ->when($search, function ($q) use ($search) {
                $q->where('message_post', 'like', "%{$search}%")
                  ->orWhere('post_id', 'like', "%{$search}%");
            })
            ->orderBy('temps_creer_post', 'desc')
            ->paginate(30);
    }
}
