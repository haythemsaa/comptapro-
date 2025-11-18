<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Rapports & Analyses</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row g-4">
                    <!-- Financial Reports -->
                    <div class="col-12">
                        <h5 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i>Rapports financiers</h5>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.profit-loss')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-graph-up text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Compte de Résultat</h6>
                                        <small class="text-muted">Profit & Loss</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Analyse des revenus et dépenses sur une période
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.balance-sheet')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-bank text-success" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Bilan</h6>
                                        <small class="text-muted">Balance Sheet</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Actifs, passifs et capitaux propres
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.cash-flow')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-cash-stack text-info" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Flux de trésorerie</h6>
                                        <small class="text-muted">Cash Flow</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Mouvements de trésorerie par activité
                                </p>
                            </div>
                        </Link>
                    </div>

                    <!-- Tax Reports -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3"><i class="bi bi-receipt me-2"></i>Rapports fiscaux</h5>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.vat')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-percent text-warning" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Rapport TVA</h6>
                                        <small class="text-muted">VAT Report</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    TVA collectée, déductible et à payer
                                </p>
                            </div>
                        </Link>
                    </div>

                    <!-- Customer Reports -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3"><i class="bi bi-people me-2"></i>Rapports clients</h5>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.sales-by-customer')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-bar-chart text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Ventes par client</h6>
                                        <small class="text-muted">Sales by Customer</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Chiffre d'affaires détaillé par client
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('reports.aged-receivables')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-hourglass text-danger" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Balance âgée</h6>
                                        <small class="text-muted">Aged Receivables</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Créances classées par ancienneté
                                </p>
                            </div>
                        </Link>
                    </div>

                    <!-- Accounting Reports -->
                    <div class="col-12 mt-4">
                        <h5 class="mb-3"><i class="bi bi-journal-text me-2"></i>Rapports comptables</h5>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('accounting.general-ledger')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-book text-secondary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Grand livre</h6>
                                        <small class="text-muted">General Ledger</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Liste de tous les comptes et mouvements
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <Link :href="route('accounting.trial-balance')" class="card h-100 text-decoration-none hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-calculator text-success" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Balance générale</h6>
                                        <small class="text-muted">Trial Balance</small>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Soldes de tous les comptes
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<style scoped>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
}
</style>
