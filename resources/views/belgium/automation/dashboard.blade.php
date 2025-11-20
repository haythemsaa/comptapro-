@extends('layouts.app')

@section('title', 'Automatisation IA - Belgique')

@section('content')
<div class="animate-fade-in-up">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="bi bi-robot text-primary me-2"></i>
                Automatisation IA Belgique
            </h1>
            <p class="text-muted">Laissez l'IA gérer votre comptabilité PCMN automatiquement</p>
        </div>
        <button class="btn btn-gradient btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-cloud-upload me-2"></i>
            Uploader un document
        </button>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Documents Traités</p>
                            <h3 class="stat-value text-primary counter" data-target="{{ $stats['documents_processed'] }}">0</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-arrow-up text-success"></i> +12% ce mois
                            </p>
                        </div>
                        <div class="stat-icon primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card success card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Déclarations TVA</p>
                            <h3 class="stat-value text-success counter" data-target="{{ $stats['vat_declarations'] }}">0</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-check-circle text-success"></i> Automatique
                            </p>
                        </div>
                        <div class="stat-icon success">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card warning card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Fiches de Paie</p>
                            <h3 class="stat-value text-warning counter" data-target="{{ $stats['payrolls_processed'] }}">0</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-people text-primary"></i> ONSS incluse
                            </p>
                        </div>
                        <div class="stat-icon warning">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card info card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Précision IA</p>
                            <h3 class="stat-value text-info">98<small>%</small></h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-shield-check text-success"></i> Très fiable
                            </p>
                        </div>
                        <div class="stat-icon info">
                            <i class="bi bi-cpu"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge text-warning me-2"></i>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="bi bi-cloud-upload d-block fs-1 mb-2"></i>
                                <span>Upload Document</span>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success w-100 btn-lg" onclick="generateVATDeclaration()">
                                <i class="bi bi-receipt d-block fs-1 mb-2"></i>
                                <span>Déclaration TVA</span>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning w-100 btn-lg" onclick="generatePayroll()">
                                <i class="bi bi-people d-block fs-1 mb-2"></i>
                                <span>Paie & ONSS</span>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info w-100 btn-lg" onclick="generateReports()">
                                <i class="bi bi-file-bar-graph d-block fs-1 mb-2"></i>
                                <span>États Financiers</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Activité récente -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Activité IA en Temps Réel</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($recentActivity as $activity)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-icon bg-{{ $activity['status'] == 'completed' ? 'success' : 'primary' }}-subtle text-{{ $activity['status'] == 'completed' ? 'success' : 'primary' }} rounded-circle p-2">
                                        <i class="{{ $activity['icon'] }} fs-5"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                        <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                    </div>
                                    <p class="text-muted mb-0">{{ $activity['description'] }}</p>
                                    <span class="badge bg-{{ $activity['status'] == 'completed' ? 'success' : 'warning' }} mt-1">
                                        {{ ucfirst($activity['status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Aucune activité récente</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Échéances -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Échéances Fiscales</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($deadlines as $deadline)
                        <div class="list-group-item {{ $deadline['status'] == 'overdue' ? 'bg-danger-subtle' : '' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $deadline['description'] }}</h6>
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-calendar3"></i>
                                        {{ \Carbon\Carbon::parse($deadline['deadline'])->format('d/m/Y') }}
                                    </p>
                                </div>
                                @if($deadline['status'] == 'overdue')
                                <span class="badge bg-danger">
                                    {{ abs($deadline['days_overdue']) }} jours de retard
                                </span>
                                @else
                                <span class="badge bg-warning">
                                    {{ $deadline['days_overdue'] }} jours
                                </span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center py-4">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <p class="text-muted mt-2 mb-0">Aucune échéance proche</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Upload & Traitement Automatique
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="upload-zone border-2 border-dashed rounded-3 p-5 text-center" id="uploadZone">
                    <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-3"></i>
                    <h5>Glissez-déposez votre document ici</h5>
                    <p class="text-muted">ou cliquez pour sélectionner</p>
                    <input type="file" id="documentInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                    <button class="btn btn-primary mt-3" onclick="document.getElementById('documentInput').click()">
                        Sélectionner un fichier
                    </button>
                    <p class="text-muted small mt-3 mb-0">PDF, JPG, PNG - Max 10MB</p>
                </div>

                <div class="progress mt-4 d-none" id="uploadProgress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-magic me-2"></i>
                    <strong>L'IA va automatiquement:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Scanner le document (OCR)</li>
                        <li>Extraire les données (montants, dates, TVA)</li>
                        <li>Déterminer les comptes PCMN appropriés</li>
                        <li>Générer l'écriture comptable</li>
                        <li>Valider si confiance >= 90%</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Upload document
document.getElementById('documentInput')?.addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('document', file);
    formData.append('type', 'invoice');

    const progressBar = document.querySelector('#uploadProgress .progress-bar');
    document.getElementById('uploadProgress').classList.remove('d-none');

    try {
        ComptaPro.Loading.show('Traitement du document par l\'IA...');

        const response = await fetch('{{ route('belgium.automation.upload') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const result = await response.json();

        if (result.success) {
            ComptaPro.Toast.success(
                'Document traité !',
                `Confiance IA: ${result.data.confidence}% - ${result.data.auto_validated ? 'Auto-validé' : 'En attente'}`
            );
            setTimeout(() => location.reload(), 2000);
        } else {
            ComptaPro.Toast.error('Erreur', result.error);
        }
    } catch (error) {
        ComptaPro.Toast.error('Erreur', 'Impossible de traiter le document');
    } finally {
        ComptaPro.Loading.hide();
        document.getElementById('uploadProgress').classList.add('d-none');
    }
});

// Générer déclaration TVA
async function generateVATDeclaration() {
    const year = new Date().getFullYear();
    const month = new Date().getMonth() + 1;

    if (!confirm(`Générer la déclaration TVA pour ${month}/${year} ?`)) return;

    try {
        ComptaPro.Loading.show('Génération de la déclaration TVA...');

        const response = await ComptaPro.API.post('/belgium/vat/generate', {
            year: year,
            month: month,
            period_type: 'monthly'
        });

        if (response.success) {
            ComptaPro.Toast.success(
                'TVA générée !',
                `Montant à payer: ${response.data.declaration.vat_to_pay}€`
            );
            setTimeout(() => location.href = '{{ route('belgium.vat.index') }}', 2000);
        }
    } catch (error) {
        ComptaPro.Toast.error('Erreur', 'Impossible de générer la déclaration');
    } finally {
        ComptaPro.Loading.hide();
    }
}

// Générer paie
function generatePayroll() {
    location.href = '{{ route('belgium.payroll.index') }}';
}

// Générer rapports
function generateReports() {
    location.href = '{{ route('belgium.reports.balance-sheet') }}';
}
</script>
@endpush
@endsection
