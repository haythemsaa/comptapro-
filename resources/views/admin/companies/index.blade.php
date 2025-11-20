@extends('layouts.admin')

@section('title', 'Entreprises')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Entreprises</li>
@endsection

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-building me-2"></i>Entreprises</h1>
            <p class="text-muted">Gestion de toutes les entreprises</p>
        </div>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nouvelle entreprise
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.companies.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Recherche</label>
                <input type="text" name="search" class="form-control" placeholder="Nom, email, TVA..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Pays</label>
                <select name="country" class="form-select">
                    <option value="">Tous</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ request('country') == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Secteur</label>
                <select name="sector" class="form-select">
                    <option value="">Tous</option>
                    @foreach($sectors as $sector)
                        <option value="{{ $sector }}" {{ request('sector') == $sector ? 'selected' : '' }}>
                            {{ ucfirst($sector) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactives</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Liste des entreprises --}}
<div class="card">
    <div class="card-body p-0">
        @if($companies->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>Pays</th>
                            <th>Secteur</th>
                            <th>Utilisateurs</th>
                            <th>Statut</th>
                            <th>Créée le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $company->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope"></i> {{ $company->email }}
                                            @if($company->tax_id)
                                                | <i class="bi bi-hash"></i> {{ $company->tax_id }}
                                            @endif
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($company->country_code == 'TN')
                                        <span class="badge bg-primary">🇹🇳 Tunisie</span>
                                    @elseif($company->country_code == 'BE')
                                        <span class="badge bg-info">🇧🇪 Belgique</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $company->country_code }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($company->sector)
                                        <span class="badge bg-light text-dark">{{ ucfirst($company->sector) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $company->users_count ?? 0 }} utilisateur(s)
                                    </span>
                                </td>
                                <td>
                                    @if($company->is_active)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $company->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.companies.show', $company) }}"
                                           class="btn btn-outline-primary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.companies.edit', $company) }}"
                                           class="btn btn-outline-warning" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.companies.toggle-status', $company) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-{{ $company->is_active ? 'secondary' : 'success' }}"
                                                    title="{{ $company->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="bi bi-{{ $company->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Affichage de {{ $companies->firstItem() }} à {{ $companies->lastItem() }}
                        sur {{ $companies->total() }} entreprises
                    </div>
                    {{ $companies->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-building display-1 text-muted"></i>
                <p class="text-muted mt-3">Aucune entreprise trouvée</p>
                <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Créer la première entreprise
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
