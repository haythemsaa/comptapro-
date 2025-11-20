<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - ComptaPro</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    {{-- Animate.css --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #0d6efd;
            --sidebar-bg: #212529;
            --sidebar-hover: #2c3136;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #fff;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            border-radius: 0;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }

        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .nav-section {
            padding: 1rem 1rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.5);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.show {
            max-height: 500px;
        }

        .submenu .nav-link {
            padding-left: 3rem;
            font-size: 0.9rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .header {
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-wrapper {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            margin-bottom: 1.5rem;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .alert {
            border: none;
            border-left: 4px solid;
        }

        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-calculator"></i> ComptaPro</h4>
            <small class="text-muted">Administration</small>
        </div>

        <nav class="sidebar-nav">
            {{-- Dashboard --}}
            <div class="nav-section">Tableau de bord</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            {{-- Gestion --}}
            <div class="nav-section">Gestion</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Entreprises</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Utilisateurs</span>
                    </a>
                </li>
            </ul>

            {{-- Comptabilité --}}
            <div class="nav-section">Comptabilité</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.accounting.chart-of-accounts.index') }}" class="nav-link {{ request()->routeIs('admin.accounting.chart-of-accounts.*') ? 'active' : '' }}">
                        <i class="bi bi-list-ul"></i>
                        <span>Plan Comptable</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.accounting.journal-entries.index') }}" class="nav-link {{ request()->routeIs('admin.accounting.journal-entries.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i>
                        <span>Écritures</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#reportsMenu">
                        <i class="bi bi-graph-up"></i>
                        <span>Rapports</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <div id="reportsMenu" class="submenu collapse {{ request()->routeIs('admin.accounting.reports.*') ? 'show' : '' }}">
                        <a href="{{ route('admin.accounting.reports.dashboard') }}" class="nav-link">Tableau de bord</a>
                        <a href="{{ route('admin.accounting.reports.balance-sheet') }}" class="nav-link">Bilan</a>
                        <a href="{{ route('admin.accounting.reports.income-statement') }}" class="nav-link">Compte de résultat</a>
                        <a href="{{ route('admin.accounting.reports.trial-balance') }}" class="nav-link">Balance générale</a>
                        <a href="{{ route('admin.accounting.reports.general-ledger') }}" class="nav-link">Grand livre</a>
                    </div>
                </li>
            </ul>

            {{-- Tunisia --}}
            <div class="nav-section">🇹🇳 Tunisie</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.tunisia.employees.index') }}" class="nav-link {{ request()->routeIs('admin.tunisia.employees.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Employés TN</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tunisia.payrolls.index') }}" class="nav-link {{ request()->routeIs('admin.tunisia.payrolls.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Paie TN</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.tunisia.taxes.index') }}" class="nav-link {{ request()->routeIs('admin.tunisia.taxes.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Déclarations TN</span>
                    </a>
                </li>
            </ul>

            {{-- Belgium --}}
            <div class="nav-section">🇧🇪 Belgique</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.belgium.employees.index') }}" class="nav-link {{ request()->routeIs('admin.belgium.employees.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Employés BE</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.belgium.payrolls.index') }}" class="nav-link {{ request()->routeIs('admin.belgium.payrolls.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Paie BE</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.belgium.taxes.index') }}" class="nav-link {{ request()->routeIs('admin.belgium.taxes.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Déclarations BE</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        {{-- Header --}}
        <header class="header">
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline ms-2">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <div class="content-wrapper">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container"></div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Sidebar Toggle (Mobile)
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                bootstrap.Alert.getInstance(alert)?.close();
            });
        }, 5000);

        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            window.axios = window.axios || {};
            window.axios.defaults = window.axios.defaults || {};
            window.axios.defaults.headers = window.axios.defaults.headers || {};
            window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }
    </script>

    @stack('scripts')
</body>
</html>
