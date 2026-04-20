@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div style="max-width: 440px; margin: 60px auto;">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-lock"></i> Connexion
        </div>
        <div class="card-body">
            <form method="POST" action="/login">
                @csrf
                <div class="form-group">
                    <label for="email"><i class="fa-solid fa-envelope"></i> Adresse e-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="votre@email.com">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password"><i class="fa-solid fa-key"></i> Mot de passe</label>
                    <input type="password" id="password" name="password" required placeholder="Votre mot de passe">
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content: center;"><i class="fa-solid fa-right-to-bracket"></i> Se connecter</button>
            </form>
        </div>
    </div>
</div>
@endsection
