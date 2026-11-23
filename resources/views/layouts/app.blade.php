<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('page-title', 'Dashboard') | {{ $branding['system_name'] }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($branding['favicon_path'])<link rel="icon" type="image/png" href="{{ asset('storage/'.$branding['favicon_path']) }}">@endif

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            background: #f5f7fb;
            color: #1f2937;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        button, a { -webkit-tap-highlight-color: transparent; }
        button:focus-visible, a:focus-visible { outline: 2px solid #2dd4bf; outline-offset: 3px; }

        /* =========================
           APP
        ========================= */

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            width: 250px;
            background: #111827;
            color: #fff;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .brand {
            height: 75px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 12px;
        }

        .brand-text h2 {
            font-size: 17px;
            font-weight: 700;
        }

        .brand-text span {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .menu {
            padding: 20px 12px;
            flex: 1;
        }

        .menu-title {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 12px;
            margin: 10px 0 8px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 4px;
            border-radius: 9px;
            color: #d1d5db;
            font-size: 14px;
            transition: .2s;
        }

        .menu a:hover,
        .menu a.active {
            background: #2563eb;
            color: #fff;
        }

        .menu-icon {
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .user-mini {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-mini strong {
            display: block;
            font-size: 13px;
        }

        .user-mini span {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .logout-btn {
            width: 100%;
            border: 0;
            background: #1f2937;
            color: #d1d5db;
            padding: 11px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }

        .logout-btn:hover {
            background: #374151;
            color: #fff;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .topbar {
            height: 75px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 500;
        }

        .page-title h1 {
            font-size: 21px;
            color: #111827;
        }

        .page-title p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 3px;
        }

        .top-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-user .avatar {
            width: 34px;
            height: 34px;
            font-size: 13px;
        }

        .content {
            padding: 30px;
        }

        /* =========================
           CARDS
        ========================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(15,23,42,.03);
        }

        .stat-label {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 700;
            color: #111827;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 11px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* =========================
           CONTENT CARDS
        ========================= */

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(15,23,42,.03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-header h2 {
            font-size: 16px;
            color: #111827;
        }

        .card-header p {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        /* =========================
           BUTTONS
        ========================= */

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-success {
            background: #dcfce7;
            color: #166534;
        }

        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 13px 12px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        td {
            padding: 14px 12px;
            border-top: 1px solid #f0f0f0;
            font-size: 13px;
        }

        tr:hover td {
            background: #fafafa;
        }

        /* =========================
           BADGES
        ========================= */

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* =========================
           FORMS
        ========================= */

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            color: #111827;
            outline: none;
            font-size: 13px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        /* =========================
           ALERT
        ========================= */

        .alert {
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* =========================
           QUICK ACTIONS
        ========================= */

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .quick-action {
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            background: #fff;
            transition: .2s;
        }

        .quick-action:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
        }

        .quick-action-icon {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .quick-action strong {
            display: block;
            font-size: 14px;
        }

        .quick-action span {
            color: #6b7280;
            font-size: 11px;
        }

        /* =========================
           MOBILE
        ========================= */

        .mobile-menu {
            display: none;
            border: 0;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
        }

        .sidebar-backdrop {
            display: none;
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 750px) {
            .sidebar {
                transform: translateX(-100%);
                transition: .25s;
                width: min(285px, 86vw);
                box-shadow: 18px 0 45px rgba(0,0,0,.35);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 900;
                display: block;
                border: 0;
                background: rgba(2,8,18,.68);
                opacity: 0;
                pointer-events: none;
                transition: opacity .25s ease;
                backdrop-filter: blur(2px);
            }

            .sidebar-backdrop.open {
                opacity: 1;
                pointer-events: auto;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .mobile-menu {
                display: block;
            }

            .topbar {
                padding: 0 18px;
            }

            .content {
                padding: 18px;
            }

            .stats-grid,
            .quick-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Unified dark application theme */
        body { background: #07111f; color: #dce7f1; }
        .sidebar { background: #172437; border-right: 1px solid #29394e; box-shadow: 8px 0 30px rgba(0,0,0,.12); }
        .brand { border-color: #29394e; }
        .brand-icon, .avatar { background: linear-gradient(135deg, #0aa39c, #087875); box-shadow: inset 0 1px 0 rgba(255,255,255,.12); }
        .brand-icon svg { width: 21px; height: 21px; }
        .brand-text h2 { letter-spacing: -.02em; }
        .brand-text span, .menu-title, .page-title p { color: #8298ad; }
        .menu a { color: #b8c6d5; font-weight: 500; letter-spacing: -.01em; border: 1px solid transparent; }
        .menu a:hover { background: rgba(8,139,137,.18); border-color: rgba(20,184,166,.18); color: #fff; transform: translateX(2px); }
        .menu a.active { background: linear-gradient(135deg, #078b89, #087875); border-color: rgba(94,234,212,.18); color: #fff; box-shadow: 0 7px 18px rgba(3,105,101,.18); }
        .menu-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 20px; }
        .menu-icon svg { width: 18px; height: 18px; stroke-width: 1.8; }
        .sidebar-footer { border-color: #29394e; }
        .logout-btn { display: flex; align-items: center; justify-content: center; gap: 8px; background: #202f44; color: #cad5df; font-weight: 600; transition: .2s ease; }
        .logout-btn svg { width: 16px; height: 16px; }
        .main { background: #07111f; }
        .topbar { height: 68px; background: rgba(23,36,55,.96); border-color: #29394e; padding-inline: 24px; backdrop-filter: blur(16px); }
        .page-title h1 { color: #f4f8fb; font-size: 19px; font-weight: 700; letter-spacing: -.025em; line-height: 1.25; }
        .page-title p { font-size: 11px; letter-spacing: .01em; }
        .content { width: 100%; max-width: 1720px; margin-inline: auto; padding: 20px 24px 28px; }
        .content > * { margin-top: 0 !important; }
        .content > * + * { margin-top: 20px !important; }
        .content .navbar { display: none; }
        .card, .stat-card { background: #0c1828; border-color: #2a4055; box-shadow: 0 12px 35px rgba(0,0,0,.14); }
        .card { padding: clamp(18px, 2vw, 24px); }
        .stat-card { min-height: 112px; padding: 20px; }
        .stats-grid { gap: 16px; margin-bottom: 0; }
        .card-header { gap: 16px; margin-bottom: 20px; }
        .card-header h2, .stat-value { color: #f4f8fb; }
        .card-header p, .stat-label { color: #8298ad; }
        .stat-icon { background: rgba(20,184,166,.12); color: #5eead4; }
        .btn-primary { background: #078b89; }
        .btn-primary:hover { background: #0a9f9b; }
        .btn-secondary { background: #202f44; color: #dce7f1; border: 1px solid #34465c; }
        .btn-danger { background: rgba(244,63,94,.14); color: #fda4af; }
        .btn-success { background: rgba(16,185,129,.14); color: #6ee7b7; }
        th { background: #111e30; color: #8298ad; font-size: 10px; font-weight: 700; letter-spacing: .075em; }
        td { border-color: #223247; color: #c9d5e2; line-height: 1.45; }
        tr:hover td { background: rgba(30,47,68,.5); }
        label { color: #cbd7e3; }
        input, select, textarea { background: #101d2e; border-color: #34465c; color: #f1f5f9; transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease; }
        input::placeholder, textarea::placeholder { color: #64778b; }
        input:focus, select:focus, textarea:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.15); }
        .quick-action { background: #101d2e; border-color: #2a4055; }
        .quick-action:hover { border-color: #14b8a6; }
        .quick-action strong { color: #f4f8fb; }
        .quick-action span { color: #8298ad; }
        .alert-success { background: rgba(16,185,129,.12); color: #6ee7b7; border-color: rgba(16,185,129,.35); }
        .alert-error { background: rgba(244,63,94,.12); color: #fda4af; border-color: rgba(244,63,94,.35); }
        .desktop-user { font-size: 13px; color: #dce7f1; }
        .top-user .avatar { border: 2px solid rgba(94,234,212,.18); }
        ::selection { background: rgba(20,184,166,.35); color: #fff; }

        @media (min-width: 751px) {
            .desktop-user { display: block !important; }
        }

        @media (max-width: 750px) {
            .content { padding: 14px 12px 22px; }
            .topbar { padding-inline: 14px; }
            .page-title h1 { font-size: 17px; }
            .page-title p { display: none; }
            .content > * + * { margin-top: 14px !important; }
            .card, .stat-card { padding: 16px; border-radius: 12px; }
            .card-header { align-items: flex-start; flex-direction: column; margin-bottom: 16px; }
            th, td { padding: 12px 14px; }
        }
    </style>

    @stack('styles')
    @include('settings._theme', ['branding' => $branding])
</head>

<body class="glass-theme">

<div class="app">

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">
                @if($branding['logo_path'])
                    <img src="{{ asset('storage/'.$branding['logo_path']) }}" alt="{{ $branding['system_name'] }} logo">
                @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke-width="1.8" stroke-linejoin="round"/></svg>
                @endif
            </div>

            <div class="brand-text">
                <h2 title="{{ $branding['system_name'] }}">{{ $branding['system_name'] }}</h2>
                <span>{{ $branding['tagline'] }}</span>
            </div>
        </div>

        <nav class="menu">
            @if(! auth()->user()->isSuperadmin())
                @php($currentSection = null)
                @foreach($sidebarItems as $item)
                    @if($item['section'] !== $currentSection)
                        @if($item['section'] !== '')
                            <div class="menu-title">{{ $item['section'] }}</div>
                        @endif
                        @php($currentSection = $item['section'])
                    @endif
                    <a href="{{ $item['destination'] === 'external' ? $item['url'] : route($item['destination']) }}"
                       class="{{ $item['destination'] !== 'external' && request()->routeIs($item['destination'] === 'dashboard' ? 'dashboard' : str_replace('.index', '.*', $item['destination'])) ? 'active' : '' }}"
                       @if($item['destination'] === 'external') target="_blank" rel="noopener noreferrer" @endif>
                        <span class="menu-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ \App\Models\SystemSetting::SIDEBAR_ICONS[$item['icon']] }}"/></svg></span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @foreach($customPages as $page)
                    @if($page['section'] !== $currentSection)
                        <div class="menu-title">{{ $page['section'] }}</div>
                        @php($currentSection = $page['section'])
                    @endif
                    <a href="{{ route('pages.show', $page['slug']) }}" class="{{ request()->routeIs('pages.show') && request()->route('page') === $page['slug'] ? 'active' : '' }}"><span class="menu-icon">+</span>{{ $page['title'] }}</a>
                @endforeach
            @else
                <div class="menu-title">Customization</div>
                <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.edit') ? 'active' : '' }}">System Settings</a>
                <a href="{{ route('settings.sidebar.edit') }}" class="{{ request()->routeIs('settings.sidebar.*') ? 'active' : '' }}">Sidebar Editor</a>
                <a href="{{ route('settings.builder.edit') }}" class="{{ request()->routeIs('settings.builder.*') ? 'active' : '' }}">Appearance & Dashboard</a>
                <a href="{{ route('settings.pages.index') }}" class="{{ request()->routeIs('settings.pages.*') ? 'active' : '' }}">Custom Pages</a>
                <a href="{{ route('stores.index') }}" class="{{ request()->routeIs('stores.*') ? 'active' : '' }}">Store Branding</a>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Accounts</a>
            @endif
            @if(! auth()->user()->isSuperadmin() && auth()->user()->isAdministrator() && $needsSuperadminSetup)
                <div class="menu-title">System</div>
                <a href="{{ route('superadmin.setup') }}" class="{{ request()->routeIs('superadmin.*') ? 'active' : '' }}"><span class="menu-icon">+</span>Superadmin Setup</a>
            @endif
        </nav>
        @if(! auth()->user()->isSuperadmin())
            <div class="px-4 pb-4 text-xs text-slate-400">Assigned store: {{ auth()->user()->store?->name ?? 'Not assigned' }}</div>
        @endif

        <div class="sidebar-footer">

            <a href="{{ route('profile.edit') }}" class="user-mini rounded-lg transition hover:bg-white/5" aria-label="Edit my profile" title="Edit my profile">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->role }}</span>
                </div>

            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Logout
                </button>
            </form>

        </div>

    </aside>

    <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)" aria-label="Close navigation"></button>

    <main class="main">

        <header class="topbar">

            <div style="display:flex;align-items:center;gap:15px;">

                <button class="mobile-menu"
                        onclick="toggleSidebar()"
                        aria-label="Toggle navigation">
                    <svg style="width:22px;height:22px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/></svg>
                </button>

                <div class="page-title">

                    <h1>
                        @yield('page-title', 'Dashboard')
                    </h1>

                    <p>
                        {{ auth()->user()->isSuperadmin() ? 'Superadmin customization panel' : $branding['header_text'] }}
                    </p>

                </div>

            </div>

            <a href="{{ route('profile.edit') }}" class="top-user rounded-lg transition hover:bg-white/5" aria-label="Edit my profile" title="Edit my profile">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div style="display:none;" class="desktop-user">
                    <strong>{{ auth()->user()->name }}</strong>
                </div>

            </a>

        </header>

        <section class="content">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Please check the following:</strong>

                    <ul style="margin:7px 0 0 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </section>

    </main>

</div>

<script>
function toggleSidebar(forceOpen = null) {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const shouldOpen = forceOpen ?? !sidebar.classList.contains('open');

    sidebar.classList.toggle('open', shouldOpen);
    backdrop.classList.toggle('open', shouldOpen);
    document.body.style.overflow = shouldOpen ? 'hidden' : '';
}

document.querySelectorAll('#sidebar a').forEach((link) => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 750) {
            toggleSidebar(false);
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleSidebar(false);
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 750) {
        toggleSidebar(false);
    }
});
</script>

@stack('scripts')

</body>
</html>
