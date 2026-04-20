<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Commentaire;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportFacebookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function handle(): void
    {
        $fb = new FacebookService();

        Log::channel('single')->info('========== IMPORT FACEBOOK AUTO - DEBUT ==========');

        $posts = $fb->getPagePosts(100);

        if (empty($posts)) {
            Log::channel('single')->warning('[IMPORT] Aucun post recupere depuis Facebook.');
            return;
        }

        Log::channel('single')->info("[IMPORT] {$this->count($posts)} posts recuperes depuis l'API Facebook.");

        $postsCreated = 0;
        $postsUpdated = 0;
        $commentsCreated = 0;
        $commentsTotal = 0;
        $errors = 0;

        foreach ($posts as $index => $fbPost) {
            $postId = $fbPost['id'] ?? null;
            if (!$postId) continue;

            try {
                $post = Post::updateOrCreate(
                    ['post_id' => $postId],
                    [
                        'message_post' => $fbPost['message'] ?? null,
                        'image_url' => $fbPost['full_picture'] ?? null,
                        'temps_creer_post' => $fbPost['created_time'] ?? null,
                        'lien' => $fbPost['permalink_url'] ?? null,
                    ]
                );

                $status = $post->wasRecentlyCreated ? 'NOUVEAU' : 'MIS A JOUR';
                if ($post->wasRecentlyCreated) {
                    $postsCreated++;
                } else {
                    $postsUpdated++;
                }

                $msgPreview = mb_substr($fbPost['message'] ?? '(sans texte)', 0, 50);
                Log::channel('single')->info("[IMPORT] Post " . ($index + 1) . "/{$this->count($posts)} [{$status}] {$postId} - \"{$msgPreview}...\"");

                $comments = $fb->getPostComments($postId);
                $commentsTotal += count($comments);

                if (count($comments) > 0) {
                    Log::channel('single')->info("[IMPORT]   -> {$this->count($comments)} commentaire(s) pour ce post");
                }

                foreach ($comments as $fbComment) {
                    $commentId = $fbComment['id'] ?? null;
                    if (!$commentId) continue;

                    $comment = Commentaire::updateOrCreate(
                        ['id_commentaire' => $commentId],
                        [
                            'post_id' => $postId,
                            'message_commentaire' => $fbComment['message'] ?? '',
                            'nom_auteur' => $fbComment['from']['name'] ?? 'Utilisateur Facebook',
                            'temps_creer' => $fbComment['created_time'] ?? null,
                        ]
                    );

                    if ($comment->wasRecentlyCreated) {
                        $commentsCreated++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                Log::channel('single')->error("[IMPORT] ERREUR post {$postId}: " . $e->getMessage());
            }
        }

        Log::channel('single')->info('========== IMPORT FACEBOOK AUTO - RESULTAT ==========');
        Log::channel('single')->info("[IMPORT] Posts: {$postsCreated} nouveaux, {$postsUpdated} mis a jour");
        Log::channel('single')->info("[IMPORT] Commentaires: {$commentsCreated} nouveaux sur {$commentsTotal} recuperes");
        Log::channel('single')->info("[IMPORT] Erreurs: {$errors}");
        Log::channel('single')->info("[IMPORT] Total en BDD: " . Post::count() . " posts, " . Commentaire::count() . " commentaires");
        Log::channel('single')->info('====================================================');
    }

    private function count(array $arr): int
    {
        return count($arr);
    }
}
