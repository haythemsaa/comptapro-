<template>
    <Teleport to="body">
        <Transition name="search-modal">
            <div v-if="isOpen" class="global-search-overlay" @click="close">
                <div class="global-search-modal" @click.stop>
                    <!-- Search Input -->
                    <div class="search-header">
                        <i class="bi bi-search search-icon"></i>
                        <input
                            ref="searchInput"
                            v-model="query"
                            type="text"
                            class="search-input"
                            placeholder="Rechercher clients, factures, produits..."
                            @input="handleSearch"
                            @keydown.down.prevent="navigateDown"
                            @keydown.up.prevent="navigateUp"
                            @keydown.enter.prevent="selectResult"
                            @keydown.esc="close"
                        />
                        <button @click="close" class="close-btn">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isSearching" class="search-loading">
                        <div class="spinner"></div>
                        <span>Recherche en cours...</span>
                    </div>

                    <!-- Results -->
                    <div v-else-if="query.length > 0" class="search-results">
                        <!-- No Results -->
                        <div v-if="!hasResults" class="no-results">
                            <i class="bi bi-search"></i>
                            <p>Aucun résultat pour "{{ query }}"</p>
                        </div>

                        <!-- Results by Category -->
                        <div v-else class="results-container">
                            <!-- Customers -->
                            <div v-if="results.customers?.length > 0" class="result-category">
                                <div class="category-header">
                                    <i class="bi bi-people"></i>
                                    <span>Clients ({{ results.customers.length }})</span>
                                </div>
                                <div
                                    v-for="(customer, index) in results.customers"
                                    :key="`customer-${customer.id}`"
                                    :class="['result-item', { 'active': selectedIndex === getResultIndex('customers', index) }]"
                                    @click="navigateToCustomer(customer)"
                                    @mouseenter="selectedIndex = getResultIndex('customers', index)"
                                >
                                    <div class="result-icon customer-icon">
                                        {{ getInitials(customer.name) }}
                                    </div>
                                    <div class="result-content">
                                        <div class="result-title" v-html="highlightMatch(customer.name)"></div>
                                        <div class="result-meta">
                                            <span v-if="customer.email">{{ customer.email }}</span>
                                            <span v-if="customer.customer_number">{{ customer.customer_number }}</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right result-arrow"></i>
                                </div>
                            </div>

                            <!-- Invoices -->
                            <div v-if="results.invoices?.length > 0" class="result-category">
                                <div class="category-header">
                                    <i class="bi bi-receipt"></i>
                                    <span>Factures ({{ results.invoices.length }})</span>
                                </div>
                                <div
                                    v-for="(invoice, index) in results.invoices"
                                    :key="`invoice-${invoice.id}`"
                                    :class="['result-item', { 'active': selectedIndex === getResultIndex('invoices', index) }]"
                                    @click="navigateToInvoice(invoice)"
                                    @mouseenter="selectedIndex = getResultIndex('invoices', index)"
                                >
                                    <div class="result-icon invoice-icon">
                                        <i class="bi bi-receipt-cutoff"></i>
                                    </div>
                                    <div class="result-content">
                                        <div class="result-title" v-html="highlightMatch(invoice.number)"></div>
                                        <div class="result-meta">
                                            <span>{{ invoice.customer_name }}</span>
                                            <span>{{ formatAmount(invoice.total) }}</span>
                                        </div>
                                    </div>
                                    <span :class="['status-badge', `status-${invoice.status}`]">
                                        {{ getStatusLabel(invoice.status) }}
                                    </span>
                                    <i class="bi bi-arrow-right result-arrow"></i>
                                </div>
                            </div>

                            <!-- Products -->
                            <div v-if="results.products?.length > 0" class="result-category">
                                <div class="category-header">
                                    <i class="bi bi-box"></i>
                                    <span>Produits ({{ results.products.length }})</span>
                                </div>
                                <div
                                    v-for="(product, index) in results.products"
                                    :key="`product-${product.id}`"
                                    :class="['result-item', { 'active': selectedIndex === getResultIndex('products', index) }]"
                                    @click="navigateToProduct(product)"
                                    @mouseenter="selectedIndex = getResultIndex('products', index)"
                                >
                                    <div class="result-icon product-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div class="result-content">
                                        <div class="result-title" v-html="highlightMatch(product.name)"></div>
                                        <div class="result-meta">
                                            <span>{{ product.reference }}</span>
                                            <span>{{ formatAmount(product.price) }}</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right result-arrow"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="search-empty">
                        <i class="bi bi-search"></i>
                        <p>Commencez à taper pour rechercher</p>
                        <div class="search-tips">
                            <div class="tip">
                                <kbd>↑</kbd> <kbd>↓</kbd> pour naviguer
                            </div>
                            <div class="tip">
                                <kbd>Enter</kbd> pour sélectionner
                            </div>
                            <div class="tip">
                                <kbd>Esc</kbd> pour fermer
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'search']);

