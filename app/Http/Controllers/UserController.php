<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->input('role');
        $lguId = $request->input('lgu_id');
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $query = User::with('lgu', 'devices')->orderBy('name');
        
        $currentUser = Auth::user();
        if ($currentUser->isOperator() && !$currentUser->isAdmin()) {
            if ($currentUser->lgu_id) {
                $query->where('lgu_id', $currentUser->lgu_id);
            }
        }

        if (!empty($role)) {
            $query->where('role', $role);
        }

        if (!empty($lguId)) {
            $query->where('lgu_id', $lguId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('badge_id', 'ilike', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();
        $lgus = Lgu::orderBy('name')->get();

        return view('users.index', compact('users', 'lgus', 'role', 'lguId', 'status', 'search'));
    }

    public function create(): View
    {
        $lgus = Lgu::orderBy('name')->get();
        return view('users.create', compact('lgus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'alpha_dash'],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'role'     => ['required', 'in:admin,super_admin,province_admin,operator,lgu_admin,treasurer,cashier,traffic_supervisor,supervisor,traffic_officer,issuing_officer,records_officer,auditor,view_only'],
            'agency'   => ['nullable', 'string', 'max:100'],
            'badge_id' => ['nullable', 'string', 'max:50'],
            'status'   => ['nullable', 'in:active,inactive'],
            'lgu_id'   => ['nullable', 'exists:lgus,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['status'] = $data['status'] ?? 'active';

        $user = User::create($data);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->useLog('user')
            ->log("Created new user account '{$user->username}'.");

        return redirect()->route('users.index')
            ->with('success', "User '{$user->username}' created successfully.");
    }

    public function edit(User $user): View
    {
        $lgus = Lgu::orderBy('name')->get();
        $user->loadMissing('devices');
        return view('users.edit', compact('user', 'lgus'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', "unique:users,username,{$user->id}", 'alpha_dash'],
            'email'    => ['nullable', 'email', 'max:150', "unique:users,email,{$user->id}"],
            'role'     => ['required', 'in:admin,super_admin,province_admin,operator,lgu_admin,treasurer,cashier,traffic_supervisor,supervisor,traffic_officer,issuing_officer,records_officer,auditor,view_only'],
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

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->useLog('user')
            ->log("Updated user account '{$user->username}'.");

        return redirect()->route('users.index')
            ->with('success', "User '{$user->username}' updated successfully.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = ($user->status === 'inactive') ? 'active' : 'inactive';
        $user->update(['status' => $newStatus]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->useLog('user')
            ->log("Changed user status for '{$user->username}' to {$newStatus}.");

        return back()->with('success', "User '{$user->username}' status changed to {$newStatus}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $request->password,
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->useLog('user')
            ->log("Administrator reset password for user '{$user->username}'.");

        return back()->with('success', "Password for '{$user->username}' reset successfully.");
    }

    public function revokeSessions(User $user): RedirectResponse
    {
        // Delete web sessions from sessions table if exists
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // ignore if sessions table driver is not database
        }

        // Delete device registrations
        $user->devices()->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->useLog('security')
            ->log("Revoked all active sessions and device tokens for '{$user->username}'.");

        return back()->with('success', "All sessions and registered devices for '{$user->username}' have been revoked.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $username = $user->username;
        $user->delete();

        activity()
            ->causedBy(Auth::user())
            ->useLog('user')
            ->log("Deleted user account '{$username}'.");

        return redirect()->route('users.index')
            ->with('success', "User '{$username}' deleted successfully.");
    }
}
