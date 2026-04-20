<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return redirect('/users')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('users.index', compact('users', 'user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
        ]);

        $data = $request->only('username', 'email', 'role');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);
        return redirect('/users')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->username === config('fbmanager.default_admin_username', 'admin')) {
            return redirect('/users')->with('error', 'Impossible de supprimer l\'administrateur principal.');
        }

        $user->delete();
        return redirect('/users')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
