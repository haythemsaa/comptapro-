<template>
    <AuthenticatedLayout>
        <div class="customer-show-page">
            <!-- Header -->
            <div class="page-header animate-fadeInDown">
                <div class="header-content">
                    <div class="header-left">
                        <Link :href="route('customers.index')" class="back-btn">
                            <i class="bi bi-arrow-left"></i>
                        </Link>
                        <div>
                            <h1 class="page-title">
                                <div class="customer-avatar-header">
                                    {{ getInitials(customer.name) }}
                                </div>
                                {{ customer.name }}
                            </h1>
                            <p class="page-subtitle">{{ customer.customer_number }}</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <span
                            class="status-badge"
                            :class="customer.is_active ? 'status-active' : 'status-inactive'"
                        >
                            {{ customer.is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        <Link :href="route('customers.edit', customer.id)" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>
                            Modifier
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Customer Information Card -->
                <div class="info-card animate-fadeInUp">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Informations
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div v-if="customer.email" class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Email</div>
                                    <a :href="`mailto:${customer.email}`" class="info-value link">
                                        {{ customer.email }}
                                    </a>
                                </div>
                            </div>

                            <div v-if="customer.phone" class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Téléphone</div>
                                    <a :href="`tel:${customer.phone}`" class="info-value link">
                                        {{ customer.phone }}
                                    </a>
                                </div>
                            </div>

                            <div v-if="customer.vat_number" class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-hash"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Numéro TVA</div>
                                    <div class="info-value">{{ customer.vat_number }}</div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Délai de paiement</div>
                                    <div class="info-value">{{ formatPaymentTerm(customer.payment_term) }}</div>
                                </div>
                            </div>

                            <div v-if="customer.credit_limit" class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-currency-euro"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Limite de crédit</div>
                                    <div class="info-value">{{ formatCurrency(customer.credit_limit) }}</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="customer.address" class="address-section">
                            <div class="info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Adresse</div>
                                <div class="info-value">
                                    {{ customer.address }}<br />
                                    <span v-if="customer.postal_code || customer.city">
                                        {{ customer.postal_code }} {{ customer.city }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="customer.notes" class="notes-section">
                            <div class="info-icon">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Notes</div>
                                <div class="info-value notes-text">{{ customer.notes }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-section animate-fadeInUp animate-delay-100">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Factures</div>
                            <div class="stat-value">{{ customer.invoices?.length || 0 }}</div>
                        </div>
                    </div>

                    <div class="stat-card stat-card-success">
                        <div class="stat-icon">
                            <i class="bi bi-currency-euro"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Chiffre d'affaires</div>
                            <div class="stat-value">{{ formatCurrency(totalRevenue) }}</div>
                        </div>
                    </div>

                    <div class="stat-card stat-card-warning">
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">En attente</div>
                            <div class="stat-value">{{ formatCurrency(pendingAmount) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="invoices-card animate-fadeInUp animate-delay-200">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-receipt me-2"></i>
                            Factures récentes
                        </h3>
                        <Link :href="route('invoices.create', { customer_id: customer.id })" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Nouvelle facture
                        </Link>
                    </div>
                    <div class="card-body">
                        <div v-if="customer.invoices && customer.invoices.length > 0" class="invoices-list">
                            <div
                                v-for="invoice in customer.invoices"
                                :key="invoice.id"
                                class="invoice-item"
                            >
                                <div class="invoice-left">
                                    <div class="invoice-icon" :class="`invoice-${invoice.status}`">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div class="invoice-info">
                                        <div class="invoice-number">{{ invoice.invoice_number }}</div>
                                        <div class="invoice-date">
                                            {{ formatDate(invoice.invoice_date) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="invoice-right">
                                    <div class="invoice-amount">{{ formatCurrency(invoice.total_amount) }}</div>
                                    <span class="invoice-status" :class="`status-${invoice.status}`">
                                        {{ formatStatus(invoice.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="empty-state-small">
                            <i class="bi bi-inbox"></i>
                            <p>Aucune facture</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    customer: Object,
});

const totalRevenue = computed(() => {
    if (!props.customer.invoices) return 0;
    return props.customer.invoices
        .filter(inv => inv.status === 'paid')
        .reduce((sum, inv) => sum + parseFloat(inv.total_amount), 0);
});

const pendingAmount = computed(() => {
    if (!props.customer.invoices) return 0;
    return props.customer.invoices
        .filter(inv => ['sent', 'overdue'].includes(inv.status))
        .reduce((sum, inv) => sum + parseFloat(inv.total_amount), 0);
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatPaymentTerm = (term) => {
    const terms = {
        immediate: 'Comptant',
        '15_days': '15 jours',
        '30_days': '30 jours',
        '45_days': '45 jours',
        '60_days': '60 jours',
    };
    return terms[term] || term;
};

const formatStatus = (status) => {
    const statuses = {
        draft: 'Brouillon',
        sent: 'Envoyée',
        paid: 'Payée',
        cancelled: 'Annulée',
        overdue: 'En retard',
    };
    return statuses[status] || status;
};
</script>

<style scoped>
.customer-show-page {
    padding: 32px;
    max-width: 1400px;
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

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    min-width: 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.back-btn {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #fff;
    border: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 20px;
    transition: all 0.3s;
    text-decoration: none;
    flex-shrink: 0;
}

.back-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #1f2937;
}

.page-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 16px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-avatar-header {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 4px 0 0 0;
    font-size: 16px;
    font-family: 'Courier New', monospace;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 24px;
}

/* Info Card */
.info-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    grid-row: span 2;
}

.card-header {
    padding: 24px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.card-title i {
    color: #3b82f6;
}

.card-body {
    padding: 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.info-item {
    display: flex;
    gap: 12px;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.info-content {
    flex: 1;
    min-width: 0;
}

.info-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 500;
}

.info-value {
    font-size: 15px;
    color: #1f2937;
    font-weight: 500;
    word-break: break-word;
}

.info-value.link {
    color: #3b82f6;
    text-decoration: none;
    transition: color 0.3s;
}

.info-value.link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.address-section,
.notes-section {
    display: flex;
    gap: 12px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 24px;
}

.notes-text {
    white-space: pre-wrap;
    line-height: 1.6;
}

/* Statistics */
.stats-section {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
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

.stat-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

/* Invoices */
.invoices-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.invoices-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.invoice-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
    transition: all 0.3s;
}

.invoice-item:hover {
    background: #f3f4f6;
    transform: translateX(4px);
}

.invoice-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.invoice-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
}

.invoice-draft {
    background: #9ca3af;
}

.invoice-sent {
    background: #3b82f6;
}

.invoice-paid {
    background: #10b981;
}

.invoice-overdue {
    background: #ef4444;
}

.invoice-cancelled {
    background: #6b7280;
}

.invoice-number {
    font-weight: 700;
    color: #1f2937;
    font-size: 15px;
    font-family: 'Courier New', monospace;
}

.invoice-date {
    font-size: 13px;
    color: #6b7280;
}

.invoice-right {
    text-align: right;
}

.invoice-amount {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.invoice-status {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.status-draft {
    background: #f3f4f6;
    color: #6b7280;
}

.status-sent {
    background: #dbeafe;
    color: #1e40af;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-overdue {
    background: #fee2e2;
    color: #991b1b;
}

.status-cancelled {
    background: #f3f4f6;
    color: #4b5563;
}

.empty-state-small {
    text-align: center;
    padding: 40px 20px;
    color: #9ca3af;
}

.empty-state-small i {
    font-size: 48px;
    margin-bottom: 12px;
}

.empty-state-small p {
    margin: 0;
    font-size: 15px;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    text-decoration: none;
    font-size: 15px;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .info-card {
        grid-row: span 1;
    }

    .stats-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .customer-show-page {
        padding: 16px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        width: 100%;
        justify-content: space-between;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .invoice-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .invoice-right {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
}
</style>
