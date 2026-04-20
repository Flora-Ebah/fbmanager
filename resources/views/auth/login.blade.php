<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'FBManager') }} — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bleu-france: #002395;
            --bleu-fonce: #001a6e;
            --blanc: #FFFFFF;
            --rouge-france: #ED2939;
            --gris-clair: #F5F7FB;
            --gris-moyen: #E1E5EE;
            --gris-texte: #6B7280;
            --gris-fonce: #1F2937;
            --radius: 10px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--gris-clair);
            color: var(--gris-fonce);
            min-height: 100vh;
            display: flex;
        }
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        /* ── LEFT PANEL (branding) ── */
        .login-brand {
            flex: 1;
            background: linear-gradient(135deg, var(--bleu-france) 0%, var(--bleu-fonce) 100%);
            color: var(--blanc);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }
        .login-brand::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -150px; left: -150px;
            width: 500px; height: 500px;
            background: rgba(237,41,57,0.08);
            border-radius: 50%;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 1;
        }
        .brand-logo .logo-icon {
            width: 50px; height: 50px;
            background: var(--blanc);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bleu-france);
            font-weight: 800;
            font-size: 22px;
        }
        .brand-logo .brand-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .brand-hero {
            position: relative;
            z-index: 1;
            max-width: 480px;
        }
        .brand-hero h1 {
            font-size: 42px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        .brand-hero p {
            font-size: 17px;
            line-height: 1.6;
            opacity: 0.85;
            font-weight: 400;
        }
        .brand-features {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            opacity: 0.9;
        }
        .brand-feature i {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        /* ── RIGHT PANEL (form) ── */
        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--blanc);
        }
        .login-form-box {
            width: 100%;
            max-width: 420px;
        }
        .login-form-box h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--gris-fonce);
            margin-bottom: 8px;
        }
        .login-form-box .subtitle {
            font-size: 15px;
            color: var(--gris-texte);
            margin-bottom: 35px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--gris-fonce);
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gris-texte);
            font-size: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            border: 1.5px solid var(--gris-moyen);
            border-radius: var(--radius);
            outline: none;
            background: var(--gris-clair);
            color: var(--gris-fonce);
            transition: all 0.2s;
        }
        .form-group input:focus {
            border-color: var(--bleu-france);
            background: var(--blanc);
            box-shadow: 0 0 0 3px rgba(0,35,149,0.1);
        }
        .form-error {
            color: var(--rouge-france);
            font-size: 13px;
            margin-top: 6px;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--bleu-france);
            color: var(--blanc);
            border: none;
            border-radius: var(--radius);
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s;
            margin-top: 8px;
        }
        .btn-login:hover {
            background: var(--bleu-fonce);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,35,149,0.25);
        }
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        .login-footer-text {
            text-align: center;
            margin-top: 28px;
            font-size: 13px;
            color: var(--gris-texte);
        }

        @media (max-width: 900px) {
            .login-brand { display: none; }
            .login-form-panel { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-brand">
            <div class="brand-logo">
                <div class="logo-icon">fb</div>
                <div class="brand-name">FBManager</div>
            </div>

            <div class="brand-hero">
                <h1>Gérez votre communauté Facebook en toute simplicité.</h1>
                <p>Une plateforme centralisée pour suivre vos publications, modérer vos commentaires et répondre à vos abonnés avec l'aide de l'intelligence artificielle.</p>
            </div>

            <div class="brand-features">
                <div class="brand-feature">
                    <i class="fa-brands fa-facebook"></i>
                    <span>Suivi en temps réel de vos publications</span>
                </div>
                <div class="brand-feature">
                    <i class="fa-solid fa-comments"></i>
                    <span>Gestion centralisée des commentaires</span>
                </div>
                <div class="brand-feature">
                    <i class="fa-solid fa-robot"></i>
                    <span>Réponses suggérées par l'IA</span>
                </div>
            </div>
        </div>

        <div class="login-form-panel">
            <div class="login-form-box">
                <h2>Bon retour 👋</h2>
                <p class="subtitle">Connectez-vous à votre espace FBManager.</p>

                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="/login">
                    @csrf
                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                        </div>
                        @error('email') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" required placeholder="••••••••">
                        </div>
                        @error('password') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn-login">
                        Se connecter
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="login-footer-text">
                    © {{ date('Y') }} FBManager — Tous droits réservés
                </div>
            </div>
        </div>
    </div>
</body>
</html>
