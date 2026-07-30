<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeviceRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /** Traffic officers may have at most this many devices registered at once. */
    private const MAX_OFFICER_DEVICES = 2;

    private const DEVICE_COOKIE = 'tvirs_device';

    public function showLoginForm()
    {
        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (! Auth::validate(['username' => $request->username, 'password' => $request->password])) {
            activity()
                ->useLog('auth')
                ->event('login_failed')
                ->withProperties([
                    'ip'         => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'username'   => $request->username,
                ])
                ->log("Failed login attempt for username '{$request->username}'");

            return back()->withErrors([
                'username' => 'The provided credentials do not match our records.',
            ])->onlyInput('username');
        }

        /** @var User $user */
        $user = User::where('username', $request->username)->firstOrFail();
        $remember = $request->boolean('remember');

        if ($user->isTrafficOfficer()) {
            $deviceError = $this->enforceDeviceRegistration($request, $user);

            if ($deviceError) {
                activity()
                    ->causedBy($user)
                    ->useLog('auth')
                    ->event('login_blocked')
                    ->withProperties([
                        'ip'         => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'reason'     => 'max_devices_exceeded',
                    ])
                    ->log("Login blocked for officer '{$user->username}': Max devices reached.");

                return back()->withErrors(['username' => $deviceError])->onlyInput('username');
            }
        }

        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('2fa:remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        activity()
            ->causedBy($user)
            ->useLog('auth')
            ->event('login_success')
            ->withProperties([
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'role'       => $user->role,
            ])
            ->log("Successful login for user '{$user->username}'");

        if ($user->isTrafficOfficer()) {
            return redirect()->route('officer.dashboard');
        }

        $this->sanitizeIntendedUrl($user);

        if ($user->isProvinceAdmin()) {
            return redirect()->intended(route('province.dashboard'));
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Clear intended URL if it points to a route restricted for the logged-in user's role.
     */
    private function sanitizeIntendedUrl(User $user): void
    {
        $intended = session()->get('url.intended');
        if (! $intended) {
            return;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?? '';

        if ($user->isSuperAdmin()) {
            return;
        }

        if (str_starts_with($path, '/lgus')) {
            session()->forget('url.intended');
            return;
        }

        if (str_starts_with($path, '/province') && ! $user->isProvinceAdmin()) {
            session()->forget('url.intended');
            return;
        }

        if (str_starts_with($path, '/officer') && ! $user->isTrafficOfficer()) {
            session()->forget('url.intended');
            return;
        }

        if (str_starts_with($path, '/cashier') && ! in_array($user->role, ['cashier', 'treasurer', 'admin', 'super_admin'])) {
            session()->forget('url.intended');
            return;
        }
    }

    /**
     * Registers the current device for this officer if they're under the cap,
     * otherwise returns an error message. Returns null when login may proceed.
     */
    private function enforceDeviceRegistration(Request $request, User $user): ?string
    {
        $token = $request->cookie(self::DEVICE_COOKIE);
        $hash = $token ? hash('sha256', $token) : null;

        if ($hash) {
            $device = DeviceRegistration::where('user_id', $user->id)
                ->where('token_hash', $hash)
                ->first();

            if ($device) {
                $device->update([
                    'last_used_at' => now(),
                    'ip_address' => $request->ip(),
                ]);

                return null;
            }
        }

        // Unrecognized (or no) device cookie — register it if this officer is under the cap.
        if ($user->devices()->count() >= self::MAX_OFFICER_DEVICES) {
            return 'Maximum registered devices reached for this account. Ask an administrator to reset your device registrations.';
        }

        $newToken = Str::random(64);

        DeviceRegistration::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $newToken),
            'label' => $this->describeDevice($request->userAgent()),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'last_used_at' => now(),
        ]);

        Cookie::queue(Cookie::make(
            self::DEVICE_COOKIE,
            $newToken,
            60 * 24 * 365 * 2, // ~2 years
            null,
            null,
            ! app()->isLocal(),
            true,
            false,
            'Lax'
        ));

        return null;
    }

    private function describeDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Unknown device';
        }

        $os = match (true) {
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };

        $browser = match (true) {
            str_contains($userAgent, 'Chrome') => 'Chrome',
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Safari') => 'Safari',
            str_contains($userAgent, 'Edg/') => 'Edge',
            default => 'Browser',
        };

        return "{$os} · {$browser}";
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            activity()
                ->causedBy($user)
                ->useLog('auth')
                ->event('logout')
                ->withProperties(['ip' => $request->ip()])
                ->log("User '{$user->username}' logged out");
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
