<?php

namespace App\Http\Controllers;

use App\Models\DeviceRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceRegistrationController extends Controller
{
    public function update(Request $request, User $user, DeviceRegistration $device)
    {
        $currentUser = Auth::user();
        if ($currentUser->id !== $user->id && !$currentUser->isAdmin() && !$currentUser->isLguAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        abort_unless((int) $device->user_id === (int) $user->id, 404);

        $validated = $request->validate([
            'label' => 'required|string|max:100',
        ]);

        $device->update([
            'label' => $validated['label'],
        ]);

        return back()->with('success', 'Device name updated successfully.');
    }

    public function destroy(User $user, DeviceRegistration $device)
    {
        $currentUser = Auth::user();
        if ($currentUser->id !== $user->id && !$currentUser->isAdmin() && !$currentUser->isLguAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        abort_unless((int) $device->user_id === (int) $user->id, 404);

        $device->delete();

        return back()->with('success', 'Device registration revoked.');
    }
}
