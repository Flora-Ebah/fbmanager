@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
<div class="page-header">
    <h1><i class="fa-solid fa-users-gear"></i> Gestion des Utilisateurs</h1>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <div style="position:relative;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleColMenu()" id="col-toggle-btn"><i class="fa-solid fa-table-columns"></i> Colonnes</button>
            <div id="col-menu" style="display:none; position:absolute; right:0; top:100%; margin-top:6px; background:var(--blanc); border:1px solid var(--gris-moyen); border-radius:var(--radius); padding:10px; z-index:50; min-width:200px; box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-id')"> # ID</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-user')"> Utilisateur</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-email')"> E-mail</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-pass')"> Mot de passe</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-role')"> Role</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-actif')"> Actif</label>
                <label class="col-check"><input type="checkbox" checked onchange="toggleCol('col-login')"> Derniere connexion</label>
            </div>
        </div>
        <button type="button" class="btn btn-success" onclick="showForm('create')"><i class="fa-solid fa-user-plus"></i> Nouvel utilisateur</button>
    </div>
</div>

{{-- Formulaire d'ajout / edition (au-dessus du tableau) --}}
<div id="user-form-card" class="card" style="display:none; margin-bottom:20px;">
    <div class="card-header">
        <span id="form-title"><i class="fa-solid fa-user-plus"></i> Nouvel utilisateur</span>
        <button type="button" style="background:none; border:none; color:var(--blanc); cursor:pointer; font-size:20px;" onclick="hideForm()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="card-body">
        <form method="POST" id="user-form" action="/users">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="f-username"><i class="fa-solid fa-user"></i> Nom d'utilisateur</label>
                    <input type="text" id="f-username" name="username" required placeholder="Nom d'utilisateur" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="f-email"><i class="fa-solid fa-envelope"></i> E-mail</label>
                    <input type="email" id="f-email" name="email" required placeholder="email@exemple.com" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="f-password"><i class="fa-solid fa-key"></i> Mot de passe</label>
                    <input type="password" id="f-password" name="password" placeholder="Mot de passe" class="form-input">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="f-role"><i class="fa-solid fa-shield-halved"></i> Role</label>
                    <select id="f-role" name="role" class="form-input">
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-success" onclick="confirmSubmit()"><i class="fa-solid fa-check"></i> Enregistrer</button>
                <button type="button" class="btn btn-secondary" onclick="hideForm()"><i class="fa-solid fa-xmark"></i> Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Forms de suppression --}}
@foreach($users as $u)
<form method="POST" action="/users/{{ $u->id }}" id="delete-form-{{ $u->id }}" style="display:none;">@csrf @method('DELETE')</form>
@endforeach

<div class="card">
    <table class="table-bordered">
        <thead>
            <tr>
                <th class="col-id">#</th>
                <th class="col-user">Utilisateur</th>
                <th class="col-email">E-mail</th>
                <th class="col-pass">Mot de passe</th>
                <th class="col-role">Role</th>
                <th class="col-actif">Actif</th>
                <th class="col-login">Derniere connexion</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
            <tr>
                <td class="col-id">{{ $u->id }}</td>
                <td class="col-user" style="font-weight:bold;">{{ $u->username }}</td>
                <td class="col-email">{{ $u->email }}</td>
                <td class="col-pass" style="color:#999;">&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</td>
                <td class="col-role">
                    <span class="badge {{ $u->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                        {{ strtoupper($u->role) }}
                    </span>
                </td>
                <td class="col-actif">
                    <span style="color: {{ $u->is_active ? 'var(--vert-ok)' : 'var(--rouge-france)' }}; font-family:'Outfit',sans-serif; font-size:20px;">
                        {{ $u->is_active ? 'Oui' : 'Non' }}
                    </span>
                </td>
                <td class="col-login" style="font-family:'Outfit',sans-serif; font-size:16px; color:#888;">
                    {{ $u->last_login ? $u->last_login->format('d/m/Y H:i') : 'Jamais' }}
                </td>
                <td>
                    <div class="action-btns">
                        <button type="button" class="btn btn-primary btn-sm" onclick="showForm('edit', {{ $u->id }}, '{{ addslashes($u->username) }}', '{{ $u->email }}', '{{ $u->role }}')"><i class="fa-solid fa-pen"></i> Modifier</button>
                        @if($u->username !== config('fbmanager.default_admin_username', 'admin'))
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->username) }}')"><i class="fa-solid fa-trash"></i> Suppr.</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="empty-state">Aucun utilisateur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
    <div class="pagination-wrapper">
        {{ $users->links() }}
    </div>
    @endif
</div>

