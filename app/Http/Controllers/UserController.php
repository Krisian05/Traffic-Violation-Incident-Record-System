<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Lgu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('lgu')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $lgus = Lgu::orderBy('name')->get();
        return view('users.create', compact('lgus'));
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', "User '{$data['username']}' created successfully.");
    }

    public function edit(User $user)
    {
        $lgus = Lgu::orderBy('name')->get();
        return view('users.edit', compact('user', 'lgus'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $passwordRules = $user ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'role'     => ['required', Rule::in(UserRole::values())],
            'lgu_id'   => ['nullable', 'exists:lgus,id'],
            'password' => $passwordRules,
        ]);

        // Admin/Province Admin oversee every LGU and aren't pinned to one.
        if (!in_array($data['role'], UserRole::lguScopedValues(), true)) {
            $data['lgu_id'] = null;
        } elseif (empty($data['lgu_id'])) {
            throw ValidationException::withMessages([
                'lgu_id' => 'LGU is required for this role.',
            ]);
        }

        return $data;
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
