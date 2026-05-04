<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected string $baseUrl;
    protected string $pageId;
    protected string $accessToken;

    public function __construct()
    {
        $version = config('fbmanager.facebook_graph_version', 'v25.0');
        $this->baseUrl = "https://graph.facebook.com/{$version}";
        $this->pageId = config('fbmanager.facebook_page_id', '');
        $this->accessToken = config('fbmanager.facebook_page_access_token', '');
    }

    /**
     * Recuperer tous les posts de la page avec pagination
     */
    public function getPagePosts(int $limit = 100): array
    {
        $allPosts = [];
        $url = "{$this->baseUrl}/{$this->pageId}/feed";

        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,message,created_time,full_picture,permalink_url',
            'limit' => min($limit, 100),
        ];

        do {
            $response = $this->requestWithRetry($url, $params);

            if (!$response || !$response->successful()) {
                Log::error('Facebook API error (posts): ' . ($response ? $response->body() : 'no response'));
                break;
            }

            $data = $response->json();
            $posts = $data['data'] ?? [];
            $allPosts = array_merge($allPosts, $posts);

            // Pagination suivante : forcer le token si absent
            $nextUrl = $data['paging']['next'] ?? null;
            if ($nextUrl && !str_contains($nextUrl, 'access_token=')) {
                $sep = str_contains($nextUrl, '?') ? '&' : '?';
                $nextUrl .= $sep . 'access_token=' . urlencode($this->accessToken);
            }
            $url = $nextUrl;
            $params = [];

            if (count($allPosts) >= $limit) {
                break;
            }
        } while ($url);

        return $allPosts;
    }

    /**
     * Recuperer les commentaires d'un post avec pagination
     */
    public function getPostComments(string $postId, int $limit = 500): array
    {
        $allComments = [];
        $url = "{$this->baseUrl}/{$postId}/comments";

        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,message,from,created_time',
            'limit' => min($limit, 100),
            'order' => 'reverse_chronological',
        ];

        do {
            $response = $this->requestWithRetry($url, $params);

            if (!$response || !$response->successful()) {
                $errorCode = $response ? $response->json('error.code') : null;
                // Code 104 = post non accessible (pub, cross-post, etc.) - on skip silencieusement
                if ($errorCode === 104) {
                    Log::info("Post {$postId} inaccessible (probablement publicite/cross-post) - ignore");
                } else {
                    Log::error("Facebook API error (comments for {$postId}): " . ($response ? $response->body() : 'no response'));
                }
                break;
            }

            $data = $response->json();
            $comments = $data['data'] ?? [];
            $allComments = array_merge($allComments, $comments);

            // Pagination suivante
            $url = $data['paging']['next'] ?? null;
            $params = [];

            if (count($allComments) >= $limit || count($comments) === 0) {
                break;
            }
        } while ($url);

        return $allComments;
    }

    /**
     * Effectuer une requete HTTP avec retry et backoff
     */
    protected function requestWithRetry(string $url, array $params = [], int $maxRetries = 3): ?\Illuminate\Http\Client\Response
    {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                usleep(300_000); // 300ms de delai entre chaque requete

                $response = Http::timeout(30)
                    ->connectTimeout(15)
                    ->get($url, $params);

                return $response;
            } catch (\Exception $e) {
                Log::warning("Facebook API tentative {$attempt}/{$maxRetries} echouee: " . $e->getMessage());

                if ($attempt < $maxRetries) {
                    sleep($attempt * 2); // Backoff: 2s, 4s
                }
            }
        }

        return null;
    }

    /**
     * Recuperer les conversations Messenger de la page
     */
    public function getConversations(int $limit = 100): array
    {
        $allConvos = [];
        $url = "{$this->baseUrl}/{$this->pageId}/conversations";

        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,updated_time,message_count,unread_count,participants,snippet',
            'limit' => min($limit, 100),
        ];

        do {
            $response = $this->requestWithRetry($url, $params);

            if (!$response || !$response->successful()) {
                Log::error('Facebook API error (conversations): ' . ($response ? $response->body() : 'no response'));
                break;
            }

            $data = $response->json();
            $convos = $data['data'] ?? [];
            $allConvos = array_merge($allConvos, $convos);

            $nextUrl = $data['paging']['next'] ?? null;
            if ($nextUrl && !str_contains($nextUrl, 'access_token=')) {
                $sep = str_contains($nextUrl, '?') ? '&' : '?';
                $nextUrl .= $sep . 'access_token=' . urlencode($this->accessToken);
            }
            $url = $nextUrl;
            $params = [];

            if (count($allConvos) >= $limit) {
                break;
            }
        } while ($url);

        return $allConvos;
    }

    /**
     * Recuperer les messages d'une conversation
     */
    public function getConversationMessages(string $conversationId, int $limit = 100): array
    {
        $allMessages = [];
        $url = "{$this->baseUrl}/{$conversationId}/messages";

        $params = [
            'access_token' => $this->accessToken,
            'fields' => 'id,message,from,created_time',
            'limit' => min($limit, 100),
        ];

        do {
            $response = $this->requestWithRetry($url, $params);

            if (!$response || !$response->successful()) {
                Log::error("Facebook API error (messages for {$conversationId}): " . ($response ? $response->body() : 'no response'));
                break;
            }

            $data = $response->json();
            $messages = $data['data'] ?? [];
            $allMessages = array_merge($allMessages, $messages);

            $nextUrl = $data['paging']['next'] ?? null;
            if ($nextUrl && !str_contains($nextUrl, 'access_token=')) {
                $sep = str_contains($nextUrl, '?') ? '&' : '?';
                $nextUrl .= $sep . 'access_token=' . urlencode($this->accessToken);
            }
            $url = $nextUrl;
            $params = [];

            if (count($allMessages) >= $limit) {
                break;
            }
        } while ($url);

        return $allMessages;
    }

    /**
     * Tester la connexion a l'API Facebook
     */
    public function testConnection(): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/{$this->pageId}", [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,category,fan_count',
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('error.message') ?? 'Erreur inconnue',
        ];
    }
}
