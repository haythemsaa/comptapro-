<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">{{ invoice.invoice_number }}</h2>
                <div class="d-flex gap-2">
                    <Link :href="route('invoices.index')" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </Link>
                    <Link v-if="invoice.status === 'draft'" :href="route('invoices.edit', invoice.id)" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Modifier
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Invoice Header -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <h5 class="text-muted small mb-1">{{ getTypeLabel(invoice.type) }}</h5>
                                        <h3 class="mb-3">{{ invoice.invoice_number }}</h3>
                                        <div>
                                            <span :class="getStatusClass(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <h5>{{ invoice.company.name }}</h5>
                                        <p class="text-muted mb-0">
                                            {{ invoice.company.address_line1 }}<br>
                                            {{ invoice.company.postal_code }} {{ invoice.company.city }}<br>
                                            TVA: {{ invoice.company.vat_number || 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6 class="text-muted small mb-2">CLIENT</h6>
                                        <strong>{{ invoice.customer.name }}</strong><br>
                                        <span v-if="invoice.customer.email">{{ invoice.customer.email }}<br></span>
                                        <span v-if="invoice.customer.phone">{{ invoice.customer.phone }}<br></span>
                                        <span v-if="invoice.customer.address_line1">
                                            {{ invoice.customer.address_line1 }}<br>
                                            {{ invoice.customer.postal_code }} {{ invoice.customer.city }}
                                        </span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td class="text-muted">Date d'émission:</td>
                                                <td class="text-end"><strong>{{ formatDate(invoice.issue_date) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Date d'échéance:</td>
                                                <td class="text-end"><strong>{{ formatDate(invoice.due_date) }}</strong></td>
                                            </tr>
                                            <tr v-if="invoice.payment_date">
                                                <td class="text-muted">Date de paiement:</td>
                                                <td class="text-end"><strong>{{ formatDate(invoice.payment_date) }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Lines -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-center">Quantité</th>
                                                <th class="text-end">Prix unitaire</th>
                                                <th class="text-center">TVA</th>
                                                <th class="text-end">Total HT</th>
                                                <th class="text-end">TVA</th>
                                                <th class="text-end">Total TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="line in invoice.lines" :key="line.id">
                                                <td>{{ line.description }}</td>
                                                <td class="text-center">{{ line.quantity }}</td>
                                                <td class="text-end">{{ formatCurrency(line.unit_price) }}</td>
                                                <td class="text-center">{{ line.vat_rate }}%</td>
                                                <td class="text-end">{{ formatCurrency(line.subtotal) }}</td>
                                                <td class="text-end">{{ formatCurrency(line.tax_amount) }}</td>
                                                <td class="text-end fw-bold">{{ formatCurrency(line.total) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="border-top">
                                            <tr>
                                                <td colspan="6" class="text-end"><strong>Total HT</strong></td>
                                                <td class="text-end"><strong>{{ formatCurrency(invoice.subtotal) }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="text-end"><strong>TVA</strong></td>
                                                <td class="text-end"><strong>{{ formatCurrency(invoice.tax_amount) }}</strong></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="6" class="text-end"><strong>Total TTC</strong></td>
                                                <td class="text-end"><strong>{{ formatCurrency(invoice.total) }}</strong></td>
                                            </tr>
                                            <tr v-if="invoice.paid_amount > 0">
                                                <td colspan="6" class="text-end">Montant payé</td>
                                                <td class="text-end">{{ formatCurrency(invoice.paid_amount) }}</td>
                                            </tr>
                                            <tr v-if="invoice.paid_amount > 0 && invoice.paid_amount < invoice.total">
                                                <td colspan="6" class="text-end"><strong>Reste à payer</strong></td>
                                                <td class="text-end"><strong>{{ formatCurrency(invoice.total - invoice.paid_amount) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div v-if="invoice.notes" class="mt-4">
                                    <h6 class="text-muted small">NOTES</h6>
                                    <p class="mb-0">{{ invoice.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Sidebar -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 1rem;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <!-- Download PDF -->
                                    <button type="button" class="btn btn-primary" disabled>
                                        <i class="bi bi-file-pdf me-2"></i>Télécharger PDF
                                        <small class="d-block text-white-50" style="font-size: 0.7rem;">(Bientôt disponible)</small>
                                    </button>

                                    <!-- Send Email -->
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="bi bi-envelope me-2"></i>Envoyer par email
                                        <small class="d-block" style="font-size: 0.7rem;">(Bientôt disponible)</small>
                                    </button>

                                    <hr>

                                    <!-- Mark as Sent -->
                                    <button
                                        v-if="invoice.status === 'draft'"
                                        type="button"
                                        @click="markAsSent"
                                        class="btn btn-info text-white"
                                    >
                                        <i class="bi bi-send me-2"></i>Marquer comme envoyée
                                    </button>

                                    <!-- Record Payment -->
                                    <button
                                        v-if="invoice.status === 'sent' || invoice.status === 'overdue'"
                                        type="button"
                                        @click="showPaymentModal"
                                        class="btn btn-success"
                                    >
                                        <i class="bi bi-cash me-2"></i>Enregistrer un paiement
                                    </button>

                                    <!-- Edit -->
                                    <Link
                                        v-if="invoice.status === 'draft'"
                                        :href="route('invoices.edit', invoice.id)"
                                        class="btn btn-warning"
                                    >
                                        <i class="bi bi-pencil me-2"></i>Modifier
                                    </Link>

                                    <!-- Delete -->
                                    <button
                                        v-if="invoice.status === 'draft'"
                                        type="button"
                                        @click="deleteInvoice"
                                        class="btn btn-danger"
                                    >
                                        <i class="bi bi-trash me-2"></i>Supprimer
                                    </button>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="card-body border-top">
                                <h6 class="text-muted small mb-3">RÉSUMÉ</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Devise:</td>
                                        <td class="text-end">{{ invoice.company.country.currency }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Conditions:</td>
                                        <td class="text-end">{{ invoice.customer.payment_term || 'Standard' }}</td>
                                    </tr>
                                    <tr v-if="invoice.status === 'paid'">
                                        <td class="text-muted">Payée le:</td>
                                        <td class="text-end">{{ formatDate(invoice.payment_date) }}</td>
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
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    invoice: Object
});

const paymentForm = reactive({
    payment_date: new Date().toISOString().split('T')[0],
    amount: props.invoice.total - props.invoice.paid_amount
});

let paymentModal = null;

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    const currency = props.invoice.company.country.currency;
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency
    }).format(amount || 0);
};

const getTypeLabel = (type) => {
    const labels = {
        quote: 'DEVIS',
        invoice: 'FACTURE',
        credit_note: 'AVOIR'
    };
    return labels[type] || type;
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

const markAsSent = () => {
    if (confirm('Marquer cette facture comme envoyée ?')) {
        router.post(route('invoices.mark-sent', props.invoice.id));
    }
};

const showPaymentModal = () => {
    if (!paymentModal) {
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    }
    paymentModal.show();
};

const recordPayment = () => {
    router.post(route('invoices.mark-paid', props.invoice.id), paymentForm, {
        onSuccess: () => {
            if (paymentModal) {
                paymentModal.hide();
            }
        }
    });
};

const deleteInvoice = () => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${props.invoice.invoice_number} ?`)) {
        router.delete(route('invoices.destroy', props.invoice.id), {
            onSuccess: () => {
                router.visit(route('invoices.index'));
            }
        });
    }
};
</script>
