@extends('layouts.app')
@section('title', 'Inscription')

@section('content')
<div style="max-width: 440px; margin: 60px auto;">
    <div class="card">
        <div class="card-header">
            &#9998; Inscription
        </div>
        <div class="card-body">
            <form method="POST" action="/register">
                @csrf
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus placeholder="VotreNom">
                    @error('username') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="votre@email.com">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="Minimum 6 caracteres">
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Retapez le mot de passe">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content: center;">Creer mon compte</button>
            </form>
            <p style="text-align:center; margin-top:16px; font-family: 'Poppins', sans-serif; font-size: 18px;">
                Deja un compte ? <a href="/login" style="color: var(--bleu-france);">Se connecter</a>
            </p>
        </div>
    </div>
</div>
@endsection
