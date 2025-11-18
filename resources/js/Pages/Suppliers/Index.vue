<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Fournisseurs</h2>
                <Link :href="route('suppliers.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouveau fournisseur
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form @submit.prevent="applyFilters" class="row g-3">
                            <div class="col-md-6">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Rechercher par nom, numéro ou email..."
                                >
                            </div>
                            <div class="col-md-4">
                                <select v-model="filters.category" class="form-select">
                                    <option value="">Toutes les catégories</option>
                                    <option value="goods">Biens</option>
                                    <option value="services">Services</option>
                                    <option value="both">Les deux</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Suppliers List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Catégorie</th>
                                        <th>Délai paiement</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                                        <td><code>{{ supplier.supplier_number }}</code></td>
                                        <td>
                                            <div class="fw-bold">{{ supplier.name }}</div>
                                            <small class="text-muted" v-if="supplier.vat_number">
                                                TVA: {{ supplier.vat_number }}
                                            </small>
                                        </td>
                                        <td>
                                            <div v-if="supplier.email">
                                                <i class="bi bi-envelope me-1"></i>{{ supplier.email }}
                                            </div>
                                            <div v-if="supplier.phone">
                                                <i class="bi bi-telephone me-1"></i>{{ supplier.phone }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getCategoryClass(supplier.category)">
                                                {{ getCategoryLabel(supplier.category) }}
                                            </span>
                                        </td>
                                        <td>{{ getPaymentTermLabel(supplier.payment_term) }}</td>
                                        <td>
                                            <span class="badge" :class="supplier.is_active ? 'bg-success' : 'bg-secondary'">
                                                {{ supplier.is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <Link :href="route('suppliers.show', supplier.id)" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <Link :href="route('suppliers.edit', supplier.id)" class="btn btn-sm btn-outline-secondary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button
                                                @click="confirmDelete(supplier)"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="suppliers.links">
                            <div class="text-muted">
                                Affichage {{ suppliers.from }} à {{ suppliers.to }} sur {{ suppliers.total }} fournisseurs
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li v-for="link in suppliers.links" :key="link.label" class="page-item" :class="{ active: link.active }">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            class="page-link"
                                            v-html="link.label"
                                            preserve-scroll
                                        />
                                        <span v-else class="page-link" v-html="link.label"></span>
                                    </li>
                                </ul>
                            </nav>
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
    suppliers: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters.search || '',
    category: props.filters.category || ''
});

const applyFilters = () => {
    router.get(route('suppliers.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const getCategoryClass = (category) => {
    const classes = {
        goods: 'bg-primary',
        services: 'bg-info',
        both: 'bg-success'
    };
    return classes[category] || 'bg-secondary';
};

const getCategoryLabel = (category) => {
    const labels = {
        goods: 'Biens',
        services: 'Services',
        both: 'Les deux'
    };
    return labels[category] || category;
};

const getPaymentTermLabel = (term) => {
    const labels = {
        immediate: 'Immédiat',
        '15_days': '15 jours',
        '30_days': '30 jours',
        '45_days': '45 jours',
        '60_days': '60 jours',
        '90_days': '90 jours'
    };
    return labels[term] || term;
};

const confirmDelete = (supplier) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le fournisseur "${supplier.name}" ?`)) {
        router.delete(route('suppliers.destroy', supplier.id));
    }
};
</script>
