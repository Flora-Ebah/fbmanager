<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FBManager — Politique de Confidentialité</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bleu-france: #002395;
            --blanc: #FFFFFF;
            --gris-clair: #F0F0F0;
            --gris-fonce: #333333;
            --radius: 6px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--gris-clair);
            color: var(--gris-fonce);
            min-height: 100vh;
        }
        .header {
            background: var(--bleu-france);
            color: var(--blanc);
            padding: 30px 0;
        }
        .header-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 60px;
        }
        .header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
        }
        .header p {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            opacity: 0.8;
            margin-top: 5px;
        }
        .content {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px 60px;
            background: var(--blanc);
            border-radius: var(--radius);
            border: 1px solid #D4D4D4;
        }
        h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            color: var(--bleu-france);
            margin: 25px 0 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--bleu-france);
        }
        h2:first-of-type { margin-top: 0; }
        p, li {
            font-size: 15px;
            line-height: 1.7;
            margin-bottom: 10px;
        }
        ul { padding-left: 25px; margin-bottom: 15px; }
        .footer {
            text-align: center;
            padding: 20px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            color: #999;
        }
        @media (max-width: 768px) {
            .header { padding: 20px 0; }
            .header-inner { padding: 0 20px; }
            .content { padding: 20px; margin: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-inner">
            <h1>Politique de Confidentialité</h1>
            <p>FBManager — Facebook Comment Manager</p>
        </div>
    </div>

    <div class="content">
        <h2>1. Introduction</h2>
        <p>FBManager est une application de gestion de commentaires Facebook destinée aux community managers. La présente politique de confidentialité décrit comment nous collectons, utilisons et protégeons les données traitees par notre application.</p>
        <p>Dernière mise à jour : {{ date('d/m/Y') }}</p>

        <h2>2. Données collectées</h2>
        <p>Notre application collecte et traite les données suivantes vià l'API Facebook Graph :</p>
        <ul>
            <li><strong>Publications de Page :</strong> identifiant, contenu textuel, image, date de publication et lien permanent</li>
            <li><strong>Commentaires :</strong> identifiant, contenu du commentaire, nom de l'auteur et date de publication</li>
            <li><strong>Conversations Messenger :</strong> identifiant de conversation, nom de l'expéditeur, contenu des messages et horodatage</li>
        </ul>
        <p>Nous collectons également les données de compte utilisateur de l'application (nom d'utilisateur, mot de passe hashé).</p>

        <h2>3. Finalité du traitement</h2>
        <p>Les données collectées sont utilisées exclusivement pour :</p>
        <ul>
            <li>Afficher les publications et commentaires de la Page Facebook dans un tableau de bord centralisé</li>
            <li>Permettre aux community managers de consulter et gérer les interactions avec les abonnés</li>
            <li>Générer des suggestions de réponses vià l'intelligence artificielle pour faciliter le travail des agents</li>
            <li>Conserver un historique des réponses générées pour assurer la traçabilité</li>
        </ul>

        <h2>4. Stockage et sécurité des données</h2>
        <ul>
            <li>Les données sont stockées dans une base de données MySQL sécurisée</li>
            <li>L'accès à l'application est protégé par authentification (identifiant et mot de passe)</li>
            <li>Les mots de passe sont hashés avec l'algorithme bcrypt</li>
            <li>Les sessions sont protégées par des tokens CSRF</li>
            <li>Seuls les utilisateurs autorisés par l'administrateur ont accès aux données</li>
        </ul>

        <h2>5. Partage des données</h2>
        <p>Nous ne vendons, ne louons ni ne partageons les données personnelles collectées avec des tiers, sauf dans les cas suivants :</p>
        <ul>
            <li><strong>API Groq/OpenAI :</strong> le contenu des commentaires est transmis à l'API d'intelligence artificielle uniquement dans le but de générer des suggestions de réponses. Aucune donnée personnelle identifiable n'est stockée par ce service au-delà du traitement de la requête.</li>
            <li><strong>Obligation légale :</strong> si la loi l'exige</li>
        </ul>

        <h2>6. Conservation des données</h2>
        <p>Les données sont conservées aussi longtemps que nécessaire pour les finalités décrites ci-dessus. Les données peuvent être supprimées sur demande de l'administrateur de l'application.</p>

        <h2>7. Droits des utilisateurs</h2>
        <p>Conformément a la réglementation applicable, les utilisateurs disposent des droits suivants :</p>
        <ul>
            <li>Droit d'accès aux données les concernant</li>
            <li>Droit de rectification des données inexactes</li>
            <li>Droit à l'effacement des données</li>
            <li>Droit à la limitation du traitement</li>
        </ul>
        <p>Pour exercer ces droits, veuillez contacter l'administrateur de l'application.</p>

        <h2>8. Utilisation des API Facebook</h2>
        <p>Notre application utilise l'API Facebook Graph conformément aux conditions d'utilisation de la plateforme Meta. Les données Facebook sont :</p>
        <ul>
            <li>Utilisées uniquement dans le cadre de la gestion de la Page Facebook</li>
            <li>Accessibles uniquement aux utilisateurs autorisés de l'application</li>
            <li>Non utilisées à des fins publicitaires ou de profilage</li>
            <li>Traitées conformément aux politiques de la plateforme Meta</li>
        </ul>

        <h2>9. Suppression des données</h2>
        <p>Pour demander la suppression de vos données, vous pouvez :</p>
        <ul>
            <li>Contacter l'administrateur de l'application</li>
            <li>Révoquer l'accès de l'application via les paramètres de votre compte Facebook (<a href="https://www.facebook.com/settings?tab=business_tools" target="_blank">Paramètres > Applications et sites web</a>)</li>
        </ul>

        <h2>10. Contact</h2>
        <p>Pour toute question relative a cette politique de confidentialité, veuillez contacter l'administrateur de l'application FBManager.</p>
    </div>

    <div class="footer">
        FBManager &copy; {{ date('Y') }} — Facebook Comment Manager
    </div>
</body>
</html>
