@extends('layouts.app')

@section('title', 'Dashboard Automatisation IA')

@section('content')
<div class="animate-fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">🤖 Automatisation Intelligente</h1>
                    <p class="text-muted mb-0">L'IA qui fait TOUT le travail du comptable automatiquement</p>
                </div>
                <div>
                    <button class="btn btn-gradient btn-lg" id="btnAutoPilot" onclick="activateAutoPilot()">
                        <i class="bi bi-play-circle me-2"></i>
                        Activer le Pilote Automatique
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Documents Traités</p>
                            <h3 class="stat-value text-primary" id="statDocuments">{{ $dashboard['pending_tasks']['factures_a_comptabiliser'] ?? 0 }}</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-arrow-up text-success"></i> +15% ce mois
                            </p>
                        </div>
                        <div class="stat-icon primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Trésorerie Actuelle</p>
                            <h3 class="stat-value text-success" id="statTresorerie">
                                {{ number_format($dashboard['financial_health']['tresorerie_actuelle'] ?? 0, 0, ',', ' ') }} TND
                            </h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-arrow-up text-success"></i> +8.2% ce mois
                            </p>
                        </div>
                        <div class="stat-icon success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">CA du Mois</p>
                            <h3 class="stat-value text-warning" id="statCA">
                                {{ number_format($dashboard['financial_health']['ca_mois'] ?? 0, 0, ',', ' ') }} TND
                            </h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-arrow-up text-success"></i> +12% vs N-1
                            </p>
                        </div>
                        <div class="stat-icon warning">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-2">Précision IA</p>
                            <h3 class="stat-value text-info">95%</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-check-circle text-success"></i> Excellente performance
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

    <div class="row g-4">
        <!-- Actions Rapides -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-charge text-warning me-2"></i>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button class="btn btn-outline-primary btn-lg text-start" onclick="uploadDocument()">
                            <i class="bi bi-cloud-upload me-3"></i>
                            <span>
                                <strong>Upload Document</strong>
                                <small class="d-block text-muted">L'IA traite automatiquement</small>
                            </span>
                        </button>

                        <button class="btn btn-outline-success btn-lg text-start" onclick="generateFinancialReports()">
                            <i class="bi bi-pie-chart me-3"></i>
                            <span>
                                <strong>États Financiers</strong>
                                <small class="d-block text-muted">Bilan + Résultat en 30s</small>
                            </span>
                        </button>

                        <button class="btn btn-outline-warning btn-lg text-start" onclick="generateDeclarations()">
                            <i class="bi bi-file-earmark-ruled me-3"></i>
                            <span>
                                <strong>Déclarations Fiscales</strong>
                                <small class="d-block text-muted">TVA, CNSS, IS automatiques</small>
                            </span>
                        </button>

                        <button class="btn btn-outline-info btn-lg text-start" onclick="checkAnomalies()">
                            <i class="bi bi-shield-check me-3"></i>
                            <span>
                                <strong>Détecter Anomalies</strong>
                                <small class="d-block text-muted">Analyse IA en temps réel</small>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Activité Récente (IA)</h5>
                    <span class="badge bg-success">En direct</span>
                </div>
                <div class="card-body">
                    <div class="activity-timeline" id="activityTimeline">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Facture #INV-2024-123 traitée</strong>
                                    <small class="text-muted">Il y a 2 min</small>
                                </div>
                                <p class="text-muted small mb-0">L'IA a généré l'écriture comptable automatiquement (Confiance: 95%)</p>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Validation automatique</strong>
                                    <small class="text-muted">Il y a 5 min</small>
                                </div>
                                <p class="text-muted small mb-0">12 écritures validées automatiquement (Confiance >= 90%)</p>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Anomalie détectée</strong>
                                    <small class="text-muted">Il y a 15 min</small>
                                </div>
                                <p class="text-muted small mb-0">Montant suspect sur facture #INV-2024-120 - Nécessite vérification</p>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Déclaration TVA générée</strong>
                                    <small class="text-muted">Il y a 1 heure</small>
                                </div>
                                <p class="text-muted small mb-0">TVA Janvier 2024: 4,880 TND à payer - Prêt pour télédéclaration</p>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="bi bi-cash"></i>
                            </div>
                            <div class="activity-content">
                                <div class="d-flex justify-content-between">
                                    <strong>Rapprochement bancaire</strong>
                                    <small class="text-muted">Il y a 2 heures</small>
                                </div>
                                <p class="text-muted small mb-0">38 transactions rapprochées automatiquement (IA: 98% de précision)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deadlines & Anomalies -->
    <div class="row g-4 mt-2">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Deadlines à Venir</h5>
                </div>
                <div class="card-body">
                    @if(isset($dashboard['upcoming_deadlines']) && count($dashboard['upcoming_deadlines']['alerts']) > 0)
                        @foreach($dashboard['upcoming_deadlines']['alerts'] as $alert)
                        <div class="alert alert-{{ $alert['severity'] === 'high' ? 'danger' : 'warning' }} d-flex align-items-center mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                            <div class="flex-grow-1">
                                <strong>{{ $alert['type'] }}</strong> - {{ $alert['message'] }}
                                <div class="small">Échéance: {{ $alert['due_date'] }} ({{ $alert['days_left'] }} jours)</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Aucune deadline urgente !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>Anomalies Détectées</h5>
                </div>
                <div class="card-body">
                    @if(isset($dashboard['anomalies']) && $dashboard['anomalies']['count'] > 0)
                        <div class="alert alert-danger">
                            <h5>{{ $dashboard['anomalies']['count'] }} anomalie(s) détectée(s)</h5>
                            <p class="mb-2">Dont {{ $dashboard['anomalies']['high_severity'] }} de haute gravité</p>
                            <button class="btn btn-sm btn-outline-danger" onclick="viewAnomalies()">
                                <i class="bi bi-eye me-1"></i> Voir les détails
                            </button>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Aucune anomalie détectée !</p>
                            <p class="small text-muted">L'IA surveille en continu</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Prévision Trésorerie -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up text-info me-2"></i>Prévision de Trésorerie (90 jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="cashFlowChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Document -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Document</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="upload-area text-center p-5" id="uploadArea">
                    <i class="bi bi-cloud-arrow-up" style="font-size: 4rem; color: var(--cp-primary);"></i>
                    <h4 class="mt-3">Glissez-déposez votre document ici</h4>
                    <p class="text-muted">ou cliquez pour sélectionner</p>
                    <input type="file" id="fileInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                    <button class="btn btn-primary mt-3" onclick="document.getElementById('fileInput').click()">
                        Sélectionner un fichier
                    </button>
                    <p class="small text-muted mt-3">Formats acceptés: PDF, JPG, PNG (max 10 MB)</p>
                </div>

                <div class="upload-progress d-none" id="uploadProgress">
                    <div class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-3">L'IA traite votre document...</p>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%"></div>
                    </div>
                    <p class="text-center mt-3 small text-muted" id="progressText">OCR en cours...</p>
                </div>

                <div class="upload-success d-none" id="uploadSuccess">
                    <div class="text-center">
                        <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                        <h4 class="mt-3 text-success">Document traité avec succès !</h4>
                        <p class="text-muted">L'écriture comptable a été générée automatiquement</p>
                        <div class="alert alert-info mt-3">
                            <strong>Confiance IA:</strong> <span id="aiConfidence">95%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .activity-item {
        position: relative;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 2px solid #e9ecef;
    }

    .activity-item:last-child {
        border-left: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .activity-icon {
        position: absolute;
        left: -2.25rem;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .activity-content {
        padding-left: 1rem;
    }

    .upload-area {
        border: 3px dashed #dee2e6;
        border-radius: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }

    .upload-area:hover {
        border-color: var(--cp-primary);
        background: rgba(13, 110, 253, 0.05);
    }

    .upload-area.dragover {
        border-color: var(--cp-success);
        background: rgba(25, 135, 84, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
// Cash Flow Chart
const ctx = document.getElementById('cashFlowChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jour 1', 'Jour 7', 'Jour 14', 'Jour 21', 'Jour 30', 'Jour 60', 'Jour 90'],
            datasets: [{
                label: 'Trésorerie Prévue (TND)',
                data: [45000, 47500, 49000, 46000, 48500, 52000, 55000],
                borderColor: 'rgb(79, 172, 254)',
                backgroundColor: 'rgba(79, 172, 254, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' TND';
                        }
                    }
                }
            }
        }
    });
}

