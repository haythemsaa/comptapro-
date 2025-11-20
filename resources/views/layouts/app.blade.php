<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ComptaPro Tunisia') }} - @yield('title', 'Automatisation IA')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/comptapro.css') }}">

    @stack('styles')

    <style>
        :root {
            --cp-primary: #0d6efd;
            --cp-secondary: #6c757d;
            --cp-success: #198754;
            --cp-danger: #dc3545;
            --cp-warning: #ffc107;
            --cp-info: #0dcaf0;
            --cp-purple: #6f42c1;
            --cp-indigo: #6610f2;
            --cp-gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --cp-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --cp-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --cp-gradient-4: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar moderne */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 260px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-brand i {
            font-size: 2rem;
            margin-right: 0.75rem;
            background: var(--cp-gradient-3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-item {
            margin: 0.25rem 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--cp-gradient-3);
            color: white;
        }

        .nav-link i {
            width: 24px;
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Topbar moderne */
        .topbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-search {
            flex: 1;
            max-width: 500px;
            position: relative;
        }

        .topbar-search input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .topbar-search input:focus {
            border-color: var(--cp-primary);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
        }

        .topbar-search i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
        }

        .topbar-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .topbar-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: none;
            position: relative;
            transition: all 0.3s;
        }

        .topbar-btn:hover {
            background: #e9ecef;
            transform: scale(1.1);
        }

        .topbar-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        /* Cards modernes */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0 !important;
        }

        /* Stats Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
            background: white;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .stat-card.primary::before { background: var(--cp-gradient-1); }
        .stat-card.success::before { background: var(--cp-gradient-4); }
        .stat-card.warning::before { background: var(--cp-gradient-2); }
        .stat-card.info::before { background: var(--cp-gradient-3); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }

        .stat-icon.primary { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); color: #667eea; }
        .stat-icon.success { background: linear-gradient(135deg, rgba(67, 233, 123, 0.1), rgba(56, 249, 215, 0.1)); color: #43e97b; }
        .stat-icon.warning { background: linear-gradient(135deg, rgba(240, 147, 251, 0.1), rgba(245, 87, 108, 0.1)); color: #f093fb; }
        .stat-icon.info { background: linear-gradient(135deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.1)); color: #4facfe; }

        /* Boutons modernes */
        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .btn-gradient {
            background: var(--cp-gradient-1);
            border: none;
            color: white;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Badge moderne */
        .badge {
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
        }

        /* Progress bars */
        .progress {
            height: 10px;
            border-radius: 10px;
            background: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <i class="bi bi-robot"></i>
                <span>ComptaPro TN</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('automation.dashboard') }}" class="nav-link {{ request()->routeIs('automation.*') ? 'active' : '' }}">
                        <i class="bi bi-robot"></i>
                        <span>Automatisation IA</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Documents</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-journal-text"></i>
                        <span>Écritures</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-pie-chart"></i>
                        <span>États Financiers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-receipt"></i>
                        <span>Factures</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>Paie & CNSS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-file-earmark-ruled"></i>
                        <span>Déclarations Fiscales</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-patch-check"></i>
                        <span>El Fatoora</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-bank"></i>
                        <span>Rapprochement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-chat-dots"></i>
                        <span>Assistant IA</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-gear"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-search">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Rechercher...">
            </div>

            <div class="topbar-actions">
                <button class="topbar-btn" data-bs-toggle="tooltip" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-danger">3</span>
                </button>

                <button class="topbar-btn" data-bs-toggle="tooltip" title="Messages">
                    <i class="bi bi-envelope"></i>
                    <span class="badge bg-primary">5</span>
                </button>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        {{ Auth::user()->name ?? 'Utilisateur' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (optionnel) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Custom Scripts -->
    <script src="{{ asset('js/comptapro.js') }}"></script>

    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
