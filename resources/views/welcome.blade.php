<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Traffic Violation Incident Record System</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/app-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            min-height: 100vh;
            background: #050d1a;
            overflow-x: hidden;
        }

        /* Police station photo background — full screen */
        .bg-hero {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            z-index: 0;
        }

        /* Light tint overlay — let the image show clearly */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(5, 18, 50, 0.28);
            z-index: 1;
        }

        .page-wrapper {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── TOP NAV ─── */
        .top-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 200;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            transition: background .3s ease, backdrop-filter .3s ease, border-color .3s ease;
        }

        .top-nav.scrolled {
            background: rgba(5, 18, 50, 0.28);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom-color: rgba(255,255,255,0.10);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #fff;
            text-decoration: none;
        }

        .nav-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #1e4fb5, #2563eb);
            border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
        }

        .nav-brand-icon i { color: #fff; font-size: 1.1rem; }
        .nav-brand-text { font-weight: 700; font-size: .95rem; line-height: 1.2; color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,.6); }
        .nav-brand-text span { display: block; color: #bfdbfe; font-size: .72rem; font-weight: 500; }

        .nav-actions { display: flex; gap: .75rem; align-items: center; }

        .btn-nav-about {
            padding: .4rem 1.1rem;
            border: 1px solid rgba(255,255,255,0.30);
            border-radius: .375rem;
            color: rgba(255,255,255,0.85);
            background: transparent;
            font-size: .85rem;
            font-weight: 500;
            transition: all .2s;
            cursor: pointer;
        }

        .btn-nav-about:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(255,255,255,0.55);
            color: #fff;
        }

        .btn-nav-login {
            padding: .4rem 1.1rem;
            border: 1px solid rgba(255,255,255,0.45);
            border-radius: .375rem;
            color: #fff;
            background: rgba(255,255,255,0.12);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 600;
            transition: all .2s;
        }

        .btn-nav-login:hover {
            background: rgba(255,255,255,0.22);
            border-color: rgba(255,255,255,0.7);
            color: #fff;
        }

        .btn-nav-dashboard {
            padding: .4rem 1.1rem;
            border-radius: .375rem;
            color: #fff;
            background: linear-gradient(135deg, #1e4fb5, #2563eb);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            transition: opacity .2s;
        }

        .btn-nav-dashboard:hover { opacity: .85; color: #fff; }

        /* ─── HERO SECTION ─── */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6rem 1.5rem 3rem;
        }

        /* Police badge SVG shield */
        .badge-shield {
            width: 200px; height: 200px;
            margin: 0 auto 0.25rem;
            position: relative;
        }

        .badge-shield svg { width: 100%; height: 100%; filter: drop-shadow(0 0 28px rgba(96,165,250,0.7)); }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(37,99,235,0.25);
            border: 1px solid rgba(147,197,253,0.55);
            border-radius: 2rem;
            color: #e0f2fe;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: .35rem .95rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 1px 4px rgba(0,0,0,.5);
        }

        .hero-title {
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 700;
            color: #fff;
            line-height: 1.15;
            margin-bottom: .75rem;
            letter-spacing: -.02em;
            text-shadow: 0 2px 12px rgba(0,0,0,.7);
        }

        .hero-title span {
            background: linear-gradient(90deg, #93c5fd, #bfdbfe);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 6px rgba(96,165,250,0.4));
        }

        .hero-subtitle {
            font-size: 1rem;
            color: #e2e8f0;
            max-width: 520px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
            text-shadow: 0 1px 6px rgba(0,0,0,.65);
            font-weight: 500;
        }

        /* ─── CTA BUTTONS ─── */
        .cta-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }

        .btn-cta-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 2rem;
            background: linear-gradient(135deg, #1e4fb5, #2563eb);
            color: #fff;
            border-radius: .5rem;
            font-size: .95rem;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 24px rgba(37,99,235,0.4);
            transition: all .2s;
        }

        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(37,99,235,0.5);
            color: #fff;
        }

        .btn-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 2rem;
            background: rgba(255,255,255,0.13);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: .5rem;
            font-size: .95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }

        .btn-cta-secondary:hover {
            background: rgba(255,255,255,0.22);
            color: #fff;
            border-color: rgba(255,255,255,.7);
            transform: translateY(-2px);
        }

        /* ─── FEATURE CARDS ─── */
        .features {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
        }
        /* Top row: 3 cards, each spans 2 of 6 columns */
        .feature-card { grid-column: span 2; }
        /* Bottom row: 2 cards centered — start at col 2 and col 4 */
        .feature-card:nth-child(4) { grid-column: 2 / 4; }
        .feature-card:nth-child(5) { grid-column: 4 / 6; }

        .feature-card {
            background: rgba(10, 25, 70, 0.55);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: .75rem;
            padding: 1.25rem 1rem;
            text-align: center;
            transition: background .2s, border-color .2s;
            backdrop-filter: blur(6px);
        }

        .feature-card:hover {
            background: rgba(10, 25, 70, 0.72);
            border-color: rgba(255,255,255,0.3);
        }

        .feature-icon {
            width: 48px; height: 48px;
            border-radius: .5rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto .75rem;
            font-size: 1.4rem;
        }

        .feature-card h6 {
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            margin-bottom: .35rem;
            text-shadow: 0 1px 4px rgba(0,0,0,.5);
        }

        .feature-card p {
            color: #bfdbfe;
            font-size: .8rem;
            line-height: 1.45;
            margin: 0;
        }

        /* ─── FOOTER ─── */
        .site-footer {
            text-align: center;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-shadow: 0 1px 4px rgba(0,0,0,.5);
        }

        /* ─── DIVIDER LINE ─── */
        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #1e4fb5, #60a5fa);
            border-radius: 2px;
            margin: 0 auto 2rem;
        }

        /* ─── MOBILE ─── */
        @media (max-width: 767px) {
            /* Nav */
            .top-nav { padding: .6rem .9rem; }
            .nav-brand img { width: 36px !important; height: 36px !important; margin: 0 !important; }
            .nav-brand { gap: .5rem; }
            .nav-brand-text { font-size: .82rem; }
            .btn-nav-about,
            .btn-nav-login,
            .btn-nav-dashboard { padding: .3rem .65rem; font-size: .78rem; }

            /* Hero — tight, centered, no wasted space */
            .hero {
                padding: 4.5rem .9rem 1.5rem;
                justify-content: flex-start;
            }

            /* PNP logo — smaller */
            .badge-shield { width: 100px; height: 100px; margin-bottom: 0; }
            .badge-shield img {
                width: 120px !important;
                height: 120px !important;
                margin: -10px !important;
            }

            /* Tag — shorten text via CSS, wrap neatly */
            .hero-tag {
                font-size: .66rem;
                padding: .28rem .65rem;
                margin-bottom: .9rem;
                letter-spacing: .04em;
            }

            /* Title */
            .hero-title {
                font-size: clamp(1.55rem, 7vw, 2rem);
                margin-bottom: .5rem;
            }

            /* Subtitle — tighten */
            .hero-subtitle {
                font-size: .82rem;
                line-height: 1.55;
                margin-bottom: 1.25rem;
                max-width: 100%;
            }

            /* Divider */
            .divider { margin: 0 auto 1.1rem; }

            /* CTA buttons — full width, stacked */
            .cta-group {
                flex-direction: column;
                align-items: stretch;
                gap: .6rem;
                margin-bottom: 1.5rem;
                width: 100%;
            }
            .btn-cta-primary,
            .btn-cta-secondary {
                width: 100%;
                justify-content: center;
                padding: .65rem 1rem;
                font-size: .88rem;
            }

            /* Feature cards — compact horizontal rows (icon + title, no description) */
            .features {
                grid-template-columns: 1fr 1fr;
                max-width: 100%;
                gap: .5rem;
            }
            .feature-card { grid-column: span 1 !important; cursor: pointer; }
            .feature-card {
                padding: .6rem .65rem;
                display: flex;
                flex-direction: row;
                align-items: center;
                text-align: left;
                gap: .55rem;
            }
            .feature-icon {
                width: 34px; height: 34px;
                font-size: 1rem;
                margin: 0;
                flex-shrink: 0;
            }
            .feature-card h6 { font-size: .75rem; margin: 0; }
            .feature-card p { display: none; }

            /* Footer */
            .site-footer { padding: 1rem; font-size: .7rem; }
        }

        @media (max-width: 400px) {
            .features { grid-template-columns: 1fr; }
            .hero-tag { font-size: .62rem; }
        }
    </style>
