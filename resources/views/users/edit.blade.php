@extends('layouts.app')
@section('title', 'Modifier ' . $user->username)

@section('content')
<div class="page-header">
    <h1><i class="fa-solid fa-user-pen"></i> Modifier l'utilisateur</h1>
    <a href="/users" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Retour</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">Modifier : {{ $user->username }}</div>
    <div class="card-body">
        <form method="POST" action="/users/{{ $user->id }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                @error('username') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="password" name="password">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Utilisateur</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrateur</option>
                </select>
                @error('role') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>
</div>
@endsection
