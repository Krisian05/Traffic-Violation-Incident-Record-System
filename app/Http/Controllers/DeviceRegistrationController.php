<?php

namespace App\Http\Controllers;

use App\Models\DeviceRegistration;
use App\Models\User;

class DeviceRegistrationController extends Controller
{
    public function destroy(User $user, DeviceRegistration $device)
    {
        abort_unless($device->user_id === $user->id, 404);

        $device->delete();

        return back()->with('success', 'Device registration revoked.');
    }
}
