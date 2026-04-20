<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Commentaire;
use App\Services\FacebookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFacebook extends Command
{
    protected $signature = 'import:facebook
                            {--fresh : Vider les tables posts/commentaires avant import}
                            {--limit=100 : Nombre max de posts a recuperer}
                            {--test : Tester la connexion uniquement}';

    protected $description = 'Importer les posts et commentaires directement depuis l\'API Facebook Graph';

    public function handle()
    {
        $fb = new FacebookService();

        // Mode test : verifier la connexion
        if ($this->option('test')) {
            $this->info('Test de connexion a l\'API Facebook...');
            $result = $fb->testConnection();

            if ($result['success']) {
                $this->info('Connexion reussie !');
                $this->table(
                    ['Champ', 'Valeur'],
                    collect($result['data'])->map(fn($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->values()->toArray()
                );
            } else {
                $this->error('Echec de connexion : ' . $result['error']);
            }
            return 0;
        }

        $limit = (int) $this->option('limit');

        // Option --fresh
        if ($this->option('fresh')) {
            $this->warn('Vidage des tables posts et commentaires...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Commentaire::truncate();
            Post::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // ─── Etape 1 : Recuperer les posts ───
        $this->info("Recuperation des posts (max {$limit})...");
        $posts = $fb->getPagePosts($limit);

        if (empty($posts)) {
            $this->warn('Aucun post recupere. Verifiez le token et les permissions.');
            return 1;
        }

        $this->info(count($posts) . ' posts recuperes depuis Facebook.');

        $postsCreated = 0;
        $postsUpdated = 0;
        $commentsCreated = 0;
        $commentsTotal = 0;

        $bar = $this->output->createProgressBar(count($posts));
        $bar->start();

        $errors = 0;

        foreach ($posts as $fbPost) {
            $postId = $fbPost['id'] ?? null;
            if (!$postId) {
                $bar->advance();
                continue;
            }

            try {
                // Upsert post
                $post = Post::updateOrCreate(
                    ['post_id' => $postId],
                    [
                        'message_post' => $fbPost['message'] ?? null,
                        'image_url' => $fbPost['full_picture'] ?? null,
                        'temps_creer_post' => $fbPost['created_time'] ?? null,
                        'lien' => $fbPost['permalink_url'] ?? null,
                    ]
                );

                if ($post->wasRecentlyCreated) {
                    $postsCreated++;
                } else {
                    $postsUpdated++;
                }

                // ─── Etape 2 : Recuperer les commentaires de ce post ───
                $comments = $fb->getPostComments($postId);
                $commentsTotal += count($comments);

                foreach ($comments as $fbComment) {
                    $commentId = $fbComment['id'] ?? null;
                    if (!$commentId) continue;

                    $authorName = $fbComment['from']['name'] ?? 'Utilisateur Facebook';

                    $comment = Commentaire::updateOrCreate(
                        ['id_commentaire' => $commentId],
                        [
                            'post_id' => $postId,
                            'message_commentaire' => $fbComment['message'] ?? '',
                            'nom_auteur' => $authorName,
                            'temps_creer' => $fbComment['created_time'] ?? null,
                        ]
                    );

                    if ($comment->wasRecentlyCreated) {
                        $commentsCreated++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                $this->warn("  Erreur post {$postId}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resume
        $this->info('Import termine !');
        $this->table(
            ['Donnee', 'Nombre'],
            [
                ['Posts recuperes', count($posts)],
                ['Nouveaux posts', $postsCreated],
                ['Posts mis a jour', $postsUpdated],
                ['Commentaires recuperes', $commentsTotal],
                ['Nouveaux commentaires', $commentsCreated],
                ['Total posts en BDD', Post::count()],
                ['Total commentaires en BDD', Commentaire::count()],
                ['Erreurs', $errors],
            ]
        );

        return 0;
    }
}
