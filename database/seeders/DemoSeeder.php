<?php
namespace Database\Seeders;

use App\Models\Post;
use App\Models\Commentaire;
use App\Models\MessengerConversation;
use App\Models\MessengerMessage;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['post_id' => 'fb_post_001', 'message_post' => 'Découvrez notre nouvelle collection printemps 2026 ! Des couleurs vibrantes et des matières nobles pour sublimer votre quotidien.', 'image_url' => 'https://picsum.photos/seed/post1/800/600', 'temps_creer_post' => '2026-03-20 10:30:00', 'lien' => 'https://facebook.com/post/001'],
            ['post_id' => 'fb_post_002', 'message_post' => 'SOLDES FLASH -50% sur toute la boutique ! Offre valable 48h seulement. Ne ratez pas cette occasion unique !', 'image_url' => 'https://picsum.photos/seed/post2/800/600', 'temps_creer_post' => '2026-03-19 14:00:00', 'lien' => 'https://facebook.com/post/002'],
            ['post_id' => 'fb_post_003', 'message_post' => 'Merci à tous nos clients fidèles ! Nous avons franchi le cap des 10 000 abonnés. Pour fêter ça, un concours exceptionnel arrive...', 'image_url' => 'https://picsum.photos/seed/post3/800/600', 'temps_creer_post' => '2026-03-18 09:15:00', 'lien' => 'https://facebook.com/post/003'],
            ['post_id' => 'fb_post_004', 'message_post' => 'Notre équipe au salon professionnel de Paris. Venez nous rencontrer stand B42 !', 'image_url' => 'https://picsum.photos/seed/post4/800/600', 'temps_creer_post' => '2026-03-17 16:45:00', 'lien' => 'https://facebook.com/post/004'],
            ['post_id' => 'fb_post_005', 'message_post' => 'Nouveau tuto vidéo : Comment entretenir vos produits pour qu\'ils durent plus longtemps ? Lien en bio !', 'image_url' => 'https://picsum.photos/seed/post5/800/600', 'temps_creer_post' => '2026-03-16 11:00:00', 'lien' => 'https://facebook.com/post/005'],
        ];

        foreach ($posts as $p) {
            Post::create($p);
        }

        $comments = [
            ['post_id' => 'fb_post_001', 'id_commentaire' => 'cmt_001', 'message_commentaire' => 'Magnifique ! J\'adore les couleurs de cette collection !', 'nom_auteur' => 'Marie Dupont', 'temps_creer' => '2026-03-20 11:00:00'],
            ['post_id' => 'fb_post_001', 'id_commentaire' => 'cmt_002', 'message_commentaire' => 'Est-ce que vous livrez en Belgique ?', 'nom_auteur' => 'Jean Martin', 'temps_creer' => '2026-03-20 11:30:00'],
            ['post_id' => 'fb_post_001', 'id_commentaire' => 'cmt_003', 'message_commentaire' => 'Les prix sont indiqués où svp ?', 'nom_auteur' => 'Sophie Laurent', 'temps_creer' => '2026-03-20 12:00:00'],
            ['post_id' => 'fb_post_002', 'id_commentaire' => 'cmt_004', 'message_commentaire' => 'Commande passée ! Merci pour cette promo incroyable !', 'nom_auteur' => 'Pierre Moreau', 'temps_creer' => '2026-03-19 14:30:00'],
            ['post_id' => 'fb_post_002', 'id_commentaire' => 'cmt_005', 'message_commentaire' => 'C\'est cumulable avec le code fidélité ?', 'nom_auteur' => 'Claire Petit', 'temps_creer' => '2026-03-19 15:00:00'],
            ['post_id' => 'fb_post_003', 'id_commentaire' => 'cmt_006', 'message_commentaire' => 'Bravo ! Vous le méritez, toujours au top !', 'nom_auteur' => 'Lucas Bernard', 'temps_creer' => '2026-03-18 10:00:00'],
            ['post_id' => 'fb_post_003', 'id_commentaire' => 'cmt_007', 'message_commentaire' => 'Hâte de voir le concours !!!', 'nom_auteur' => 'Emma Thomas', 'temps_creer' => '2026-03-18 10:30:00'],
            ['post_id' => 'fb_post_004', 'id_commentaire' => 'cmt_008', 'message_commentaire' => 'On passe demain au stand !', 'nom_auteur' => 'Hugo Robert', 'temps_creer' => '2026-03-17 17:00:00'],
            ['post_id' => 'fb_post_005', 'id_commentaire' => 'cmt_009', 'message_commentaire' => 'Super utile ce tuto, merci beaucoup !', 'nom_auteur' => 'Léa Richard', 'temps_creer' => '2026-03-16 11:30:00'],
            ['post_id' => 'fb_post_005', 'id_commentaire' => 'cmt_010', 'message_commentaire' => 'Vous pourriez faire un tuto sur le nettoyage aussi ?', 'nom_auteur' => 'Antoine Durand', 'temps_creer' => '2026-03-16 12:00:00'],
        ];

        foreach ($comments as $c) {
            Commentaire::create($c);
        }

        $convos = [
            ['conversation_id' => 'conv_001', 'nom_expediteur' => 'Marie Dupont', 'dernier_message' => 'D\'accord, merci pour votre réponse rapide !', 'temps_dernier_message' => '2026-03-20 15:30:00', 'nombre_messages' => 4, 'statut' => 'lu'],
            ['conversation_id' => 'conv_002', 'nom_expediteur' => 'Jean Martin', 'dernier_message' => 'Bonjour, je n\'ai toujours pas reçu ma commande...', 'temps_dernier_message' => '2026-03-21 09:00:00', 'nombre_messages' => 3, 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_003', 'nom_expediteur' => 'Sophie Laurent', 'dernier_message' => 'Est-ce possible d\'avoir un devis ?', 'temps_dernier_message' => '2026-03-19 16:00:00', 'nombre_messages' => 2, 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_004', 'nom_expediteur' => 'Pierre Moreau', 'dernier_message' => 'Merci, bonne journée !', 'temps_dernier_message' => '2026-03-18 14:00:00', 'nombre_messages' => 6, 'statut' => 'lu'],
            ['conversation_id' => 'conv_005', 'nom_expediteur' => 'Claire Petit', 'dernier_message' => 'Je voudrais échanger un article, c\'est possible ?', 'temps_dernier_message' => '2026-03-22 10:00:00', 'nombre_messages' => 1, 'statut' => 'non_lu'],
        ];

        foreach ($convos as $cv) {
            MessengerConversation::create($cv);
        }

        $msgs = [
            ['conversation_id' => 'conv_001', 'nom_expediteur' => 'Marie Dupont', 'message' => 'Bonjour, je voudrais savoir si le produit X est disponible en bleu ?', 'temps_envoi' => '2026-03-20 14:00:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_001', 'nom_expediteur' => 'Page', 'message' => 'Bonjour Marie ! Oui, le produit X est disponible en bleu. Souhaitez-vous le commander ?', 'temps_envoi' => '2026-03-20 14:15:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_001', 'nom_expediteur' => 'Marie Dupont', 'message' => 'Oui je le prends ! Comment faire ?', 'temps_envoi' => '2026-03-20 15:00:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_001', 'nom_expediteur' => 'Marie Dupont', 'message' => 'D\'accord, merci pour votre réponse rapide !', 'temps_envoi' => '2026-03-20 15:30:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_002', 'nom_expediteur' => 'Jean Martin', 'message' => 'Bonjour, j\'ai passé commande il y a 10 jours et je n\'ai rien reçu.', 'temps_envoi' => '2026-03-21 08:00:00', 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_002', 'nom_expediteur' => 'Jean Martin', 'message' => 'Mon numéro de commande est CMD-2026-4521', 'temps_envoi' => '2026-03-21 08:30:00', 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_002', 'nom_expediteur' => 'Jean Martin', 'message' => 'Bonjour, je n\'ai toujours pas reçu ma commande...', 'temps_envoi' => '2026-03-21 09:00:00', 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_003', 'nom_expediteur' => 'Sophie Laurent', 'message' => 'Bonjour, je suis intéressée par vos services pour mon entreprise.', 'temps_envoi' => '2026-03-19 15:30:00', 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_003', 'nom_expediteur' => 'Sophie Laurent', 'message' => 'Est-ce possible d\'avoir un devis ?', 'temps_envoi' => '2026-03-19 16:00:00', 'statut' => 'non_lu'],
            ['conversation_id' => 'conv_004', 'nom_expediteur' => 'Pierre Moreau', 'message' => 'Bonjour ! Ma commande est bien arrivée, tout est parfait !', 'temps_envoi' => '2026-03-18 10:00:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_004', 'nom_expediteur' => 'Page', 'message' => 'Merci Pierre ! Ravi que tout vous convienne.', 'temps_envoi' => '2026-03-18 10:30:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_004', 'nom_expediteur' => 'Pierre Moreau', 'message' => 'Merci, bonne journée !', 'temps_envoi' => '2026-03-18 14:00:00', 'statut' => 'lu'],
            ['conversation_id' => 'conv_005', 'nom_expediteur' => 'Claire Petit', 'message' => 'Je voudrais échanger un article, c\'est possible ?', 'temps_envoi' => '2026-03-22 10:00:00', 'statut' => 'non_lu'],
        ];

        foreach ($msgs as $m) {
            MessengerMessage::create($m);
        }
    }
}
