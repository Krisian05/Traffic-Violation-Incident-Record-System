<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TVIRS Officer">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/Balamban.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Balamban.png') }}">
    <title>@yield('title', 'TVIRS Officer') — TVIRS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --blue: #1d4ed8;
            --blue-dark: #1e40af;
            --blue-deep: #1e3a8a;
            --red: #dc2626;
            --red-dark: #b91c1c;
            --text-dark: #0f172a;
            --text-med: #334155;
            --text-light: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;
            --surface: #f8fafc;
            --top-h: 56px;
            --bot-h: env(safe-area-inset-bottom, 0px);
            --safe-top: env(safe-area-inset-top, 0px);
            --nav-h: 60px;
        }

        html, body {
            height: 100%;
            margin: 0;
            background: var(--surface);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: var(--text-dark);
        }

        /* ── TOP BAR ── */
        .mob-topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: calc(var(--top-h) + var(--safe-top));
            padding-top: var(--safe-top);
            background: var(--blue);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: .875rem;
            padding-right: .875rem;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.2);
        }

        .mob-topbar-left {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex: 1;
            min-width: 0;
        }

        .mob-topbar-title {
            font-size: .9rem;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mob-back-btn, .mob-logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 10px;
            border: none;
            background: rgba(255,255,255,.15);
            color: #fff;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s;
            flex-shrink: 0;
        }
        .mob-back-btn:hover, .mob-logout-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── MAIN CONTENT ── */
        .mob-content {
            padding-top: calc(var(--top-h) + var(--safe-top) + .875rem);
            padding-bottom: calc(var(--nav-h) + var(--bot-h) + 1.25rem);
            min-height: 100vh;
            padding-left: .875rem;
            padding-right: .875rem;
        }

        /* ── BOTTOM NAV ── */
        .mob-bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            padding-top: .55rem;
            padding-bottom: calc(var(--bot-h) + .25rem);
            z-index: 95;
            box-shadow: 0 -2px 16px rgba(0,0,0,.06);
        }
        .mob-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .15rem;
            font-size: .58rem;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: .05em;
            transition: color .15s;
            -webkit-tap-highlight-color: transparent;
        }
        .mob-nav-item i { font-size: 1.3rem; transition: transform .15s; }
        .mob-nav-item.active { color: var(--blue); }
        .mob-nav-item.active i { transform: scale(1.08); }

        /* ── FAB ── */
        .mob-fab {
            position: fixed;
            bottom: calc(var(--nav-h) + var(--bot-h) + 1rem);
            right: 1.25rem;
            width: 54px; height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff;
            font-size: 1.35rem;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(29,78,216,.45);
            text-decoration: none;
            z-index: 90;
            transition: transform .15s, box-shadow .15s;
        }
        .mob-fab:hover { color: #fff; transform: scale(1.07); }
        .mob-fab--red { background: linear-gradient(135deg, var(--red), var(--red-dark)); box-shadow: 0 4px 20px rgba(220,38,38,.45); }

        /* ── CARDS ── */
        .mob-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
            overflow: hidden;
            margin-bottom: .875rem;
            border: 1px solid rgba(0,0,0,.035);
        }
        .mob-card-body { padding: 1rem; }

        /* ── HERO CARD ── */
        .mob-hero {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 55%, var(--blue-deep) 100%);
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: .875rem;
            box-shadow: 0 6px 24px rgba(29,78,216,.35);
            position: relative;
            overflow: hidden;
        }
        .mob-hero::before {
            content: '';
            position: absolute;
            top: -40px; right: -30px;
            width: 130px; height: 130px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
            pointer-events: none;
        }
        .mob-hero::after {
            content: '';
            position: absolute;
            bottom: -45px; left: 15px;
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            pointer-events: none;
        }

        /* ── SECTION HEADING ── */
        .mob-heading {
            font-size: .62rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: .55rem;
            padding-left: .1rem;
        }

        /* ── ACTION CARD ── */
        .mob-action-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 14px rgba(0,0,0,.05);
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .9rem;
            text-decoration: none;
            color: inherit;
            margin-bottom: .65rem;
            transition: transform .12s, box-shadow .12s;
            border: 1px solid rgba(0,0,0,.04);
            -webkit-tap-highlight-color: transparent;
        }
        .mob-action-card:active { transform: scale(.98); color: inherit; }
        .mob-action-card:hover  { color: inherit; }
        .mob-action-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        .mob-action-icon--blue { background: linear-gradient(135deg,var(--blue),var(--blue-dark)); color:#fff; box-shadow:0 3px 10px rgba(29,78,216,.3); }
        .mob-action-icon--red  { background: linear-gradient(135deg,var(--red),var(--red-dark));  color:#fff; box-shadow:0 3px 10px rgba(220,38,38,.3);  }

        /* ── FORM CONTROLS ── */
        .mob-label {
            font-size: .67rem; font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase; letter-spacing: .05em;
            margin-bottom: .35rem; display: block;
        }
        .mob-input {
            border-radius: 10px !important;
            font-size: .9rem;
            min-height: 44px;
            border-color: var(--border);
            color: var(--text-dark);
        }
        .mob-input:focus { box-shadow: 0 0 0 3px rgba(29,78,216,.12) !important; border-color: var(--blue) !important; }
        .mob-select { min-height: 44px; border-radius: 10px !important; font-size: .9rem; border-color: var(--border); }
        .mob-select:focus { box-shadow: 0 0 0 3px rgba(29,78,216,.12) !important; border-color: var(--blue) !important; }

        /* ── FORM SECTION DIVIDER ── */
        .mob-form-divider {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin: 1.1rem 0 .85rem;
        }
        .mob-form-divider-text {
            font-size: .62rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .09em;
            white-space: nowrap;
        }
        .mob-form-divider-line { flex: 1; height: 1px; background: var(--border); }

        /* ── BUTTONS ── */
        .mob-btn-primary {
            display: flex; align-items: center; justify-content: center; gap: .45rem;
            width: 100%; min-height: 48px;
            border-radius: 12px; border: none;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff; font-weight: 700; font-size: .9rem;
            box-shadow: 0 2px 8px rgba(29,78,216,.28);
            cursor: pointer; transition: all .15s;
            -webkit-tap-highlight-color: transparent;
        }
        .mob-btn-primary:active { transform: scale(.98); }
        .mob-btn-danger {
            background: linear-gradient(135deg, var(--red), var(--red-dark)) !important;
            box-shadow: 0 2px 8px rgba(220,38,38,.3) !important;
        }
        .mob-btn-outline {
            display: flex; align-items: center; justify-content: center; gap: .45rem;
            width: 100%; min-height: 44px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: #fff; color: var(--text-med);
            font-weight: 600; font-size: .875rem;
            cursor: pointer; transition: all .15s; text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }
        .mob-btn-outline:hover { background: var(--surface); color: var(--text-dark); }

        /* ── ALERTS ── */
        .mob-alert {
            border-radius: 12px; padding: .8rem 1rem;
            font-size: .85rem; font-weight: 500;
            display: flex; align-items: center; gap: .6rem;
            margin-bottom: .875rem;
        }
        .mob-alert-success { background: #f0fdf4; color: #15803d; border: 1.5px solid #86efac; }
        .mob-alert-danger  { background: #fef2f2; color: #b91c1c; border: 1.5px solid #fca5a5; }
        .mob-alert-warning { background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a; }

        /* ── LIST ITEMS ── */
        .mob-list-item {
            display: flex; align-items: center;
            padding: .875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none; color: inherit;
            transition: background .1s;
            -webkit-tap-highlight-color: transparent;
        }
        .mob-list-item:last-child { border-bottom: none; }
        .mob-list-item:active { background: var(--surface); }

        /* ── PAGINATION ── */
        .pagination { gap: .25rem; justify-content: center; flex-wrap: wrap; }
        .pagination .page-link {
            border-radius: 8px !important;
            min-width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            font-size: .82rem; border-color: var(--border); color: var(--text-light);
        }
        .pagination .page-item.active .page-link { background: var(--blue); border-color: var(--blue); }

        /* ── STATUS BADGES ── */
        .mob-badge {
            display: inline-flex; align-items: center;
            padding: .22rem .65rem; border-radius: 20px; border: 1.5px solid;
            font-size: .68rem; font-weight: 700; letter-spacing: .02em;
            white-space: nowrap;
        }
        .mob-badge-open      { background:#f0fdf4;color:#15803d;border-color:#86efac; }
        .mob-badge-review    { background:#eff6ff;color:#1d4ed8;border-color:#93c5fd; }
        .mob-badge-closed    { background:#f5f3f0;color:#57534e;border-color:#d6d3d1; }
        .mob-badge-pending   { background:#fffbeb;color:#92400e;border-color:#fde68a; }
        .mob-badge-settled   { background:#f0fdf4;color:#15803d;border-color:#86efac; }
        .mob-badge-overdue   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
        .mob-badge-contested { background:#f5f3ff;color:#6d28d9;border-color:#c4b5fd; }

        /* ── SECTION TITLE (within card) ── */
        .mob-section-title {
            font-size: .65rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted);
            padding: .6rem 1rem .35rem;
        }

        /* ── INFO GRID (detail views) ── */
        .mob-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .875rem;
        }
        .mob-info-grid-full { grid-column: 1 / -1; }
        .mob-info-label {
            font-size: .65rem; font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: .05em;
            margin-bottom: .2rem;
        }
        .mob-info-value {
            font-size: .88rem; font-weight: 600;
            color: var(--text-dark); line-height: 1.35;
        }

        /* ── PROFILE HEADER ── */
        .mob-profile-header {
            background: linear-gradient(135deg,var(--blue),var(--blue-dark));
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: .875rem;
            box-shadow: 0 6px 24px rgba(29,78,216,.3);
        }

        /* ── EMPTY STATE ── */
        .mob-empty {
            text-align: center;
            padding: 2.5rem 1rem;
        }
        .mob-empty-icon {
            font-size: 2.25rem;
            color: var(--border);
            display: block;
            margin-bottom: .6rem;
        }
        .mob-empty-text {
            font-size: .875rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .mob-empty-sub {
            font-size: .78rem;
            color: #c0cad8;
            margin-top: .25rem;
        }

        /* ── PHOTO GRID ── */
        .mob-photo-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .5rem; }
        .mob-photo-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .mob-photo-thumb {
            display: block;
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,.1);
            cursor: zoom-in;
            transition: transform .15s, box-shadow .15s;
        }
        .mob-photo-thumb:active { transform: scale(.97); }
        .mob-photo-single {
            width: 100%;
            max-height: 320px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
            cursor: zoom-in;
            display: block;
        }

        /* ── LIGHTBOX ── */
        .mob-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.92);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
            -webkit-tap-highlight-color: transparent;
        }
        .mob-lightbox.open { opacity: 1; pointer-events: all; }
        #mob-logout-modal.open { display: flex !important; }
        .mob-lightbox img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 8px 48px rgba(0,0,0,.6);
            display: block;
        }
        .mob-lightbox-close {
            position: absolute;
            top: calc(env(safe-area-inset-top, 0px) + .875rem);
            right: .875rem;
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,.18);
            border: none;
            color: #fff;
            font-size: 1.2rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(8px);
        }

        /* ── MEDIA TYPE CHIP ── */
        .mob-media-chip {
            display: inline-flex; align-items: center; gap: .25rem;
            font-size: .62rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            padding: .15rem .5rem;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            margin-bottom: .35rem;
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- TOP BAR --}}
<header class="mob-topbar">
    <div class="mob-topbar-left">
        @hasSection('back_url')
            <a href="@yield('back_url')" class="mob-back-btn me-1">
                <i class="ph ph-caret-left"></i>
            </a>
        @else
            <a href="{{ route('officer.dashboard') }}" class="mob-back-btn me-1">
                <i class="ph-fill ph-house"></i>
            </a>
        @endif
        <span class="mob-topbar-title">@yield('title', 'TVIRS Officer')</span>
    </div>

    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-inline">
        @csrf
        <button type="button" class="mob-logout-btn" title="Logout" onclick="document.getElementById('mob-logout-modal').classList.add('open')">
            <i class="ph ph-sign-out"></i>
        </button>
    </form>
</header>

{{-- PAGE CONTENT --}}
<main class="mob-content">

    @if(session('success'))
        <div class="mob-alert mob-alert-success">
            <i class="ph-fill ph-check-circle flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mob-alert mob-alert-danger">
            <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @yield('content')
</main>

{{-- BOTTOM NAVIGATION --}}
<nav class="mob-bottom-nav">
    <a href="{{ route('officer.dashboard') }}"
       class="mob-nav-item {{ request()->routeIs('officer.dashboard') ? 'active' : '' }}">
        <i class="ph-fill ph-house"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('officer.motorists.index') }}"
       class="mob-nav-item {{ request()->routeIs('officer.motorists.*') || request()->routeIs('officer.violations.*') ? 'active' : '' }}">
        <i class="ph-fill ph-users"></i>
        <span>Motorists</span>
    </a>
    <a href="{{ route('officer.incidents.index') }}"
       class="mob-nav-item {{ request()->routeIs('officer.incidents.*') ? 'active' : '' }}">
        <i class="ph-fill ph-flag"></i>
        <span>Incidents</span>
    </a>
