<template>
    <AuthenticatedLayout>
        <div class="customers-page">
            <!-- Header -->
            <div class="page-header animate-fadeInDown">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="page-title">
                            <i class="bi bi-people-fill me-3"></i>
                            Clients
                        </h1>
                        <p class="page-subtitle">Gérez vos clients et leurs informations</p>
                    </div>
                    <div class="header-right">
                        <Link :href="route('customers.create')" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nouveau Client
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid animate-fadeInUp">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Total Clients</div>
                        <div class="stat-value">{{ customers.total }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Clients Actifs</div>
                        <div class="stat-value">{{ activeCustomersCount }}</div>
                    </div>
                </div>
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Factures</div>
                        <div class="stat-value">{{ totalInvoices }}</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="filters-section animate-fadeInUp animate-delay-100">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="search-input"
                        placeholder="Rechercher un client..."
                        @input="handleSearch"
                    />
                    <button v-if="searchQuery" @click="clearSearch" class="clear-btn">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>

            <!-- Customers List -->
            <div class="customers-grid animate-fadeInUp animate-delay-200">
                <div
                    v-for="customer in customers.data"
                    :key="customer.id"
                    class="customer-card hover-lift"
                >
                    <div class="customer-header">
                        <div class="customer-avatar">
                            {{ getInitials(customer.name) }}
                        </div>
                        <div class="customer-info">
                            <h3 class="customer-name">{{ customer.name }}</h3>
                            <span class="customer-number">{{ customer.customer_number }}</span>
                        </div>
                        <div class="customer-status">
                            <span
                                class="status-badge"
                                :class="customer.is_active ? 'status-active' : 'status-inactive'"
                            >
                                {{ customer.is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>

                    <div class="customer-details">
                        <div class="detail-item" v-if="customer.email">
                            <i class="bi bi-envelope detail-icon"></i>
                            <span class="detail-text">{{ customer.email }}</span>
                        </div>
                        <div class="detail-item" v-if="customer.phone">
                            <i class="bi bi-telephone detail-icon"></i>
                            <span class="detail-text">{{ customer.phone }}</span>
                        </div>
                        <div class="detail-item" v-if="customer.city">
                            <i class="bi bi-geo-alt detail-icon"></i>
                            <span class="detail-text">{{ customer.city }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-receipt detail-icon"></i>
                            <span class="detail-text">{{ customer.invoices_count }} facture(s)</span>
                        </div>
                    </div>

                    <div class="customer-actions">
                        <Link
                            :href="route('customers.show', customer.id)"
                            class="btn btn-sm btn-light"
                        >
                            <i class="bi bi-eye"></i>
                            Voir
                        </Link>
                        <Link
                            :href="route('customers.edit', customer.id)"
                            class="btn btn-sm btn-primary"
                        >
                            <i class="bi bi-pencil"></i>
                            Modifier
                        </Link>
                        <button
                            @click="confirmDelete(customer)"
                            class="btn btn-sm btn-danger"
                        >
                            <i class="bi bi-trash"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="customers.data.length === 0" class="empty-state animate-fadeInUp">
                <div class="empty-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="empty-title">Aucun client trouvé</h3>
                <p class="empty-text">Commencez par créer votre premier client</p>
                <Link :href="route('customers.create')" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>
                    Créer un client
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="customers.data.length > 0" class="pagination-wrapper animate-fadeInUp animate-delay-300">
                <nav class="pagination">
                    <Link
                        v-for="link in customers.links"
                        :key="link.label"
                        :href="link.url"
                        :class="['page-link', { 'active': link.active, 'disabled': !link.url }]"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <template #header>
                <h3 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirmer la suppression
                </h3>
            </template>
            <template #body>
                <p>Êtes-vous sûr de vouloir supprimer le client <strong>{{ customerToDelete?.name }}</strong> ?</p>
                <p class="text-muted">Cette action est irréversible.</p>
            </template>
            <template #footer>
                <button @click="showDeleteModal = false" class="btn btn-secondary">
                    Annuler
                </button>
                <button @click="deleteCustomer" class="btn btn-danger">
                    <i class="bi bi-trash me-2"></i>
                    Supprimer
                </button>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    customers: Object,
});

const searchQuery = ref('');
const showDeleteModal = ref(false);
const customerToDelete = ref(null);

const activeCustomersCount = computed(() => {
    return props.customers.data.filter(c => c.is_active).length;
});

const totalInvoices = computed(() => {
    return props.customers.data.reduce((sum, c) => sum + (c.invoices_count || 0), 0);
});

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const handleSearch = () => {
    router.get(route('customers.index'), { search: searchQuery.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    handleSearch();
};

const confirmDelete = (customer) => {
    customerToDelete.value = customer;
    showDeleteModal.value = true;
};

const deleteCustomer = () => {
    if (customerToDelete.value) {
        router.delete(route('customers.destroy', customerToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                customerToDelete.value = null;
                window.$toast?.success('Client supprimé avec succès', 'Succès');
            },
            onError: () => {
                window.$toast?.error('Erreur lors de la suppression', 'Erreur');
            }
        });
    }
};
</script>

<style scoped>
.customers-page {
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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
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

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
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

/* Search and Filters */
.filters-section {
    margin-bottom: 32px;
}

.search-box {
    position: relative;
    max-width: 500px;
}

.search-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 20px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    height: 56px;
    padding: 0 56px;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    font-size: 16px;
    transition: all 0.3s;
    background: #fff;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.clear-btn {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.clear-btn:hover {
    background: #e5e7eb;
}

/* Customers Grid */
.customers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.customer-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.customer-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.customer-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    flex-shrink: 0;
}

.customer-info {
    flex: 1;
    min-width: 0;
}

.customer-name {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-number {
    font-size: 13px;
    color: #6b7280;
    font-family: 'Courier New', monospace;
}

.customer-status {
    flex-shrink: 0;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
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

.customer-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.detail-icon {
    color: #6b7280;
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.detail-text {
    color: #4b5563;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-actions {
    display: flex;
    gap: 8px;
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
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
    .customers-page {
        padding: 16px;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .customers-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
