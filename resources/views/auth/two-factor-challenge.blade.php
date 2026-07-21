<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Verification — TVIRS</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(160deg, #050d1a 0%, #0b2255 55%, #163998 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
        }
        .tfa-card {
            width: 100%; max-width: 420px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 80px rgba(0,0,0,.35);
            overflow: hidden;
        }
        .tfa-header {
            padding: 1.75rem 1.75rem 1.25rem;
            text-align: center;
            background: linear-gradient(135deg, #f0f7ff, #e8f0fe);
            border-bottom: 1px solid #e2e8f0;
        }
        .tfa-icon {
            width: 56px; height: 56px; margin: 0 auto .75rem;
            border-radius: 14px;
            background: linear-gradient(135deg, #1e4fb5, #2563eb);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 18px rgba(37,99,235,.35);
        }
        .tfa-icon i { color: #fff; font-size: 1.4rem; }
        .tfa-title { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: .25rem; }
        .tfa-sub { font-size: .82rem; color: #64748b; }
        .tfa-body { padding: 1.5rem 1.75rem 1.75rem; }
        .tfa-input {
            text-align: center;
            font-size: 1.4rem;
            letter-spacing: .3em;
            font-family: ui-monospace, monospace;
            padding: .65rem;
        }
        .tfa-input.recovery-mode { letter-spacing: .08em; font-size: 1.1rem; }
        .tfa-submit {
            background: linear-gradient(135deg, #1e4fb5, #2563eb);
            color: #fff; border: none; font-weight: 700;
            padding: .65rem; border-radius: 10px; width: 100%;
        }
        .tfa-toggle {
            background: none; border: none; color: #2563eb;
            font-size: .8rem; font-weight: 600; padding: 0;
        }
    </style>
</head>
<body>

<div class="tfa-card">
    <div class="tfa-header">
        <div class="tfa-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="tfa-title">Two-Factor Verification</div>
        <div class="tfa-sub">Enter the 6-digit code from your authenticator app.</div>
    </div>
    <div class="tfa-body">
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;border-radius:10px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
            @csrf
            <div class="mb-3">
                <input type="text" name="code" id="tfaCodeInput" class="form-control tfa-input"
                       placeholder="000000" maxlength="9" required autofocus autocomplete="one-time-code"
                       inputmode="numeric">
            </div>
            <button type="submit" class="tfa-submit">
                <i class="bi bi-unlock-fill me-1"></i> Verify
            </button>
        </form>

        <div class="text-center mt-3">
            <button type="button" class="tfa-toggle" id="tfaToggleMode">
                Use a recovery code instead
            </button>
        </div>

        <div class="text-center mt-3" style="font-size:.75rem;color:#94a3b8;">
            <a href="{{ route('login') }}" style="color:#94a3b8;">
                <i class="bi bi-arrow-left me-1"></i>Back to login
            </a>
        </div>
    </div>
</div>

<script>
    let recoveryMode = false;
    const input = document.getElementById('tfaCodeInput');
    const toggle = document.getElementById('tfaToggleMode');

    toggle.addEventListener('click', function () {
        recoveryMode = !recoveryMode;
        if (recoveryMode) {
            input.placeholder = 'XXXX-XXXX';
            input.maxLength = 9;
            input.classList.add('recovery-mode');
            toggle.textContent = 'Use authenticator app code instead';
        } else {
            input.placeholder = '000000';
            input.maxLength = 6;
            input.classList.remove('recovery-mode');
            toggle.textContent = 'Use a recovery code instead';
        }
        input.value = '';
        input.focus();
    });
</script>
</body>
</html>
