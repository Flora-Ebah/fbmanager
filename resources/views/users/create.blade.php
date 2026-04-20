@extends('layouts.app')
@section('title', 'Nouvel utilisateur')

@section('content')
<div class="page-header">
    <h1><i class="fa-solid fa-user-plus"></i> Nouvel Utilisateur</h1>
    <a href="/users" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">Creer un utilisateur</div>
    <div class="card-body">
        <form method="POST" action="/users">
            @csrf
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required>
                @error('username') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Utilisateur</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrateur</option>
                </select>
                @error('role') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-success">Creer l'utilisateur</button>
        </form>
    </div>
</div>
@endsection
