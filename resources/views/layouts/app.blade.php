<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Printing POS')
    </title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Inter, Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select {
            font-family: inherit;
        }

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
            margin-bottom: 16px;
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
            gap: 10px;
            margin-top: 10px;
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
            }

            .sidebar.open {
                transform: translateX(0);
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
    </style>

    @stack('styles')
</head>

<body>

<div class="app">

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">
                🖨️
            </div>

            <div class="brand-text">
                <h2>Printing POS</h2>
                <span>Management System</span>
            </div>
        </div>

        <nav class="menu">

            <div class="menu-title">Main</div>

            <a href="{{ route('dashboard') }}"
               class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="menu-icon">▣</span>
                Dashboard
            </a>

            <a href="{{ route('pos.index') }}"
               class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <span class="menu-icon">🛒</span>
                Point of Sale
            </a>

            <a href="{{ route('sales.index') }}"
               class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <span class="menu-icon">▤</span>
                Transactions
            </a>

            @if(auth()->user()->role === 'Administrator')

                <div class="menu-title">Management</div>

                <a href="{{ route('products.index') }}"
                   class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="menu-icon">◫</span>
                    Services
                </a>

                <a href="{{ route('users.index') }}"
                   class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="menu-icon">♙</span>
                    Users
                </a>

                <a href="{{ route('reports.index') }}"
                   class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="menu-icon">▥</span>
                    Reports
                </a>

            @endif

        </nav>

        <div class="sidebar-footer">

            <div class="user-mini">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->role }}</span>
                </div>

            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout-btn">
                    ⇥ &nbsp; Logout
                </button>
            </form>

        </div>

    </aside>

    <main class="main">

        <header class="topbar">

            <div style="display:flex;align-items:center;gap:15px;">

                <button class="mobile-menu"
                        onclick="toggleSidebar()">
                    ☰
                </button>

                <div class="page-title">

                    <h1>
                        @yield('page-title', 'Dashboard')
                    </h1>

                    <p>
                        Printing Services Point of Sale
                    </p>

                </div>

            </div>

            <div class="top-user">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div style="display:none;" class="desktop-user">
                    <strong>{{ auth()->user()->name }}</strong>
                </div>

            </div>

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
function toggleSidebar() {
    document
        .getElementById('sidebar')
        .classList.toggle('open');
}
</script>

@stack('scripts')

</body>
</html>