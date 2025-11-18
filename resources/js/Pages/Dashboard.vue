<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    companies: Array,
    currentCompany: Object,
    stats: Object,
});

// Format currency based on company country
const formatCurrency = (amount) => {
    if (!props.currentCompany) return amount;

    const currency = props.currentCompany.country.currency;
    const formatter = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency,
    });
    return formatter.format(amount);
};

// Compute revenue growth percentage
const revenueGrowth = computed(() => {
    if (!props.stats || !props.stats.total_revenue || !props.stats.last_month_revenue) return 0;
    const growth = ((props.stats.total_revenue - props.stats.last_month_revenue) / props.stats.last_month_revenue) * 100;
    return Math.round(growth);
});

// Compute collection rate
const collectionRate = computed(() => {
    if (!props.stats || !props.stats.total_revenue) return 0;
    const total = props.stats.total_revenue + props.stats.pending_amount;
    if (total === 0) return 0;
    return Math.round((props.stats.total_revenue / total) * 100);
});

// Quick actions
const quickActions = [
    { title: 'Nouvelle facture', icon: 'file-earmark-plus', color: 'primary', route: 'invoices.create' },
    { title: 'Nouveau client', icon: 'person-plus', color: 'success', route: 'customers.create' },
    { title: 'Nouveau produit', icon: 'box-seam', color: 'info', route: 'products.create' },
    { title: 'Rapports', icon: 'graph-up', color: 'warning', route: 'reports.index' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="dashboard-header">
                <div>
                    <h2 class="page-title">Dashboard</h2>
                    <p class="page-subtitle" v-if="currentCompany">{{ currentCompany.name }}</p>
                </div>

                <!-- Company Selector -->
                <div v-if="companies.length > 1" class="dropdown">
                    <button class="company-selector" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i>
                        <span>{{ currentCompany?.name || 'Sélectionner une société' }}</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu company-dropdown">
                        <li v-for="company in companies" :key="company.id">
                            <a class="dropdown-item" :class="{'active': company.id === currentCompany?.id}"
                               :href="route('company.switch', company.id)" method="post">
                                <i class="bi bi-building me-2"></i>
                                {{ company.name }}
                                <span class="badge bg-primary ms-auto">{{ company.country.code }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </template>

        <div class="dashboard-content">
            <!-- Welcome Banner -->
            <div v-if="currentCompany" class="welcome-banner">
                <div class="banner-content">
                    <div class="banner-icon">
                        <i class="bi bi-emoji-smile"></i>
                    </div>
                    <div class="banner-text">
                        <h4>Bienvenue sur ComptaPro !</h4>
                        <p>Gérez votre comptabilité de manière simple et efficace</p>
                    </div>
                </div>
                <div class="banner-badges">
                    <span class="info-badge">
                        <i class="bi bi-globe"></i>
                        {{ currentCompany.country.name }}
                    </span>
                    <span class="info-badge">
                        <i class="bi bi-currency-exchange"></i>
                        {{ currentCompany.country.currency }}
                    </span>
                    <span class="info-badge">
                        <i class="bi bi-star-fill"></i>
                        {{ currentCompany.subscription_plan }}
                    </span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h5 class="section-title">Actions rapides</h5>
                <div class="actions-grid">
                    <Link v-for="action in quickActions" :key="action.title"
                          :href="route(action.route)"
                          class="action-card"
                          :class="`action-${action.color}`">
                        <div class="action-icon">
                            <i class="bi" :class="`bi-${action.icon}`"></i>
                        </div>
                        <span>{{ action.title }}</span>
                    </Link>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div v-if="stats" class="stats-section">
                <h5 class="section-title">Vue d'ensemble</h5>
                <div class="stats-grid">
                    <!-- Total Customers -->
                    <div class="stat-card stat-customers">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-menu">
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Clients</div>
                            <div class="stat-value">{{ stats.total_customers }}</div>
                            <div class="stat-footer">
                                <Link :href="route('customers.index')" class="stat-link">
                                    Voir tous les clients
                                    <i class="bi bi-arrow-right"></i>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Total Invoices -->
                    <div class="stat-card stat-invoices">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="stat-menu">
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Factures</div>
                            <div class="stat-value">{{ stats.total_invoices }}</div>
                            <div class="stat-breakdown">
                                <span class="badge-success">{{ stats.paid_invoices }} payées</span>
                                <span class="badge-warning">{{ stats.draft_invoices }} brouillons</span>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Card -->
                    <div class="stat-card stat-revenue">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="bi bi-currency-euro"></i>
                            </div>
                            <div class="stat-menu">
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Chiffre d'affaires</div>
                            <div class="stat-value">{{ formatCurrency(stats.total_revenue) }}</div>
                            <div class="stat-trend" :class="revenueGrowth >= 0 ? 'trend-up' : 'trend-down'">
                                <i class="bi" :class="revenueGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                                {{ Math.abs(revenueGrowth) }}% vs mois dernier
                            </div>
                        </div>
                    </div>

                    <!-- Pending Amount -->
                    <div class="stat-card stat-pending">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="stat-menu">
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">En attente</div>
                            <div class="stat-value">{{ formatCurrency(stats.pending_amount) }}</div>
                            <div class="stat-alert" v-if="stats.overdue_invoices > 0">
                                <i class="bi bi-exclamation-circle"></i>
                                {{ stats.overdue_invoices }} facture(s) en retard
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Progress -->
            <div v-if="stats" class="charts-section">
                <div class="row g-4">
                    <!-- Collection Rate -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h6>Taux de recouvrement</h6>
                                <span class="badge bg-success">{{ collectionRate }}%</span>
                            </div>
                            <div class="chart-body">
                                <div class="progress-circle">
                                    <svg viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" class="progress-bg"></circle>
                                        <circle cx="50" cy="50" r="45" class="progress-bar"
                                                :style="{ strokeDashoffset: 283 - (283 * collectionRate / 100) }"></circle>
                                    </svg>
                                    <div class="progress-text">
                                        <div class="progress-value">{{ collectionRate }}%</div>
                                        <div class="progress-label">Encaissé</div>
                                    </div>
                                </div>
                                <div class="progress-details">
                                    <div class="detail-item">
                                        <span class="detail-dot paid"></span>
                                        <span class="detail-label">Payé</span>
                                        <span class="detail-value">{{ formatCurrency(stats.total_revenue) }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-dot pending"></span>
                                        <span class="detail-label">En attente</span>
                                        <span class="detail-value">{{ formatCurrency(stats.pending_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Status Distribution -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h6>État des factures</h6>
                                <span class="text-muted">{{ stats.total_invoices }} total</span>
                            </div>
                            <div class="chart-body">
                                <div class="status-bars">
                                    <div class="status-bar-item">
                                        <div class="status-bar-label">
                                            <span class="status-dot paid"></span>
                                            <span>Payées</span>
                                            <span class="ms-auto fw-bold">{{ stats.paid_invoices }}</span>
                                        </div>
                                        <div class="status-bar-progress">
                                            <div class="status-bar-fill paid"
                                                 :style="{ width: (stats.paid_invoices / stats.total_invoices * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                    <div class="status-bar-item">
                                        <div class="status-bar-label">
                                            <span class="status-dot sent"></span>
                                            <span>Envoyées</span>
                                            <span class="ms-auto fw-bold">{{ stats.sent_invoices || 0 }}</span>
                                        </div>
                                        <div class="status-bar-progress">
                                            <div class="status-bar-fill sent"
                                                 :style="{ width: ((stats.sent_invoices || 0) / stats.total_invoices * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                    <div class="status-bar-item">
                                        <div class="status-bar-label">
                                            <span class="status-dot draft"></span>
                                            <span>Brouillons</span>
                                            <span class="ms-auto fw-bold">{{ stats.draft_invoices }}</span>
                                        </div>
                                        <div class="status-bar-progress">
                                            <div class="status-bar-fill draft"
                                                 :style="{ width: (stats.draft_invoices / stats.total_invoices * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                    <div class="status-bar-item" v-if="stats.overdue_invoices > 0">
                                        <div class="status-bar-label">
                                            <span class="status-dot overdue"></span>
                                            <span>En retard</span>
                                            <span class="ms-auto fw-bold">{{ stats.overdue_invoices }}</span>
                                        </div>
                                        <div class="status-bar-progress">
                                            <div class="status-bar-fill overdue"
                                                 :style="{ width: (stats.overdue_invoices / stats.total_invoices * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-section">
                <div class="row g-4">
                    <!-- Recent Customers -->
                    <div class="col-lg-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h6>
                                    <i class="bi bi-people me-2"></i>
                                    Clients récents
                                </h6>
                                <Link :href="route('customers.index')" class="view-all">
                                    Voir tout
                                    <i class="bi bi-arrow-right"></i>
                                </Link>
                            </div>
                            <div class="activity-list">
                                <div v-if="stats.recent_customers && stats.recent_customers.length > 0">
                                    <div v-for="customer in stats.recent_customers" :key="customer.id" class="activity-item">
                                        <div class="activity-avatar">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="activity-details">
                                            <div class="activity-title">{{ customer.name }}</div>
                                            <div class="activity-subtitle">{{ customer.email }}</div>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="meta-badge">{{ customer.customer_number }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucun client enregistré</p>
                                </div>
                            </div>
                            <div class="activity-footer">
                                <Link :href="route('customers.create')" class="btn-action">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Nouveau client
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Invoices -->
                    <div class="col-lg-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h6>
                                    <i class="bi bi-receipt me-2"></i>
                                    Factures récentes
                                </h6>
                                <Link :href="route('invoices.index')" class="view-all">
                                    Voir tout
                                    <i class="bi bi-arrow-right"></i>
                                </Link>
                            </div>
                            <div class="activity-list">
                                <div v-if="stats.recent_invoices && stats.recent_invoices.length > 0">
                                    <div v-for="invoice in stats.recent_invoices" :key="invoice.id" class="activity-item">
                                        <div class="activity-avatar" :class="`avatar-${invoice.status}`">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div class="activity-details">
                                            <div class="activity-title">{{ invoice.invoice_number }}</div>
                                            <div class="activity-subtitle">{{ invoice.customer.name }}</div>
                                        </div>
                                        <div class="activity-meta">
                                            <div class="meta-amount">{{ formatCurrency(invoice.total) }}</div>
                                            <span class="meta-status" :class="`status-${invoice.status}`">
                                                {{ invoice.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Aucune facture créée</p>
                                </div>
                            </div>
                            <div class="activity-footer">
                                <Link :href="route('invoices.create')" class="btn-action">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Nouvelle facture
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Company Message -->
            <div v-if="!currentCompany" class="no-company-state">
                <div class="empty-state-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3>Aucune société sélectionnée</h3>
                <p>Vous devez être associé à au moins une société pour utiliser ComptaPro.</p>
                <Link :href="route('companies.create')" class="btn btn-primary btn-lg mt-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Créer une société
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Dashboard Header */
.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 4px 0 0;
    font-size: 14px;
}

.company-selector {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 2px solid #e5e7eb;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 600;
    color: #374151;
    transition: all 0.3s;
    cursor: pointer;
}

.company-selector:hover {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.company-dropdown {
    min-width: 280px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    padding: 8px;
}

.company-dropdown .dropdown-item {
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.company-dropdown .dropdown-item.active {
    background: #eff6ff;
    color: #3b82f6;
}

/* Dashboard Content */
.dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 32px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.banner-content {
    display: flex;
    align-items: center;
    gap: 24px;
}

.banner-icon {
    width: 64px;
    height: 64px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.banner-text h4 {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
}

.banner-text p {
    margin: 0;
    opacity: 0.9;
}

.banner-badges {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.info-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

/* Section Title */
.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px;
}

/* Quick Actions */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.action-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #374151;
    transition: all 0.3s;
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.action-primary:hover { border-color: #3b82f6; color: #3b82f6; }
.action-success:hover { border-color: #10b981; color: #10b981; }
.action-info:hover { border-color: #06b6d4; color: #06b6d4; }
.action-warning:hover { border-color: #f59e0b; color: #f59e0b; }

.action-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.action-primary .action-icon { background: #eff6ff; color: #3b82f6; }
.action-success .action-icon { background: #f0fdf4; color: #10b981; }
.action-info .action-icon { background: #ecfeff; color: #06b6d4; }
.action-warning .action-icon { background: #fffbeb; color: #f59e0b; }

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    border: 2px solid transparent;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.stat-customers:hover { border-color: #3b82f6; }
.stat-invoices:hover { border-color: #10b981; }
.stat-revenue:hover { border-color: #8b5cf6; }
.stat-pending:hover { border-color: #f59e0b; }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.stat-customers .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
.stat-invoices .stat-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; }
.stat-revenue .stat-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; }
.stat-pending .stat-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: #fff; }

.stat-menu {
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
}

.stat-breakdown {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 600;
}

.trend-up {
    color: #10b981;
}

.trend-down {
    color: #ef4444;
}

.stat-alert {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #ef4444;
    font-size: 14px;
    font-weight: 500;
}

.stat-footer {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.stat-link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.stat-link:hover {
    gap: 12px;
}

/* Charts Section */
.charts-section {
    margin-top: 8px;
}

.chart-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    height: 100%;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.chart-header h6 {
    margin: 0;
    font-weight: 700;
    color: #1f2937;
}

.progress-circle {
    position: relative;
    width: 200px;
    height: 200px;
    margin: 0 auto 32px;
}

.progress-circle svg {
    transform: rotate(-90deg);
}

.progress-bg {
    fill: none;
    stroke: #f3f4f6;
    stroke-width: 10;
}

.progress-bar {
    fill: none;
    stroke: url(#gradient);
    stroke-width: 10;
    stroke-linecap: round;
    stroke-dasharray: 283;
    transition: stroke-dashoffset 1s ease;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.progress-value {
    font-size: 36px;
    font-weight: 700;
    color: #1f2937;
}

.progress-label {
    font-size: 14px;
    color: #6b7280;
}

.progress-details {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.detail-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.detail-dot.paid {
    background: #10b981;
}

.detail-dot.pending {
    background: #f59e0b;
}

.detail-label {
    flex: 1;
    color: #6b7280;
    font-size: 14px;
}

.detail-value {
    font-weight: 700;
    color: #1f2937;
}

/* Status Bars */
.status-bars {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.status-bar-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-bar-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #374151;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-dot.paid { background: #10b981; }
.status-dot.sent { background: #3b82f6; }
.status-dot.draft { background: #6b7280; }
.status-dot.overdue { background: #ef4444; }

.status-bar-progress {
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.status-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.status-bar-fill.paid { background: #10b981; }
.status-bar-fill.sent { background: #3b82f6; }
.status-bar-fill.draft { background: #6b7280; }
.status-bar-fill.overdue { background: #ef4444; }

/* Activity Section */
.activity-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.activity-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-header h6 {
    margin: 0;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
}

.view-all {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: gap 0.3s;
}

.view-all:hover {
    gap: 8px;
}

.activity-list {
    flex: 1;
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid #f9fafb;
    transition: all 0.3s;
}

.activity-item:hover {
    background: #f9fafb;
}

.activity-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
}

.avatar-draft { background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); }
.avatar-sent { background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%); }
.avatar-paid { background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); }
.avatar-overdue { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.activity-details {
    flex: 1;
    min-width: 0;
}

.activity-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-subtitle {
    font-size: 14px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-meta {
    text-align: right;
}

.meta-badge {
    background: #f3f4f6;
    color: #6b7280;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.meta-amount {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.meta-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-draft { background: #f3f4f6; color: #6b7280; }
.status-sent { background: #dbeafe; color: #1e40af; }
.status-paid { background: #d1fae5; color: #065f46; }
.status-overdue { background: #fee2e2; color: #991b1b; }

.empty-state {
    padding: 60px 24px;
    text-align: center;
    color: #9ca3af;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

.activity-footer {
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
}

.btn-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: #fff;
}

/* No Company State */
.no-company-state {
    text-align: center;
    padding: 80px 24px;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 60px;
}

.no-company-state h3 {
    margin-bottom: 12px;
    color: #1f2937;
}

.no-company-state p {
    color: #6b7280;
    max-width: 500px;
    margin: 0 auto 24px;
}

/* Responsive */
@media (max-width: 991px) {
    .welcome-banner {
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
    }

    .banner-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .banner-badges {
        width: 100%;
    }

    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 575px) {
    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .action-card {
        padding: 16px;
    }

    .action-icon {
        width: 48px;
        height: 48px;
        font-size: 24px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
