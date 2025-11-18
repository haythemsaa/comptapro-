<template>
    <AuthenticatedLayout>
        <div class="invoices-page">
            <!-- Header -->
            <div class="page-header animate-fadeInDown">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="bi bi-receipt-cutoff me-3"></i>
                            Factures
                        </h1>
                        <p class="page-subtitle">Gérez vos devis, factures et avoirs</p>
                    </div>
                    <div class="header-right">
                        <Link :href="route('invoices.create')" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nouvelle Facture
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid animate-fadeInUp">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Factures</div>
                        <div class="stat-value">{{ invoices.total }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Payées</div>
                        <div class="stat-value">{{ paidCount }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">En attente</div>
                        <div class="stat-value">{{ pendingCount }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-danger">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">En retard</div>
                        <div class="stat-value">{{ overdueCount }}</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-card animate-fadeInUp animate-delay-100">
                <div class="filters-header">
                    <h3 class="filters-title">
                        <i class="bi bi-funnel me-2"></i>
                        Filtres
                    </h3>
                    <button v-if="hasActiveFilters" @click="clearFilters" class="btn-text">
                        <i class="bi bi-x-circle me-1"></i>
                        Réinitialiser
                    </button>
                </div>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">Recherche</label>
                        <div class="search-box-filter">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                v-model="filters.search"
                                @input="applyFilters"
                                type="text"
                                class="filter-input"
                                placeholder="Numéro, client..."
                            />
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select v-model="filters.type" @change="applyFilters" class="filter-select">
                            <option value="">Tous les types</option>
                            <option value="quote">Devis</option>
                            <option value="invoice">Facture</option>
                            <option value="credit_note">Avoir</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Statut</label>
                        <select v-model="filters.status" @change="applyFilters" class="filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="draft">Brouillon</option>
                            <option value="sent">Envoyée</option>
                            <option value="paid">Payée</option>
                            <option value="overdue">En retard</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Invoices Grid -->
            <div class="invoices-grid animate-fadeInUp animate-delay-200">
                <div
                    v-for="invoice in invoices.data"
                    :key="invoice.id"
                    class="invoice-card hover-lift"
                    :class="`invoice-${invoice.status}`"
                >
                    <div class="invoice-header">
                        <div class="invoice-icon-wrapper">
                            <div class="invoice-icon" :class="`icon-${invoice.type}`">
                                <i class="bi" :class="getTypeIcon(invoice.type)"></i>
                            </div>
                        </div>
                        <div class="invoice-meta">
                            <Link :href="route('invoices.show', invoice.id)" class="invoice-number">
                                {{ invoice.invoice_number }}
                            </Link>
                            <div class="invoice-type">
                                <span class="type-badge" :class="`type-${invoice.type}`">
                                    {{ getTypeLabel(invoice.type) }}
                                </span>
                            </div>
                        </div>
                        <div class="invoice-status-badge">
                            <span class="status-badge" :class="`status-${invoice.status}`">
                                {{ getStatusLabel(invoice.status) }}
                            </span>
                        </div>
                    </div>

                    <div class="invoice-body">
                        <div class="invoice-customer">
                            <i class="bi bi-person-circle customer-icon"></i>
                            <span class="customer-name">{{ invoice.customer?.name }}</span>
                        </div>

                        <div class="invoice-details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Date</span>
                                <span class="detail-value">{{ formatDate(invoice.issue_date) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Échéance</span>
                                <span class="detail-value">{{ formatDate(invoice.due_date) }}</span>
                            </div>
                        </div>

                        <div class="invoice-amounts">
                            <div class="amount-row">
                                <span class="amount-label">HT</span>
                                <span class="amount-value">{{ formatCurrency(invoice.subtotal) }}</span>
                            </div>
                            <div class="amount-row">
                                <span class="amount-label">TVA</span>
                                <span class="amount-value">{{ formatCurrency(invoice.tax_amount) }}</span>
                            </div>
                            <div class="amount-row amount-total">
                                <span class="amount-label">TTC</span>
                                <span class="amount-value">{{ formatCurrency(invoice.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-actions">
                        <Link
                            :href="route('invoices.show', invoice.id)"
                            class="btn btn-sm btn-light"
                        >
                            <i class="bi bi-eye"></i>
                            Voir
                        </Link>
                        <Link
                            v-if="invoice.status === 'draft'"
                            :href="route('invoices.edit', invoice.id)"
                            class="btn btn-sm btn-primary"
                        >
                            <i class="bi bi-pencil"></i>
                            Modifier
                        </Link>
                        <button
                            v-if="invoice.status === 'draft'"
                            @click="markAsSent(invoice)"
                            class="btn btn-sm btn-info"
                        >
                            <i class="bi bi-send"></i>
                            Envoyer
                        </button>
                        <button
                            v-if="invoice.status === 'sent' || invoice.status === 'overdue'"
                            @click="showPaymentModal(invoice)"
                            class="btn btn-sm btn-success"
                        >
                            <i class="bi bi-cash"></i>
                            Paiement
                        </button>
                        <button
                            v-if="invoice.status === 'draft'"
                            @click="confirmDelete(invoice)"
                            class="btn btn-sm btn-danger"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="invoices.data.length === 0" class="empty-state animate-fadeInUp">
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-title">Aucune facture trouvée</h3>
                <p class="empty-text">Commencez par créer votre première facture</p>
                <Link :href="route('invoices.create')" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>
                    Créer une facture
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.data.length > 0" class="pagination-wrapper animate-fadeInUp animate-delay-300">
                <nav class="pagination">
                    <Link
                        v-for="link in invoices.links"
                        :key="link.label"
                        :href="link.url"
                        :class="['page-link', { 'active': link.active, 'disabled': !link.url }]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </nav>
            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModalState" @close="showPaymentModalState = false">
            <template #header>
                <h3 class="modal-title">
                    <i class="bi bi-cash-coin me-2"></i>
                    Enregistrer un paiement
                </h3>
            </template>
            <template #body>
                <form @submit.prevent="recordPayment" class="payment-form">
                    <div class="form-group">
                        <label for="payment_date" class="form-label">Date de paiement</label>
                        <input
                            id="payment_date"
                            v-model="paymentForm.payment_date"
                            type="date"
                            class="form-input"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="amount" class="form-label">Montant</label>
                        <div class="input-with-icon">
                            <i class="bi bi-currency-euro input-icon"></i>
                            <input
                                id="amount"
                                v-model="paymentForm.amount"
                                type="number"
                                step="0.01"
                                class="form-input with-icon"
                                required
                            />
                        </div>
                    </div>
                </form>
            </template>
            <template #footer>
                <button @click="showPaymentModalState = false" class="btn btn-secondary">
                    Annuler
                </button>
                <button @click="recordPayment" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>
                    Enregistrer
                </button>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #header>
                <h3 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmer la suppression
                </h3>
            </template>
            <template #body>
                <p>Êtes-vous sûr de vouloir supprimer la facture <strong>{{ invoiceToDelete?.invoice_number }}</strong> ?</p>
                <p class="text-muted">Cette action est irréversible.</p>
            </template>
            <template #footer>
                <button @click="showDeleteModal = false" class="btn btn-secondary">
                    Annuler
                </button>
                <button @click="deleteInvoice" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>
                    Supprimer
                </button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

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

const showPaymentModalState = ref(false);
const showDeleteModal = ref(false);
const currentInvoice = ref(null);
const invoiceToDelete = ref(null);

// Computed Statistics
const paidCount = computed(() => {
    return props.invoices.data?.filter(inv => inv.status === 'paid').length || 0;
});

const pendingCount = computed(() => {
    return props.invoices.data?.filter(inv => inv.status === 'sent').length || 0;
});

const overdueCount = computed(() => {
    return props.invoices.data?.filter(inv => inv.status === 'overdue').length || 0;
});

const hasActiveFilters = computed(() => {
    return filters.search || filters.type || filters.status;
});

const clearFilters = () => {
    filters.search = '';
    filters.type = '';
    filters.status = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(route('invoices.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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

const getTypeIcon = (type) => {
    const icons = {
        quote: 'bi-file-earmark-richtext',
        invoice: 'bi-file-earmark-text',
        credit_note: 'bi-file-earmark-minus'
    };
    return icons[type] || 'bi-file-earmark';
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

const markAsSent = (invoice) => {
    router.post(route('invoices.mark-sent', invoice.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            window.$toast?.success('Facture marquée comme envoyée', 'Succès');
        }
    });
};

const showPaymentModal = (invoice) => {
    currentInvoice.value = invoice;
    paymentForm.amount = invoice.total - (invoice.paid_amount || 0);
    showPaymentModalState.value = true;
};

const recordPayment = () => {
    if (currentInvoice.value) {
        router.post(route('invoices.mark-paid', currentInvoice.value.id), paymentForm, {
            preserveScroll: true,
            onSuccess: () => {
                showPaymentModalState.value = false;
                window.$toast?.success('Paiement enregistré avec succès', 'Succès');
            },
            onError: () => {
                window.$toast?.error('Erreur lors de l\'enregistrement du paiement', 'Erreur');
            }
        });
    }
};

const confirmDelete = (invoice) => {
    invoiceToDelete.value = invoice;
    showDeleteModal.value = true;
};

const deleteInvoice = () => {
    if (invoiceToDelete.value) {
        router.delete(route('invoices.destroy', invoiceToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                invoiceToDelete.value = null;
                window.$toast?.success('Facture supprimée avec succès', 'Succès');
            },
            onError: () => {
                window.$toast?.error('Erreur lors de la suppression', 'Erreur');
            }
        });
    }
};
</script>

<style scoped>
.invoices-page {
    padding: 32px;
    max-width: 1600px;
    margin: 0 auto;
}

/* Header */
.page-header {
    margin-bottom: 32px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.page-title i {
    color: #3b82f6;
}

.page-subtitle {
    color: #6b7280;
    margin: 8px 0 0 0;
    font-size: 16px;
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}

.stat-card-danger .stat-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
}

/* Filters Card */
.filters-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 32px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.filters-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.filters-title i {
    color: #3b82f6;
}

.btn-text {
    background: none;
    border: none;
    color: #3b82f6;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.btn-text:hover {
    background: #eff6ff;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.search-box-filter {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.filter-input,
.filter-select {
    width: 100%;
    height: 48px;
    padding: 0 16px 0 44px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s;
    background: #fff;
}

.filter-select {
    padding-left: 16px;
    cursor: pointer;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Invoices Grid */
.invoices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.invoice-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.invoice-card.invoice-draft {
    border-left-color: #9ca3af;
}

.invoice-card.invoice-sent {
    border-left-color: #3b82f6;
}

.invoice-card.invoice-paid {
    border-left-color: #10b981;
}

.invoice-card.invoice-overdue {
    border-left-color: #ef4444;
}

.invoice-card.invoice-cancelled {
    border-left-color: #6b7280;
}

.invoice-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.invoice-icon-wrapper {
    flex-shrink: 0;
}

.invoice-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
}

.invoice-icon.icon-quote {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.invoice-icon.icon-invoice {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.invoice-icon.icon-credit_note {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.invoice-meta {
    flex: 1;
    min-width: 0;
}

.invoice-number {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    text-decoration: none;
    display: block;
    margin-bottom: 4px;
    font-family: 'Courier New', monospace;
}

.invoice-number:hover {
    color: #3b82f6;
}

.type-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.type-badge.type-quote {
    background: #f3e8ff;
    color: #6d28d9;
}

.type-badge.type-invoice {
    background: #dbeafe;
    color: #1e40af;
}

.type-badge.type-credit_note {
    background: #fef3c7;
    color: #92400e;
}

.invoice-status-badge {
    flex-shrink: 0;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.status-draft {
    background: #f3f4f6;
    color: #4b5563;
}

.status-badge.status-sent {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-overdue {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.status-cancelled {
    background: #f3f4f6;
    color: #374151;
}

.invoice-body {
    margin-bottom: 20px;
}

.invoice-customer {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 10px;
}

.customer-icon {
    font-size: 24px;
    color: #6b7280;
}

.customer-name {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
}

.invoice-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
}

.detail-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.invoice-amounts {
    padding: 16px;
    background: #f9fafb;
    border-radius: 10px;
}

.amount-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.amount-row:last-child {
    margin-bottom: 0;
}

.amount-row.amount-total {
    padding-top: 12px;
    border-top: 2px solid #e5e7eb;
    margin-top: 4px;
}

.amount-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

.amount-row.amount-total .amount-label {
    font-weight: 700;
    color: #1f2937;
}

.amount-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 600;
}

.amount-row.amount-total .amount-value {
    font-size: 18px;
    font-weight: 700;
    color: #3b82f6;
}

.invoice-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 20px;
    border-top: 1px solid #f3f4f6;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    justify-content: center;
    text-decoration: none;
    font-weight: 600;
}

.btn-light {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-light:hover {
    background: #e5e7eb;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-info {
    background: #06b6d4;
    color: #fff;
}

.btn-info:hover {
    background: #0891b2;
}

.btn-success {
    background: #10b981;
    color: #fff;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #ef4444;
    color: #fff;
}

.btn-danger:hover {
    background: #dc2626;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 24px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    font-size: 80px;
    color: #d1d5db;
    margin-bottom: 24px;
}

.empty-title {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 12px 0;
}

.empty-text {
    color: #6b7280;
    margin-bottom: 32px;
    font-size: 16px;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 8px;
}

.page-link {
    padding: 12px 18px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.page-link:hover:not(.disabled) {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.page-link.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: #fff;
}

.page-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Buttons */
.btn-lg {
    padding: 14px 28px;
    font-size: 16px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    text-decoration: none;
}

.btn-primary.btn-lg {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
    padding: 12px 24px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Modal Form */
.payment-form .form-group {
    margin-bottom: 20px;
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    display: block;
}

.form-input {
    width: 100%;
    height: 48px;
    padding: 0 16px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s;
    background: #fff;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.input-with-icon {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.form-input.with-icon {
    padding-left: 44px;
}

.text-muted {
    color: #6b7280;
    font-size: 14px;
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.modal-title.text-danger {
    color: #dc2626;
}

.modal-title i {
    color: inherit;
}

/* Responsive */
@media (max-width: 768px) {
    .invoices-page {
        padding: 16px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .invoices-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }
}
</style>
