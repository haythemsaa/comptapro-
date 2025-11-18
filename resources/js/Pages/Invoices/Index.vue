<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Factures</h2>
                <Link :href="route('invoices.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle facture
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form @submit.prevent="applyFilters" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Recherche</label>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Numéro de facture, client..."
                                >
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select v-model="filters.type" class="form-select">
                                    <option value="">Tous les types</option>
                                    <option value="quote">Devis</option>
                                    <option value="invoice">Facture</option>
                                    <option value="credit_note">Avoir</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select v-model="filters.status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="draft">Brouillon</option>
                                    <option value="sent">Envoyée</option>
                                    <option value="paid">Payée</option>
                                    <option value="overdue">En retard</option>
                                    <option value="cancelled">Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-secondary w-100">
                                    <i class="bi bi-funnel me-2"></i>Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="card">
                    <div class="card-body">
                        <div v-if="invoices.data && invoices.data.length > 0" class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Type</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th class="text-end">Montant HT</th>
                                        <th class="text-end">TVA</th>
                                        <th class="text-end">Total TTC</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                                        <td>
                                            <Link :href="route('invoices.show', invoice.id)" class="text-decoration-none fw-semibold">
                                                {{ invoice.invoice_number }}
                                            </Link>
                                        </td>
                                        <td>
                                            <span :class="getTypeClass(invoice.type)">
                                                {{ getTypeLabel(invoice.type) }}
                                            </span>
                                        </td>
                                        <td>{{ invoice.customer?.name }}</td>
                                        <td>{{ formatDate(invoice.issue_date) }}</td>
                                        <td>{{ formatDate(invoice.due_date) }}</td>
                                        <td class="text-end">{{ formatCurrency(invoice.subtotal) }}</td>
                                        <td class="text-end">{{ formatCurrency(invoice.tax_amount) }}</td>
                                        <td class="text-end fw-bold">{{ formatCurrency(invoice.total) }}</td>
                                        <td>
                                            <span :class="getStatusClass(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <Link :href="route('invoices.show', invoice.id)" class="dropdown-item">
                                                            <i class="bi bi-eye me-2"></i>Voir
                                                        </Link>
                                                    </li>
                                                    <li v-if="invoice.status === 'draft'">
                                                        <Link :href="route('invoices.edit', invoice.id)" class="dropdown-item">
                                                            <i class="bi bi-pencil me-2"></i>Modifier
                                                        </Link>
                                                    </li>
                                                    <li v-if="invoice.status === 'draft'">
                                                        <a href="#" @click.prevent="markAsSent(invoice)" class="dropdown-item">
                                                            <i class="bi bi-send me-2"></i>Marquer envoyée
                                                        </a>
                                                    </li>
                                                    <li v-if="invoice.status === 'sent' || invoice.status === 'overdue'">
                                                        <a href="#" @click.prevent="showPaymentModal(invoice)" class="dropdown-item">
                                                            <i class="bi bi-cash me-2"></i>Enregistrer paiement
                                                        </a>
                                                    </li>
                                                    <li v-if="invoice.status === 'draft'">
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li v-if="invoice.status === 'draft'">
                                                        <a href="#" @click.prevent="deleteInvoice(invoice)" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i>Supprimer
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Aucune facture trouvée</p>
                            <Link :href="route('invoices.create')" class="btn btn-primary">
                                Créer votre première facture
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <nav v-if="invoices.data && invoices.data.length > 0" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: !invoices.prev_page_url }">
                                    <Link class="page-link" :href="invoices.prev_page_url || '#'" preserve-scroll>
                                        Précédent
                                    </Link>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">Page {{ invoices.current_page }} / {{ invoices.last_page }}</span>
                                </li>
                                <li class="page-item" :class="{ disabled: !invoices.next_page_url }">
                                    <Link class="page-link" :href="invoices.next_page_url || '#'" preserve-scroll>
                                        Suivant
                                    </Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Enregistrer un paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="recordPayment">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Date de paiement</label>
                                <input v-model="paymentForm.payment_date" type="date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant</label>
                                <input v-model="paymentForm.amount" type="number" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    invoices: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    status: props.filters?.status || ''
});

const paymentForm = reactive({
    payment_date: new Date().toISOString().split('T')[0],
    amount: 0
});

let currentInvoice = null;
let paymentModal = null;

const applyFilters = () => {
    router.get(route('invoices.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount || 0);
};

const getTypeLabel = (type) => {
    const labels = {
        quote: 'Devis',
        invoice: 'Facture',
        credit_note: 'Avoir'
    };
    return labels[type] || type;
};

const getTypeClass = (type) => {
    const classes = {
        quote: 'badge bg-info',
        invoice: 'badge bg-primary',
        credit_note: 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Brouillon',
        sent: 'Envoyée',
        paid: 'Payée',
        overdue: 'En retard',
        cancelled: 'Annulée'
    };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = {
        draft: 'badge bg-secondary',
        sent: 'badge bg-primary',
        paid: 'badge bg-success',
        overdue: 'badge bg-danger',
        cancelled: 'badge bg-dark'
    };
    return classes[status] || 'badge bg-secondary';
};

const markAsSent = (invoice) => {
    if (confirm('Marquer cette facture comme envoyée ?')) {
        router.post(route('invoices.mark-sent', invoice.id), {}, {
            preserveScroll: true
        });
    }
};

const showPaymentModal = (invoice) => {
    currentInvoice = invoice;
    paymentForm.amount = invoice.total - invoice.paid_amount;

    if (!paymentModal) {
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    }
    paymentModal.show();
};

const recordPayment = () => {
    router.post(route('invoices.mark-paid', currentInvoice.id), paymentForm, {
        preserveScroll: true,
        onSuccess: () => {
            paymentModal.hide();
        }
    });
};

const deleteInvoice = (invoice) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la facture ${invoice.invoice_number} ?`)) {
        router.delete(route('invoices.destroy', invoice.id), {
            preserveScroll: true
        });
    }
};
</script>
