<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with('lgu')->orderBy('name');
        
        $currentUser = Auth::user();
        if ($currentUser->isOperator() && !$currentUser->isAdmin()) {
            if ($currentUser->lgu_id) {
                $query->where('lgu_id', $currentUser->lgu_id);
            }
        }
        
        $users = $query->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $lgus = Lgu::orderBy('name')->get();
        $roles = Auth::user()->assignableRoles();
        return view('users.create', compact('lgus', 'roles'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $allowedRoleKeys = array_keys($currentUser->assignableRoles());
        if ($currentUser->isSuperAdmin()) {
            $allowedRoleKeys = array_merge($allowedRoleKeys, ['super_admin', 'lgu_admin']);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'role'     => ['required', 'in:' . implode(',', array_unique($allowedRoleKeys))],
            'agency'   => ['nullable', 'string', 'max:100'],
            'badge_id' => ['nullable', 'string', 'max:50'],
            'status'   => ['nullable', 'in:active,inactive'],
            'lgu_id'   => ['nullable', 'exists:lgus,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', "User '{$data['username']}' created successfully.");
    }

    public function edit(User $user)
    {
        $lgus = Lgu::orderBy('name')->get();
        $user->loadMissing('devices');
        $roles = Auth::user()->assignableRoles();
        if (!isset($roles[$user->role])) {
            $roles[$user->role] = User::ROLES[$user->role] ?? ucfirst($user->role);
        }
        return view('users.edit', compact('user', 'lgus', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $allowedRoleKeys = array_keys($currentUser->assignableRoles());
        $allowedRoleKeys[] = $user->role;
        if ($currentUser->isSuperAdmin()) {
            $allowedRoleKeys = array_merge($allowedRoleKeys, ['super_admin', 'lgu_admin']);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', "unique:users,username,{$user->id}", 'alpha_dash'],
            'role'     => ['required', 'in:' . implode(',', array_unique($allowedRoleKeys))],
            'agency'   => ['nullable', 'string', 'max:100'],
            'badge_id' => ['nullable', 'string', 'max:50'],
            'status'   => ['nullable', 'in:active,inactive'],
            'lgu_id'   => ['nullable', 'exists:lgus,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted.');
    }
}
