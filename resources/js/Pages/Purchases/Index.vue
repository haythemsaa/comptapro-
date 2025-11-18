<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Factures d'achat</h2>
                <div>
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload me-2"></i>Télécharger (OCR)
                    </button>
                    <Link :href="route('purchases.create')" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Nouvelle facture
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form @submit.prevent="applyFilters" class="row g-3">
                            <div class="col-md-4">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="N° facture, fournisseur..."
                                >
                            </div>
                            <div class="col-md-3">
                                <select v-model="filters.status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="draft">Brouillon</option>
                                    <option value="received">Reçue</option>
                                    <option value="approved">Approuvée</option>
                                    <option value="paid">Payée</option>
                                    <option value="cancelled">Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select v-model="filters.type" class="form-select">
                                    <option value="">Tous les types</option>
                                    <option value="purchase">Facture d'achat</option>
                                    <option value="credit_note">Avoir</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invoices List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Fournisseur</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th>Montant TTC</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                                        <td>
                                            <div><code>{{ invoice.invoice_number }}</code></div>
                                            <small class="text-muted" v-if="invoice.supplier_invoice_number">
                                                Réf: {{ invoice.supplier_invoice_number }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ invoice.supplier.name }}</div>
                                            <small class="text-muted">{{ invoice.supplier.supplier_number }}</small>
                                        </td>
                                        <td>
                                            <span class="badge" :class="getTypeClass(invoice.type)">
                                                {{ getTypeLabel(invoice.type) }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(invoice.invoice_date) }}</td>
                                        <td>{{ formatDate(invoice.due_date) }}</td>
                                        <td class="fw-bold">{{ formatCurrency(invoice.total) }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusClass(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <Link :href="route('purchases.show', invoice.id)" class="btn btn-sm btn-outline-primary">
                                                    Voir
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                                    data-bs-toggle="dropdown"
                                                >
                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li v-if="invoice.status === 'draft'">
                                                        <Link :href="route('purchases.edit', invoice.id)" class="dropdown-item">
                                                            <i class="bi bi-pencil me-2"></i>Modifier
                                                        </Link>
                                                    </li>
                                                    <li v-if="invoice.status === 'draft'">
                                                        <button @click="markReceived(invoice)" class="dropdown-item">
                                                            <i class="bi bi-check-circle me-2"></i>Marquer reçue
                                                        </button>
                                                    </li>
                                                    <li v-if="invoice.status === 'received'">
                                                        <button @click="approve(invoice)" class="dropdown-item">
                                                            <i class="bi bi-check-circle me-2"></i>Approuver
                                                        </button>
                                                    </li>
                                                    <li v-if="['approved', 'received'].includes(invoice.status)">
                                                        <button @click="showPaymentModal(invoice)" class="dropdown-item">
                                                            <i class="bi bi-cash me-2"></i>Paiement
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li v-if="invoice.status !== 'paid'">
                                                        <button @click="confirmDelete(invoice)" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i>Supprimer
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3" v-if="invoices.links">
                            <div class="text-muted">
                                Affichage {{ invoices.from }} à {{ invoices.to }} sur {{ invoices.total }} factures
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li v-for="link in invoices.links" :key="link.label" class="page-item" :class="{ active: link.active }">
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

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Enregistrer un paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="recordPayment">
                            <div class="mb-3">
                                <label class="form-label">Montant</label>
                                <input v-model.number="paymentForm.amount" type="number" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date de paiement</label>
                                <input v-model="paymentForm.payment_date" type="date" class="form-control" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" @click="recordPayment" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload OCR Modal -->
        <div class="modal fade" id="uploadModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Télécharger une facture (OCR)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="uploadWithOcr">
                            <div class="mb-3">
                                <label class="form-label">Fournisseur</label>
                                <select v-model="uploadForm.supplier_id" class="form-select" required>
                                    <option value="">Sélectionner...</option>
                                    <!-- Options would be populated from suppliers -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fichier (PDF, JPG, PNG)</label>
                                <input @change="handleFileUpload" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control" required>
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                L'OCR extraira automatiquement les données de la facture.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" @click="uploadWithOcr" class="btn btn-primary">Télécharger</button>
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
    invoices: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    type: props.filters.type || ''
});

const paymentForm = reactive({
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0]
});

const uploadForm = reactive({
    supplier_id: '',
    file: null
});

let currentInvoice = null;
let paymentModal = null;

const applyFilters = () => {
    router.get(route('purchases.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const getTypeClass = (type) => {
    return type === 'credit_note' ? 'bg-warning' : 'bg-primary';
};

const getTypeLabel = (type) => {
    const labels = {
        purchase: 'Facture',
        credit_note: 'Avoir'
    };
    return labels[type] || type;
};

const getStatusClass = (status) => {
    const classes = {
        draft: 'bg-secondary',
        received: 'bg-info',
        approved: 'bg-primary',
        paid: 'bg-success',
        cancelled: 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Brouillon',
        received: 'Reçue',
        approved: 'Approuvée',
        paid: 'Payée',
        cancelled: 'Annulée'
    };
    return labels[status] || status;
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

const markReceived = (invoice) => {
    router.post(route('purchases.mark-received', invoice.id), {}, {
        preserveScroll: true
    });
};

const approve = (invoice) => {
    router.post(route('purchases.approve', invoice.id), {}, {
        preserveScroll: true
    });
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
    router.post(route('purchases.mark-paid', currentInvoice.id), paymentForm, {
        preserveScroll: true,
        onSuccess: () => {
            paymentModal.hide();
        }
    });
};

const handleFileUpload = (event) => {
    uploadForm.file = event.target.files[0];
};

const uploadWithOcr = () => {
    if (!uploadForm.supplier_id || !uploadForm.file) return;

    const formData = new FormData();
    formData.append('supplier_id', uploadForm.supplier_id);
    formData.append('file', uploadForm.file);

    router.post(route('purchases.upload-ocr'), formData, {
        onSuccess: () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
            modal.hide();
        }
    });
};

const confirmDelete = (invoice) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la facture "${invoice.invoice_number}" ?`)) {
        router.delete(route('purchases.destroy', invoice.id));
    }
};
</script>
