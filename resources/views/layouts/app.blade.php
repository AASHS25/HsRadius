<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - HsRadius</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-active: #3b82f6;
            --content-bg: #f1f5f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--content-bg);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: #cbd5e1;
            z-index: 1040;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand .logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-brand h5 {
            margin: 0;
            color: #f8fafc;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-brand small {
            color: #64748b;
            font-size: 0.7rem;
            display: block;
        }

        .sidebar-nav {
            padding: 0.75rem 0;
        }

        .sidebar-nav .nav-label {
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            padding: 0.6rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            color: #e2e8f0;
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-nav .nav-link.active {
            color: #fff;
            background-color: rgba(59, 130, 246, 0.15);
            border-left-color: var(--sidebar-active);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            width: 22px;
            text-align: center;
        }

        .sidebar-nav .submenu {
            list-style: none;
            padding: 0;
        }

        .sidebar-nav .submenu .nav-link {
            padding-left: 3.5rem;
            font-size: 0.82rem;
        }

        .sidebar-nav .nav-link[data-bs-toggle="collapse"] .bi-chevron-down {
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.2s;
        }

        .sidebar-nav .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.5rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
        }

        .top-navbar .search-box {
            position: relative;
            max-width: 350px;
        }

        .top-navbar .search-box input {
            padding-left: 2.5rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: var(--content-bg);
            font-size: 0.875rem;
            height: 38px;
        }

        .top-navbar .search-box input:focus {
            background: #fff;
            border-color: var(--sidebar-active);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .top-navbar .search-box i {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .top-navbar .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .top-navbar .nav-actions .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: #64748b;
            position: relative;
            transition: all 0.2s;
        }

        .top-navbar .nav-actions .btn-icon:hover {
            background: var(--content-bg);
            color: #1e293b;
        }

        .top-navbar .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .top-navbar .user-dropdown .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            background: transparent;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
        }

        .top-navbar .user-dropdown .btn:hover {
            background: var(--content-bg);
        }

        .top-navbar .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* Content Area */
        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h3 {
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .page-header .breadcrumb {
            margin: 0;
            font-size: 0.8rem;
        }

        /* Footer */
        .main-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #94a3b8;
            text-align: center;
            background: #fff;
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #475569;
            padding: 0.25rem;
            margin-right: 0.75rem;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            padding: 1rem 1.25rem;
        }

        /* Tables */
        .table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            border-bottom-width: 1px;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--sidebar-active);
            border-color: var(--sidebar-active);
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }
        }

        @yield('styles')
    </style>
    @stack('styles')
</head>
<body>
    {{-- Sidebar Overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar --}}
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <i class="bi bi-broadcast"></i>
            </div>
            <div>
                <h5>HsRadius</h5>
                <small>RADIUS Management</small>
            </div>
        </div>

        <div class="sidebar-nav">
            @can('manage-tenants')
            <div class="nav-label">Landlord</div>
            <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Kelola Tenant
            </a>
            @endcan

            <div class="nav-label">Menu Utama</div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            @can('manage-customers')
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Pelanggan
            </a>
            @endcan

            @can('manage-packages')
            <a href="{{ route('packages.index') }}" class="nav-link {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i> Paket Layanan
            </a>
            @endcan

            @can('manage-nas')
            <a href="{{ route('nas.index') }}" class="nav-link {{ request()->routeIs('nas.*') ? 'active' : '' }}">
                <i class="bi bi-router"></i> NAS/Router
            </a>
            @endcan

            @can('manage-vouchers')
            <a href="{{ route('vouchers.index') }}" class="nav-link {{ request()->routeIs('vouchers.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> Voucher
            </a>
            @endcan

            @can('manage-sessions')
            <a href="{{ route('sessions.index') }}" class="nav-link {{ request()->routeIs('sessions.*') ? 'active' : '' }}">
                <i class="bi bi-wifi"></i> Sesi Aktif
            </a>
            @endcan

            @can('manage-billing')
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Invoice
            </a>
            @endcan

            @can('view-reports')
            <div class="nav-label">Laporan</div>

            <a href="#laporanSubmenu" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                <i class="bi bi-graph-up"></i> Laporan
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="laporanSubmenu">
                <ul class="submenu">
                    <li>
                        <a href="{{ route('reports.traffic') }}" class="nav-link {{ request()->routeIs('reports.traffic') ? 'active' : '' }}">
                            <i class="bi bi-activity"></i> Traffic
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.revenue') }}" class="nav-link {{ request()->routeIs('reports.revenue') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i> Revenue
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.customers') }}" class="nav-link {{ request()->routeIs('reports.customers') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i> Pelanggan
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Top Navbar --}}
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="search-box d-none d-md-block">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" placeholder="Cari pelanggan, paket, voucher...">
                </div>
            </div>

            <div class="nav-actions">
                <button class="btn-icon" title="Notifikasi">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge"></span>
                </button>

                <div class="user-dropdown dropdown">
                    <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->username ?? Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="d-none d-md-inline text-dark fw-medium" style="font-size: 0.875rem;">
                            {{ Auth::user()->name ?? Auth::user()->username ?? 'Admin' }}
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person me-2"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i> Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @yield('content')
        </div>

        {{-- Footer --}}
        <footer class="main-footer">
            HsRadius v1.0 - RADIUS Management System
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
        }

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
            setTimeout(function () {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
    @stack('scripts')
</body>
</html>