{{-- Dialog de confirmation --}}
<div id="confirm-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--blanc); border-radius:var(--radius); padding:30px; max-width:440px; width:90%; border:2px solid var(--bleu-france);">
        <h3 style="font-family:'Outfit',sans-serif; font-size:26px; color:var(--bleu-france); margin-bottom:16px;">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--rouge-france);"></i> <span id="confirm-title"></span>
        </h3>
        <p id="confirm-message" style="font-family:'Outfit',sans-serif; font-size:15px; margin-bottom:24px; line-height:1.5;"></p>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeDialog()"><i class="fa-solid fa-xmark"></i> Annuler</button>
            <button type="button" class="btn btn-danger" id="confirm-btn"><i class="fa-solid fa-check"></i> Confirmer</button>
        </div>
    </div>
</div>

<style>
    .table-bordered { width: 100%; }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid var(--gris-moyen) !important;
    }
    .table-bordered th {
        border-color: rgba(255,255,255,0.2) !important;
        border-bottom: 2px solid var(--gris-moyen) !important;
    }
    .form-input {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        padding: 8px 12px;
        border: 2px solid var(--gris-moyen);
        border-radius: 4px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-input:focus {
        border-color: var(--bleu-france);
    }
    .action-btns {
        display: flex;
        gap: 6px;
        align-items: center;
        white-space: nowrap;
    }
    .action-btns .btn-sm {
        font-size: 15px;
        padding: 5px 10px;
        white-space: nowrap;
    }
    .col-check {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 0;
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        cursor: pointer;
        color: var(--gris-fonce);
    }
    .col-check input { cursor: pointer; width: 16px; height: 16px; }
    .col-hidden { display: none !important; }
</style>

<script>
    var currentEditId = null;

    // --- Column toggle ---
    function toggleColMenu() {
        var menu = document.getElementById('col-menu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('col-menu');
        var btn = document.getElementById('col-toggle-btn');
        if (menu && !menu.contains(e.target) && !btn.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
    function toggleCol(cls) {
        document.querySelectorAll('.' + cls).forEach(function(c) { c.classList.toggle('col-hidden'); });
    }

    // --- Form show/hide ---
    function showForm(mode, id, username, email, role) {
        var card = document.getElementById('user-form-card');
        var form = document.getElementById('user-form');
        var title = document.getElementById('form-title');
        var method = document.getElementById('form-method');

        if (mode === 'create') {
            title.innerHTML = '<i class="fa-solid fa-user-plus"></i> Nouvel utilisateur';
            form.action = '/users';
            method.value = 'POST';
            document.getElementById('f-username').value = '';
            document.getElementById('f-email').value = '';
            document.getElementById('f-password').value = '';
            document.getElementById('f-password').required = true;
            document.getElementById('f-password').placeholder = 'Mot de passe';
            document.getElementById('f-role').value = 'user';
            currentEditId = null;
            history.pushState(null, '', '/users/create');
        } else {
            title.innerHTML = '<i class="fa-solid fa-user-pen"></i> Modifier : ' + username;
            form.action = '/users/' + id;
            method.value = 'PUT';
            document.getElementById('f-username').value = username;
            document.getElementById('f-email').value = email;
            document.getElementById('f-password').value = '';
            document.getElementById('f-password').required = false;
            document.getElementById('f-password').placeholder = 'Inchange si vide';
            document.getElementById('f-role').value = role;
            currentEditId = id;
            history.pushState(null, '', '/users/' + id + '/edit');
        }

        card.style.display = 'block';
        document.getElementById('f-username').focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideForm() {
        showConfirmDialog('Annuler', 'Voulez-vous vraiment annuler ?', function() {
            document.getElementById('user-form-card').style.display = 'none';
            currentEditId = null;
            history.pushState(null, '', '/users');
        });
    }

    function confirmSubmit() {
        var label = currentEditId ? 'Modifier l\'utilisateur' : 'Creer un utilisateur';
        var msg = currentEditId ? 'Voulez-vous vraiment enregistrer les modifications ?' : 'Voulez-vous vraiment creer ce nouvel utilisateur ?';
        showConfirmDialog(label, msg, function() {
            document.getElementById('user-form').submit();
        });
    }

    // --- Delete ---
    function confirmDelete(id, username) {
        showConfirmDialog('Supprimer l\'utilisateur', 'Supprimer "' + username + '" ? Cette action est irreversible.', function() {
            document.getElementById('delete-form-' + id).submit();
        });
    }

    // --- Dialog ---
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

    // Auto-open si URL /create ou /edit
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname.includes('/users/create')) {
            showForm('create');
        }
        @if(isset($user))
        showForm('edit', {{ $user->id }}, '{{ addslashes($user->username) }}', '{{ $user->email }}', '{{ $user->role }}');
        @endif
    });
</script>
@endsection
