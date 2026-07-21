<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('security.two-factor', ['user' => $user]);
    }

    public function enable(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // Held in session until the user proves they can generate a valid code —
        // never touches the users table until confirm() succeeds.
        session(['2fa_setup_secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            Auth::user()->username,
            $secret
        );

        return view('security.two-factor-setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $secret = session('2fa_setup_secret');

        if (! $secret) {
            return redirect()->route('security.two-factor.show')
                ->with('error', 'Your 2FA setup session expired. Please start again.');
        }

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'That code is invalid or expired. Please try again.']);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4) . '-' . Str::random(4)))
            ->all();

        $user = Auth::user();
        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        session()->forget('2fa_setup_secret');

        return view('security.two-factor-recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('security.two-factor.show')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('security.two-factor.show');
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4) . '-' . Str::random(4)))
            ->all();

        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->save();

        return view('security.two-factor-recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }
}
