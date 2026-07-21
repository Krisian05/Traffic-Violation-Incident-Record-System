<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\DeviceRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_OFFICER_DEVICES = 2;

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'device_id' => ['nullable', 'string'],
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials do not match our records.'], 422);
        }

        // Validate device counts for traffic officers
        if ($user->isTrafficOfficer()) {
            $deviceId = $request->input('device_id');
            $deviceName = $request->input('device_name') ?: $request->userAgent() ?: 'Mobile Device';
            
            $deviceMatch = false;
            if ($deviceId) {
                $hash = hash('sha256', $deviceId);
                $device = DeviceRegistration::where('user_id', $user->id)
                    ->where('token_hash', $hash)
                    ->first();
                if ($device) {
                    $device->update([
                        'last_used_at' => now(),
                        'ip_address' => $request->ip(),
                    ]);
                    $deviceMatch = true;
                }
            }

            if (!$deviceMatch) {
                if ($user->devices()->count() >= self::MAX_OFFICER_DEVICES) {
                    return response()->json([
                        'message' => 'Maximum registered devices reached for this account. Ask an administrator to reset your device registrations.'
                    ], 403);
                }

                // Register the device
                $newToken = $deviceId ?: Str::random(64);
                DeviceRegistration::create([
                    'user_id' => $user->id,
                    'token_hash' => hash('sha256', $newToken),
                    'label' => $deviceName,
                    'user_agent' => $request->userAgent() ?: 'API Client',
                    'ip_address' => $request->ip(),
                    'last_used_at' => now(),
                ]);
            }
        }

        // Generate token — only the plaintext form is ever returned to the client;
        // the database stores just its SHA-256 hash (see BearerTokenMiddleware).
        $tokenStr = Str::random(80);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $tokenStr),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $tokenStr,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'lgu_id' => $user->lgu_id,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            ApiToken::where('token', hash('sha256', $matches[1]))->delete();
        }

        return response()->json(['message' => 'Successfully logged out.']);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'lgu_id' => $user->lgu_id,
            'lgu' => $user->lgu ? [
                'id' => $user->lgu->id,
                'name' => $user->lgu->name,
            ] : null
        ]);
    }
}