const query = ref('');
const results = ref({
    customers: [],
    invoices: [],
    products: []
});
const isSearching = ref(false);
const selectedIndex = ref(0);
const searchInput = ref(null);

const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const hasResults = computed(() => {
    return results.value.customers?.length > 0 ||
           results.value.invoices?.length > 0 ||
           results.value.products?.length > 0;
});

const totalResults = computed(() => {
    return (results.value.customers?.length || 0) +
           (results.value.invoices?.length || 0) +
           (results.value.products?.length || 0);
});

// Rechercher (simulation - à remplacer par vraie API)
const performSearch = debounce(async () => {
    if (query.value.length === 0) {
        results.value = { customers: [], invoices: [], products: [] };
        return;
    }

    isSearching.value = true;

    try {
        // Simulation d'API - À remplacer par vraie requête
        const mockSearch = () => {
            const q = query.value.toLowerCase();

            return {
                customers: [
                    { id: 1, name: 'Acme Corporation', email: 'contact@acme.com', customer_number: 'C-001' },
                    { id: 2, name: 'TechStart SA', email: 'hello@techstart.fr', customer_number: 'C-002' }
                ].filter(c => c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)),

                invoices: [
                    { id: 1, number: 'FA-2024-001', customer_name: 'Acme Corp', total: 15000, status: 'paid' },
                    { id: 2, number: 'FA-2024-002', customer_name: 'TechStart', total: 8500, status: 'pending' }
                ].filter(i => i.number.toLowerCase().includes(q) || i.customer_name.toLowerCase().includes(q)),

                products: [
                    { id: 1, name: 'Licence Pro', reference: 'LIC-PRO-001', price: 299 },
                    { id: 2, name: 'Support Premium', reference: 'SUP-PREM-001', price: 99 }
                ].filter(p => p.name.toLowerCase().includes(q) || p.reference.toLowerCase().includes(q))
            };
        };

        await new Promise(resolve => setTimeout(resolve, 300));
        results.value = mockSearch();
        selectedIndex.value = 0;

    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isSearching.value = false;
    }
}, 300);

const handleSearch = () => {
    performSearch();
    emit('search', query.value);
};

// Navigation
const getResultIndex = (category, index) => {
    let totalIndex = 0;

    if (category === 'customers') {
        return index;
    }

    totalIndex += results.value.customers?.length || 0;

    if (category === 'invoices') {
        return totalIndex + index;
    }

    totalIndex += results.value.invoices?.length || 0;

    if (category === 'products') {
        return totalIndex + index;
    }

    return totalIndex;
};

const navigateDown = () => {
    if (selectedIndex.value < totalResults.value - 1) {
        selectedIndex.value++;
        scrollToSelected();
    }
};

const navigateUp = () => {
    if (selectedIndex.value > 0) {
        selectedIndex.value--;
        scrollToSelected();
    }
};