</head>
<body>

<div class="bg-hero" id="bgHero"></div>
<div class="bg-overlay"></div>

<div class="page-wrapper">

    {{-- TOP NAVIGATION --}}
    <nav class="top-nav">
        <a href="{{ url('/') }}" class="nav-brand">
            <img src="{{ asset('images/Balamban.png') }}" alt="PNP Logo"
                 style="width:60px;height:60px;object-fit:contain;flex-shrink:0;margin:-8px 0;">
            <div class="nav-brand-text">
                <span class="d-none d-sm-block">Traffic Violation Incident Record System</span>
                <span class="d-sm-none">TVIRS</span>
            </div>
        </a>
        <div class="nav-actions">
            <button class="btn-nav-about" data-bs-toggle="modal" data-bs-target="#aboutModal">
                <i class="bi bi-info-circle me-1"></i> About
            </button>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-nav-dashboard">
                    <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
                </a>
            @else
                <button class="btn-nav-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                </button>
            @endauth
        </div>
    </nav>

    {{-- HERO --}}
    <section class="hero">

        {{-- PNP Logo --}}
        <div class="badge-shield">
            <img src="{{ asset('images/PNP.png') }}" alt="PNP Logo"
                 style="width:240px;height:240px;object-fit:contain;margin:-20px;filter:drop-shadow(0 0 28px rgba(96,165,250,0.7));">
        </div>

        <div class="hero-tag">
            <i class="bi bi-shield-lock-fill"></i>
            <span class="d-none d-sm-inline">Balamban Municipal Police Station — Official Records System</span>
            <span class="d-sm-none">Balamban MPS · Official Records</span>
        </div>

        <h1 class="hero-title">
            Traffic Violation Incident<br>
            <span>Record System</span>
        </h1>

        <p class="hero-subtitle">
            A centralized digital platform for recording and managing traffic violations,
            road incidents, and motorist profiles — empowering the Balamban Municipal Police Station
            with accurate, searchable, and auditable enforcement records.
        </p>

        <div class="divider"></div>

        <div class="cta-group">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-cta-primary">
                    <i class="bi bi-speedometer2"></i>
                    Go to Dashboard
                </a>
            @else
                <button class="btn-cta-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Log In to System
                </button>
                <a href="#features" class="btn-cta-secondary">
                    <i class="bi bi-info-circle"></i>
                    Learn More
                </a>
            @endauth
        </div>

        {{-- FEATURE CARDS --}}
        <div class="features" id="features">
            <div class="feature-card" data-feature="motorist" role="button" tabindex="0">
                <div class="feature-icon" style="background:rgba(59,130,246,0.15);">
                    <i class="bi bi-person-lines-fill" style="color:#60a5fa;"></i>
                </div>
                <h6>Motorist Profiling</h6>
                <p>Maintain complete motorist records — personal details, license information, and full violation and incident history in one profile.</p>
            </div>
            <div class="feature-card" data-feature="violation" role="button" tabindex="0">
                <div class="feature-icon" style="background:rgba(239,68,68,0.15);">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#f87171;"></i>
                </div>
                <h6>Violation Management</h6>
                <p>Record and track traffic citations with real-time status monitoring — pending, settled, contested, or overdue.</p>
            </div>
            <div class="feature-card" data-feature="incident" role="button" tabindex="0">
                <div class="feature-icon" style="background:rgba(251,146,60,0.15);">
                    <i class="bi bi-flag-fill" style="color:#fb923c;"></i>
                </div>
                <h6>Incident Recording</h6>
                <p>Document road incidents with involved motorists, vehicles, charges, and photo evidence — from open to closed.</p>
            </div>
            <div class="feature-card" data-feature="vehicle" role="button" tabindex="0">
                <div class="feature-icon" style="background:rgba(234,179,8,0.15);">
                    <i class="bi bi-car-front-fill" style="color:#fbbf24;"></i>
                </div>
                <h6>Vehicle Records</h6>
                <p>Register motor vehicles and motorcycles, link them to their owners, and track involvement across violations and incidents.</p>
            </div>
            <div class="feature-card" data-feature="reports" role="button" tabindex="0">
                <div class="feature-icon" style="background:rgba(16,185,129,0.15);">
                    <i class="bi bi-bar-chart-fill" style="color:#34d399;"></i>
                </div>
                <h6>Reports &amp; Analytics</h6>
                <p>Generate enforcement summaries, identify repeat offenders, and view statistics by violation type, period, and status.</p>
            </div>
        </div>

        @guest
        <div class="mt-4" style="font-size:.82rem; color:#bfdbfe; text-shadow:0 1px 5px rgba(0,0,0,.7); font-weight:500;">
            <i class="bi bi-lock-fill me-1"></i>
            Restricted to authorized Balamban MPS personnel only. Contact your administrator to request access.
        </div>
        @endguest

    </section>

    {{-- FOOTER --}}
    <footer class="site-footer">
        &copy; {{ date('Y') }} Traffic Violation Incident Record System. All rights reserved.
    </footer>

