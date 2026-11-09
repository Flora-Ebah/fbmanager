@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('main')
<main class="content" style="width:100%;">
    <div class="top-bar">
        <div class="top-bar__title">
            <i class="ph ph-users" style="color:var(--bleu-france);"></i>
            Utilisateurs
            <span class="breadcrumb">· {{ $users->total() }} au total</span>
        </div>
        <div class="top-bar__actions">
            <button type="button" class="btn btn-primary btn-sm" onclick="showForm('create')">
                <i class="ph ph-user-plus"></i> Nouvel utilisateur
            </button>
        </div>
    </div>

    <div class="content-full">
        <div class="content-full__inner">

            {{-- ─── Formulaire (caché par defaut) ─── --}}
            <div id="user-form-card" class="card" style="display:none; margin-bottom:20px;">
                <div class="card-header">
                    <span id="form-title"><i class="ph ph-user-plus"></i> Nouvel utilisateur</span>
                    <button type="button" class="btn btn-ghost btn-icon btn-sm" onclick="hideForm()"><i class="ph ph-x"></i></button>
                </div>
                <div class="card-body">
                    <form method="POST" id="user-form" action="/users">
                        @csrf
                        <input type="hidden" name="_method" id="form-method" value="POST">
                        <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:16px;">
                            <div class="form-group" style="margin-bottom:0;">
                                <label for="f-username">Nom d'utilisateur</label>
                                <input type="text" id="f-username" name="username" required placeholder="Nom d'utilisateur">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label for="f-email">E-mail</label>
                                <input type="email" id="f-email" name="email" required placeholder="email@exemple.com">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label for="f-password">Mot de passe</label>
                                <input type="password" id="f-password" name="password" placeholder="Mot de passe">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label for="f-role">Rôle</label>
                                <select id="f-role" name="role">
                                    <option value="user">Utilisateur</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; margin-top:20px;">
                            <button type="button" class="btn btn-primary" onclick="confirmSubmit()"><i class="ph ph-check"></i> Enregistrer</button>
                            <button type="button" class="btn btn-secondary" onclick="hideForm()">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Formulaires de suppression --}}
            @foreach($users as $u)
            <form method="POST" action="/users/{{ $u->id }}" id="delete-form-{{ $u->id }}" style="display:none;">@csrf @method('DELETE')</form>
            @endforeach

            {{-- ─── Tableau ─── --}}
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>E-mail</th>
                            <th>Rôle</th>
                            <th>Actif</th>
                            <th>Dernière connexion</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        @php
                            $initials = strtoupper(substr($u->username, 0, 2));
                            $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                            $color = $colors[abs(crc32($u->username)) % count($colors)];
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px; height:34px; border-radius:50%; background:{{ $color }}; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:12px;">{{ $initials }}</div>
                                    <span style="font-weight:600;">{{ $u->username }}</span>
                                </div>
                            </td>
                            <td style="color:var(--text-secondary);">{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ $u->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td>
                                @if($u->is_active)
                                    <span style="color:var(--vert-ok); font-weight:500;"><i class="ph ph-check-circle"></i> Oui</span>
                                @else
                                    <span style="color:var(--rouge-france); font-weight:500;"><i class="ph ph-x-circle"></i> Non</span>
                                @endif
                            </td>
                            <td style="color:var(--text-muted); font-size:13px;">
                                {{ $u->last_login ? $u->last_login->format('d/m/Y H:i') : 'Jamais' }}
                            </td>
                            <td style="text-align:right;">
                                <div style="display:flex; gap:6px; justify-content:flex-end;">
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="showForm('edit', {{ $u->id }}, '{{ addslashes($u->username) }}', '{{ $u->email }}', '{{ $u->role }}')" title="Modifier">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    @if($u->username !== config('fbmanager.default_admin_username', 'admin'))
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->username) }}')" title="Supprimer" style="color:var(--rouge-france);">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">Aucun utilisateur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div style="margin-top:16px; display:flex; justify-content:center;">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</main>

{{-- Dialog confirmation --}}
<div id="confirm-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:var(--radius-lg); padding:24px; max-width:420px; width:90%; box-shadow:var(--shadow-lg);">
        <h3 style="font-family:'Sora',sans-serif; font-size:17px; font-weight:600; margin-bottom:10px; display:flex; align-items:center; gap:10px;">
            <i class="ph ph-warning" style="color:var(--rouge-france);"></i>
            <span id="confirm-title"></span>
        </h3>
        <p id="confirm-message" style="font-size:14px; color:var(--text-secondary); margin-bottom:20px;"></p>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeDialog()">Annuler</button>
            <button type="button" class="btn btn-danger btn-sm" id="confirm-btn">Confirmer</button>
        </div>
    </div>
</div>

<script>
    var currentEditId = null;

    function showForm(mode, id, username, email, role) {
        var card = document.getElementById('user-form-card');
        var form = document.getElementById('user-form');
        var title = document.getElementById('form-title');
        var method = document.getElementById('form-method');

        if (mode === 'create') {
            title.innerHTML = '<i class="ph ph-user-plus"></i> Nouvel utilisateur';
            form.action = '/users';
            method.value = 'POST';
            document.getElementById('f-username').value = '';
            document.getElementById('f-email').value = '';
            document.getElementById('f-password').value = '';
            document.getElementById('f-password').required = true;
            document.getElementById('f-password').placeholder = 'Mot de passe';
            document.getElementById('f-role').value = 'user';
            currentEditId = null;
        } else {
            title.innerHTML = '<i class="ph ph-user"></i> Modifier : ' + username;
            form.action = '/users/' + id;
            method.value = 'PUT';
            document.getElementById('f-username').value = username;
            document.getElementById('f-email').value = email;
            document.getElementById('f-password').value = '';
            document.getElementById('f-password').required = false;
            document.getElementById('f-password').placeholder = 'Inchangé si vide';
            document.getElementById('f-role').value = role;
            currentEditId = id;
        }
        card.style.display = 'block';
        document.getElementById('f-username').focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideForm() {
        document.getElementById('user-form-card').style.display = 'none';
        currentEditId = null;
    }

    function confirmSubmit() {
        var msg = currentEditId ? 'Enregistrer les modifications ?' : 'Créer ce nouvel utilisateur ?';
        showConfirmDialog(currentEditId ? 'Modifier' : 'Créer', msg, function() {
            document.getElementById('user-form').submit();
        });
    }

    function confirmDelete(id, username) {
        showConfirmDialog('Supprimer', 'Supprimer "' + username + '" ? Cette action est irréversible.', function() {
            document.getElementById('delete-form-' + id).submit();
        });
    }

    function showConfirmDialog(title, message, onConfirm) {
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        var dialog = document.getElementById('confirm-dialog');
        dialog.style.display = 'flex';
        var btn = document.getElementById('confirm-btn');
        var newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        newBtn.addEventListener('click', function() { closeDialog(); onConfirm(); });
    }

    function closeDialog() {
        document.getElementById('confirm-dialog').style.display = 'none';
    }

    document.getElementById('confirm-dialog').addEventListener('click', function(e) {
        if (e.target === this) closeDialog();
    });

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname.includes('/users/create')) showForm('create');
        @if(isset($user))
        showForm('edit', {{ $user->id }}, '{{ addslashes($user->username) }}', '{{ $user->email }}', '{{ $user->role }}');
        @endif
    });
</script>
@endsection
