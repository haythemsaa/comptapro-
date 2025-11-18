<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">{{ supplier.name }}</h2>
                <div>
                    <Link :href="route('suppliers.edit', supplier.id)" class="btn btn-outline-primary me-2">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </Link>
                    <button @click="confirmDelete" class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Main Info -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Informations générales</h5>
                                <table class="table">
                                    <tr>
                                        <th style="width: 30%;">Numéro fournisseur</th>
                                        <td><code>{{ supplier.supplier_number }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Nom</th>
                                        <td>{{ supplier.name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catégorie</th>
                                        <td>
                                            <span class="badge" :class="getCategoryClass(supplier.category)">
                                                {{ getCategoryLabel(supplier.category) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ supplier.email || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Téléphone</th>
                                        <td>{{ supplier.phone || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Personne de contact</th>
                                        <td>{{ supplier.contact_person || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Numéro de TVA</th>
                                        <td>{{ supplier.vat_number || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>
                                            <span class="badge" :class="supplier.is_active ? 'bg-success' : 'bg-secondary'">
                                                {{ supplier.is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Adresse</h5>
                                <table class="table">
                                    <tr>
                                        <th style="width: 30%;">Adresse</th>
                                        <td>{{ supplier.address || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Code postal</th>
                                        <td>{{ supplier.postal_code || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Ville</th>
                                        <td>{{ supplier.city || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pays</th>
                                        <td>{{ supplier.country_code || '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Informations bancaires</h5>
                                <table class="table">
                                    <tr>
                                        <th style="width: 30%;">Compte bancaire</th>
                                        <td>{{ supplier.bank_account || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>IBAN</th>
                                        <td>{{ supplier.iban || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>BIC/SWIFT</th>
                                        <td>{{ supplier.bic || '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div v-if="supplier.notes" class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Notes</h5>
                                <p class="mb-0">{{ supplier.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Statistiques</h5>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Factures totales</small>
                                    <div class="h4 mb-0">{{ stats.total_invoices }}</div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Total dépensé</small>
                                    <div class="h4 mb-0">{{ formatCurrency(stats.total_spent) }}</div>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Montant en attente</small>
                                    <div class="h4 mb-0 text-danger">{{ formatCurrency(stats.pending_amount) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Conditions</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Délai de paiement</th>
                                        <td>{{ getPaymentTermLabel(supplier.payment_term) }}</td>
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
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    supplier: Object,
    stats: Object
});

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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount || 0);
};

const confirmDelete = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le fournisseur "${props.supplier.name}" ?`)) {
        router.delete(route('suppliers.destroy', props.supplier.id));
    }
};
</script>