</nav>

{{-- LOGOUT CONFIRMATION MODAL --}}
<div id="mob-logout-modal" onclick="if(event.target===this)this.classList.remove('open')"
     style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.45);align-items:flex-end;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:20px 20px 16px 16px;width:100%;max-width:420px;padding:1.5rem 1.25rem 1.25rem;box-shadow:0 -4px 32px rgba(0,0,0,.15);">
        <div style="text-align:center;margin-bottom:1.1rem;">
            <div style="width:52px;height:52px;border-radius:50%;background:#fef2f2;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
                <i class="ph-fill ph-sign-out" style="font-size:1.4rem;color:#dc2626;"></i>
            </div>
            <div style="font-size:1rem;font-weight:800;color:#0f172a;">Log out?</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:.3rem;">You will be signed out of your account.</div>
        </div>
        <button onclick="document.getElementById('logout-form').submit()"
                style="display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;min-height:46px;border-radius:12px;background:#dc2626;color:#fff;border:none;font-weight:800;font-size:.9rem;margin-bottom:.6rem;cursor:pointer;">
            <i class="ph-bold ph-sign-out"></i> Yes, Log Out
        </button>
        <button onclick="document.getElementById('mob-logout-modal').classList.remove('open')"
                style="display:flex;align-items:center;justify-content:center;width:100%;min-height:44px;border-radius:12px;background:#f1f5f9;color:#64748b;border:none;font-weight:700;font-size:.875rem;cursor:pointer;">
            Cancel
        </button>
    </div>