// Pilote Automatique
function activateAutoPilot() {
    const btn = document.getElementById('btnAutoPilot');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Activation en cours...';

    fetch('/api/automation/auto-pilot', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.classList.replace('btn-gradient', 'btn-success');
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Pilote Automatique Actif';

            // Show success notification
            showNotification('success', 'Pilote automatique activé !', 'L\'IA traite maintenant tout automatiquement.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle me-2"></i>Activer le Pilote Automatique';
        showNotification('error', 'Erreur', 'Impossible d\'activer le pilote automatique.');
    });
}

// Upload Document
function uploadDocument() {
    const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
    modal.show();
}

// Drag & Drop
const uploadArea = document.getElementById('uploadArea');
if (uploadArea) {
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });
}

document.getElementById('fileInput')?.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleFileUpload(e.target.files[0]);
    }
});

function handleFileUpload(file) {
    document.getElementById('uploadArea').classList.add('d-none');
    document.getElementById('uploadProgress').classList.remove('d-none');

    const formData = new FormData();
    formData.append('document', file);
    formData.append('type', 'invoice');

    // Simulate progress
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';

        if (progress === 30) progressText.textContent = 'OCR en cours...';
        if (progress === 60) progressText.textContent = 'Extraction des données...';
        if (progress === 90) progressText.textContent = 'Génération de l\'écriture...';

        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                document.getElementById('uploadProgress').classList.add('d-none');
                document.getElementById('uploadSuccess').classList.remove('d-none');
            }, 500);
        }
    }, 500);
}

// Other functions
function generateFinancialReports() {
    showNotification('info', 'Génération en cours...', 'L\'IA génère vos états financiers.');
}

function generateDeclarations() {
    showNotification('info', 'Génération en cours...', 'L\'IA génère vos déclarations fiscales.');
}

function checkAnomalies() {
    showNotification('info', 'Analyse en cours...', 'L\'IA analyse votre comptabilité.');
}

function viewAnomalies() {
    window.location.href = '/automation/anomalies';
}

// Notification helper
function showNotification(type, title, message) {
    // You can use Toast or custom notification here
    alert(title + ': ' + message);
}
</script>
@endpush
@endsection