const scrollToSelected = () => {
    nextTick(() => {
        const activeElement = document.querySelector('.result-item.active');
        if (activeElement) {
            activeElement.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    });
};

const selectResult = () => {
    let currentIndex = 0;

    // Customers
    for (let i = 0; i < (results.value.customers?.length || 0); i++) {
        if (currentIndex === selectedIndex.value) {
            navigateToCustomer(results.value.customers[i]);
            return;
        }
        currentIndex++;
    }

    // Invoices
    for (let i = 0; i < (results.value.invoices?.length || 0); i++) {
        if (currentIndex === selectedIndex.value) {
            navigateToInvoice(results.value.invoices[i]);
            return;
        }
        currentIndex++;
    }

    // Products
    for (let i = 0; i < (results.value.products?.length || 0); i++) {
        if (currentIndex === selectedIndex.value) {
            navigateToProduct(results.value.products[i]);
            return;
        }
        currentIndex++;
    }
};

// Navigation helpers
const navigateToCustomer = (customer) => {
    router.visit(route('customers.show', customer.id));
    close();
};

const navigateToInvoice = (invoice) => {
    router.visit(route('invoices.show', invoice.id));
    close();
};

const navigateToProduct = (product) => {
    router.visit(route('products.edit', product.id));
    close();
};

// Helpers
const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const highlightMatch = (text) => {
    if (!query.value) return text;

    const regex = new RegExp(`(${query.value})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
};

const formatAmount = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const getStatusLabel = (status) => {
    const labels = {
        paid: 'Payée',
        pending: 'En attente',
        overdue: 'En retard',
        draft: 'Brouillon'
    };
    return labels[status] || status;
};

const close = () => {
    isOpen.value = false;
    query.value = '';
    results.value = { customers: [], invoices: [], products: [] };
    selectedIndex.value = 0;
};

// Focus input when opened
watch(isOpen, (newValue) => {
    if (newValue) {
        nextTick(() => {
            searchInput.value?.focus();
        });
    }
});
</script>

<style scoped>
.global-search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 100px 20px 20px;
    overflow-y: auto;
}

.global-search-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 700px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    max-height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
}

.search-header {
    display: flex;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid #f3f4f6;
    gap: 12px;
    flex-shrink: 0;
}

.search-icon {
    font-size: 24px;
    color: #9ca3af;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 18px;
    color: #1f2937;
}

.search-input::placeholder {
    color: #9ca3af;
}

.close-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: none;
    background: #f3f4f6;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 20px;
    color: #6b7280;
}

.close-btn:hover {
    background: #e5e7eb;
}

.search-loading {
    padding: 60px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    color: #6b7280;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.search-results {
    flex: 1;
    overflow-y: auto;
    min-height: 200px;
}

.results-container {
    padding: 12px 0;
}

.result-category {
    margin-bottom: 20px;
}

.category-header {
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.result-item {
    padding: 12px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.result-item:hover,
.result-item.active {
    background: #f3f4f6;
}

.result-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
    flex-shrink: 0;
}

.customer-icon {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    font-size: 16px;
}

.invoice-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
}

.product-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
}

.result-content {
    flex: 1;
    min-width: 0;
}

.result-title {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.result-title :deep(mark) {
    background: #fef3c7;
    color: #92400e;
    padding: 2px 4px;
    border-radius: 4px;
}

.result-meta {
    display: flex;
    gap: 12px;
    font-size: 13px;
    color: #6b7280;
}

.result-meta span:not(:last-child)::after {
    content: '•';
    margin-left: 12px;
    color: #d1d5db;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-overdue {
    background: #fee2e2;
    color: #991b1b;
}

.result-arrow {
    color: #d1d5db;
    font-size: 16px;
    flex-shrink: 0;
}

.no-results,
.search-empty {
    padding: 80px 24px;
    text-align: center;
    color: #6b7280;
}

.no-results i,
.search-empty i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 16px;
    display: block;
}

.no-results p,
.search-empty p {
    font-size: 16px;
    margin: 0;
}

.search-tips {
    display: flex;
    justify-content: center;
    gap: 24px;
    margin-top: 32px;
}

.tip {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #9ca3af;
}

kbd {
    padding: 4px 8px;
    border-radius: 4px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    font-family: monospace;
    font-size: 12px;
    color: #4b5563;
}

/* Transitions */
.search-modal-enter-active,
.search-modal-leave-active {
    transition: opacity 0.2s;
}

.search-modal-enter-active .global-search-modal,
.search-modal-leave-active .global-search-modal {
    transition: transform 0.2s, opacity 0.2s;
}

.search-modal-enter-from,
.search-modal-leave-to {
    opacity: 0;
}

.search-modal-enter-from .global-search-modal,
.search-modal-leave-to .global-search-modal {
    transform: scale(0.95);
    opacity: 0;
}

/* Scrollbar */
.search-results::-webkit-scrollbar {
    width: 8px;
}

.search-results::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.search-results::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.search-results::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
