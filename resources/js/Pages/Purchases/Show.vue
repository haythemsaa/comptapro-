<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Facture d'achat - {{ invoice.invoice_number }}</h2>
                <div class="d-flex gap-2">
                    <Link v-if="invoice.status === 'draft'" :href="route('purchases.edit', invoice.id)" class="btn btn-outline-primary">
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
                    <div class="col-lg-8">
                        <!-- Invoice Info -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5>Fournisseur</h5>
                                        <p class="mb-0">
                                            <strong>{{ invoice.supplier.name }}</strong><br>
                                            {{ invoice.supplier.supplier_number }}<br>
                                            <span v-if="invoice.supplier.email">{{ invoice.supplier.email }}<br></span>
                                            <span v-if="invoice.supplier.vat_number">TVA: {{ invoice.supplier.vat_number }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <p class="mb-1"><strong>Type:</strong>
                                            <span class="badge" :class="invoice.type === 'credit_note' ? 'bg-warning' : 'bg-primary'">
                                                {{ invoice.type === 'credit_note' ? 'Avoir' : 'Facture' }}
                                            </span>
                                        </p>
                                        <p class="mb-1"><strong>Statut:</strong>
                                            <span class="badge" :class="getStatusClass(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </p>
                                        <p class="mb-1"><strong>Date:</strong> {{ formatDate(invoice.invoice_date) }}</p>
                                        <p class="mb-1" v-if="invoice.due_date"><strong>Échéance:</strong> {{ formatDate(invoice.due_date) }}</p>
                                        <p class="mb-1" v-if="invoice.supplier_invoice_number"><strong>N° fournisseur:</strong> {{ invoice.supplier_invoice_number }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Lines -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Lignes de facture</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Qté</th>
                                                <th class="text-end">P.U. HT</th>
                                                <th class="text-end">TVA</th>
                                                <th class="text-end">Total TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="line in invoice.lines" :key="line.id">
                                                <td>
                                                    {{ line.description }}
                                                    <br><small class="text-muted" v-if="line.account">{{ line.account.code }} - {{ line.account.name }}</small>
                                                </td>
                                                <td class="text-end">{{ line.quantity }} {{ line.unit }}</td>
                                                <td class="text-end">{{ formatCurrency(line.unit_price) }}</td>
                                                <td class="text-end">{{ line.vat_rate }}%</td>
                                                <td class="text-end">{{ formatCurrency(line.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="invoice.notes" class="card">
                            <div class="card-body">
                                <h5>Notes</h5>
                                <p class="mb-0">{{ invoice.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Actions -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="mb-3">Actions</h5>
                                <div class="d-grid gap-2">
                                    <button v-if="invoice.status === 'draft'" @click="markReceived" class="btn btn-outline-primary">
                                        <i class="bi bi-check-circle me-2"></i>Marquer reçue
                                    </button>
                                    <button v-if="invoice.status === 'received'" @click="approve" class="btn btn-outline-success">
                                        <i class="bi bi-check2-circle me-2"></i>Approuver
                                    </button>
                                    <button v-if="['approved', 'received'].includes(invoice.status)" @click="showPaymentModal" class="btn btn-outline-primary">
                                        <i class="bi bi-cash me-2"></i>Enregistrer paiement
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-body">
                                <h5 class="mb-3">Récapitulatif</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Total HT</td>
                                        <td class="text-end">{{ formatCurrency(invoice.subtotal) }}</td>
                                    </tr>
                                    <tr>
                                        <td>TVA</td>
                                        <td class="text-end">{{ formatCurrency(invoice.tax_amount) }}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Total TTC</strong></td>
                                        <td class="text-end"><strong>{{ formatCurrency(invoice.total) }}</strong></td>
                                    </tr>
                                    <tr v-if="invoice.paid_amount > 0">
                                        <td>Déjà payé</td>
                                        <td class="text-end text-success">{{ formatCurrency(invoice.paid_amount) }}</td>
                                    </tr>
                                    <tr v-if="invoice.paid_amount < invoice.total">
                                        <td><strong>Reste à payer</strong></td>
                                        <td class="text-end"><strong class="text-danger">{{ formatCurrency(invoice.total - invoice.paid_amount) }}</strong></td>
                                    </tr>
                                </table>
                            </div>
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
                        <div class="mb-3">
                            <label class="form-label">Montant</label>
                            <input v-model.number="paymentForm.amount" type="number" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input v-model="paymentForm.payment_date" type="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" @click="recordPayment" class="btn btn-primary">Enregistrer</button>
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
    invoice: Object
});

const paymentForm = reactive({
    amount: props.invoice.total - props.invoice.paid_amount,
    payment_date: new Date().toISOString().split('T')[0]
});

let paymentModal = null;

const getStatusClass = (status) => {
    const classes = { draft: 'bg-secondary', received: 'bg-info', approved: 'bg-primary', paid: 'bg-success', cancelled: 'bg-danger' };
    return classes[status] || 'bg-secondary';
};

const getStatusLabel = (status) => {
    const labels = { draft: 'Brouillon', received: 'Reçue', approved: 'Approuvée', paid: 'Payée', cancelled: 'Annulée' };
    return labels[status] || status;
};

const formatDate = (date) => date ? new Date(date).toLocaleDateString('fr-FR') : '-';

const formatCurrency = (amount) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount || 0);

const markReceived = () => router.post(route('purchases.mark-received', props.invoice.id), {}, { preserveScroll: true });

const approve = () => router.post(route('purchases.approve', props.invoice.id), {}, { preserveScroll: true });

const showPaymentModal = () => {
    paymentForm.amount = props.invoice.total - props.invoice.paid_amount;
    if (!paymentModal) paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
};

const recordPayment = () => {
    router.post(route('purchases.mark-paid', props.invoice.id), paymentForm, {
        preserveScroll: true,
        onSuccess: () => paymentModal.hide()
    });
};

const confirmDelete = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')) {
        router.delete(route('purchases.destroy', props.invoice.id));
    }
};
</script>
