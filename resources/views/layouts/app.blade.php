<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Traffic Violation Incident Record System</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet"></noscript>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <style>
        /* ── Anti-FOUC: hide until CSS is ready ── */
        body { visibility: hidden; }

        /* ── FLATPICKR CUSTOM THEME ── */
        .flatpickr-calendar {
            background: #fffdf9;
            border: 1px solid #ddd0be;
            border-radius: .5rem;
            box-shadow: 0 6px 20px rgba(120,80,20,0.13);
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            font-size: .8rem;
            width: 252px !important;
            padding: 0;
        }
        .flatpickr-calendar.arrowTop:before { border-bottom-color: #ddd0be; }
        .flatpickr-calendar.arrowTop:after  { border-bottom-color: #1d4ed8; }

        /* Header */
        .flatpickr-months {
            border-radius: .5rem .5rem 0 0;
            overflow: hidden;
            padding: 4px 2px;
            background: #1d4ed8;
            align-items: center;
        }
        .flatpickr-months .flatpickr-month { background: #1d4ed8; color: #fff; height: 30px; }
        .flatpickr-current-month {
            font-size: .82rem;
            font-weight: 600;
            color: #fff;
            padding-top: 4px;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            background: transparent;
            color: #fff;
            font-weight: 600;
            font-size: .82rem;
            border: none;
        }
        .flatpickr-current-month input.cur-year {
            color: #fff;
            font-weight: 700;
            font-size: .82rem;
        }
        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            fill: #fff;
            color: #fff;
            padding: 4px 8px;
            height: 30px;
            line-height: 22px;
        }
        .flatpickr-prev-month svg, .flatpickr-next-month svg { fill: #fff !important; width: 11px; height: 11px; }
        .flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg { fill: #bfdbfe !important; }

        /* Weekdays row */
        .flatpickr-weekdays { background: #2563eb; padding: 2px 0; }
        span.flatpickr-weekday {
            background: #2563eb;
            color: #bfdbfe;
            font-size: .7rem;
            font-weight: 700;
        }

        /* Days grid */
        .flatpickr-days { border-color: #ede8df; }
        .dayContainer { padding: 4px; min-width: unset; max-width: unset; width: 100%; }
        .flatpickr-day {
            color: #44403c;
            border-radius: .3rem;
            height: 30px;
            line-height: 30px;
            max-width: 30px;
            font-size: .78rem;
            margin: 1px;
        }
        .flatpickr-day:hover { background: #fdf8f0; border-color: #ddd0be; }
        .flatpickr-day.today {
            border-color: #d97706;
            font-weight: 700;
            color: #92400e;
        }
        .flatpickr-day.today:hover { background: #fef3c7; border-color: #d97706; color: #92400e; }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
            font-weight: 700;
        }
        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.flatpickr-disabled:hover { color: #d4c9b8; }
        .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #c8b99a; }

        /* Input styling */
        .flatpickr-input { cursor: text; }
        .numInputWrapper span { border-color: rgba(255,255,255,0.25); }
        .numInputWrapper span:after { border-top-color: #fff; border-bottom-color: #fff; }

        /* ── BASE ── */
        html {
            scrollbar-gutter: stable;
        }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #f5f0e8;
            color: #44403c;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 270px;
            min-height: 100vh;
            background: #1d4ed8;
            border-right: 1px solid rgba(0,0,0,0.08);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: .5rem;
        }

        .sidebar-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            flex-shrink: 0;
            /* scale up the badge beyond its internal whitespace */
            transform: scale(1.45);
            transform-origin: center;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.35));
        }

        .sidebar-brand h6 {
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.01em;
            margin: 0;
            line-height: 1.3;
            text-align: center;
        }

        .sidebar-brand span {
            color: #bfdbfe;
            font-size: 0.68rem;
            font-weight: 500;
            display: block;
            text-align: center;
        }

        .sidebar-nav { padding: 0.75rem 0 1.25rem; flex: 1; overflow-y: auto; }

        .sidebar-nav .nav-label {
            color: rgba(255,255,255,0.45);
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.75rem 1.25rem 0.3rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            margin: 0 .5rem;
            border-radius: .4rem;
        }

        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border-left: 3px solid #fff;
            padding-left: calc(1.25rem - 3px);
            font-weight: 600;
        }

        .sidebar-nav a i { font-size: 1rem; width: 1.2rem; }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.15);
            position: sticky;
            bottom: 0;
            background: linear-gradient(180deg, rgba(29,78,216,0.90), rgba(29,78,216,0.95));
            backdrop-filter: blur(6px);
            z-index: 50;
            display: none;
        }

        .sidebar-footer .user-info { color: rgba(255,255,255,0.85); font-size: 0.72rem; margin-bottom: 0.2rem; }
        .sidebar-footer .user-name { color: #fff; font-weight: 700; font-size: 0.875rem; }

        .logout-btn {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }
        .logout-btn:hover {
            background: rgba(239,68,68,0.75);
            border-color: rgba(239,68,68,0.9);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.35);
        }
        .logout-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* ── MAIN WRAPPER ── */
        .main-wrapper {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── MOBILE SIDEBAR ── */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 99;
        }
        .hamburger-btn {
            display: none;
            background: none;
            border: 1px solid #ddd0be;
            border-radius: .4rem;
            padding: .3rem .5rem;
            cursor: pointer;
            color: #57534e;
            margin-right: .5rem;
            line-height: 1;
        }
        .hamburger-btn:hover { background: #fdf8f0; }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(.4,0,.2,1);
                z-index: 200;
                height: 100vh;
            }
            .sidebar-nav {
                max-height: calc(100vh - 70px);
                overflow-y: auto;
            }
            .sidebar.sidebar-open {
                transform: translateX(0);
            }
            .sidebar-backdrop.sidebar-open {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .hamburger-btn {
                display: inline-flex;
                align-items: center;
            }
        }

        /* ── TOPBAR ── */
        .topbar {
            background: #fdf8f0;
            border-bottom: 1px solid #ddd0be;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(120,80,20,0.07);
        }

        .topbar-title {
            font-weight: 700;
            color: #292524;
            font-size: 1rem;
        }

        .topbar-date {
            font-size: 0.8rem;
            color: #78716c;
        }

        .topbar-logout-btn {
            display: none;
            padding: .4rem .6rem;
            border: 1px solid #ddd0be;
            border-radius: .4rem;
            background: #fef2f2;
            color: #c82e2e;
            font-weight: 600;
            font-size: .78rem;
            cursor: pointer;
        }
        .topbar-logout-btn:hover {
            background: #fce7e7;
            color: #991b1b;
        }

        @media (max-width: 991.98px) {
            .topbar-logout-btn {
                display: inline-flex;
                align-items: center;
                gap: .25rem;
            }
        }

        /* ── PROFILE DROPDOWN ── */
        /* Elements only shown on mobile sheet — hidden on desktop */
        .profile-sheet-handle  { display: none; }
        .profile-sheet-header  { display: none; }
        .profile-sheet-avatar  { display: none; }
        .profile-sheet-system  { display: none; }
        .profile-sheet-divider { display: none; }
        .profile-sheet-backdrop { display: none; }

        .topbar-profile-menu { position: relative; }

        .topbar-profile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 50%;
            border: 1.5px solid #ddd0be;
            background: #fdf8f0;
            color: #1d4ed8;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }
        .topbar-profile-btn:hover {
            background: #f5f0e8;
            border-color: #1d4ed8;
        }

        /* Desktop dropdown */
        .topbar-profile-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: #fff;
            border: 1px solid #ddd0be;
            border-radius: 0.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            min-width: 200px;
            display: none;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.15s, transform 0.15s;
            overflow: hidden;
        }
        .topbar-profile-dropdown.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* Desktop: user info section */
        .profile-sheet-body {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0ebe3;
        }
        .profile-sheet-name {
            font-size: 0.9rem;
            color: #292524;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .profile-sheet-meta { margin-bottom: 0; }
        .profile-sheet-role-badge {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 0.22rem 0.5rem;
            border-radius: 0.25rem;
        }
        .profile-sheet-role-badge.role-operator { background: #1d4ed8; color: #fff; }
        .profile-sheet-role-badge.role-viewer   { background: #dbeafe; color: #1e40af; }

        /* Desktop: logout section */
        .profile-sheet-actions { padding: 0.75rem 1rem; }
        .profile-sheet-logout-btn {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #fee2e2;
            border-radius: 0.375rem;
            background: #fef2f2;
            color: #991b1b;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }
        .profile-sheet-logout-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* ── CONTENT ── */
        .content { padding: 1.5rem; flex: 1; }

        /* ── BADGES ── */
        .badge-operator { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; }
        .badge-viewer   { background: #93c5fd; color: #1e3a8a; }

        /* ── CARD ── */
        .card {
            background: #fffdf9;
            border: 1px solid #ddd0be;
            color: #44403c;
            box-shadow: 0 1px 4px rgba(120,80,20,0.06);
        }

        .card-header {
            background: #fdf8f0 !important;
            border-bottom: 1px solid #ddd0be !important;
            color: #292524 !important;
            font-weight: 600;
        }

        .card-footer {
            background: #fdf8f0 !important;
            border-top: 1px solid #ddd0be !important;
            color: #78716c !important;
        }

        /* ── TABLE ── */
        .table {
            color: #44403c;
            --bs-table-bg: transparent;
            --bs-table-hover-bg: rgba(180,120,40,0.05);
            --bs-table-striped-bg: rgba(180,120,40,0.03);
        }

        .table thead th, .table th {
            color: #57534e;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 2px solid #ddd0be;
            background: #fdf8f0;
        }

        .table td {
            border-color: #ede8df;
            vertical-align: middle;
        }

        .table-light {
            --bs-table-bg: #fdf8f0;
            --bs-table-color: #57534e;
            color: #57534e;
        }

        .table-hover tbody tr:hover td {
            background: rgba(180,120,40,0.05);
            color: #292524;
        }

        /* ── FORM ── */
        .form-control, .form-select {
            background: #fffdf9;
            border: 1px solid #c8b99a;
            color: #44403c;
        }

        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: #d97706;
            color: #292524;
            box-shadow: 0 0 0 .2rem rgba(217,119,6,0.15);
        }

        .form-control::placeholder { color: #a8a29e; }

        .form-label { color: #57534e; font-weight: 600; font-size: .875rem; }

        .input-group-text {
            background: #fdf8f0;
            border: 1px solid #c8b99a;
            color: #78716c;
        }

        .form-check-input:checked {
            background-color: #d97706;
            border-color: #d97706;
        }

        .form-check-label { color: #57534e; }

        /* ── LIST GROUP ── */
        .list-group-item {
            background: #fffdf9;
            border-color: #ddd0be;
            color: #44403c;
        }

        .list-group-flush .list-group-item { border-color: #ede8df; }

        /* ── TOAST NOTIFICATIONS ── */
        #toastContainer {
            position: fixed;
            top: 1.1rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .55rem;
            pointer-events: none;
        }
        .app-toast {
            pointer-events: all;
            min-width: 300px;
            max-width: 380px;
            border-radius: 12px;
            padding: .8rem 1rem;
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            box-shadow: 0 8px 30px rgba(0,0,0,.13), 0 2px 8px rgba(0,0,0,.08);
            animation: toastIn .28s cubic-bezier(.22,1,.36,1) both;
            font-size: .875rem;
            font-weight: 500;
            border-left: 4px solid transparent;
        }
        .app-toast.toast-success {
            background: #f0fdf4;
            border-left-color: #22c55e;
            color: #166534;
        }
        .app-toast.toast-error {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        .app-toast.toast-warning {
            background: #fffbeb;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        .app-toast-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: .05rem; }
        .app-toast-body { flex: 1; line-height: 1.45; }
        .app-toast-close {
            background: none; border: none; padding: 0;
            cursor: pointer; opacity: .5; font-size: .9rem;
            line-height: 1; flex-shrink: 0; margin-top: .1rem;
            color: inherit;
        }
        .app-toast-close:hover { opacity: 1; }
        .app-toast-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            border-radius: 0 0 12px 12px;
            background: currentColor;
            opacity: .25;
            animation: toastProgress 4s linear forwards;
        }
        .app-toast { position: relative; overflow: hidden; }
        @keyframes toastIn {
            from { opacity:0; transform:translateX(60px) scale(.95); }
            to   { opacity:1; transform:translateX(0)    scale(1);   }
        }
        @keyframes toastOut {
            from { opacity:1; transform:translateX(0)    scale(1);   max-height:200px; margin-bottom:0; }
            to   { opacity:0; transform:translateX(60px) scale(.95); max-height:0;     margin-bottom:-8px; }
        }
        @keyframes toastProgress {
            from { width: 100%; }
            to   { width: 0%;   }
        }

        /* ── PAGINATION ── */
        .pagination .page-link {
            background: #fffdf9;
            border-color: #ddd0be;
            color: #57534e;
        }

        .pagination .page-link:hover {
            background: #fdf8f0;
            color: #292524;
        }

        .pagination .page-item.active .page-link {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            background: #fdf8f0;
            color: #a8a29e;
            border-color: #ddd0be;
        }

        /* ── MISC ── */
        .text-muted { color: #78716c !important; }
        .border-bottom { border-color: #ddd0be !important; }
        .fw-500 { font-weight: 500; }

        .btn-outline-secondary {
            color: #57534e;
            border-color: #c8b99a;
        }

        .btn-outline-secondary:hover {
            background: #fdf8f0;
            border-color: #a8a29e;
            color: #292524;
        }

        .btn-outline-primary {
            color: #2563eb;
            border-color: #93c5fd;
        }

        .btn-outline-primary:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #1d4ed8;
        }

        .btn-outline-warning {
            color: #b45309;
            border-color: #fcd34d;
        }

        .btn-outline-warning:hover {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }

        .btn-outline-danger {
            color: #dc2626;
            border-color: #fca5a5;
        }

        .btn-outline-danger:hover {
            background: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
        }

        a { color: #92400e; }
        a:hover { color: #78350f; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f5f0e8; }
        ::-webkit-scrollbar-thumb { background: #c8b99a; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8956e; }

        /* ─── Global print reset ─── */
        @media print {
            .sidebar, .sidebar-backdrop, .topbar, .hamburger-btn { display: none !important; }
            .main-wrapper { margin-left: 0 !important; min-height: 0 !important; }
            .content { padding: 0 !important; margin: 0 !important; }
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
        }

        /* ── BREADCRUMB ── */
        .tvrs-breadcrumb-nav .breadcrumb {
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.7);
            border: 1px solid #e7e5e4;
            border-radius: 20px;
            padding: 3px 12px;
            margin: 0;
            font-size: .75rem;
            --bs-breadcrumb-divider: '›';
        }
        .tvrs-breadcrumb-nav .breadcrumb-item {
            display: flex;
            align-items: center;
        }
        .tvrs-breadcrumb-nav .breadcrumb-item + .breadcrumb-item::before {
            color: #c4b5a5;
            font-size: 1rem;
            line-height: 1;
            padding-right: 6px;
        }
        .tvrs-breadcrumb-nav .breadcrumb-item a {
            color: #78716c !important;
            text-decoration: none;
            font-weight: 500;
            transition: color .15s ease;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .tvrs-breadcrumb-nav .breadcrumb-item a:hover {
            color: #1d4ed8 !important;
        }
        .tvrs-breadcrumb-nav .breadcrumb-item.active {
            color: #292524 !important;
            font-weight: 600;
        }

        /* ─── GLOBAL MOBILE ─── */
        @media (max-width: 767px) {

            /* Topbar */
            .topbar { padding: .5rem .75rem; gap: .35rem; }
            .topbar-title { font-size: .85rem; }
            .topbar-date { display: none; }
            .tvrs-breadcrumb-nav { display: none; } /* hide breadcrumb on mobile — too cramped */
            .topbar > .d-flex:last-child { gap: .5rem !important; }

            /* Content */
            .content { padding: .75rem; }

            /* Toasts: compact right-aligned on mobile */
            #toastContainer { left: auto; right: .75rem; top: .75rem; width: min(calc(100vw - 1.5rem), 320px); }
            .app-toast { min-width: 0; width: 100%; }

            /* Filter card headers: wrap action buttons below label */
            .filter-card-header,
            .vlt-filter-header {
                flex-wrap: wrap;
                gap: .5rem;
            }
            .filter-card-header .ms-auto,
            .vlt-filter-header .ms-auto {
                margin-left: 0 !important;
                width: 100%;
            }

            /* Filter form rows: stack fields vertically */
            .filter-card-body form > .d-flex,
            .vlt-filter-body form > .d-flex {
                flex-wrap: wrap !important;
            }
            .filter-card-body form > .d-flex > div,
            .vlt-filter-body form > .d-flex > div {
                flex: 1 1 100% !important;
                min-width: 0 !important;
            }

            /* Reports filter: already flex-wrap, just ensure field widths */
            .rpt-filter-body form > div[style] {
                flex: 1 1 100% !important;
                min-width: 0 !important;
            }
            .rpt-filter-body form > .d-flex.gap-2 {
                width: 100%;
            }

            /* Pagination: compact and wrapping */
            .pagination { flex-wrap: wrap; gap: .25rem; }
            .pagination .page-link { padding: .35rem .6rem; font-size: .8rem; }

            /* Table action buttons: icon-only, larger touch target */
            .vlt-act-btn span { display: none; }
            .vlt-act-btn {
                min-width: 36px;
                min-height: 36px;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                padding: .45rem !important;
            }

            /* Generic index page action buttons (icon-only, larger tap) */
            .act-btn, [class*="act-btn"], [class*="-act-btn"] {
                min-width: 36px;
                min-height: 36px;
            }

            /* Pagination footer wrappers: stack count + links */
            .d-flex.justify-content-between.align-items-center {
                flex-wrap: wrap;
                gap: .5rem;
            }

            /* ── Profile → bottom sheet on mobile ── */

            /* Backdrop */
            .profile-sheet-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                backdrop-filter: blur(2px);
                z-index: 1050;
            }
            .profile-sheet-backdrop.show { display: block; }

            /* Sheet container */
            .topbar-profile-dropdown {
                position: fixed !important;
                top: auto !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                min-width: 0 !important;
                max-width: 100% !important;
                border-radius: 22px 22px 0 0 !important;
                border: none !important;
                box-shadow: 0 -4px 48px rgba(0,0,0,.22) !important;
                z-index: 1051 !important;
                display: block !important;
                transform: translateY(110%) !important;
                transition: transform .32s cubic-bezier(.4,0,.2,1) !important;
                opacity: 1 !important;
                background: #fff !important;
                overflow: hidden !important;
            }
            .topbar-profile-dropdown.show {
                transform: translateY(0) !important;
            }

            /* Drag handle */
            .profile-sheet-handle {
                display: block;
                width: 28px; height: 3px;
                background: rgba(255,255,255,.4);
                border-radius: 2px;
                position: absolute;
                top: 7px;
                left: 50%; transform: translateX(-50%);
                z-index: 2;
            }

            /* Gradient header band — slim */
            .profile-sheet-header {
                display: block;
                background: linear-gradient(135deg,#1d4ed8,#3b82f6);
                height: 52px;
                position: relative;
            }

            /* Avatar — straddles header/body boundary */
            .profile-sheet-avatar {
                display: flex !important;
                position: absolute;
                bottom: -22px;
                left: 50%; transform: translateX(-50%);
                width: 44px; height: 44px;
                border-radius: 50%;
                background: #fff;
                color: #1d4ed8;
                font-weight: 800;
                font-size: 1rem;
                align-items: center;
                justify-content: center;
                border: 2.5px solid #fff;
                box-shadow: 0 2px 10px rgba(29,78,216,.22);
                z-index: 3;
            }

            /* Body */
            .profile-sheet-body {
                padding: 28px 1rem .5rem;
                text-align: center;
                border-bottom: none !important;
            }
            .profile-sheet-name {
                font-size: .9rem;
                font-weight: 700;
                color: #1c1917;
                margin-bottom: .2rem;
                line-height: 1.2;
            }
            .profile-sheet-meta { margin-bottom: .3rem; }
            .profile-sheet-role-badge {
                display: inline-flex;
                align-items: center;
                gap: .2rem;
                font-size: .65rem;
                font-weight: 600;
                padding: .18rem .55rem;
                border-radius: 9999px;
            }
            .profile-sheet-role-badge.role-operator { background: #1d4ed8; color: #fff; }
            .profile-sheet-role-badge.role-viewer   { background: #dbeafe; color: #1e40af; }
            .profile-sheet-system {
                display: block;
                font-size: .62rem;
                color: #a8a29e;
                font-weight: 500;
            }

            /* Divider */
            .profile-sheet-divider {
                display: block;
                height: 1px;
                background: #f0ebe3;
                margin: .5rem 1rem 0;
            }

            /* Actions */
            .profile-sheet-actions {
                padding: .5rem 1rem 1rem;
            }
            .profile-sheet-logout-btn {
                width: 100%;
                min-height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .4rem;
                background: linear-gradient(135deg,#dc2626,#b91c1c);
                color: #fff;
                border: none;
                border-radius: 8px;
                font-size: .82rem;
                font-weight: 700;
                cursor: pointer;
                transition: opacity .15s, transform .1s;
                box-shadow: 0 2px 8px rgba(220,38,38,.25);
            }
            .profile-sheet-logout-btn:active {
                opacity: .88;
                transform: scale(.98);
            }

            /* Confirm modal: full width */
            #confirmModal .modal-dialog { margin: .5rem; max-width: 100%; }

            /* Mobile-hide utility */
            .d-mobile-none { display: none !important; }
        }

        @media (max-width: 400px) {
            .content { padding: .5rem; }
            .topbar { padding: .45rem .6rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<div class="profile-sheet-backdrop" id="profileSheetBackdrop"></div>
<div class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/PNP.png') }}" alt="PNP Logo" class="sidebar-logo">
        <div>
            <h6>Traffic Violation Incident Record System</h6>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-label">Records</div>
        <a href="{{ route('violators.index') }}" class="{{ request()->routeIs('violators.*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i> Motorists
        </a>
        <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*', 'vehicle-photos.*') ? 'active' : '' }}">
            <i class="bi bi-car-front-fill"></i> Vehicles
        </a>
        <a href="{{ route('violations.index') }}" class="{{ request()->routeIs('violations.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle-fill"></i> Violations
        </a>
        <a href="{{ route('incidents.index') }}" class="{{ request()->routeIs('incidents.*') ? 'active' : '' }}">
            <i class="bi bi-flag-fill"></i> Incidents
        </a>

        <div class="nav-label">References</div>
        <a href="{{ route('violation-types.index') }}" class="{{ request()->routeIs('violation-types.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i> Violation Types
        </a>
        <a href="{{ route('incident-charge-types.index') }}" class="{{ request()->routeIs('incident-charge-types.*') ? 'active' : '' }}">
            <i class="bi bi-shield-exclamation"></i> Charge Types
        </a>
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i> Reports
        </a>

        @if(Auth::user()->isOperator())
        <div class="nav-label">Administration</div>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Users
        </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">Logged in as</div>
        <div class="user-name">{{ Auth::user()->name }}</div>
        <div class="mt-1">
            <span class="badge {{ Auth::user()->isOperator() ? 'badge-operator' : 'badge-viewer' }}">
                {{ ucfirst(Auth::user()->role) }}
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" class="btn btn-sm w-100 logout-btn">
                <i class="bi bi-box-arrow-right"></i> Log out
            </button>
        </form>
    </div>
</div>

<div class="main-wrapper">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn" id="sidebarToggle" aria-label="Open navigation menu" aria-expanded="false">
                <i class="bi bi-list" style="font-size:1.25rem;"></i>
            </button>
            <img src="{{ asset('images/PNP.png') }}" alt="PNP" style="width:24px;height:24px;object-fit:contain;">
            <div>
                <div class="topbar-title">@yield('title', 'Dashboard')</div>
                @hasSection('topbar-sub')
                    <div style="font-size:.73rem;color:#78716c;margin-top:1px;">@yield('topbar-sub')</div>
                @endif
            </div>
        </div>
        @hasSection('breadcrumbs')
            <nav aria-label="breadcrumb" class="tvrs-breadcrumb-nav">
                <ol class="breadcrumb">
                    @yield('breadcrumbs')
                </ol>
            </nav>
        @endif
        <div class="d-flex align-items-center gap-3">
            <span class="topbar-date">{{ now()->format('F d, Y') }}</span>
            
            <!-- Profile Dropdown / Mobile Bottom Sheet -->
            <div class="topbar-profile-menu">
                <button class="topbar-profile-btn" id="profileMenuToggle" title="Profile & Logout">
                    <i class="bi bi-person-circle"></i>
                </button>
                <div class="topbar-profile-dropdown" id="profileMenuDropdown">

                    {{-- Mobile-only: gradient header with drag handle + avatar --}}
                    <div class="profile-sheet-header">
                        <div class="profile-sheet-handle"></div>
                        <div class="profile-sheet-avatar">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>

                    {{-- User info (desktop: plain text block / mobile: centered below avatar) --}}
                    <div class="profile-sheet-body">
                        <div class="profile-sheet-name">{{ Auth::user()->name }}</div>
                        <div class="profile-sheet-meta">
                            <span class="profile-sheet-role-badge {{ Auth::user()->isOperator() ? 'role-operator' : 'role-viewer' }}">
                                <i class="bi bi-shield-fill" style="font-size:.6rem;"></i>
                                {{ ucfirst(Auth::user()->role) }}
                            </span>
                        </div>
                        <div class="profile-sheet-system">Traffic Violation Incident Record System</div>
                    </div>

                    {{-- Mobile-only divider --}}
                    <div class="profile-sheet-divider"></div>

                    {{-- Logout --}}
                    <div class="profile-sheet-actions">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="profile-sheet-logout-btn">
                                <i class="bi bi-box-arrow-right"></i> Log out
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="content">
        {{-- Flash toasts are rendered globally in #toastContainer below --}}

        @yield('content')
    </div>
</div>

{{-- ── Global Confirm Modal ── --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;background:#fffdf9;">
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:44px;height:44px;background:#fee2e2;">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div class="fw-700 mb-1" style="color:#1c1917;font-size:.95rem;">Are you sure?</div>
                        <div id="confirmModalMessage" style="color:#57534e;font-size:.85rem;line-height:1.5;"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-4"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmModalOk"
                            class="btn btn-sm btn-danger px-4 fw-600">
                        <i class="bi bi-trash me-1" style="font-size:.8rem;"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
/**
 * applyDateMask(el)
 * Enforces YYYY-MM-DD format on a flatpickr text input:
 *  – Only digits allowed while typing
 *  – Auto-inserts dashes after year and month
 *  – Clamps month to 01-12 (auto-pads if first digit > 1)
 *  – Clamps day to 01-31  (auto-pads if first digit > 3)
 */
function applyDateMask(el) {
    if (!el) return;

    // Block anything that isn't a digit or a control key
    el.addEventListener('keydown', function (e) {
        const allowed = ['Backspace','Delete','Tab','Escape','Enter',
                         'ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
        if (allowed.includes(e.key) || e.ctrlKey || e.metaKey) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    el.addEventListener('input', function () {
        // Strip everything except digits, cap at 8
        let d = this.value.replace(/\D/g, '').substring(0, 8);

        // ── Month corrections (positions 4-5) ──
        if (d.length === 5 && parseInt(d[4]) > 1) {
            // e.g. user typed '3' as first month digit → pad to '03'
            d = d.substring(0, 4) + '0' + d[4];
        }
        if (d.length >= 6) {
            let m = parseInt(d.substring(4, 6));
            if (m > 12) d = d.substring(0, 4) + '12' + d.substring(6);
            if (m === 0)  d = d.substring(0, 4) + '01' + d.substring(6);
        }

        // ── Day corrections (positions 6-7) ──
        if (d.length === 7 && parseInt(d[6]) > 3) {
            // e.g. user typed '5' as first day digit → pad to '05'
            d = d.substring(0, 6) + '0' + d[6];
        }
        if (d.length === 8) {
            let day = parseInt(d.substring(6, 8));
            if (day > 31) d = d.substring(0, 6) + '31';
            if (day === 0) d = d.substring(0, 6) + '01';
        }

        // ── Re-format with dashes ──
        let out = d.substring(0, 4);
        if (d.length > 4) out += '-' + d.substring(4, 6);
        if (d.length > 6) out += '-' + d.substring(6, 8);
        this.value = out;
    });
}
</script>
{{-- ── Confirm Modal JS ── --}}
<script>
(function () {
    const modal   = new bootstrap.Modal(document.getElementById('confirmModal'));
    const message = document.getElementById('confirmModalMessage');
    const okBtn   = document.getElementById('confirmModalOk');
    let pendingForm = null;

    document.addEventListener('submit', function (e) {
        const form = e.target.closest('form[data-confirm]');
        if (!form) return;
        e.preventDefault();
        pendingForm = form;
        message.textContent = form.dataset.confirm;
        modal.show();
    });

    okBtn.addEventListener('click', function () {
        if (!pendingForm) return;
        modal.hide();
        const f = pendingForm;
        pendingForm = null;
        f.removeAttribute('data-confirm'); // prevent re-trigger
        f.submit();
    });

    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function () {
        pendingForm = null;
    });
})();
</script>
{{-- ── Global: Submit Loading Spinner ── --}}
<script>
(function () {
    // Applies to all POST/PUT/PATCH/DELETE forms (not GET filter forms)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.method === 'get') return;
        // Skip if this is a data-confirm form that hasn't been confirmed yet
        // (the confirm modal JS handles that separately)
        if (form.hasAttribute('data-confirm')) return;

        const btn = form.querySelector('[type="submit"]:not([data-no-loading])');
        if (!btn) return;

        // Snapshot original content
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Please wait…';

        // Re-enable after 8 s as a safety net (e.g. server error)
        setTimeout(function () {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }, 8000);
    });
})();
</script>
{{-- ── Global: Bootstrap Tooltips ── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
{{-- ── Global: Mobile Sidebar Toggle ── --}}
<script>
(function () {
    var sidebar   = document.getElementById('appSidebar');
    var backdrop  = document.getElementById('sidebarBackdrop');
    var toggleBtn = document.getElementById('sidebarToggle');
    if (!toggleBtn) return;

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        backdrop.classList.add('sidebar-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        backdrop.classList.remove('sidebar-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });
    backdrop.addEventListener('click', closeSidebar);

    // Close on nav link click (navigating away)
    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });
})();

// Profile Dropdown / Mobile Bottom Sheet
(function () {
    var profileBtn      = document.getElementById('profileMenuToggle');
    var profileDropdown = document.getElementById('profileMenuDropdown');
    var sheetBackdrop   = document.getElementById('profileSheetBackdrop');
    if (!profileBtn || !profileDropdown) return;

    function isMobile() { return window.innerWidth <= 767; }

    function openProfile() {
        profileDropdown.classList.add('show');
        if (isMobile() && sheetBackdrop) sheetBackdrop.classList.add('show');
    }

    function closeProfile() {
        profileDropdown.classList.remove('show');
        if (sheetBackdrop) sheetBackdrop.classList.remove('show');
    }

    profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        profileDropdown.classList.contains('show') ? closeProfile() : openProfile();
    });

    // Backdrop tap → close
    if (sheetBackdrop) {
        sheetBackdrop.addEventListener('click', closeProfile);
    }

    // Click outside (desktop)
    document.addEventListener('click', function (e) {
        if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            closeProfile();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeProfile();
    });
})();
</script>
{{-- ── Global Toast Container (flash data in data-attributes to avoid Blade-in-JS linter errors) ── --}}
<div id="toastContainer" aria-live="polite" aria-atomic="true"
     data-flash-success="{{ session('success') }}"
     data-flash-error="{{ session('error') }}"
     data-flash-warning="{{ session('warning') }}"></div>

{{-- ── Toast JS ── --}}
<script>
(function () {
    var DURATION = 4000;

    function showToast(message, type) {
        var icons = { success: 'bi-check-circle-fill', error: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill' };
        var icon  = icons[type] || icons.success;
        var toast = document.createElement('div');
        toast.className = 'app-toast toast-' + type;
        toast.setAttribute('role', 'alert');
        toast.innerHTML =
            '<i class="bi ' + icon + ' app-toast-icon"></i>' +
            '<span class="app-toast-body">' + message + '</span>' +
            '<button class="app-toast-close" aria-label="Dismiss">' +
                '<i class="bi bi-x-lg"></i>' +
            '</button>' +
            '<span class="app-toast-progress"></span>';

        var container = document.getElementById('toastContainer');
        container.appendChild(toast);

        function dismiss() {
            toast.style.animation = 'toastOut .25s ease forwards';
            toast.addEventListener('animationend', function () { toast.remove(); }, { once: true });
        }

        toast.querySelector('.app-toast-close').addEventListener('click', dismiss);
        setTimeout(dismiss, DURATION);
    }

    window.showToast = showToast;

    document.addEventListener('DOMContentLoaded', function () {
        var c = document.getElementById('toastContainer');
        var s = c.getAttribute('data-flash-success');
        var e = c.getAttribute('data-flash-error');
        var w = c.getAttribute('data-flash-warning');
        if (s) showToast(s, 'success');
        if (e) showToast(e, 'error');
        if (w) showToast(w, 'warning');
    });
})();
</script>
@stack('scripts')
<script>document.body.style.visibility='visible';</script>
</body>
</html>
