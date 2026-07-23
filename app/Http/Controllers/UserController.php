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
        return view('users.create', compact('lgus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'role'     => ['required', 'in:admin,super_admin,province_admin,operator,lgu_admin,treasurer,cashier,traffic_supervisor,supervisor,traffic_officer,issuing_officer,records_officer,auditor,view_only'],
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
        return view('users.edit', compact('user', 'lgus'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', "unique:users,username,{$user->id}", 'alpha_dash'],
            'role'     => ['required', 'in:admin,super_admin,province_admin,operator,lgu_admin,treasurer,cashier,traffic_supervisor,supervisor,traffic_officer,issuing_officer,records_officer,auditor,view_only'],
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
