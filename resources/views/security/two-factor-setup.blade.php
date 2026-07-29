@extends('layouts.app')
@section('title', 'Enable Two-Factor Authentication')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('security.two-factor.show') }}" style="color:#0369a1;text-decoration:none;">Two-Factor Authentication</a></li>
    <li class="breadcrumb-item active" aria-current="page">Enable</li>
@endsection

@section('content')

<div class="sec-card mx-auto" style="max-width: 500px; margin: 0 auto;">
    <div class="sec-header">
        <span class="sec-icon" style="background:linear-gradient(135deg,#0369a1,#075985);box-shadow:0 3px 10px rgba(3,105,161,.35);">
            <i class="bi bi-qr-code" style="color:#fff;font-size:1rem;"></i>
        </span>
        <div>
            <div class="sec-title">Scan the QR Code</div>
            <div class="sec-sub">Use Google Authenticator, Authy, or any TOTP app.</div>
        </div>
    </div>

    <div class="sec-body">
        @error('code')
            <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:10px;font-size:.85rem;">{{ $message }}</div>
        @enderror

        <div class="text-center mb-3">
            <div class="p-2.5 bg-white d-inline-block rounded-3 border shadow-sm">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                     alt="2FA QR Code" 
                     width="200" height="200"
                     class="img-fluid"
                     style="display:block;"
                     onerror="this.onerror=null; this.src='https://quickchart.io/qr?size=200&text={{ urlencode($qrCodeUrl) }}';">
            </div>
        </div>

        <div class="mb-3">
            <label class="sec-label">Can't scan? Enter this key manually</label>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ $secret }}" readonly style="font-family: ui-monospace, monospace; font-size:.85rem;">
            </div>
        </div>

        <form method="POST" action="{{ route('security.two-factor.confirm') }}">
            @csrf
            <div class="mb-3">
                <label class="sec-label">Enter the 6-digit code to confirm</label>
                <input type="text" name="code" class="form-control" placeholder="000000" maxlength="6" required autofocus
                       inputmode="numeric" style="text-align:center; font-size:1.3rem; letter-spacing:.3em; font-family: ui-monospace, monospace;">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="sec-btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Confirm &amp; Enable
                </button>
                <a href="{{ route('security.two-factor.show') }}" class="sec-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.sec-card { background:#fff; border-radius:18px; box-shadow:0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.06); overflow:hidden; margin:0 auto; }
.sec-header { display:flex; align-items:center; gap:1rem; padding:1.1rem 1.4rem; background:linear-gradient(135deg,#f0f9ff 0%,#e0f2fe 100%); border-bottom:1.5px solid #ece5da; }
.sec-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sec-title { font-size:.95rem; font-weight:700; color:#1c1917; }
.sec-sub { font-size:.74rem; color:#a8a29e; margin-top:.1rem; }
.sec-body { padding:1.4rem; }
.sec-label { font-size:.72rem; font-weight:700; color:#78716c; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.4rem; display:block; }
.sec-btn-primary { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1.25rem; border-radius:10px; font-size:.84rem; font-weight:700; background:linear-gradient(135deg,#0369a1,#075985); color:#fff; border:none; }
.sec-btn-secondary { display:inline-flex; align-items:center; padding:.5rem 1.1rem; border-radius:10px; font-size:.82rem; font-weight:600; background:#fff; color:#78716c; border:1.5px solid #d6d3d1; text-decoration:none; }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
<script>
    QRCode.toCanvas(document.getElementById('tfaQrCanvas'), @json($qrCodeUrl), {
        width: 220,
        margin: 1,
    });
</script>
@endpush
