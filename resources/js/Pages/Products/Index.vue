<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Produits & Services</h2>
                <Link :href="route('products.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouveau produit
                </Link>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form @submit.prevent="applyFilters" class="row g-3">
                            <div class="col-md-6">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Rechercher par nom, SKU, description..."
                                >
                            </div>
                            <div class="col-md-4">
                                <select v-model="filters.category" class="form-select">
                                    <option value="">Toutes les catégories</option>
                                    <option value="Service">Service</option>
                                    <option value="Product">Produit</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary w-100">
                                    <i class="bi bi-funnel me-2"></i>Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-body">
                        <div v-if="products.data && products.data.length > 0" class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>SKU</th>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th class="text-end">Prix HT</th>
                                        <th class="text-end">Prix revient</th>
                                        <th class="text-center">TVA</th>
                                        <th class="text-center">Stock</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in products.data" :key="product.id">
                                        <td><code>{{ product.sku }}</code></td>
                                        <td>
                                            <strong>{{ product.name }}</strong>
                                            <div v-if="product.description" class="small text-muted">
                                                {{ product.description?.substring(0, 60) }}{{ product.description?.length > 60 ? '...' : '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span v-if="product.category" class="badge bg-secondary">{{ product.category }}</span>
                                        </td>
                                        <td class="text-end">{{ formatCurrency(product.unit_price) }}</td>
                                        <td class="text-end">{{ formatCurrency(product.cost_price) }}</td>
                                        <td class="text-center">{{ product.vat_rate }}%</td>
                                        <td class="text-center">
                                            <span v-if="product.stock_quantity !== null" :class="getStockClass(product)">
                                                {{ product.stock_quantity }} {{ product.unit }}
                                            </span>
                                            <span v-else class="text-muted">-</span>
                                        </td>
                                        <td>
                                            <span :class="product.is_active ? 'badge bg-success' : 'badge bg-secondary'">
                                                {{ product.is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <Link :href="route('products.edit', product.id)" class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    @click="toggleActive(product)"
                                                    class="btn btn-outline-secondary"
                                                    :title="product.is_active ? 'Désactiver' : 'Activer'"
                                                >
                                                    <i :class="product.is_active ? 'bi bi-pause' : 'bi bi-play'"></i>
                                                </button>
                                                <button
                                                    @click="deleteProduct(product)"
                                                    class="btn btn-outline-danger"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-5 text-muted">
                            <i class="bi bi-box" style="font-size: 3rem;"></i>
                            <p class="mt-3">Aucun produit trouvé</p>
                            <Link :href="route('products.create')" class="btn btn-primary">
                                Créer votre premier produit
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <nav v-if="products.data && products.data.length > 0" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: !products.prev_page_url }">
                                    <Link class="page-link" :href="products.prev_page_url || '#'" preserve-scroll>Précédent</Link>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">Page {{ products.current_page }} / {{ products.last_page }}</span>
                                </li>
                                <li class="page-item" :class="{ disabled: !products.next_page_url }">
                                    <Link class="page-link" :href="products.next_page_url || '#'" preserve-scroll>Suivant</Link>
                                </li>
                            </ul>
                        </nav>
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
    products: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    category: props.filters?.category || ''
});

const applyFilters = () => {
    router.get(route('products.index'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount || 0);
};

const getStockClass = (product) => {
    if (product.stock_quantity === null) return '';
    if (product.min_stock_level && product.stock_quantity <= product.min_stock_level) {
        return 'text-danger fw-bold';
    }
    return '';
};

const toggleActive = (product) => {
    router.post(route('products.toggle-active', product.id), {}, {
        preserveScroll: true
    });
};

const deleteProduct = (product) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le produit "${product.name}" ?`)) {
        router.delete(route('products.destroy', product.id), {
            preserveScroll: true
        });
    }
};
</script>
