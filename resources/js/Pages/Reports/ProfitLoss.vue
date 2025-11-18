<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Compte de Résultat</h2>
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
                        <!-- Revenue Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-success bg-opacity-10">
                                <h5 class="card-title mb-0 text-success">
                                    <i class="bi bi-arrow-down-circle me-2"></i>Produits
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr v-for="account in revenueAccounts" :key="account.id">
                                            <td width="20%"><code>{{ account.code }}</code></td>
                                            <td>{{ account.name }}</td>
                                            <td class="text-end" width="25%">
                                                {{ formatCurrency(calculateAccountBalance(account)) }}
                                            </td>
                                        </tr>
                                        <tr class="fw-bold border-top">
                                            <td colspan="2">TOTAL PRODUITS</td>
                                            <td class="text-end">{{ formatCurrency(totalRevenue) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-danger bg-opacity-10">
                                <h5 class="card-title mb-0 text-danger">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Charges
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr v-for="account in expenseAccounts" :key="account.id">
                                            <td width="20%"><code>{{ account.code }}</code></td>
                                            <td>{{ account.name }}</td>
                                            <td class="text-end" width="25%">
                                                {{ formatCurrency(calculateAccountBalance(account)) }}
                                            </td>
                                        </tr>
                                        <tr class="fw-bold border-top">
                                            <td colspan="2">TOTAL CHARGES</td>
                                            <td class="text-end">{{ formatCurrency(totalExpenses) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Net Income -->
                        <div class="card">
                            <div class="card-body" :class="netIncome >= 0 ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0" :class="netIncome >= 0 ? 'text-success' : 'text-danger'">
                                        {{ netIncome >= 0 ? 'BÉNÉFICE NET' : 'PERTE NETTE' }}
                                    </h4>
                                    <h3 class="mb-0" :class="netIncome >= 0 ? 'text-success' : 'text-danger'">
                                        {{ formatCurrency(Math.abs(netIncome)) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Sidebar -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Résumé</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Période</small>
                                    <p class="mb-0">{{ formatDate(fromDate) }} au {{ formatDate(toDate) }}</p>
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-success">Total Produits</span>
                                        <strong class="text-success">{{ formatCurrency(totalRevenue) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-danger">Total Charges</span>
                                        <strong class="text-danger">{{ formatCurrency(totalExpenses) }}</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong :class="netIncome >= 0 ? 'text-success' : 'text-danger'">
                                            Résultat Net
                                        </strong>
                                        <strong :class="netIncome >= 0 ? 'text-success' : 'text-danger'">
                                            {{ formatCurrency(netIncome) }}
                                        </strong>
                                    </div>
                                </div>
                                <hr>
                                <div class="small text-muted">
                                    <p class="mb-1"><i class="bi bi-building me-2"></i>{{ company.name }}</p>
                                    <p class="mb-0"><i class="bi bi-cash me-2"></i>{{ company.country.currency }}</p>
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
    revenueAccounts: Array,
    expenseAccounts: Array,
    totalRevenue: Number,
    totalExpenses: Number,
    netIncome: Number,
    fromDate: String,
    toDate: String
});

const filters = reactive({
    from_date: props.fromDate,
    to_date: props.toDate
});

const applyFilters = () => {
    router.get(route('reports.profit-loss'), filters, {
        preserveState: true
    });
};

const calculateAccountBalance = (account) => {
    const credits = account.credit_lines?.reduce((sum, line) => sum + parseFloat(line.amount), 0) || 0;
    const debits = account.debit_lines?.reduce((sum, line) => sum + parseFloat(line.amount), 0) || 0;
    return account.type === 'revenue' ? credits - debits : debits - credits;
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
