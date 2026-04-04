<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Traffic Violation Incident Record System</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/Balamban.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Balamban.png') }}">

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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 860px;
            width: 100%;
            margin: 0 auto;
        }

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
                Traffic Violation Incident Record System
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
            Official Police Records System
        </div>

        <h1 class="hero-title">
            Traffic Violation Incident<br>
            <span>Record System</span>
        </h1>

        <p class="hero-subtitle">
            A secure digital profiling system for recording and tracking traffic violations,
            monitoring repeat offenders, and generating monthly enforcement reports.
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
            <div class="feature-card">
                <div class="feature-icon" style="background:rgba(59,130,246,0.15);">
                    <i class="bi bi-person-lines-fill" style="color:#60a5fa;"></i>
                </div>
                <h6>Violator Profiling</h6>
                <p>Record complete personal information and track each individual's violation history.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:rgba(239,68,68,0.15);">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#f87171;"></i>
                </div>
                <h6>Violation Tracking</h6>
                <p>Record and monitor traffic violations with status updates — pending, settled, or contested.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:rgba(234,179,8,0.15);">
                    <i class="bi bi-car-front-fill" style="color:#fbbf24;"></i>
                </div>
                <h6>Vehicle Records</h6>
                <p>Link MV/MC details to violator profiles and search by plate number instantly.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:rgba(16,185,129,0.15);">
                    <i class="bi bi-bar-chart-fill" style="color:#34d399;"></i>
                </div>
                <h6>Reports &amp; Analytics</h6>
                <p>Generate monthly reports, identify repeat offenders, and view violation statistics.</p>
            </div>
        </div>

        @guest
        <div class="mt-4" style="font-size:.82rem; color:#bfdbfe; text-shadow:0 1px 5px rgba(0,0,0,.7); font-weight:500;">
            <i class="bi bi-lock-fill me-1"></i>
            Access is restricted to authorized police personnel only.
            Contact your administrator to request an account.
        </div>
        @endguest

    </section>

    {{-- FOOTER --}}
    <footer class="site-footer">
        &copy; {{ date('Y') }} Traffic Violation Incident Record System. All rights reserved.
    </footer>

</div>

{{-- ABOUT MODAL --}}
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow" style="overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:#0a1628;">
                <div class="w-100 text-center pt-3">
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                        <img src="{{ asset('images/PNP.png') }}" alt="PNP" style="width:54px;height:54px;object-fit:contain;">
                        <img src="{{ asset('images/Balamban.png') }}" alt="Balamban" style="width:54px;height:54px;object-fit:contain;">
                    </div>
                    <h5 class="fw-bold mb-0" style="color:#fff;font-size:1rem;">Traffic Violation Incident Record System</h5>
                    <div style="font-size:.75rem;color:#93c5fd;margin-top:.2rem;">Balamban Municipal Police Station</div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="background:#fff;">

                {{-- About the System --}}
                <div class="px-4 pt-4 pb-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="width:28px;height:28px;border-radius:8px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-info-circle-fill" style="color:#1d4ed8;font-size:.85rem;"></i>
                        </span>
                        <h6 class="mb-0 fw-700" style="color:#1c1917;font-size:.9rem;">About the System</h6>
                    </div>
                    <p style="font-size:.85rem;color:#44403c;line-height:1.7;margin:0;">
                        The <strong>Traffic Violation Incident Record System (TVIRS)</strong> is a secure, web-based records management
                        platform developed for the <strong>Balamban Municipal Police Station</strong>, Cebu Police Provincial Office,
                        Philippine National Police — Police Regional Office 7.
                    </p>
                    <p style="font-size:.85rem;color:#44403c;line-height:1.7;margin:.75rem 0 0;">
                        The system enables authorized police personnel to digitally record, manage, and track traffic violations
                        and road incidents — including violator profiling, vehicle records, incident documentation, and enforcement reports.
                        It replaces manual logbooks with a centralized, searchable, and auditable digital system.
                    </p>

                    <div class="row g-2 mt-3">
                        @foreach([
                            ['bi-person-lines-fill','#3b82f6','Violator Profiling'],
                            ['bi-exclamation-triangle-fill','#ef4444','Violation Tracking'],
                            ['bi-flag-fill','#f59e0b','Incident Records'],
                            ['bi-car-front-fill','#eab308','Vehicle Records'],
                            ['bi-bar-chart-fill','#10b981','Reports & Analytics'],
                            ['bi-shield-lock-fill','#8b5cf6','Role-Based Access'],
                        ] as [$icon,$color,$label])
                        <div class="col-6 col-md-4">
                            <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#57534e;background:#fafaf9;border:1px solid #e7e5e4;border-radius:8px;padding:.4rem .6rem;">
                                <i class="bi {{ $icon }}" style="color:{{ $color }};font-size:.8rem;flex-shrink:0;"></i>
                                {{ $label }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <hr class="mx-4 my-0" style="border-color:#f0ece8;">

                {{-- About the Developer --}}
                <div class="px-4 pt-3 pb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="width:28px;height:28px;border-radius:8px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-code-slash" style="color:#15803d;font-size:.85rem;"></i>
                        </span>
                        <h6 class="mb-0 fw-700" style="color:#1c1917;font-size:.9rem;">About the Developer</h6>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#0284c7,#0369a1);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.3rem;color:#fff;font-weight:800;">
                            K
                        </div>
                        <div>
                            <div style="font-weight:700;color:#1c1917;font-size:.92rem;">Kristian</div>
                            <div style="font-size:.78rem;color:#78716c;margin-top:1px;">System Developer</div>
                            <p style="font-size:.8rem;color:#57534e;line-height:1.65;margin:.5rem 0 0;">
                                Developed as part of a project for the Balamban Municipal Police Station to
                                modernize and digitize traffic enforcement record-keeping.
                            </p>
                        </div>
                    </div>
                </div>

                <div style="background:#fafaf9;border-top:1px solid #f0ece8;padding:.75rem 1.5rem;text-align:center;">
                    <span style="font-size:.73rem;color:#a8a29e;">
                        <i class="bi bi-c-circle me-1"></i>{{ date('Y') }} Traffic Violation Incident Record System &mdash; For official use only.
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- LOGIN MODAL --}}
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true"
     data-has-errors="{{ $errors->any() ? '1' : '0' }}">
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
</script>
</body>
</html>
