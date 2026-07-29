@extends('layouts.app')
@section('title', 'Recovery Codes')

@section('content')

<div class="d-flex align-items-center justify-content-center w-100 py-4" style="min-height: calc(82vh - 120px);">
    <div class="sec-card w-100 mx-auto" style="max-width: 500px; margin: 0 auto;">
        <div class="sec-header">
            <span class="sec-icon" style="background:linear-gradient(135deg,#15803d,#166534);box-shadow:0 3px 10px rgba(21,128,61,.35);">
                <i class="bi bi-key-fill" style="color:#fff;font-size:1rem;"></i>
            </span>
            <div>
                <div class="sec-title">Save Your Recovery Codes</div>
                <div class="sec-sub">Each code can be used once if you lose access to your authenticator app.</div>
            </div>
        </div>

        <div class="sec-body">
            <div class="alert alert-warning py-2 px-3 mb-3" style="border-radius:10px;font-size:.82rem;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                These codes will not be shown again. Save them somewhere safe now.
            </div>

            <div class="sec-codes-grid">
                @foreach($recoveryCodes as $code)
                    <div class="sec-code-chip">{{ $code }}</div>
                @endforeach
            </div>

            <a href="{{ route('security.two-factor.show') }}" class="sec-btn-primary mt-4 d-inline-flex">
                <i class="bi bi-check-lg me-1"></i> I've Saved My Codes
            </a>
        </div>
    </div>
</div>

<style>
.sec-card { background:#fff; border-radius:18px; box-shadow:0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06); overflow:hidden; margin:0 auto; }
.sec-header { display:flex; align-items:center; gap:1rem; padding:1.1rem 1.4rem; background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%); border-bottom:1.5px solid #ece5da; }
.sec-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sec-title { font-size:.95rem; font-weight:700; color:#1c1917; }
.sec-sub { font-size:.74rem; color:#a8a29e; margin-top:.1rem; }
.sec-body { padding:1.4rem; }
.sec-codes-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
.sec-code-chip { font-family: ui-monospace, monospace; font-size:.88rem; font-weight:700; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:.55rem .7rem; text-align:center; color:#1e293b; letter-spacing:.03em; }
.sec-btn-primary { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1.25rem; border-radius:10px; font-size:.84rem; font-weight:700; background:linear-gradient(135deg,#15803d,#166534); color:#fff; border:none; text-decoration:none; }
</style>

@endsection
