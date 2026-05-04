<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    public function suggestReply(string $postContent, string $commentContent, string $authorName, string $tone = 'professional'): string
    {
        $apiKey = config('fbmanager.groq_api_key');

        if (!$apiKey) {
            return 'Erreur : Cle API Groq non configuree.';
        }

        $toneLabels = [
            'professional' => 'professionnel et courtois',
            'friendly' => 'amical et chaleureux',
            'casual' => 'decontracte et sympathique',
        ];

        $toneDesc = $toneLabels[$tone] ?? $toneLabels['professional'];

        $systemPrompt = "Tu es un assistant de community management pour la SODECI (Societe de Distribution d'Eau de Cote d'Ivoire). Tu aides a rediger des reponses aux commentaires Facebook. Tes reponses doivent etre en francais, sur un ton {$toneDesc}. Sois concis (2-3 phrases max). Ne mets pas de guillemets autour de ta reponse.";

        $userPrompt = "Publication Facebook :\n\"{$postContent}\"\n\nCommentaire de {$authorName} :\n\"{$commentContent}\"\n\nRedige une reponse appropriee a ce commentaire.";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'temperature' => 0.7,
                'max_tokens' => 300,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Erreur : reponse vide.';
            }

            return 'Erreur API : ' . ($response->json('error.message') ?? 'Erreur inconnue');
        } catch (\Exception $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }

    public function suggestMultipleReplies(string $postContent, string $commentContent, string $authorName): array
    {
        $replies = [];
        foreach (['professional', 'friendly', 'casual'] as $tone) {
            $replies[$tone] = $this->suggestReply($postContent, $commentContent, $authorName, $tone);
        }
        return $replies;
    }

    /**
     * Suggerer une reponse a un message Messenger en tenant compte du contexte
     */
    public function suggestMessengerReply(array $contextMessages, string $lastMessage, string $authorName, string $tone = 'professional'): string
    {
        $apiKey = config('fbmanager.groq_api_key');

        if (!$apiKey) {
            return 'Erreur : Cle API Groq non configuree.';
        }

        $toneLabels = [
            'professional' => 'professionnel et courtois',
            'friendly' => 'amical et chaleureux',
            'casual' => 'decontracte et sympathique',
        ];

        $toneDesc = $toneLabels[$tone] ?? $toneLabels['professional'];

        $systemPrompt = "Tu es un agent de service client SODECI (Societe de Distribution d'Eau de Cote d'Ivoire) qui repond aux messages prives Messenger. Tu reponds en francais, sur un ton {$toneDesc}. Sois concis (2-4 phrases max). Tiens compte de tout le contexte de la conversation. Ne mets pas de guillemets autour de ta reponse, ne signe pas le message.";

        // Construire le contexte de la conversation
        $contextStr = "";
        foreach ($contextMessages as $m) {
            $sender = $m['is_page'] ? 'SODECI' : $authorName;
            $contextStr .= "{$sender}: {$m['message']}\n";
        }

        $userPrompt = "Voici l'historique de la conversation Messenger :\n\n{$contextStr}\nDernier message de {$authorName} : \"{$lastMessage}\"\n\nRedige la reponse de SODECI a ce dernier message en tenant compte de tout le contexte.";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'temperature' => 0.7,
                'max_tokens' => 400,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Erreur : reponse vide.';
            }

            return 'Erreur API : ' . ($response->json('error.message') ?? 'Erreur inconnue');
        } catch (\Exception $e) {
            return 'Erreur : ' . $e->getMessage();
        }
    }
}
