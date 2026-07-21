<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::find($userId);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            $request->session()->forget(['2fa:user:id', '2fa:remember']);
            return redirect()->route('login');
        }

        $code = trim($request->input('code'));
        $verified = false;

        if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $code)) {
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $match = collect($recoveryCodes)->first(fn ($rc) => strcasecmp($rc, $code) === 0);

            if ($match) {
                $verified = true;
                $user->two_factor_recovery_codes = array_values(array_diff($recoveryCodes, [$match]));
                $user->save();
            }
        } else {
            $google2fa = new Google2FA();
            $verified = $google2fa->verifyKey($user->two_factor_secret, $code);
        }

        if (! $verified) {
            return back()->withErrors(['code' => 'That code is invalid or expired.']);
        }

        $remember = (bool) $request->session()->get('2fa:remember', false);
        $request->session()->forget(['2fa:user:id', '2fa:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($user->isTrafficOfficer()) {
            return redirect()->route('officer.dashboard');
        }

        return redirect()->intended('/dashboard');
    }
}
