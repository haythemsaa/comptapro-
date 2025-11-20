@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</h1>
    <p class="text-muted">Vue d'ensemble de votre plateforme ComptaPro</p>
</div>

{{-- Statistiques globales --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Entreprises</div>
                        <div class="stat-value text-primary">{{ $stats['total_companies'] ?? 0 }}</div>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> {{ $stats['new_companies_this_month'] ?? 0 }} ce mois
                        </small>
                    </div>
                    <div class="fs-1 text-primary opacity-25">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Entreprises actives</div>
                        <div class="stat-value text-success">{{ $stats['active_companies'] ?? 0 }}</div>
                        <small class="text-muted">
                            {{ $stats['total_companies'] > 0 ? round(($stats['active_companies'] / $stats['total_companies']) * 100) : 0 }}% du total
                        </small>
                    </div>
                    <div class="fs-1 text-success opacity-25">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Utilisateurs</div>
                        <div class="stat-value text-info">{{ $stats['total_users'] ?? 0 }}</div>
                        <small class="text-muted">
                            {{ $stats['active_users'] ?? 0 }} actifs
                        </small>
                    </div>
                    <div class="fs-1 text-info opacity-25">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Pays supportés</div>
                        <div class="stat-value text-warning">2</div>
                        <small class="text-muted">
                            TN & BE
                        </small>
                    </div>
                    <div class="fs-1 text-warning opacity-25">
                        <i class="bi bi-globe"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Répartition par pays --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Répartition par pays</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="mb-3">
                            <div class="display-4 text-primary">🇹🇳</div>
                            <h3 class="mt-2">{{ $stats['companies_tunisia'] ?? 0 }}</h3>
                            <p class="text-muted">Tunisie</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <div class="display-4 text-info">🇧🇪</div>
                            <h3 class="mt-2">{{ $stats['companies_belgium'] ?? 0 }}</h3>
                            <p class="text-muted">Belgique</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Répartition par secteur</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['by_sector']) && count($stats['by_sector']) > 0)
                    @foreach($stats['by_sector'] as $sector)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize">{{ $sector->sector ?? 'Autre' }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $sector->count }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">Aucune donnée disponible</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Entreprises récentes --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Entreprises récentes</h5>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                @if(isset($recentCompanies) && $recentCompanies->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentCompanies as $company)
                            <a href="{{ route('admin.companies.show', $company) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $company->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $company->country_code }}
                                            @if($company->sector)
                                                | <i class="bi bi-briefcase"></i> {{ $company->sector }}
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge {{ $company->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $company->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center p-3">Aucune entreprise</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Utilisateurs récents</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                @if(isset($recentUsers) && $recentUsers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentUsers as $user)
                            <a href="{{ route('admin.users.show', $user) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $user->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> {{ $user->email }}
                                            @if($user->company)
                                                | <i class="bi bi-building"></i> {{ $user->company->name }}
                                            @endif
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-info">{{ $user->role }}</span>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center p-3">Aucun utilisateur</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Évolution mensuelle --}}
@if(isset($monthlyStats) && count($monthlyStats) > 0)
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Évolution sur 12 mois</h5>
    </div>
    <div class="card-body">
        <canvas id="monthlyChart" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('monthlyChart');
    const monthlyData = @json($monthlyStats);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(m => m.month),
            datasets: [{
                label: 'Nouvelles entreprises',
                data: monthlyData.map(m => m.companies),
                borderColor: 'rgb(13, 110, 253)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Nouveaux utilisateurs',
                data: monthlyData.map(m => m.users),
                borderColor: 'rgb(13, 202, 240)',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
@endif

@endsection
