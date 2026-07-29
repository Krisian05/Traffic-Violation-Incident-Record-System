@extends('layouts.app')
@section('title', 'Two-Factor Authentication')

@section('content')

<div class="d-flex align-items-center justify-content-center w-100 py-4" style="min-height: calc(100vh - 140px); margin-top: 1rem;">
    <div class="sec-card w-100 mx-auto" style="max-width: 640px; margin: 0 auto;">

        <div class="sec-header">
            <span class="sec-icon" style="background:linear-gradient(135deg,#0369a1,#075985);box-shadow:0 3px 10px rgba(3,105,161,.35);">
                <i class="bi bi-shield-lock-fill" style="color:#fff;font-size:1rem;"></i>
            </span>
            <div>
                <div class="sec-title">Two-Factor Authentication</div>
                <div class="sec-sub">Add an extra layer of protection to your account.</div>
            </div>
        </div>

        <div class="sec-body">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3" style="border-radius:10px;font-size:.85rem;">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:10px;font-size:.85rem;">{{ session('error') }}</div>
            @endif

            @if($user->hasTwoFactorEnabled())
                <div class="sec-status sec-status-on">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>Two-factor authentication is enabled.</strong>
                        <div style="font-size:.78rem;color:#15803d;">You'll be asked for a code from your authenticator app every time you log in.</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <form method="POST" action="{{ route('security.two-factor.recovery-codes') }}">
                        @csrf
                        <button type="submit" class="sec-btn-secondary">
                            <i class="bi bi-arrow-repeat me-1"></i> Regenerate Recovery Codes
                        </button>
                    </form>

                    <button type="button" class="sec-btn-danger" data-bs-toggle="modal" data-bs-target="#disable2faModal">
                        <i class="bi bi-shield-slash-fill me-1"></i> Disable 2FA
                    </button>
                </div>
            @else
                <div class="sec-status sec-status-off">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Two-factor authentication is not enabled.</strong>
                        <div style="font-size:.78rem;color:#b45309;">Your account is protected by password only.</div>
                    </div>
                </div>

                <a href="{{ route('security.two-factor.enable') }}" class="sec-btn-primary mt-4 d-inline-flex">
                    <i class="bi bi-shield-plus me-1"></i> Enable Two-Factor Authentication
                </a>
            @endif
        </div>
    </div>
</div>

@if($user->hasTwoFactorEnabled())
<div class="modal fade" id="disable2faModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <form method="POST" action="{{ route('security.two-factor.disable') }}">
                @csrf @method('DELETE')
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">Disable Two-Factor Authentication</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:.85rem;color:#64748b;">Confirm your password to disable 2FA on this account.</p>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Current password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disable 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.sec-card { background:#fff; border-radius:18px; box-shadow:0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06); overflow:hidden; margin:0 auto; }
.sec-header { display:flex; align-items:center; gap:1rem; padding:1.1rem 1.4rem; background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); border-bottom:1.5px solid #ece5da; }
.sec-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sec-title { font-size:.95rem; font-weight:700; color:#1c1917; }
.sec-sub { font-size:.74rem; color:#a8a29e; margin-top:.1rem; }
.sec-body { padding:1.4rem; }

.sec-status { display:flex; align-items:flex-start; gap:.75rem; padding:.9rem 1rem; border-radius:12px; font-size:.85rem; }
.sec-status-on { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }
.sec-status-on i { font-size:1.2rem; color:#16a34a; }
.sec-status-off { background:#fffbeb; border:1px solid #fde68a; color:#b45309; }
.sec-status-off i { font-size:1.2rem; color:#d97706; }

.sec-btn-primary { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1.25rem; border-radius:10px; font-size:.84rem; font-weight:700; background:linear-gradient(135deg,#0369a1,#075985); color:#fff; border:none; text-decoration:none; box-shadow:0 2px 8px rgba(3,105,161,.3); }
.sec-btn-secondary { display:inline-flex; align-items:center; padding:.5rem 1.1rem; border-radius:10px; font-size:.82rem; font-weight:600; background:#fff; color:#334155; border:1.5px solid #d6d3d1; }
.sec-btn-danger { display:inline-flex; align-items:center; padding:.5rem 1.1rem; border-radius:10px; font-size:.82rem; font-weight:600; background:#fff1f2; color:#b91c1c; border:1.5px solid #fca5a5; }
</style>

@endsection