</div>

{{-- ABOUT MODAL --}}
<style>
/* ── Modal shell ── */
.about-modal .modal-content {
    border: 0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(0,0,0,.4);
}

/* ── Hero banner ── */
.about-hero {
    background: linear-gradient(160deg, #04111f 0%, #0b2255 55%, #163998 100%);
    padding: 2.25rem 2rem 1.75rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.about-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 50% -10%, rgba(96,165,250,.22) 0%, transparent 65%);
    pointer-events: none;
}
.about-hero-logos {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
    margin-bottom: 1rem;
}
.about-hero-logo-wrap {
    width: 68px; height: 68px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.about-hero-logo-wrap img {
    width: 68px; height: 68px;
    object-fit: contain;
    filter: drop-shadow(0 2px 10px rgba(0,0,0,.45));
}
.about-hero-logo-wrap:first-child img { transform: scale(1.32); }
.about-hero-sep {
    width: 1px; height: 44px;
    background: linear-gradient(to bottom, transparent, rgba(255,255,255,.3), transparent);
    flex-shrink: 0;
}
.about-hero-title {
    font-size: 1rem; font-weight: 800; color: #fff;
    letter-spacing: .015em; line-height: 1.35; margin-bottom: .25rem;
}
.about-hero-sub {
    font-size: .7rem; font-weight: 500;
    color: #93c5fd; letter-spacing: .07em; text-transform: uppercase;
    line-height: 1.6;
}
.about-hero-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    margin-top: .75rem; padding: .2rem .7rem;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
    border-radius: 9999px; font-size: .67rem; color: #bfdbfe;
}

