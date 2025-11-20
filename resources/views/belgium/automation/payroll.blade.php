@extends('layouts.app')

@section('title', 'Paie & ONSS - Belgique')

@section('content')
<div class="animate-fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="bi bi-people text-warning me-2"></i>
                Paie & ONSS Belgique
            </h1>
            <p class="text-muted">ONSS: 13.07% employé + ~27% employeur</p>
        </div>
        <button class="btn btn-gradient" onclick="generateAllPayrolls()">
            <i class="bi bi-calculator me-2"></i>
            Générer Toutes les Paies du Mois
        </button>
    </div>

    <!-- Statistiques du mois -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <p class="stat-label">Salaires Bruts</p>
                    <h3 class="stat-value text-warning">{{ number_format($stats['total_gross'], 0) }}€</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <p class="stat-label">Salaires Nets</p>
                    <h3 class="stat-value text-success">{{ number_format($stats['total_net'], 0) }}€</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card danger">
                <div class="card-body">
                    <p class="stat-label">ONSS Total</p>
                    <h3 class="stat-value text-danger">{{ number_format($stats['total_onss'], 0) }}€</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <p class="stat-label">Employés</p>
                    <h3 class="stat-value text-info">{{ $stats['employee_count'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des fiches de paie -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Fiches de Paie</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employé</th>
                            <th>NISS</th>
                            <th>Période</th>
                            <th class="text-end">Brut</th>
                            <th class="text-end">ONSS Employé</th>
                            <th class="text-end">Précompte</th>
                            <th class="text-end">Net</th>
                            <th class="text-end">ONSS Employeur</th>
                            <th class="text-end">Coût Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                        <tr>
                            <td>
                                <strong>{{ $payroll->employee->full_name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $payroll->employee->employee_number ?? '' }}</small>
                            </td>
                            <td>
                                <code class="small">{{ $payroll->employee->formatted_niss ?? '' }}</code>
                            </td>
                            <td>{{ $payroll->month }}/{{ $payroll->year }}</td>
                            <td class="text-end">{{ number_format($payroll->gross_salary, 2) }}€</td>
                            <td class="text-end text-danger">
                                -{{ number_format($payroll->onss_employee, 2) }}€
                                <small class="text-muted d-block">13.07%</small>
                            </td>
                            <td class="text-end text-danger">
                                -{{ number_format($payroll->withholding_tax, 2) }}€
                            </td>
                            <td class="text-end">
                                <strong class="text-success">{{ number_format($payroll->net_salary, 2) }}€</strong>
                            </td>
                            <td class="text-end text-warning">
                                {{ number_format($payroll->onss_employer, 2) }}€
                                <small class="text-muted d-block">~27%</small>
                            </td>
                            <td class="text-end">
                                <strong class="text-primary">{{ number_format($payroll->employer_cost, 2) }}€</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{
                                    $payroll->status == 'paid' ? 'success' :
                                    ($payroll->status == 'validated' ? 'info' : 'secondary')
                                }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewPayroll({{ $payroll->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="downloadPayslip({{ $payroll->id }})">
                                        <i class="bi bi-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Aucune fiche de paie</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payrolls->hasPages())
        <div class="card-footer">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
async function generateAllPayrolls() {
    const year = new Date().getFullYear();
    const month = new Date().getMonth() + 1;

    if (!confirm(`Générer toutes les paies pour ${month}/${year} ?`)) return;

    try {
        ComptaPro.Loading.show('Génération des paies...');

        const response = await ComptaPro.API.post('/belgium/payroll/generate-all', {
            year: year,
            month: month
        });

        if (response.success) {
            ComptaPro.Toast.success(
                'Paies générées !',
                `${response.data.count} fiches créées`
            );
            setTimeout(() => location.reload(), 2000);
        }
    } catch (error) {
        ComptaPro.Toast.error('Erreur', error.message);
    } finally {
        ComptaPro.Loading.hide();
    }
}

function viewPayroll(id) {
    window.location.href = `/belgium/payroll/${id}`;
}

function downloadPayslip(id) {
    window.open(`/belgium/payroll/${id}/pdf`, '_blank');
}
</script>
@endpush
@endsection
