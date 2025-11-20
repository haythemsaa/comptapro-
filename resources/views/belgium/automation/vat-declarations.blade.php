@extends('layouts.app')

@section('title', 'Déclarations TVA - Belgique')

@section('content')
<div class="animate-fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="bi bi-receipt text-success me-2"></i>
                Déclarations TVA Belgique
            </h1>
            <p class="text-muted">Taux: 21%, 12%, 6%, 0% - Génération automatique</p>
        </div>
        <button class="btn btn-gradient" onclick="generateNewDeclaration()">
            <i class="bi bi-plus-circle me-2"></i>
            Générer Nouvelle Déclaration
        </button>
    </div>

    <!-- Prochaine échéance -->
    @if($nextDeadline)
    <div class="alert alert-warning mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-calendar-event fs-2 me-3"></i>
            <div>
                <h5 class="mb-1">Prochaine échéance: {{ $nextDeadline->deadline->format('d/m/Y') }}</h5>
                <p class="mb-0">
                    Déclaration {{ $nextDeadline->period_name }} -
                    Montant: {{ number_format($nextDeadline->vat_to_pay, 2) }}€ -
                    Communication: {{ $nextDeadline->payment_reference }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des déclarations -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Historique des Déclarations</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Période</th>
                            <th>Type</th>
                            <th class="text-end">CA 21%</th>
                            <th class="text-end">CA 12%</th>
                            <th class="text-end">CA 6%</th>
                            <th class="text-end">TVA Collectée</th>
                            <th class="text-end">TVA Déductible</th>
                            <th class="text-end">À Payer</th>
                            <th>Communication</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($declarations as $decl)
                        <tr>
                            <td>
                                <strong>{{ $decl->period_name }}</strong><br>
                                <small class="text-muted">Deadline: {{ $decl->deadline->format('d/m/Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $decl->period_type == 'monthly' ? 'primary' : 'info' }}">
                                    {{ $decl->period_type == 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($decl->sales_21, 2) }}€</td>
                            <td class="text-end">{{ number_format($decl->sales_12, 2) }}€</td>
                            <td class="text-end">{{ number_format($decl->sales_6, 2) }}€</td>
                            <td class="text-end text-success">{{ number_format($decl->vat_collected, 2) }}€</td>
                            <td class="text-end text-danger">{{ number_format($decl->vat_deductible, 2) }}€</td>
                            <td class="text-end">
                                <strong class="text-primary">{{ number_format($decl->vat_to_pay, 2) }}€</strong>
                            </td>
                            <td>
                                <code class="small">{{ $decl->payment_reference }}</code>
                            </td>
                            <td>
                                <span class="badge bg-{{
                                    $decl->status == 'paid' ? 'success' :
                                    ($decl->status == 'submitted' ? 'info' :
                                    ($decl->status == 'validated' ? 'warning' : 'secondary'))
                                }}">
                                    {{ ucfirst($decl->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewDeclaration({{ $decl->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="downloadPDF({{ $decl->id }})">
                                        <i class="bi bi-file-pdf"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Aucune déclaration TVA</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($declarations->hasPages())
        <div class="card-footer">
            {{ $declarations->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
async function generateNewDeclaration() {
    const year = new Date().getFullYear();
    const month = new Date().getMonth() + 1;

    if (!confirm(`Générer la déclaration TVA pour ${month}/${year} ?`)) return;

    try {
        ComptaPro.Loading.show('Génération en cours...');

        const response = await ComptaPro.API.post('/belgium/vat/generate', {
            year: year,
            month: month,
            period_type: 'monthly'
        });

        if (response.success) {
            ComptaPro.Toast.success('TVA générée !', `Montant: ${response.data.declaration.vat_to_pay}€`);
            setTimeout(() => location.reload(), 2000);
        }
    } catch (error) {
        ComptaPro.Toast.error('Erreur', error.message);
    } finally {
        ComptaPro.Loading.hide();
    }
}

function viewDeclaration(id) {
    window.location.href = `/belgium/vat/${id}`;
}

function downloadPDF(id) {
    window.open(`/belgium/vat/${id}/pdf`, '_blank');
}
</script>
@endpush
@endsection
