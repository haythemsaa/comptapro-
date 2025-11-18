<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Rapport TVA</h2>
                <Link :href="route('reports.index')" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form @submit.prevent="applyFilters" class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Du</label>
                                <input v-model="filters.from_date" type="date" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Au</label>
                                <input v-model="filters.to_date" type="date" class="form-control">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    Actualiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- TVA collectée -->
                        <div class="card mb-4">
                            <div class="card-header bg-success bg-opacity-10">
                                <h5 class="card-title mb-0 text-success">
                                    <i class="bi bi-arrow-down-circle me-2"></i>TVA Collectée (Ventes)
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Taux TVA</th>
                                            <th class="text-end">Base HT</th>
                                            <th class="text-end">TVA</th>
                                            <th class="text-end">Total TTC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="vat in vatByRate" :key="vat.rate">
                                            <td>{{ vat.rate }}%</td>
                                            <td class="text-end">{{ formatCurrency(vat.base) }}</td>
                                            <td class="text-end">{{ formatCurrency(vat.vat) }}</td>
                                            <td class="text-end">{{ formatCurrency(vat.base + vat.vat) }}</td>
                                        </tr>
                                        <tr class="fw-bold border-top">
                                            <td>TOTAL</td>
                                            <td class="text-end">{{ formatCurrency(salesTotal) }}</td>
                                            <td class="text-end">{{ formatCurrency(vatCollected) }}</td>
                                            <td class="text-end">{{ formatCurrency(salesTotal + vatCollected) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TVA déductible -->
                        <div class="card mb-4">
                            <div class="card-header bg-info bg-opacity-10">
                                <h5 class="card-title mb-0 text-info">
                                    <i class="bi bi-arrow-up-circle me-2"></i>TVA Déductible (Achats)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Module Achats à venir. TVA déductible = {{ formatCurrency(vatPaid) }}
                                </div>
                            </div>
                        </div>

                        <!-- TVA à payer -->
                        <div class="card">
                            <div class="card-body" :class="vatDue > 0 ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10'">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">
                                        {{ vatDue > 0 ? 'TVA À PAYER' : 'TVA À RÉCUPÉRER' }}
                                    </h4>
                                    <h3 class="mb-0" :class="vatDue > 0 ? 'text-warning' : 'text-success'">
                                        {{ formatCurrency(Math.abs(vatDue)) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Sidebar -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Résumé TVA</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Période</small>
                                    <p class="mb-0">{{ formatDate(fromDate) }} au {{ formatDate(toDate) }}</p>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>TVA Collectée</span>
                                        <strong class="text-success">{{ formatCurrency(vatCollected) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>TVA Déductible</span>
                                        <strong class="text-info">{{ formatCurrency(vatPaid) }}</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>TVA Nette</strong>
                                        <strong :class="vatDue > 0 ? 'text-warning' : 'text-success'">
                                            {{ formatCurrency(vatDue) }}
                                        </strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Statistiques</small>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Ventes HT</span>
                                        <span class="small">{{ formatCurrency(salesTotal) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small">Achats HT</span>
                                        <span class="small">{{ formatCurrency(purchasesTotal) }}</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="small text-muted">
                                    <p class="mb-1"><i class="bi bi-building me-2"></i>{{ company.name }}</p>
                                    <p class="mb-1"><i class="bi bi-geo-alt me-2"></i>{{ company.country.name }}</p>
                                    <p class="mb-0"><i class="bi bi-percent me-2"></i>Taux standard: {{ company.country.default_vat_rate }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    company: Object,
    fromDate: String,
    toDate: String,
    vatCollected: Number,
    vatPaid: Number,
    vatDue: Number,
    salesTotal: Number,
    purchasesTotal: Number,
    vatByRate: Array
});

const filters = reactive({
    from_date: props.fromDate,
    to_date: props.toDate
});

const applyFilters = () => {
    router.get(route('reports.vat'), filters, {
        preserveState: true
    });
};

const formatCurrency = (amount) => {
    const currency = props.company.country.currency;
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency
    }).format(amount || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};
</script>
