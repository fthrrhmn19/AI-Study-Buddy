<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - AI Study Buddy</title>
    <meta name="description" content="AI Study Buddy membantu mahasiswa memahami materi kuliah dengan ringkasan AI, quiz otomatis, dan chat dokumen. Dibuat dengan Laravel, MongoDB, dan Groq API.">
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('aiStudyBuddyTheme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.dataset.theme = savedTheme || (prefersDark ? 'dark' : 'light');
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
    @php
        $appIconPath = public_path('assets/images/app-icon.png');
        $logoPath = public_path('assets/images/logo-ai-study-buddy.png');
        $appIconUrl = asset('assets/images/app-icon.png').(is_file($appIconPath) ? '?v='.filemtime($appIconPath) : '');
        $logoUrl = asset('assets/images/logo-ai-study-buddy.png').(is_file($logoPath) ? '?v='.filemtime($logoPath) : '');
    @endphp
    <link rel="icon" type="image/png" href="{{ $appIconUrl }}">
    <link rel="apple-touch-icon" href="{{ $appIconUrl }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --accent: #06b6d4;
            --accent-light: #22d3ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --surface-glass: rgba(255,255,255,.72);
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #312e81 70%, #4f46e5 100%);
            --gradient-card: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
            --shadow-sm: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
            --shadow-md: 0 4px 16px rgba(15,23,42,.08), 0 2px 4px rgba(15,23,42,.04);
            --shadow-lg: 0 12px 40px rgba(15,23,42,.12);
            --shadow-glow: 0 0 30px rgba(79,70,229,.15);
            --radius: 16px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --text-muted: #475569;
        }
        :root,
        [data-theme="light"] {
            color-scheme: light;
        }
        [data-theme="dark"] {
            color-scheme: dark;
            --primary: #818cf8;
            --primary-light: #a5b4fc;
            --primary-dark: #6366f1;
            --accent: #22d3ee;
            --accent-light: #67e8f9;
            --success: #34d399;
            --warning: #fbbf24;
            --danger: #fb7185;
            --surface: #111827;
            --surface-alt: #0b1020;
            --surface-glass: rgba(17,24,39,.78);
            --text: #f8fafc;
            --text-muted: #cbd5e1;
            --border: #334155;
            --gradient-hero: linear-gradient(135deg, #020617 0%, #0f172a 42%, #1e1b4b 76%, #312e81 100%);
            --gradient-card: linear-gradient(135deg, #6366f1 0%, #7c3aed 52%, #06b6d4 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,.28), 0 1px 2px rgba(0,0,0,.22);
            --shadow-md: 0 8px 24px rgba(0,0,0,.28), 0 2px 8px rgba(0,0,0,.2);
            --shadow-lg: 0 18px 56px rgba(0,0,0,.36);
            --shadow-glow: 0 0 30px rgba(129,140,248,.18);
        }
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background: var(--surface-alt); color: var(--text); overflow-x: hidden; }
        #particles-canvas { position:fixed; top:0; left:0; width:100%; height:100%; z-index:0; pointer-events:none; opacity:.45; }
        .navbar-main, main, .footer-main { position:relative; z-index:1; }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        @keyframes slideDown { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.6; } }
        @keyframes shimmer { 0% { background-position:-200% 0; } 100% { background-position:200% 0; } }
        @keyframes float { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-6px); } }
        @keyframes scaleIn { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
        .animate-fade-up { animation: fadeInUp .5s ease both; }
        .animate-fade-in { animation: fadeIn .6s ease both; }
        .animate-scale-in { animation: scaleIn .4s ease both; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .stagger-1 { animation-delay: .05s; }
        .stagger-2 { animation-delay: .1s; }
        .stagger-3 { animation-delay: .15s; }
        .stagger-4 { animation-delay: .2s; }
        .stagger-5 { animation-delay: .25s; }

        /* ===== NAVBAR ===== */
        .navbar-main {
            background: var(--surface-glass);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border);
            padding: .6rem 0;
            z-index: 1000;
        }
        .navbar-brand-custom {
            font-weight: 800;
            font-size: 1.25rem;
            background: var(--gradient-card);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -.02em;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .navbar-brand-custom .brand-icon {
            width: 36px; height: 36px;
            background: var(--gradient-card);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            -webkit-text-fill-color: white;
        }
        .nav-link-custom {
            color: var(--text-muted);
            font-weight: 500;
            font-size: .875rem;
            padding: .45rem .85rem;
            border-radius: 10px;
            transition: all .2s ease;
            text-decoration: none;
            white-space: nowrap;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            color: var(--primary);
            background: rgba(79,70,229,.08);
        }
        .nav-link-custom.nav-cta {
            background: var(--gradient-card);
            color: #fff !important;
            font-weight: 600;
        }
        .nav-link-custom.nav-cta:hover { opacity: .9; box-shadow: var(--shadow-glow); }
        .mobile-toggle {
            border: none; background: none; padding: .5rem;
            font-size: 1.5rem; color: var(--text); cursor: pointer;
            display: none;
        }
        .theme-toggle-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            color: var(--text-muted);
            font-weight: 700;
            font-size: .84rem;
            padding: .45rem .75rem;
            transition: all .2s ease;
        }
        .theme-toggle-button:hover {
            color: var(--primary);
            border-color: var(--primary-light);
            background: rgba(79,70,229,.08);
        }
        .nav-dropdown .theme-toggle-button {
            width: 100%;
            justify-content: flex-start;
            margin: .25rem 0;
            padding: .65rem 1rem;
            border-radius: 10px;
        }
        @media (max-width: 991px) {
            .mobile-toggle { display: block; }
            .nav-links-desktop { display: none !important; }
        }
        .nav-dropdown {
            display: none;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 8px 24px rgba(15,23,42,.1);
            animation: slideDown .25s ease;
        }
        .nav-dropdown.open { display: block; }
        .nav-dropdown .nav-link-custom {
            display: block;
            padding: .65rem 1rem;
            font-size: .95rem;
            border-radius: 10px;
            margin-bottom: 2px;
        }
        .nav-dropdown .nav-link-custom:hover { background: rgba(79,70,229,.06); }

        /* ===== HERO ===== */
        .hero {
            background: var(--gradient-hero);
            color: #fff;
            border-radius: var(--radius-xl);
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%; right: -30%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,.3) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -40%; left: -20%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(6,182,212,.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* ===== CARDS ===== */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            background: var(--surface);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }
        [data-theme="dark"] .card {
            background: var(--surface);
            border-color: var(--border);
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-glass {
            background: var(--surface-glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.4);
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all .3s ease;
            border: 1px solid var(--border);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 1rem;
        }
        .stat-icon.purple { background: rgba(79,70,229,.1); color: var(--primary); }
        .stat-icon.cyan { background: rgba(6,182,212,.1); color: var(--accent); }
        .stat-icon.emerald { background: rgba(16,185,129,.1); color: var(--success); }
        .stat-icon.amber { background: rgba(245,158,11,.1); color: var(--warning); }
        .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: .25rem; letter-spacing: -.03em; }
        .stat-label { color: var(--text-muted); font-size: .85rem; font-weight: 500; }

        /* ===== BADGES ===== */
        .badge-soft { background: rgba(79,70,229,.1); color: var(--primary); font-weight: 600; }
        .badge-soft-success { background: rgba(16,185,129,.1); color: var(--success); font-weight: 600; }
        .badge-soft-warning { background: rgba(245,158,11,.1); color: var(--warning); font-weight: 600; }
        .badge-soft-danger { background: rgba(239,68,68,.1); color: var(--danger); font-weight: 600; }
        .badge-soft-cyan { background: rgba(6,182,212,.1); color: var(--accent); font-weight: 600; }

        /* ===== RESULT BOX ===== */
        .result-box {
            white-space: pre-wrap;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            border-radius: var(--radius);
            padding: 1.25rem;
            min-height: 120px;
            font-size: .9rem;
            line-height: 1.7;
            border: 1px solid rgba(255,255,255,.06);
        }
        .result-box b { color: #a5b4fc; }
        .result-box ul, .result-box ol { padding-left: 1.5rem; }
        .result-box li { margin-bottom: .35rem; }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: var(--gradient-card);
            border: none;
            font-weight: 600;
            border-radius: 12px;
            padding: .6rem 1.5rem;
            transition: all .25s ease;
        }
        .btn-primary:hover { opacity: .9; transform: translateY(-1px); box-shadow: var(--shadow-glow); background: var(--gradient-card); }
        .btn-outline-primary { border-color: var(--primary); color: var(--primary); border-radius: 12px; font-weight: 500; }
        .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); }
        .btn-success { background: var(--success); border: none; border-radius: 12px; font-weight: 600; }
        .btn-success:hover { background: #059669; }

        /* ===== FORMS ===== */
        .form-control, .form-select {
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--text);
            padding: .65rem 1rem;
            font-size: .9rem;
            transition: all .2s ease;
        }
        .form-control::placeholder { color: var(--text-muted); opacity: .78; }
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background: #0f172a;
            color: var(--text);
            border-color: var(--border);
        }
        [data-theme="dark"] .form-select option {
            background: #0f172a;
            color: var(--text);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(79,70,229,.1);
        }
        textarea.form-control { min-height: 180px; line-height: 1.6; }
        .form-label { font-weight: 600; font-size: .85rem; color: var(--text); margin-bottom: .4rem; }

        /* ===== LOADING ===== */
        .loading-dots::after {
            content: ''; animation: dots 1.5s infinite;
        }
        @keyframes dots { 0% { content: ''; } 25% { content: '.'; } 50% { content: '..'; } 75% { content: '...'; } }
        .skeleton {
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
            height: 20px;
        }

        /* ===== SECTION TITLES ===== */
        .section-title {
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -.02em;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .section-title .icon-box {
            width: 38px; height: 38px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        /* ===== FOOTER ===== */
        .footer-main {
            border-top: 1px solid var(--border);
            padding: 2rem 0 1.5rem;
            margin-top: 3rem;
        }
        .footer-main .footer-text { color: var(--text-muted); font-size: .85rem; }
        .footer-main .footer-brand { font-weight: 700; color: var(--primary); }

        /* ===== MISC ===== */
        .material-item {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: .85rem 1rem;
            cursor: pointer;
            transition: all .25s ease;
            background: var(--surface);
        }
        .material-item:hover {
            border-color: var(--primary-light);
            background: rgba(79,70,229,.03);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }
        .chat-bubble {
            padding: 1rem 1.25rem;
            border-radius: 18px;
            line-height: 1.6;
            font-size: .9rem;
            max-width: 85%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .chat-bubble-user {
            background: var(--gradient-card);
            color: white;
            border-bottom-right-radius: 6px;
        }
        .chat-bubble-ai {
            background: var(--surface);
            border: 1px solid var(--border);
            border-bottom-left-radius: 6px;
        }
        .quiz-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
            background: var(--surface);
            transition: all .2s ease;
        }
        .quiz-card:hover { box-shadow: var(--shadow-sm); border-color: var(--primary-light); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero { border-radius: var(--radius-lg); }
            .stat-value { font-size: 1.5rem; }
            .section-title { font-size: 1.15rem; }
            #particles-canvas { opacity:.25; }
        }
        /* ===== WEB SOURCES ===== */
        .web-sources { margin-top:1rem; }
        .web-source-item { display:flex; align-items:flex-start; gap:.6rem; padding:.6rem .8rem; border-radius:10px; border:1px solid var(--border); background:var(--surface); margin-bottom:.4rem; transition:all .2s ease; font-size:.82rem; }
        .web-source-item:hover { border-color:var(--primary-light); background:rgba(79,70,229,.03); transform:translateX(3px); }
        .web-source-item a { color:var(--primary); font-weight:600; text-decoration:none; }
        .web-source-item a:hover { text-decoration:underline; }
        .web-source-item .desc { color:var(--text-muted); font-size:.78rem; margin-top:2px; }
        /* ===== GLOW EFFECTS ===== */
        @keyframes glowPulse { 0%,100%{box-shadow:0 0 8px rgba(79,70,229,.1);} 50%{box-shadow:0 0 20px rgba(79,70,229,.2);} }
        .card:hover { box-shadow: var(--shadow-md), 0 0 20px rgba(79,70,229,.06); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg), 0 0 24px rgba(79,70,229,.08); }
        .btn-primary:active { transform:scale(.97); }
        /* ===== NAV TABS POLISH ===== */
        .nav-tabs { border-bottom:2px solid var(--border); }
        .nav-tabs .nav-link { border:none; border-bottom:2px solid transparent; margin-bottom:-2px; color:var(--text-muted); font-weight:600; transition:all .2s; }
        .nav-tabs .nav-link.active { color:var(--primary); border-bottom-color:var(--primary); background:transparent; }
        .nav-tabs .nav-link:hover:not(.active) { color:var(--text); border-bottom-color:var(--border); }


        /* ===== PREMIUM PARTICLE / VISUAL POLISH ===== */
        .tilt-card { transform-style: preserve-3d; will-change: transform; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; }
        }
    </style>
    <link href="{{ asset('assets/css/ai-study-buddy.css') }}?v={{ filemtime(public_path('assets/css/ai-study-buddy.css')) }}" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar-main sticky-top">
    <div class="container d-flex align-items-center">
        <a class="navbar-brand-custom" href="/">
            <span class="brand-icon brand-logo-mark">
                <img src="{{ $logoUrl }}" alt="AI Study Buddy logo" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
            </span>
            AI Study Buddy
        </a>
        <div class="d-flex align-items-center ms-auto nav-links-desktop">
            <div class="nav-primary-group">
                <a href="/" class="nav-link-custom {{ request()->is('/') ? 'active' : '' }}"><i class="bi bi-house-door me-1"></i>Dashboard</a>
                <a href="{{ route('ai.summarize') }}" class="nav-link-custom {{ request()->routeIs('ai.summarize') ? 'active' : '' }}"><i class="bi bi-stars me-1"></i>Ringkasan</a>
                <a href="{{ route('ai.quiz') }}" class="nav-link-custom {{ request()->routeIs('ai.quiz') ? 'active' : '' }}"><i class="bi bi-patch-question me-1"></i>Kuis</a>
                <a href="{{ route('ai.study-plan') }}" class="nav-link-custom {{ request()->routeIs('ai.study-plan') ? 'active' : '' }}"><i class="bi bi-calendar-check me-1"></i>Rencana</a>
                <a href="{{ route('ai.history') }}" class="nav-link-custom {{ request()->routeIs('ai.history') ? 'active' : '' }}"><i class="bi bi-clock-history me-1"></i>Riwayat</a>
            </div>
            <div class="nav-utility-group">
                <a href="/docs" class="nav-utility-button nav-docs-link {{ request()->is('docs*') ? 'active' : '' }}" title="Dokumentasi">
                    <i class="bi bi-book"></i><span>Docs</span>
                </a>
                <button class="theme-toggle-button" type="button" data-theme-toggle aria-label="Ganti tema">
                    <i class="bi bi-moon-stars me-1"></i><span>Tema</span>
                </button>
                @auth
                    <a href="{{ route('profile.edit') }}" class="nav-user-pill {{ request()->routeIs('profile.edit') ? 'active' : '' }}" title="{{ auth()->user()->name }}">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="Foto profil {{ auth()->user()->name }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/avatar-user.png') }}';">
                        @else
                            <img src="{{ asset('assets/images/avatar-user.png') }}" alt="Avatar pengguna" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                        @endif
                        <span class="nav-user-name">{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="nav-icon-button nav-icon-danger nav-logout-button" type="submit" aria-label="Keluar" title="Keluar">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link-custom {{ request()->routeIs('login') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
                    <a href="{{ route('register') }}" class="nav-link-custom nav-auth-cta {{ request()->routeIs('register') ? 'active' : '' }}"><i class="bi bi-person-plus me-1"></i>Daftar</a>
                @endauth
            </div>
        </div>
        @auth
            <div class="nav-session-compact ms-auto">
                <a href="{{ route('profile.edit') }}" class="nav-icon-button {{ request()->routeIs('profile.edit') ? 'active' : '' }}" aria-label="Profil">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Foto profil {{ auth()->user()->name }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/avatar-user.png') }}';">
                    @else
                        <img src="{{ asset('assets/images/avatar-user.png') }}" alt="Avatar pengguna" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                    @endif
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-icon-button nav-icon-danger" type="submit" aria-label="Keluar">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        @endauth
        <button class="mobile-toggle ms-auto" onclick="toggleMobile()" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
    </div>
</nav>

<!-- Dropdown Nav -->
<div class="nav-dropdown" id="navDropdown">
    <div class="container py-2">
        <a href="/" class="nav-link-custom {{ request()->is('/') ? 'active' : '' }}"><i class="bi bi-house-door me-2"></i>Dashboard</a>
        <a href="{{ route('ai.summarize') }}" class="nav-link-custom {{ request()->routeIs('ai.summarize') ? 'active' : '' }}"><i class="bi bi-stars me-2"></i>Ringkasan</a>
        <a href="{{ route('ai.quiz') }}" class="nav-link-custom {{ request()->routeIs('ai.quiz') ? 'active' : '' }}"><i class="bi bi-patch-question me-2"></i>Kuis</a>
        <a href="{{ route('ai.study-plan') }}" class="nav-link-custom {{ request()->routeIs('ai.study-plan') ? 'active' : '' }}"><i class="bi bi-calendar-check me-2"></i>Rencana Belajar</a>
        <a href="{{ route('ai.history') }}" class="nav-link-custom {{ request()->routeIs('ai.history') ? 'active' : '' }}"><i class="bi bi-clock-history me-2"></i>Riwayat</a>
        <a href="/docs" class="nav-link-custom nav-cta mt-1 {{ request()->is('docs*') ? 'active' : '' }}"><i class="bi bi-book me-2"></i>Dokumentasi</a>
        <button class="theme-toggle-button" type="button" data-theme-toggle aria-label="Ganti tema">
            <i class="bi bi-moon-stars me-2"></i><span>Tema</span>
        </button>
        @auth
            <a href="{{ route('profile.edit') }}" class="nav-user-mobile {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="Foto profil {{ auth()->user()->name }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/avatar-user.png') }}';">
                @else
                    <img src="{{ asset('assets/images/avatar-user.png') }}" alt="Avatar pengguna" onerror="this.onerror=null;this.src='{{ asset('assets/icons/fallback-illustration.svg') }}';">
                @endif
                {{ auth()->user()->name }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link-custom nav-auth-button w-100 text-start" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="nav-link-custom {{ request()->routeIs('login') ? 'active' : '' }}"><i class="bi bi-box-arrow-in-right me-2"></i>Masuk</a>
            <a href="{{ route('register') }}" class="nav-link-custom nav-auth-cta mt-1 {{ request()->routeIs('register') ? 'active' : '' }}"><i class="bi bi-person-plus me-2"></i>Daftar</a>
        @endauth
    </div>
</div>

<main class="{{ request()->routeIs('home') ? 'main-full' : 'container my-4' }}">
    @yield('content')
</main>

<footer class="footer-main">
    <div class="container text-center">
        <div class="footer-text">
            <span class="footer-brand">AI Study Buddy</span> - Asisten belajar AI &copy; {{ date('Y') }} Fathur Rohman
        </div>
    </div>
</footer>

<canvas id="particles-canvas"></canvas>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('assets/js/three-scene.js') }}?v={{ filemtime(public_path('assets/js/three-scene.js')) }}"></script>
<script>
(function(){
    const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Soft particle background canvas
    const canvas = document.getElementById('particles-canvas');
    if (canvas && !prefersReduced) {
        const ctx = canvas.getContext('2d');
        let w = canvas.width = window.innerWidth;
        let h = canvas.height = window.innerHeight;
        const particles = Array.from({length: Math.min(88, Math.floor(w/18))}, () => ({
            x: Math.random()*w, y: Math.random()*h, r: Math.random()*2.2+0.8,
            vx: (Math.random()-.5)*.35, vy: (Math.random()-.5)*.35,
            a: Math.random()*.38+.12
        }));
        function resize(){ w = canvas.width = window.innerWidth; h = canvas.height = window.innerHeight; }
        window.addEventListener('resize', resize, {passive:true});
        function draw(){
            ctx.clearRect(0,0,w,h);
            particles.forEach((p,i)=>{
                p.x += p.vx; p.y += p.vy;
                if(p.x<0||p.x>w) p.vx *= -1;
                if(p.y<0||p.y>h) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
                ctx.fillStyle = `rgba(79,70,229,${p.a})`;
                ctx.fill();
                for(let j=i+1;j<particles.length;j++){
                    const q=particles[j], dx=p.x-q.x, dy=p.y-q.y, d=Math.hypot(dx,dy);
                    if(d<112){ ctx.strokeStyle=`rgba(6,182,212,${(1-d/112)*.12})`; ctx.lineWidth=1; ctx.beginPath(); ctx.moveTo(p.x,p.y); ctx.lineTo(q.x,q.y); ctx.stroke(); }
                }
            });
            requestAnimationFrame(draw);
        }
        draw();
    }

    // Lightweight interactive tilt for cards
    document.querySelectorAll('.tilt-card, .stat-card, .feature-proof-card, .premium-card, .hero-mini-stat').forEach(card=>{
        card.addEventListener('pointermove', e=>{
            const r=card.getBoundingClientRect();
            const x=(e.clientX-r.left)/r.width-.5, y=(e.clientY-r.top)/r.height-.5;
            card.style.transform = `perspective(900px) rotateX(${-y*4}deg) rotateY(${x*5}deg) translateY(-3px)`;
        }, {passive:true});
        card.addEventListener('pointerleave', ()=> card.style.transform = '', {passive:true});
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleMobile() {
    document.getElementById('navDropdown').classList.toggle('open');
}

function syncThemeButtons() {
    const theme = document.documentElement.dataset.theme || 'light';
    const isDark = theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        const icon = button.querySelector('i');
        const label = button.querySelector('span');
        if (icon) icon.className = `bi ${isDark ? 'bi-sun' : 'bi-moon-stars'} ${button.closest('.nav-dropdown') ? 'me-2' : 'me-1'}`;
        if (label) label.textContent = isDark ? 'Terang' : 'Gelap';
        button.setAttribute('aria-label', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    });
}

function setTheme(theme) {
    document.documentElement.dataset.theme = theme;
    try { localStorage.setItem('aiStudyBuddyTheme', theme); } catch (error) {}
    syncThemeButtons();
}

document.querySelectorAll('[data-theme-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        setTheme((document.documentElement.dataset.theme || 'light') === 'dark' ? 'light' : 'dark');
    });
});
syncThemeButtons();

// Override native alert with SweetAlert2 for beautiful responsive feedback
window.alert = function(message) {
    Swal.fire({
        text: message,
        icon: 'warning',
        confirmButtonColor: 'var(--primary)',
        confirmButtonText: 'OK',
        customClass: {
            popup: 'rounded-4 border-0 shadow-lg'
        }
    });
};
</script>
@stack('scripts')
<script src="{{ asset('assets/js/ai-session-resume.js') }}"></script>
</body>
</html>
