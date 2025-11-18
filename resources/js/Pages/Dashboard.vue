<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    companies: Array,
    currentCompany: Object,
    stats: Object,
});

// Format currency based on company country
const formatCurrency = (amount) => {
    if (!props.currentCompany) return amount;

    const currency = props.currentCompany.country.currency;
    const formatter = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency,
    });
    return formatter.format(amount);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">
                    Dashboard - ComptaPro SaaS
                </h2>

                <!-- Company Selector -->
                <div v-if="companies.length > 1" class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        {{ currentCompany?.name || 'Sélectionner une société' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li v-for="company in companies" :key="company.id">
                            <a class="dropdown-item" :class="{'active': company.id === currentCompany?.id}"
                               :href="route('company.switch', company.id)" method="post">
                                {{ company.name }}
                                <span class="badge bg-secondary ms-2">{{ company.country.code }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <div class="container-fluid py-4">
            <!-- Current Company Info -->
            <div v-if="currentCompany" class="row mb-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-1">{{ currentCompany.name }}</h5>
                                    <p class="text-muted mb-2">{{ currentCompany.legal_name }}</p>
                                    <div class="d-flex gap-3">
                                        <span class="badge bg-primary">{{ currentCompany.country.name }}</span>
                                        <span class="badge bg-info">{{ currentCompany.subscription_plan }}</span>
                                        <span class="badge bg-success">{{ currentCompany.country.currency }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <p class="mb-1"><strong>TVA:</strong> {{ currentCompany.vat_number }}</p>
                                    <p class="mb-1"><strong>Plan Comptable:</strong> {{ currentCompany.country.accounting_plan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div v-if="stats" class="row g-4 mb-4">
                <!-- Total Customers -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Clients</p>
                                    <h3 class="mb-0">{{ stats.total_customers }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people fs-4 text-primary"></i>
                                </div>
                            </div>
                            <Link :href="route('customers.index')" class="btn btn-sm btn-outline-primary mt-3">
                                Voir les clients
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Factures</p>
                                    <h3 class="mb-0">{{ stats.total_invoices }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-receipt fs-4 text-success"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-success">✓ {{ stats.paid_invoices }} payées</small><br>
                                <small class="text-warning">○ {{ stats.draft_invoices }} brouillons</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Chiffre d'affaires</p>
                                    <h3 class="mb-0">{{ formatCurrency(stats.total_revenue) }}</h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-graph-up fs-4 text-info"></i>
                                </div>
                            </div>
                            <p class="text-muted mb-0 mt-2">
                                <small>Factures payées</small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Amount -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">En attente</p>
                                    <h3 class="mb-0">{{ formatCurrency(stats.pending_amount) }}</h3>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                                </div>
                            </div>
                            <p class="text-danger mb-0 mt-2" v-if="stats.overdue_invoices > 0">
                                <small>{{ stats.overdue_invoices }} facture(s) en retard</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row g-4">
                <!-- Recent Customers -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Clients récents</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="stats.recent_customers && stats.recent_customers.length > 0">
                                <div v-for="customer in stats.recent_customers" :key="customer.id"
                                     class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                    <div>
                                        <h6 class="mb-1">{{ customer.name }}</h6>
                                        <small class="text-muted">{{ customer.email }}</small>
                                    </div>
                                    <span class="badge bg-secondary">{{ customer.customer_number }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted text-center py-4">
                                Aucun client enregistré
                            </p>
                            <Link :href="route('customers.create')" class="btn btn-primary w-100 mt-3">
                                <i class="bi bi-plus-circle"></i> Nouveau client
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Factures récentes</h5>
                        </div>
                        <div class="card-body">
                            <div v-if="stats.recent_invoices && stats.recent_invoices.length > 0">
                                <div v-for="invoice in stats.recent_invoices" :key="invoice.id"
                                     class="d-flex justify-content-between align-items-center py-3 border-bottom">
                                    <div>
                                        <h6 class="mb-1">{{ invoice.invoice_number }}</h6>
                                        <small class="text-muted">{{ invoice.customer.name }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ formatCurrency(invoice.total) }}</div>
                                        <span :class="{
                                            'badge': true,
                                            'bg-secondary': invoice.status === 'draft',
                                            'bg-primary': invoice.status === 'sent',
                                            'bg-success': invoice.status === 'paid',
                                            'bg-danger': invoice.status === 'overdue'
                                        }">{{ invoice.status }}</span>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-muted text-center py-4">
                                Aucune facture créée
                            </p>
                            <Link :href="route('invoices.create')" class="btn btn-success w-100 mt-3">
                                <i class="bi bi-file-earmark-plus"></i> Nouvelle facture
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Company Message -->
            <div v-if="!currentCompany" class="row">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">Aucune société sélectionnée</h4>
                        <p>Vous devez être associé à au moins une société pour utiliser ComptaPro SaaS.</p>
                        <hr>
                        <p class="mb-0">Contactez votre administrateur pour obtenir l'accès à une société.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
