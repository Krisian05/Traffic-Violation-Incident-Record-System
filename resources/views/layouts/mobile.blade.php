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
    <title>@yield('title', 'TVIRS Officer') - TVIRS</title>

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

        .mob-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
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

        .mob-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
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

        .mob-back-btn:hover {
            background: rgba(255,255,255,.25);
            color: #fff;
        }

        .mob-content {
            padding-top: calc(var(--top-h) + var(--safe-top) + .875rem);
            padding-bottom: calc(var(--nav-h) + var(--bot-h) + 1.25rem);
            min-height: 100vh;
            padding-left: .875rem;
            padding-right: .875rem;
        }

        .mob-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
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

        .mob-nav-item i {
            font-size: 1.3rem;
            transition: transform .15s;
        }

        .mob-nav-item.active {
            color: var(--blue);
        }

        .mob-nav-item.active i {
            transform: scale(1.08);
        }

        .mob-fab {
            position: fixed;
            bottom: calc(var(--nav-h) + var(--bot-h) + 1rem);
            right: 1.25rem;
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff;
            font-size: 1.35rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(29,78,216,.45);
            text-decoration: none;
            z-index: 90;
            transition: transform .15s, box-shadow .15s;
        }

        .mob-fab:hover {
            color: #fff;
            transform: scale(1.07);
        }

        .mob-fab--red {
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            box-shadow: 0 4px 20px rgba(220,38,38,.45);
        }

        .mob-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.04);
            overflow: hidden;
            margin-bottom: .875rem;
            border: 1px solid rgba(0,0,0,.035);
        }

        .mob-card-body {
            padding: 1rem;
        }

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
            top: -40px;
            right: -30px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
            pointer-events: none;
        }

        .mob-hero::after {
            content: '';
            position: absolute;
            bottom: -45px;
            left: 15px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            pointer-events: none;
        }

        .mob-heading {
            font-size: .62rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: .55rem;
            padding-left: .1rem;
        }

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

        .mob-action-card:active {
            transform: scale(.98);
            color: inherit;
        }

        .mob-action-card:hover {
            color: inherit;
        }

        .mob-action-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .mob-action-icon--blue {
            background: linear-gradient(135deg,var(--blue),var(--blue-dark));
            color:#fff;
            box-shadow:0 3px 10px rgba(29,78,216,.3);
        }

        .mob-action-icon--red {
            background: linear-gradient(135deg,var(--red),var(--red-dark));
            color:#fff;
            box-shadow:0 3px 10px rgba(220,38,38,.3);
        }

        .mob-label {
            font-size: .67rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .35rem;
            display: block;
        }

        .mob-input {
            border-radius: 10px !important;
            font-size: .9rem;
            min-height: 44px;
            border-color: var(--border);
            color: var(--text-dark);
        }

        .mob-input:focus {
            box-shadow: 0 0 0 3px rgba(29,78,216,.12) !important;
            border-color: var(--blue) !important;
        }

        .mob-select {
            min-height: 44px;
            border-radius: 10px !important;
            font-size: .9rem;
            border-color: var(--border);
        }

        .mob-select:focus {
            box-shadow: 0 0 0 3px rgba(29,78,216,.12) !important;
            border-color: var(--blue) !important;
        }

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

        .mob-form-divider-line {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .mob-btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            width: 100%;
            min-height: 48px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            box-shadow: 0 2px 8px rgba(29,78,216,.28);
            cursor: pointer;
            transition: all .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-btn-primary:active {
            transform: scale(.98);
        }

        .mob-btn-danger {
            background: linear-gradient(135deg, var(--red), var(--red-dark)) !important;
            box-shadow: 0 2px 8px rgba(220,38,38,.3) !important;
        }

        .mob-btn-outline {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            width: 100%;
            min-height: 44px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--text-med);
            font-weight: 600;
            font-size: .875rem;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-btn-outline:hover {
            background: var(--surface);
            color: var(--text-dark);
        }

        .mob-alert {
            border-radius: 12px;
            padding: .8rem 1rem;
            font-size: .85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .875rem;
        }

        .mob-alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1.5px solid #86efac;
        }

        .mob-alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1.5px solid #fca5a5;
        }

        .mob-alert-warning {
            background: #fffbeb;
            color: #92400e;
            border: 1.5px solid #fde68a;
        }

        .mob-list-item {
            display: flex;
            align-items: center;
            padding: .875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            color: inherit;
            transition: background .1s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-list-item:last-child {
            border-bottom: none;
        }

        .mob-list-item:active {
            background: var(--surface);
        }

        .pagination {
            gap: .25rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination .page-link {
            border-radius: 8px !important;
            min-width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            border-color: var(--border);
            color: var(--text-light);
        }

        .pagination .page-item.active .page-link {
            background: var(--blue);
            border-color: var(--blue);
        }

        .mob-badge {
            display: inline-flex;
            align-items: center;
            padding: .22rem .65rem;
            border-radius: 20px;
            border: 1.5px solid;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .mob-badge-open      { background:#f0fdf4;color:#15803d;border-color:#86efac; }
        .mob-badge-review    { background:#eff6ff;color:#1d4ed8;border-color:#93c5fd; }
        .mob-badge-closed    { background:#f5f3f0;color:#57534e;border-color:#d6d3d1; }
        .mob-badge-pending   { background:#fffbeb;color:#92400e;border-color:#fde68a; }
        .mob-badge-settled   { background:#f0fdf4;color:#15803d;border-color:#86efac; }
        .mob-badge-overdue   { background:#fef2f2;color:#b91c1c;border-color:#fca5a5; }
        .mob-badge-contested { background:#f5f3ff;color:#6d28d9;border-color:#c4b5fd; }

        .mob-section-title {
            font-size: .65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--text-muted);
            padding: .6rem 1rem .35rem;
        }

        .mob-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .875rem;
        }

        .mob-info-grid-full {
            grid-column: 1 / -1;
        }

        .mob-info-label {
            font-size: .65rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .2rem;
        }

        .mob-info-value {
            font-size: .88rem;
            font-weight: 600;
            color: var(--text-dark);
            line-height: 1.35;
        }

        .mob-profile-header {
            background: linear-gradient(135deg,var(--blue),var(--blue-dark));
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: .875rem;
            box-shadow: 0 6px 24px rgba(29,78,216,.3);
        }

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

        .mob-photo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .5rem;
        }

        .mob-photo-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

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

        .mob-photo-thumb:active {
            transform: scale(.97);
        }

        .mob-photo-single {
            width: 100%;
            max-height: 320px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
            cursor: zoom-in;
            display: block;
        }

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

        .mob-lightbox.open {
            opacity: 1;
            pointer-events: all;
        }

        /* ── User button (topbar) ── */
        .mob-user-menu-wrap { position: relative; flex-shrink: 0; }

        .mob-user-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            height: 36px; border-radius: 999px; border: 1.5px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.12); color: #fff;
            font-size: .78rem; font-weight: 700; padding: 0 .7rem 0 .35rem;
            cursor: pointer; transition: background .15s, border-color .15s;
            flex-shrink: 0; max-width: 150px; backdrop-filter: blur(4px);
        }
        .mob-user-btn:active { background: rgba(255,255,255,.22); }

        .mob-user-avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: linear-gradient(135deg,#60a5fa,#1d4ed8);
            border: 2px solid rgba(255,255,255,.5);
            display: flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 900; color: #fff; flex-shrink: 0;
        }

        /* ── Dropdown panel — minimal ── */
        .mob-user-menu {
            position: absolute; top: calc(100% + .45rem); right: 0;
            width: min(190px, calc(100vw - 1rem));
            background: #fff; border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 18px rgba(15,23,42,.13);
            overflow: hidden; opacity: 0;
            transform: translateY(-6px) scale(.97);
            pointer-events: none;
            transition: opacity .15s, transform .15s;
            z-index: 200;
        }
        .mob-user-menu.open {
            opacity: 1; transform: translateY(0) scale(1); pointer-events: auto;
        }

        /* Header — compact flat */
        .mob-user-menu-head {
            padding: .6rem .75rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; gap: .5rem;
        }

        .mob-user-menu-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: linear-gradient(135deg,#1d4ed8,#2563eb);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 800; flex-shrink: 0;
        }

        .mob-user-menu-name {
            font-size: .78rem; font-weight: 700; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.2;
        }

        .mob-user-menu-role {
            font-size: .58rem; font-weight: 600;
            color: #64748b; text-transform: uppercase; letter-spacing: .04em;
            display: flex; align-items: center; gap: .2rem;
            margin-top: .1rem;
        }

        /* Body */
        .mob-user-menu-body { padding: .3rem; display: flex; flex-direction: column; gap: .15rem; }

        .mob-user-menu-action {
            display: flex; align-items: center; gap: .5rem;
            width: 100%; height: 36px; border-radius: 8px;
            border: none; background: transparent;
            font-weight: 600; font-size: .78rem; cursor: pointer;
            padding: 0 .6rem; transition: background .1s;
            text-align: left;
        }
        .mob-user-menu-action.action-blue { color: #1d4ed8; }
        .mob-user-menu-action.action-blue:active { background: #eff6ff; }
        .mob-user-menu-action.action-red { color: #dc2626; }
        .mob-user-menu-action.action-red:active { background: #fff1f2; }

        .mob-user-menu-action-icon {
            width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            background: none;
        }

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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,.18);
            border: none;
            color: #fff;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(8px);
        }

        .mob-media-chip {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .15rem .5rem;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            margin-bottom: .35rem;
        }

        /* ── Shared section heading (replaces mot-section-heading, inc-section-heading) ── */
        .motshow-section {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .65rem;
            font-size: .6rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .1em;
        }
        .motshow-section::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Shared hint text (used in form fields) ── */
        .mob-hint {
            display: block;
            font-size: .68rem;
            color: var(--text-muted);
            margin-top: .28rem;
            line-height: 1.4;
        }
    </style>

    @stack('styles')
</head>
<body>

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

    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="d-none">@csrf</form>

    <div class="mob-user-menu-wrap">
        <button type="button" class="mob-user-btn" id="mobUserMenuToggle" aria-expanded="false" aria-controls="mob-user-menu">
            <span class="mob-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px;">{{ explode(' ', Auth::user()->name)[0] }}</span>
            <i class="ph ph-caret-down" style="font-size:.6rem;opacity:.8;flex-shrink:0;"></i>
        </button>

        <div id="mob-user-menu" class="mob-user-menu" role="menu" aria-labelledby="mobUserMenuToggle">

            {{-- Header --}}
            <div class="mob-user-menu-head">
                <div class="mob-user-menu-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="mob-user-menu-name">{{ Auth::user()->name }}</div>
                    <div class="mob-user-menu-role">
                        <i class="ph-fill ph-shield-check"></i> Traffic Officer
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mob-user-menu-body">
                <button type="button" class="mob-user-menu-action action-blue" id="mobChangePasswordAction">
                    <div class="mob-user-menu-action-icon">
                        <i class="ph-fill ph-lock-key" style="font-size:.85rem;"></i>
                    </div>
                    <span>Change Password</span>
                </button>
                <button type="button" class="mob-user-menu-action action-red" id="mobLogoutAction">
                    <div class="mob-user-menu-action-icon">
                        <i class="ph-fill ph-sign-out" style="font-size:.85rem;"></i>
                    </div>
                    <span>Log Out</span>
                </button>
            </div>

        </div>
    </div>
</header>

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

<nav class="mob-bottom-nav">
    <a href="{{ route('officer.dashboard') }}"
       class="mob-nav-item {{ request()->routeIs('officer.dashboard') ? 'active' : '' }}">
        <i class="ph-fill ph-house-simple"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('officer.motorists.index') }}"
       class="mob-nav-item {{ request()->routeIs('officer.motorists.*') || request()->routeIs('officer.violations.*') ? 'active' : '' }}">
        <i class="ph-fill ph-identification-badge"></i>
        <span>Violations</span>
    </a>
    <a href="{{ route('officer.incidents.index') }}"
       class="mob-nav-item {{ request()->routeIs('officer.incidents.*') ? 'active' : '' }}">
        <i class="ph-fill ph-siren"></i>
        <span>Incidents</span>
    </a>
</nav>

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
const mobUserMenuToggle = document.getElementById('mobUserMenuToggle');
const mobUserMenu = document.getElementById('mob-user-menu');
const mobLogoutAction = document.getElementById('mobLogoutAction');

function closeMobUserMenu() {
    if (!mobUserMenu || !mobUserMenuToggle) return;
    mobUserMenu.classList.remove('open');
    mobUserMenuToggle.setAttribute('aria-expanded', 'false');
}

mobUserMenuToggle?.addEventListener('click', function (event) {
    event.stopPropagation();
    const isOpen = mobUserMenu.classList.toggle('open');
    mobUserMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

mobUserMenu?.addEventListener('click', function (event) {
    event.stopPropagation();
});

mobLogoutAction?.addEventListener('click', function () {
    closeMobUserMenu();
    document.getElementById('logout-form').submit();
});

document.getElementById('mobChangePasswordAction')?.addEventListener('click', function () {
    closeMobUserMenu();
    new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
});

// Re-open change password modal if there were validation errors
if (document.getElementById('changePasswordModal')?.dataset.hasErrors === '1') {
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    });
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.mob-user-menu-wrap')) {
        closeMobUserMenu();
    }

    var thumb = e.target.closest('.mob-photo-thumb, .mob-photo-single');
    if (!thumb) return;

    var lb = document.getElementById('mob-lightbox');
    lb.querySelector('img').src = thumb.dataset.full || thumb.src;

    var cap = document.getElementById('mob-lightbox-caption');
    if (cap) cap.textContent = thumb.dataset.caption || thumb.alt || '';

    lb.classList.add('open');
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeMobUserMenu();
        document.getElementById('mob-lightbox').classList.remove('open');
    }
});
</script>
{{-- ── Change Password Modal ── --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-has-errors="{{ $errors->hasAny(['current_password','password']) ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px;margin:1rem auto;">
        <div class="modal-content border-0 rounded-4 shadow" style="overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);padding:1.25rem 1.5rem .9rem;display:flex;align-items:center;gap:.75rem;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ph-fill ph-lock-key" style="font-size:1.15rem;color:#fff;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:.95rem;font-weight:800;color:#fff;">Change Password</div>
                    <div style="font-size:.7rem;color:#bfdbfe;">{{ Auth::user()->name }}</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="opacity:.7;"></button>
            </div>
            <div style="padding:1.25rem 1.5rem 1.5rem;background:#fff;">
                <form method="POST" action="{{ route('officer.password.update') }}" id="changePasswordForm">
                    @csrf
                    @method('PUT')
                    @if($errors->hasAny(['current_password','password']))
                    <div class="mob-alert mob-alert-danger mb-3" style="margin:0 0 .75rem;">
                        <i class="ph-fill ph-warning-circle flex-shrink-0"></i>
                        <div>{{ $errors->first('current_password') ?: $errors->first('password') }}</div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label for="cp_current_password" class="mob-label">Current Password</label>
                        <input id="cp_current_password" type="password" name="current_password" class="form-control mob-input" required autocomplete="current-password" placeholder="Enter current password">
                    </div>
                    <div class="mb-3">
                        <label for="cp_password" class="mob-label">New Password</label>
                        <input id="cp_password" type="password" name="password" class="form-control mob-input" required autocomplete="new-password" placeholder="At least 8 characters">
                    </div>
                    <div class="mb-4">
                        <label for="cp_password_confirmation" class="mob-label">Confirm New Password</label>
                        <input id="cp_password_confirmation" type="password" name="password_confirmation" class="form-control mob-input" required autocomplete="new-password" placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="mob-btn-primary mb-2">
                        <i class="ph-bold ph-check"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@stack('scripts')

{{-- ── Global Photo Picker Helper ── --}}
<style>
.mob-photo-picker { display:flex; gap:.5rem; }
.mob-photo-picker-btn {
    flex:1; display:flex; align-items:center; justify-content:center; gap:.4rem;
    padding:.55rem .5rem; border-radius:10px; font-size:.78rem; font-weight:700;
    cursor:pointer; border:1.5px solid; transition:background .15s;
}
.mob-photo-picker-btn.camera  { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }
.mob-photo-picker-btn.gallery { background:#f0fdf4; border-color:#86efac; color:#15803d; }
.mob-photo-picker-btn i { font-size:1rem; }

/* Preview area */
.mob-picker-preview-single {
    width:100%; border-radius:12px; overflow:hidden;
    position:relative; margin-bottom:.5rem;
    background:#f1f5f9; border:1.5px solid #e2e8f0;
}
.mob-picker-preview-single img {
    width:100%; max-height:200px; object-fit:cover; display:block;
}
.mob-picker-preview-single .mob-picker-remove {
    position:absolute; top:.35rem; right:.35rem;
    width:26px; height:26px; border-radius:50%;
    background:rgba(0,0,0,.55); border:none; color:#fff;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:.8rem;
}
.mob-picker-preview-grid {
    display:grid; grid-template-columns:repeat(3,1fr); gap:.4rem; margin-bottom:.5rem;
}
.mob-picker-preview-grid-item {
    position:relative; aspect-ratio:1; border-radius:8px; overflow:hidden;
    background:#f1f5f9; border:1px solid #e2e8f0;
}
.mob-picker-preview-grid-item img { width:100%; height:100%; object-fit:cover; display:block; }
.mob-picker-preview-grid-item .mob-picker-remove {
    position:absolute; top:.2rem; right:.2rem;
    width:22px; height:22px; border-radius:50%;
    background:rgba(0,0,0,.55); border:none; color:#fff;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:.7rem;
}
</style>
<script>
/**
 * initPhotoPicker(wrapperId, inputName, options)
 * Camera / Gallery buttons + live photo preview before submit.
 * options: { multiple: bool, accept: string }
 */
function initPhotoPicker(wrapperId, inputName, options) {
    options = options || {};
    const wrapper  = document.getElementById(wrapperId);
    if (!wrapper) return;

    const accept   = options.accept   || 'image/*';
    const multiple = !!options.multiple;
    const baseName = multiple ? inputName.replace(/\[\]$/, '') : inputName;

    let files = []; // array of File objects

    // ── Hidden inputs (camera + gallery) ──
    const camInput = document.createElement('input');
    camInput.type = 'file'; camInput.accept = accept; camInput.capture = 'environment';
    if (multiple) camInput.multiple = true;
    camInput.className = 'd-none photo-picker-input';

    const galInput = document.createElement('input');
    galInput.type = 'file'; galInput.accept = accept;
    if (multiple) galInput.multiple = true;
    galInput.className = 'd-none photo-picker-input';

    // ── Preview container ──
    const previewEl = document.createElement('div');

    // ── Buttons ──
    const picker = document.createElement('div');
    picker.className = 'mob-photo-picker';

    const camBtn = document.createElement('button');
    camBtn.type = 'button'; camBtn.className = 'mob-photo-picker-btn camera';
    camBtn.innerHTML = '<i class="ph ph-camera"></i> Camera';
    camBtn.addEventListener('click', () => camInput.click());

    const galBtn = document.createElement('button');
    galBtn.type = 'button'; galBtn.className = 'mob-photo-picker-btn gallery';
    galBtn.innerHTML = '<i class="ph ph-images"></i> Gallery';
    galBtn.addEventListener('click', () => galInput.click());

    picker.appendChild(camBtn);
    picker.appendChild(galBtn);

    wrapper.appendChild(camInput);
    wrapper.appendChild(galInput);
    wrapper.appendChild(previewEl);
    wrapper.appendChild(picker);

    // ── Sync hidden file inputs to form ──
    function syncInputs() {
        wrapper.querySelectorAll('.picker-sync-input').forEach(el => el.remove());
        if (!files.length) return;
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        const inp = document.createElement('input');
        inp.type = 'file';
        inp.name = multiple ? baseName + '[]' : baseName;
        if (multiple) inp.multiple = true;
        inp.className = 'picker-sync-input d-none';
        inp.files = dt.files;
        wrapper.appendChild(inp);
    }

    // ── Render previews ──
    function renderPreviews() {
        previewEl.innerHTML = '';
        if (!files.length) return;

        if (!multiple) {
            // Single image — tall preview
            const wrap = document.createElement('div');
            wrap.className = 'mob-picker-preview-single';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(files[0]);
            const rm = document.createElement('button');
            rm.type = 'button'; rm.className = 'mob-picker-remove';
            rm.innerHTML = '<i class="ph-bold ph-x"></i>';
            rm.addEventListener('click', () => { files = []; renderPreviews(); syncInputs(); });
            wrap.appendChild(img);
            wrap.appendChild(rm);
            previewEl.appendChild(wrap);
        } else {
            // Multiple — grid
            const grid = document.createElement('div');
            grid.className = 'mob-picker-preview-grid';
            files.forEach((f, i) => {
                const item = document.createElement('div');
                item.className = 'mob-picker-preview-grid-item';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                const rm = document.createElement('button');
                rm.type = 'button'; rm.className = 'mob-picker-remove';
                rm.innerHTML = '<i class="ph-bold ph-x"></i>';
                rm.addEventListener('click', () => { files.splice(i, 1); renderPreviews(); syncInputs(); });
                item.appendChild(img); item.appendChild(rm);
                grid.appendChild(item);
            });
            previewEl.appendChild(grid);
        }
    }

    function handleFiles(input) {
        const newFiles = Array.from(input.files);
        newFiles.forEach(f => {
            if (!multiple) { files = [f]; return; }
            files.push(f);
        });
        input.value = '';
        renderPreviews();
        syncInputs();
    }

    camInput.addEventListener('change', () => handleFiles(camInput));
    galInput.addEventListener('change', () => handleFiles(galInput));
}
</script>
</body>
</html>
