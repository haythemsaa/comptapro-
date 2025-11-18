<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Sociétés</h2>
                <Link :href="route('companies.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle société
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row g-4">
                    <div v-for="company in companies" :key="company.id" class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1">{{ company.name }}</h5>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-geo-alt me-1"></i>{{ company.country.name }}
                                            <span class="ms-2">{{ company.country.currency }}</span>
                                        </p>
                                    </div>
                                    <span :class="getSubscriptionClass(company.subscription_plan)">
                                        {{ getSubscriptionLabel(company.subscription_plan) }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <i class="bi bi-envelope text-muted me-1"></i>{{ company.email }}
                                        </div>
                                        <div class="col-6" v-if="company.phone">
                                            <i class="bi bi-telephone text-muted me-1"></i>{{ company.phone }}
                                        </div>
                                        <div class="col-12" v-if="company.vat_number">
                                            <i class="bi bi-receipt text-muted me-1"></i>TVA: {{ company.vat_number }}
                                        </div>
                                        <div class="col-12">
                                            <i class="bi bi-people text-muted me-1"></i>{{ company.users_count }} utilisateur(s)
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <Link :href="route('companies.show', company.id)" class="btn btn-sm btn-outline-primary flex-fill">
                                        <i class="bi bi-eye me-1"></i>Voir
                                    </Link>
                                    <Link :href="route('companies.edit', company.id)" class="btn btn-sm btn-outline-secondary flex-fill">
                                        <i class="bi bi-pencil me-1"></i>Modifier
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="companies.length === 0" class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-building" style="font-size: 3rem;"></i>
                                <p class="mt-3">Aucune société</p>
                                <Link :href="route('companies.create')" class="btn btn-primary">
                                    Créer votre première société
                                </Link>
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
    companies: Array
});

const getSubscriptionLabel = (plan) => {
    const labels = {
        starter: 'Starter',
        professional: 'Pro',
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
</script>
