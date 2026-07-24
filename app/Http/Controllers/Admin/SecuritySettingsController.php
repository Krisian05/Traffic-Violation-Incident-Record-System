<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceRegistration;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class SecuritySettingsController extends Controller
{
    public function index(): View
    {
        SystemSetting::seedDefaults();

        $enforce2faAdmin = SystemSetting::get('enforce_2fa_admin', false);
        $sessionTimeout = SystemSetting::get('session_timeout_minutes', 120);
        $maxLoginAttempts = SystemSetting::get('max_login_attempts', 5);
        $lockoutDuration = SystemSetting::get('lockout_duration_minutes', 15);

        // Security analytics
        $totalUsers = User::count();
        $usersWith2fa = User::whereNotNull('two_factor_confirmed_at')->count();
        $twoFaAdoptionRate = $totalUsers > 0 ? round(($usersWith2fa / $totalUsers) * 100, 1) : 0;
        $activeRegisteredDevicesCount = DeviceRegistration::count();

        // Recent security logs
        $recentSecurityLogs = Activity::whereIn('log_name', ['security', 'auth', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.security.index', compact(
            'enforce2faAdmin',
            'sessionTimeout',
            'maxLoginAttempts',
            'lockoutDuration',
            'totalUsers',
            'usersWith2fa',
            'twoFaAdoptionRate',
            'activeRegisteredDevicesCount',
            'recentSecurityLogs'
        ));
    }

    public function updatePolicy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enforce_2fa_admin'        => ['nullable', 'boolean'],
            'session_timeout_minutes'  => ['required', 'integer', 'min:5', 'max:10080'],
            'max_login_attempts'       => ['required', 'integer', 'min:1', 'max:20'],
            'lockout_duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ]);

        SystemSetting::set('enforce_2fa_admin', isset($data['enforce_2fa_admin']) ? '1' : '0', 'security', 'boolean', 'Mandatory 2FA Enforcement for Administrators');
        SystemSetting::set('session_timeout_minutes', (string) $data['session_timeout_minutes'], 'security', 'integer', 'Inactive Session Expiry Timeout (Minutes)');
        SystemSetting::set('max_login_attempts', (string) $data['max_login_attempts'], 'security', 'integer', 'Maximum Failed Login Attempts Before Lockout');
        SystemSetting::set('lockout_duration_minutes', (string) $data['lockout_duration_minutes'], 'security', 'integer', 'Account Lockout Duration (Minutes)');

        activity()
            ->causedBy(Auth::user())
            ->useLog('security')
            ->log('Updated system security policies.');

        return redirect()->route('admin.security.index')
            ->with('success', 'Security Policy Settings updated successfully.');
    }

    public function activeSessions(): View
    {
        $activeDbSessions = [];
        try {
            $rawSessions = DB::table('sessions')
                ->latest('last_activity')
                ->get();

            $userIds = $rawSessions->pluck('user_id')->filter()->unique();
            $usersMap = User::with('lgu')->whereIn('id', $userIds)->get()->keyBy('id');

            foreach ($rawSessions as $session) {
                $activeDbSessions[] = [
                    'id'            => $session->id,
                    'user_id'       => $session->user_id,
                    'user'          => $usersMap[$session->user_id] ?? null,
                    'ip_address'    => $session->ip_address,
                    'user_agent'    => $session->user_agent,
                    'last_activity' => $session->last_activity,
                    'is_current'     => $session->id === request()->session()->getId(),
                ];
            }
        } catch (\Throwable $e) {
            // Sessions table might be empty or using non-db driver
        }

        $registeredDevices = DeviceRegistration::with('user.lgu')
            ->latest('last_active_at')
            ->get();

        return view('admin.security.sessions', compact('activeDbSessions', 'registeredDevices'));
    }

    public function terminateSession(string $sessionId): RedirectResponse
    {
        if ($sessionId === request()->session()->getId()) {
            return back()->with('error', 'You cannot terminate your current active session.');
        }

        try {
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            activity()
                ->causedBy(Auth::user())
                ->useLog('security')
                ->log("Terminated active web session ID {$sessionId}.");

            return back()->with('success', 'Session terminated successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to terminate session: ' . $e->getMessage());
        }
    }

    public function terminateAllOthers(): RedirectResponse
    {
        $currentSessionId = request()->session()->getId();
        $currentUserId = Auth::id();

        try {
            DB::table('sessions')
                ->where('id', '!=', $currentSessionId)
                ->delete();

            activity()
                ->causedBy(Auth::user())
                ->useLog('security')
                ->log("Emergency session containment: Terminated all other active user sessions across the system.");

            return back()->with('success', 'All other active sessions have been terminated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to terminate sessions: ' . $e->getMessage());
        }
    }
}