</div>

{{-- LIGHTBOX --}}
<div id="mob-lightbox" class="mob-lightbox" onclick="this.classList.remove('open')">
    <button class="mob-lightbox-close" onclick="event.stopPropagation();document.getElementById('mob-lightbox').classList.remove('open')">
        <i class="ph ph-x" style="font-size:1.1rem;"></i>
    </button>
    <div style="display:flex;flex-direction:column;align-items:center;gap:.75rem;max-width:100%;" onclick="event.stopPropagation()">
        <img src="" alt="Photo" style="max-width:100%;max-height:80vh;border-radius:12px;box-shadow:0 8px 48px rgba(0,0,0,.6);display:block;">
        <div id="mob-lightbox-caption" style="color:rgba(255,255,255,.75);font-size:.78rem;font-weight:600;text-align:center;padding:0 1rem;max-width:320px;line-height:1.4;min-height:1em;"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Global lightbox handler
document.addEventListener('click', function(e) {
    var thumb = e.target.closest('.mob-photo-thumb, .mob-photo-single');
    if (!thumb) return;
    var lb = document.getElementById('mob-lightbox');
    lb.querySelector('img').src = thumb.dataset.full || thumb.src;
    var cap = document.getElementById('mob-lightbox-caption');
    if (cap) cap.textContent = thumb.dataset.caption || thumb.alt || '';
    lb.classList.add('open');
});
// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('mob-lightbox').classList.remove('open');
});
</script>
@stack('scripts')
</body>
</html>