/* ── Body wrapper ── */
.about-body { background: #fff; }

/* ── Tabs ── */
.about-tabs {
    display: flex;
    background: #f8f9fb;
    border-bottom: 1.5px solid #e8ecf2;
}
.about-tab {
    flex: 1; padding: .65rem 1rem;
    font-size: .78rem; font-weight: 600; color: #64748b;
    border: 0; background: transparent; cursor: pointer;
    border-bottom: 2.5px solid transparent; margin-bottom: -1.5px;
    transition: color .15s, border-color .15s, background .15s;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
}
.about-tab:hover { color: #1e293b; background: #f1f5fb; }
.about-tab.active { color: #1d4ed8; border-bottom-color: #1d4ed8; background: #fff; }

/* ── Tab panels ── */
.about-panel { display: none; padding: 1.5rem 1.75rem; }
.about-panel.active { display: block; }

/* ── System panel ── */
.about-intro {
    font-size: .82rem; color: #374151; line-height: 1.78;
    padding: .8rem 1rem;
    background: linear-gradient(135deg, #f0f7ff, #e8f0fe);
    border: 1px solid #c7d9f8; border-radius: 11px;
    margin-bottom: 1.1rem;
}
.about-section-label {
    font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: #94a3b8; margin-bottom: .55rem;
    display: flex; align-items: center; gap: .4rem;
}
.about-section-label::after {
    content: ''; flex: 1; height: 1px; background: #e8ecf2;
}

/* Capability chips — 3 col, compact */
.about-caps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: .5rem;
    margin-bottom: 1.1rem;
}
.about-cap-chip {
    display: flex; align-items: center; gap: .55rem;
    padding: .6rem .75rem;
    background: #fff; border: 1px solid #e5e9f0; border-radius: 10px;
    transition: border-color .15s, background .15s, box-shadow .15s;
    cursor: default;
}
.about-cap-chip:hover {
    background: #f6f9ff; border-color: #c7d9f8;
    box-shadow: 0 2px 8px rgba(37,99,235,.07);
}
.about-cap-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem;
}
.about-cap-name {
    font-size: .75rem; font-weight: 600; color: #1e293b; line-height: 1.25;
}

/* Unit/platform row */
.about-platform-row {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem;
}
.about-platform-cell {
    padding: .65rem .75rem; border-radius: 10px;
    background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;
}
.about-platform-icon { font-size: .9rem; margin-bottom: .25rem; display: block; }
.about-platform-label {
    font-size: .59rem; text-transform: uppercase; letter-spacing: .08em;
    color: #94a3b8; font-weight: 700; margin-bottom: .15rem;
}
.about-platform-value { font-size: .78rem; font-weight: 700; color: #1e293b; }

/* ── Developer panel ── */
.dev-lead-card {
    background: linear-gradient(160deg, #0f2460, #1e40af);
    border-radius: 14px; margin-bottom: .85rem;
    position: relative; overflow: hidden;
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.1rem;
}
.dev-lead-card::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 100% 50%, rgba(96,165,250,.18) 0%, transparent 60%);
    pointer-events: none;
}
.dev-lead-photo-wrap { flex-shrink: 0; }
.dev-lead-photo {
    width: 90px; height: 100px;
    border-radius: 14px;
    object-fit: cover; object-position: center 15%;
    border: 2.5px solid rgba(255,255,255,.28);
    box-shadow: 0 6px 20px rgba(0,0,0,.35);
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    display: block;
}
.dev-lead-photo:hover { transform: scale(1.03); box-shadow: 0 10px 28px rgba(0,0,0,.5); }
.dev-lead-info { flex: 1; min-width: 0; }
.dev-lead-name { font-size: 1rem; font-weight: 800; color: #fff; line-height: 1.2; }
.dev-lead-role {
    font-size: .66rem; font-weight: 700; color: #93c5fd;
    text-transform: uppercase; letter-spacing: .09em; margin: .2rem 0 .4rem;
}
.dev-lead-desc { font-size: .76rem; color: rgba(255,255,255,.82); line-height: 1.6; margin: 0; }
.dev-stack { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .55rem; }
.dev-tag {
    font-size: .63rem; font-weight: 600; padding: .15rem .45rem; border-radius: 5px;
    background: rgba(255,255,255,.12); color: #bfdbfe; border: 1px solid rgba(255,255,255,.2);
}

/* Contributors grid */
.dev-contrib-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: .6rem;
    margin-bottom: .85rem;
}
.dev-contrib-card {
    display: flex; align-items: flex-start; gap: .7rem;
    padding: .75rem .85rem;
    background: #fff; border: 1px solid #e5e9f0; border-radius: 11px;
    transition: border-color .15s, box-shadow .15s;
}
.dev-contrib-card:hover { border-color: #c7d9f8; box-shadow: 0 2px 10px rgba(37,99,235,.08); }
.dev-contrib-photo {
    width: 60px; height: 68px; flex-shrink: 0;
    border-radius: 10px;
    object-fit: cover; object-position: center 15%;
    border: 1.5px solid #e5e9f0;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
    display: block;
}
.dev-contrib-photo:hover { transform: scale(1.05); box-shadow: 0 3px 12px rgba(0,0,0,.15); }
.dev-contrib-name { font-size: .8rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin-bottom: .12rem; }
.dev-contrib-role {
    font-size: .62rem; font-weight: 700; color: #3b82f6;
    text-transform: uppercase; letter-spacing: .07em; margin-bottom: .28rem;
}
.dev-contrib-desc { font-size: .7rem; color: #64748b; line-height: 1.5; margin: 0; }

.dev-quote {
    display: flex; gap: .6rem; align-items: flex-start;
    padding: .75rem .9rem;
    background: #f8fafc; border: 1px solid #e8ecf2; border-radius: 10px;
    font-size: .76rem; color: #64748b; line-height: 1.75;
}
.dev-quote i { font-size: 1.1rem; color: #c7d9f8; flex-shrink: 0; margin-top: .1rem; }

/* ── Photo lightbox ── */
.photo-lightbox {
    display: none;
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.85);
    align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    cursor: zoom-out;
    animation: lbFadeIn .18s ease;
}
.photo-lightbox.open { display: flex; }
@keyframes lbFadeIn { from { opacity:0; } to { opacity:1; } }
.photo-lightbox img {
    max-width: min(420px, 90vw);
    max-height: 88vh;
    border-radius: 16px;
    box-shadow: 0 24px 80px rgba(0,0,0,.65);
    object-fit: contain;
    cursor: default;
    animation: lbScaleIn .2s ease;
}
@keyframes lbScaleIn { from { transform:scale(.88); opacity:0; } to { transform:scale(1); opacity:1; } }
.photo-lightbox-close {
    position: absolute; top: 1rem; right: 1.25rem;
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    color: #fff; font-size: 1.1rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.photo-lightbox-close:hover { background: rgba(255,255,255,.28); }
.photo-lightbox-name {
    position: absolute; bottom: 1.25rem; left: 50%; transform: translateX(-50%);
    font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.85);
    background: rgba(0,0,0,.45); padding: .3rem .9rem; border-radius: 9999px;
    white-space: nowrap;
}

/* ── Footer strip ── */
.about-footer-strip {
    background: #f8fafc; border-top: 1px solid #eef2f8;
    padding: .6rem 1.75rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .4rem;
}
.about-footer-strip span { font-size: .7rem; color: #94a3b8; }
.about-footer-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .67rem; font-weight: 600; padding: .17rem .52rem;
    background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; border-radius: 6px;
}

/* ── Mobile ── */
@media (max-width: 575.98px) {
    .about-modal .modal-content { border-radius: 16px; }
    .about-modal .modal-dialog { margin: .75rem; }

    /* Hero — tighter */
    .about-hero { padding: 1.75rem 1.25rem 1.4rem; }
    .about-hero-logo-wrap { width: 54px; height: 54px; }
    .about-hero-logo-wrap img { width: 54px; height: 54px; }
    .about-hero-logo-wrap:first-child img { transform: scale(1.28); }
    .about-hero-sep { height: 36px; }
    .about-hero-logos { gap: 1rem; margin-bottom: .8rem; }
    .about-hero-title { font-size: .92rem; }
    .about-hero-sub {
        font-size: .62rem; letter-spacing: .04em;
        /* wrap "Cebu CPO · PRO-7" gracefully */
        line-height: 1.7;
    }
    .about-hero-badge { font-size: .62rem; margin-top: .6rem; padding: .18rem .6rem; }

    /* Tabs */
    .about-tab { font-size: .73rem; padding: .55rem .5rem; gap: .3rem; }

    /* Panels */
    .about-panel { padding: 1.1rem 1rem 1rem; }
    .about-intro { font-size: .79rem; line-height: 1.75; padding: .75rem .85rem; }
    .about-section-label { font-size: .63rem; }

    /* Intro */
    .about-intro { font-size: .77rem; padding: .7rem .85rem; }

    /* Caps — 2 col on mobile */
    .about-caps-grid { grid-template-columns: 1fr 1fr; gap: .4rem; margin-bottom: .9rem; }
    .about-cap-chip { padding: .52rem .6rem; gap: .45rem; }
    .about-cap-icon { width: 26px; height: 26px; font-size: .74rem; border-radius: 7px; }
    .about-cap-name { font-size: .7rem; }

    /* Platform row */
    .about-platform-row { gap: .4rem; }
    .about-platform-cell { padding: .55rem .4rem; }
    .about-platform-icon { font-size: .82rem; }
    .about-platform-label { font-size: .56rem; }
    .about-platform-value { font-size: .73rem; }

    /* Dev lead card */
    .dev-lead-card { gap: .75rem; padding: .85rem .9rem; }
    .dev-lead-photo { width: 76px; height: 86px; border-radius: 12px; }
    .dev-lead-name { font-size: .92rem; }
    .dev-lead-desc { font-size: .72rem; }

    /* Contributors — stack to 1 col on very small */
    .dev-contrib-grid { grid-template-columns: 1fr 1fr; gap: .45rem; }
    .dev-contrib-card { padding: .65rem .7rem; gap: .55rem; }
    .dev-contrib-photo { width: 50px; height: 58px; border-radius: 9px; }
    .dev-contrib-name { font-size: .75rem; }
    .dev-contrib-desc { font-size: .66rem; }

    .dev-quote { font-size: .72rem; padding: .65rem .8rem; }

    /* Footer strip — stack vertically */
    .about-footer-strip { flex-direction: column; align-items: flex-start; padding: .55rem 1rem; gap: .3rem; }
}
</style>

<div class="modal fade about-modal" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            {{-- Hero Banner --}}
            <div class="about-hero">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="about-hero-logos">
                    <div class="about-hero-logo-wrap">
                        <img src="{{ asset('images/PNP.png') }}" alt="PNP Logo">
                    </div>
                    <div class="about-hero-sep"></div>
                    <div class="about-hero-logo-wrap">
                        <img src="{{ asset('images/Balamban.png') }}" alt="Balamban Seal">
                    </div>
                </div>
                <div class="about-hero-title">Traffic Violation Incident Record System</div>
                <div class="about-hero-sub">
                    Balamban Municipal Police Station &nbsp;·&nbsp; Cebu Police Provincial Office &nbsp;·&nbsp; PRO-7
                </div>
                <div class="about-hero-badge">
                    <i class="bi bi-patch-check-fill" style="color:#60a5fa;"></i>
                    Official Police Records Platform &nbsp;·&nbsp; v2.0
                </div>
            </div>

            {{-- Tabs --}}
            <div class="about-body">
                <div class="about-tabs" id="aboutTabs">
                    <button class="about-tab active" data-panel="system">
                        <i class="bi bi-info-circle-fill"></i>
                        About the System
                    </button>
                    <button class="about-tab" data-panel="developer">
                        <i class="bi bi-code-slash"></i>
                        Developer
                    </button>
                </div>

                {{-- System Panel --}}
                <div class="about-panel active" id="about-panel-system">

                    <p class="about-intro mb-0">
                        The <strong>Traffic Violation Incident Record System (TVIRS)</strong> is a secure,
                        web-based platform developed for the <strong>Balamban Municipal Police Station</strong> —
                        digitizing traffic violations and road incidents, replacing manual logbooks with a
                        reliable, searchable, and auditable digital system.
                    </p>

                    <div class="about-section-label mt-3">System Capabilities</div>
                    <div class="about-caps-grid">
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(59,130,246,.12);">
                                <i class="bi bi-person-lines-fill" style="color:#3b82f6;"></i>
                            </div>
                            <span class="about-cap-name">Motorist Profiling</span>
                        </div>
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(239,68,68,.12);">
                                <i class="bi bi-exclamation-triangle-fill" style="color:#ef4444;"></i>
                            </div>
                            <span class="about-cap-name">Violation Management</span>
                        </div>
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(245,158,11,.12);">
                                <i class="bi bi-flag-fill" style="color:#f59e0b;"></i>
                            </div>
                            <span class="about-cap-name">Incident Recording</span>
                        </div>
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(202,138,4,.12);">
                                <i class="bi bi-car-front-fill" style="color:#ca8a04;"></i>
                            </div>
                            <span class="about-cap-name">Vehicle Records</span>
                        </div>
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(16,185,129,.12);">
                                <i class="bi bi-bar-chart-fill" style="color:#10b981;"></i>
                            </div>
                            <span class="about-cap-name">Reports &amp; Analytics</span>
                        </div>
                        <div class="about-cap-chip">
                            <div class="about-cap-icon" style="background:rgba(139,92,246,.12);">
                                <i class="bi bi-shield-lock-fill" style="color:#8b5cf6;"></i>
                            </div>
                            <span class="about-cap-name">Role-Based Access</span>
                        </div>
                    </div>

                    <div class="about-section-label">Unit &amp; Platform</div>
                    <div class="about-platform-row">
                        <div class="about-platform-cell">
                            <span class="about-platform-icon" style="color:#3b82f6;"><i class="bi bi-building"></i></span>
                            <div class="about-platform-label">Unit</div>
                            <div class="about-platform-value">Balamban MPS</div>
                        </div>
                        <div class="about-platform-cell">
                            <span class="about-platform-icon" style="color:#f59e0b;"><i class="bi bi-geo-alt-fill"></i></span>
                            <div class="about-platform-label">Province</div>
                            <div class="about-platform-value">Cebu CPO</div>
                        </div>
                        <div class="about-platform-cell">
                            <span class="about-platform-icon" style="color:#10b981;"><i class="bi bi-map-fill"></i></span>
                            <div class="about-platform-label">Region</div>
                            <div class="about-platform-value">PRO-7</div>
                        </div>
                    </div>

                </div>

                {{-- Developer Panel --}}
                <div class="about-panel" id="about-panel-developer">

                    {{-- Lead developer --}}
                    <div class="dev-lead-card">
                        <div class="dev-lead-photo-wrap">
                            <img src="{{ asset('images/team-kris.jpg') }}" alt="Kris Ian Calida"
                                 class="dev-lead-photo" data-lightbox="{{ asset('images/team-kris.jpg') }}"
                                 data-lightbox-name="Kris Ian Calida">
                        </div>
                        <div class="dev-lead-info">
                            <div class="dev-lead-name">Kris Ian Calida</div>
                            <div class="dev-lead-role">Lead Developer &nbsp;·&nbsp; Full-Stack, Backend &amp; UI/UX</div>
                            <p class="dev-lead-desc">
                                Designed and built TVIRS end-to-end — crafting the UI/UX, frontend interfaces,
                                backend logic, and database architecture — delivering a complete, production-ready
                                system for the Balamban Municipal Police Station.
                            </p>
                            <div class="dev-stack">
                                @foreach(['Laravel 12','PHP 8.2','PostgreSQL','Bootstrap 5','JavaScript','Flatpickr','Appwrite','DigitalOcean'] as $tech)
                                <span class="dev-tag">{{ $tech }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Contributors --}}
                    <div class="about-section-label">Contributors</div>
                    <div class="dev-contrib-grid">
                        <div class="dev-contrib-card">
                            <img src="{{ asset('images/team-alexies.jpg') }}" alt="Alexies Marie Ricafort"
                                 class="dev-contrib-photo" style="object-position:center 25%;"
                                 data-lightbox="{{ asset('images/team-alexies.jpg') }}"
                                 data-lightbox-name="Alexies Marie Ricafort">
                            <div style="min-width:0;">
                                <div class="dev-contrib-name">Alexies Marie Ricafort</div>
                                <div class="dev-contrib-role">Frontend &amp; UI/UX</div>
                                <p class="dev-contrib-desc">Assisted with frontend layout, visual design, and feature testing across the system.</p>
                            </div>
                        </div>
                        <div class="dev-contrib-card">
                            <img src="{{ asset('images/team-mariz.jpg') }}" alt="Mariz Stela Tagalog"
                                 class="dev-contrib-photo" style="object-position:center 15%;"
                                 data-lightbox="{{ asset('images/team-mariz.jpg') }}"
                                 data-lightbox-name="Mariz Stela Tagalog">
                            <div style="min-width:0;">
                                <div class="dev-contrib-name">Mariz Stela Tagalog</div>
                                <div class="dev-contrib-role">Frontend &amp; UI/UX</div>
                                <p class="dev-contrib-desc">Contributed to interface design, user experience improvements, and additional feature support.</p>
                            </div>
                        </div>
                    </div>

                    <div class="dev-quote">
                        <i class="bi bi-quote"></i>
                        Built with a focus on accuracy, ease of use, and data integrity — ensuring that police personnel
                        can record, retrieve, and report enforcement data efficiently and reliably.
                    </div>

                </div>

                <div class="about-footer-strip">
                    <span><i class="bi bi-c-circle me-1"></i>{{ date('Y') }} Traffic Violation Incident Record System</span>
                    <span class="about-footer-badge"><i class="bi bi-lock-fill"></i> For Official Use Only</span>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Photo Lightbox --}}
<div class="photo-lightbox" id="photoLightbox" role="dialog" aria-modal="true">
    <button class="photo-lightbox-close" id="photoLightboxClose" aria-label="Close">
        <i class="bi bi-x-lg"></i>
    </button>
    <img src="" alt="" id="photoLightboxImg">
    <div class="photo-lightbox-name" id="photoLightboxName"></div>
</div>

<script>
// Photo lightbox
(function() {
    var lb = document.getElementById('photoLightbox');
    var lbImg = document.getElementById('photoLightboxImg');
    var lbName = document.getElementById('photoLightboxName');
    var lbClose = document.getElementById('photoLightboxClose');

    function openLightbox(src, name) {
        lbImg.src = src;
        lbImg.alt = name;
        lbName.textContent = name;
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-lightbox]').forEach(function(el) {
        el.addEventListener('click', function() {
            openLightbox(this.dataset.lightbox, this.dataset.lightboxName || '');
        });
    });
    lbClose.addEventListener('click', closeLightbox);
    lb.addEventListener('click', function(e) { if (e.target === lb) closeLightbox(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLightbox(); });
})();

