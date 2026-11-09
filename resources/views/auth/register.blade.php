<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'FBManager') }} — Inscription</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --bleu-france: #002395; --bleu-fonce: #001a6e; --rouge-france: #ED2939; --canvas: #F5F7FA; --border: #E2E8F0; --text-primary: #0F172A; --text-secondary: #64748B; --radius: 10px; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--canvas); color: var(--text-primary); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .register-box { width: 100%; max-width: 440px; background: #fff; border-radius: 14px; padding: 36px; box-shadow: 0 10px 40px rgba(15,23,42,0.08); }
        .logo { width: 48px; height: 48px; background: linear-gradient(135deg, var(--bleu-france), var(--bleu-fonce)); color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-family: 'Sora', sans-serif; font-weight: 700; font-size: 18px; margin-bottom: 20px; }
        h2 { font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 700; margin-bottom: 6px; }
        .subtitle { color: var(--text-secondary); font-size: 14px; margin-bottom: 28px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; font-family: inherit; font-size: 14px; border: 1.5px solid var(--border); border-radius: var(--radius); background: #F8FAFC; outline: none; transition: all 0.15s; }
        input:focus { border-color: var(--bleu-france); background: #fff; box-shadow: 0 0 0 3px rgba(0,35,149,0.1); }
        .form-error { color: var(--rouge-france); font-size: 12px; margin-top: 4px; }
        .btn-primary { width: 100%; padding: 12px; background: var(--bleu-france); color: #fff; border: none; border-radius: var(--radius); font-family: inherit; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 8px; transition: all 0.15s; }
        .btn-primary:hover { background: var(--bleu-fonce); }
        .footer { text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-secondary); }
        .footer a { color: var(--bleu-france); font-weight: 500; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-box">
        <div class="logo">fb</div>
        <h2>Créer un compte</h2>
        <p class="subtitle">Rejoignez FBManager pour gérer votre communauté.</p>
        <form method="POST" action="/register">
            @csrf
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus>
                @error('username') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Adresse e-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>
        <div class="footer">
            Déjà un compte ? <a href="/login">Se connecter</a>
        </div>
    </div>
</body>
</html>
