<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">{{ company.name }}</h2>
                <div class="d-flex gap-2">
                    <Link :href="route('companies.index')" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </Link>
                    <Link :href="route('companies.edit', company.id)" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Modifier
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Company Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations générales</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Nom commercial:</td>
                                        <td><strong>{{ company.name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Raison sociale:</td>
                                        <td>{{ company.legal_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Pays:</td>
                                        <td>{{ company.country.name }} ({{ company.country.currency }})</td>
                                    </tr>
                                    <tr v-if="company.vat_number">
                                        <td class="text-muted">Numéro de TVA:</td>
                                        <td>{{ company.vat_number }}</td>
                                    </tr>
                                    <tr v-if="company.registration_number">
                                        <td class="text-muted">Numéro d'entreprise:</td>
                                        <td>{{ company.registration_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Plan d'abonnement:</td>
                                        <td>
                                            <span :class="getSubscriptionClass(company.subscription_plan)">
                                                {{ getSubscriptionLabel(company.subscription_plan) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Contact</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Email:</td>
                                        <td><a :href="`mailto:${company.email}`">{{ company.email }}</a></td>
                                    </tr>
                                    <tr v-if="company.phone">
                                        <td class="text-muted">Téléphone:</td>
                                        <td><a :href="`tel:${company.phone}`">{{ company.phone }}</a></td>
                                    </tr>
                                    <tr v-if="company.website">
                                        <td class="text-muted">Site web:</td>
                                        <td><a :href="company.website" target="_blank">{{ company.website }}</a></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Adresse:</td>
                                        <td>
                                            {{ company.address_line1 }}<br>
                                            <span v-if="company.address_line2">{{ company.address_line2 }}<br></span>
                                            {{ company.postal_code }} {{ company.city }}
                                            <span v-if="company.state">, {{ company.state }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Users -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Utilisateurs ({{ company.users.length }})</h5>
                                <Link :href="route('companies.users', company.id)" class="btn btn-sm btn-primary">
                                    <i class="bi bi-people me-1"></i>Gérer les utilisateurs
                                </Link>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div v-for="user in company.users" :key="user.id" class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ user.name }}</h6>
                                                <small class="text-muted">{{ user.email }}</small>
                                            </div>
                                            <div>
                                                <span :class="getRoleClass(user.pivot.role)">
                                                    {{ getRoleLabel(user.pivot.role) }}
                                                </span>
                                                <span v-if="!user.pivot.is_active" class="badge bg-secondary ms-2">Inactif</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Paramètres</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Exercice fiscal:</td>
                                        <td class="text-end">Commence en {{ getMonthName(company.fiscal_year_start) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Plan comptable:</td>
                                        <td class="text-end">{{ company.country.accounting_plan }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Taux TVA défaut:</td>
                                        <td class="text-end">{{ company.country.default_vat_rate }}%</td>
                                    </tr>
                                    <tr v-if="company.subscription_expires_at">
                                        <td class="text-muted">Expire le:</td>
                                        <td class="text-end">{{ formatDate(company.subscription_expires_at) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    company: Object
});

const getSubscriptionLabel = (plan) => {
    const labels = {
        starter: 'Starter',
        professional: 'Professional',
        enterprise: 'Enterprise'
    };
    return labels[plan] || plan;
};

const getSubscriptionClass = (plan) => {
    const classes = {
        starter: 'badge bg-secondary',
        professional: 'badge bg-primary',
        enterprise: 'badge bg-success'
    };
    return classes[plan] || 'badge bg-secondary';
};

const getRoleLabel = (role) => {
    const labels = {
        admin: 'Administrateur',
        accountant: 'Comptable',
        user: 'Utilisateur'
    };
    return labels[role] || role;
};

const getRoleClass = (role) => {
    const classes = {
        admin: 'badge bg-danger',
        accountant: 'badge bg-primary',
        user: 'badge bg-secondary'
    };
    return classes[role] || 'badge bg-secondary';
};

const getMonthName = (month) => {
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return months[month - 1];
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};
</script>