document.querySelectorAll('.about-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.about-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.about-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('about-panel-' + this.dataset.panel).classList.add('active');
    });
});
</script>

{{-- LOGIN MODAL --}}
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true"
     data-has-errors="{{ ($errors->any() || session('error')) ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow" style="overflow:hidden;">

            {{-- Modal Header --}}
            <div class="modal-header border-0 pb-0" style="background:#fff;">
                <div class="w-100 text-center pt-3">
                    <img src="{{ asset('images/Balamban.png') }}" alt="PNP Logo"
                         style="width:80px;height:80px;object-fit:contain;margin-bottom:.5rem;">
                    <h5 class="fw-bold mb-0" style="color:#1e293b;">Traffic Violation Incident Record System</h5>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body px-4 pb-4 pt-3" style="background:#fff;">
                @if(session('error'))
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3 mb-3" style="border-radius:10px;font-size:.875rem;">
                    <i class="bi bi-clock-history flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="modal_username" class="form-label fw-semibold" style="font-size:.875rem;">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input
                                id="modal_username"
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                class="form-control @error('username') is-invalid @enderror"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your username"
                            >
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_password" class="form-label fw-semibold" style="font-size:.875rem;">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input
                                id="modal_password"
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            <button class="btn btn-outline-secondary" type="button" id="modalTogglePassword"
                                    aria-label="Show or hide password" tabindex="-1">
                                <i class="bi bi-eye" id="modalTogglePasswordIcon"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="modal_remember" name="remember">
                        <label class="form-check-label text-muted" for="modal_remember" style="font-size:.875rem;">Remember me</label>
                    </div>

                    <button type="submit" class="btn w-100 fw-semibold"
                            style="background:#1a2340;color:#fff;padding:.65rem;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </button>
                </form>

                <div class="text-center mt-3" style="font-size:.75rem;color:#94a3b8;">
                    <i class="bi bi-lock-fill me-1"></i>
                    Restricted to authorized police personnel only.
                </div>
                <div class="text-center mt-2" style="font-size:.73rem;color:#94a3b8;">
                    <i class="bi bi-info-circle me-1"></i>Forgot your password? Contact your system administrator.
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('bgHero').style.backgroundImage = "url('{{ asset('images/police-station.jpeg') }}')";

    // Show/hide password toggle in modal
    document.getElementById('modalTogglePassword').addEventListener('click', function () {
        const pw = document.getElementById('modal_password');
        const icon = document.getElementById('modalTogglePasswordIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    // Auto-open modal if there are login validation errors
    var modalEl = document.getElementById('loginModal');
    if (modalEl.dataset.hasErrors === '1') {
        new bootstrap.Modal(modalEl).show();
    }

    // Blur header on scroll
    var nav = document.querySelector('.top-nav');
    window.addEventListener('scroll', function () {
        if (window.scrollY > 10) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    // Feature card modals
    const featureData = {
        motorist: {
            icon: 'bi-person-lines-fill',
            color: '#60a5fa',
            bg: 'rgba(59,130,246,0.12)',
            title: 'Motorist Profiling',
            desc: 'Maintain a complete digital profile for every motorist — personal details, driver\'s license information, vehicle links, and a full history of violations and incidents all in one place.',
            bullets: [
                'Personal & license information',
                'Profile photo & valid ID capture',
                'Linked violation & incident history',
                'Printable motorist record',
            ]
        },
        violation: {
            icon: 'bi-exclamation-triangle-fill',
            color: '#f87171',
            bg: 'rgba(239,68,68,0.12)',
            title: 'Violation Management',
            desc: 'Record and track every traffic citation issued by officers. Monitor payment status in real time — pending, settled, contested, or overdue — and attach citation ticket photos as evidence.',
            bullets: [
                'Issue citations with ticket numbers',
                'Attach citation & receipt photos',
                'Track status: pending, settled, contested',
                'Auto-flag overdue violations after 72 hrs',
            ]
        },
        incident: {
            icon: 'bi-flag-fill',
            color: '#fb923c',
            bg: 'rgba(251,146,60,0.12)',
            title: 'Incident Recording',
            desc: 'Document road incidents with full detail — all involved motorists and vehicles, applicable charges, photo and media evidence, and case status from open through to closed.',
            bullets: [
                'Multi-motorist incident documentation',
                'Charge type selection (RPC Art. 365)',
                'Photo & media evidence upload',
                'Open / under investigation / closed status',
            ]
        },
        vehicle: {
            icon: 'bi-car-front-fill',
            color: '#fbbf24',
            bg: 'rgba(234,179,8,0.12)',
            title: 'Vehicle Records',
            desc: 'Register and manage motor vehicles and motorcycles, link them to registered owners, and track their involvement across all recorded violations and incidents.',
            bullets: [
                'OR/CR & chassis number tracking',
                'Vehicle photo gallery',
                'Linked to owner motorist profile',
                'Violation & incident cross-reference',
            ]
        },
        reports: {
            icon: 'bi-bar-chart-fill',
            color: '#34d399',
            bg: 'rgba(16,185,129,0.12)',
            title: 'Reports & Analytics',
            desc: 'Generate enforcement summaries and statistical reports. Identify repeat offenders, analyze violation trends by type and period, and export data for command-level review.',
            bullets: [
                'Violation summary by type & date',
                'Repeat offender identification',
                'Settlement & collection tracking',
                'Exportable enforcement reports',
            ]
        }
    };

    document.querySelectorAll('.feature-card[data-feature]').forEach(function(card) {
        card.addEventListener('click', function() {
            const key = this.dataset.feature;
            const f = featureData[key];
            if (!f) return;

            document.getElementById('fm-icon').className = 'bi ' + f.icon;
            document.getElementById('fm-icon').style.color = f.color;
            document.getElementById('fm-icon-wrap').style.background = f.bg;
            document.getElementById('fm-title').textContent = f.title;
            document.getElementById('fm-desc').textContent = f.desc;

            const ul = document.getElementById('fm-bullets');
            ul.innerHTML = '';
            f.bullets.forEach(function(b) {
                const li = document.createElement('li');
                li.textContent = b;
                ul.appendChild(li);
            });

            new bootstrap.Modal(document.getElementById('featureModal')).show();
        });

        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
        });
    });
</script>

{{-- FEATURE DETAIL MODAL --}}
<div class="modal fade" id="featureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px;margin:1rem auto;">
        <div class="modal-content border-0 rounded-4 shadow" style="overflow:hidden;">
            <div style="padding:1.5rem 1.5rem 1rem;display:flex;align-items:flex-start;gap:1rem;">
                <div id="fm-icon-wrap" style="width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i id="fm-icon" style="font-size:1.35rem;"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <h5 id="fm-title" style="font-size:1rem;font-weight:800;color:#0f172a;margin:0 0 .15rem;"></h5>
                    <span style="font-size:.68rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;">System Feature</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="margin-top:-.15rem;"></button>
            </div>
            <div style="padding:0 1.5rem 1.5rem;">
                <p id="fm-desc" style="font-size:.84rem;color:#475569;line-height:1.7;margin-bottom:1rem;"></p>
                <ul id="fm-bullets" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.45rem;"></ul>
            </div>
        </div>
    </div>
</div>
<style>
#fm-bullets li {
    display: flex;
    align-items: center;
    gap: .55rem;
    font-size: .81rem;
    font-weight: 600;
    color: #334155;
    padding: .4rem .65rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
#fm-bullets li::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #3b82f6;
    flex-shrink: 0;
}
</style>
<script>
window.addEventListener('pageshow', function (e) {
    if (e.persisted) { window.location.reload(); }
});
</script>
</body>
</html>
