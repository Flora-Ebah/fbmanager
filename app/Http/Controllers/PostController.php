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

    // Endpoint live: liste JSON des posts (poll toutes les 15s)
    public function liveList(Request $request)
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
            ->limit(30)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->post_id,
                    'preview' => mb_substr((string) $p->message_post, 0, 80),
                    'time' => $p->temps_creer_post,
                    'comments_count' => $p->commentaires_count,
                ];
            });

        return response()->json(['posts' => $posts]);
    }

    // Endpoint live: nouveaux commentaires sur un post (poll toutes les 10s)
    public function liveComments(Request $request, $postId)
    {
        $post = Post::where('post_id', $postId)->firstOrFail();
        $afterId = (int) $request->get('after', 0);

        $comments = $post->commentaires()
            ->whereNotIn('nom_auteur', ['SODECI', 'Sara Conseiller Client Sodeci'])
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json(['comments' => $comments]);
    }
}
