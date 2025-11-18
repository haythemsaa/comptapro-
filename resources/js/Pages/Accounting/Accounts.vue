<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Plan comptable</h2>
                <button @click="showCreateModal" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Nouveau compte
                </button>
            </div>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input
                                    v-model="filters.search"
                                    @input="applyFilters"
                                    type="text"
                                    class="form-control"
                                    placeholder="Rechercher par code ou nom..."
                                >
                            </div>
                            <div class="col-md-4">
                                <select v-model="filters.type" @change="applyFilters" class="form-select">
                                    <option value="">Tous les types</option>
                                    <option value="asset">Actif</option>
                                    <option value="liability">Passif</option>
                                    <option value="equity">Capitaux propres</option>
                                    <option value="revenue">Produits</option>
                                    <option value="expense">Charges</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accounts Table -->
                <div class="card">
                    <div class="card-body">
                        <div v-if="accounts.data && accounts.data.length > 0" class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th class="text-end">Solde</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account in accounts.data" :key="account.id">
                                        <td><code>{{ account.code }}</code></td>
                                        <td>{{ account.name }}</td>
                                        <td>
                                            <span :class="getTypeClass(account.type)">
                                                {{ getTypeLabel(account.type) }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ formatCurrency(account.balance) }}</td>
                                        <td>
                                            <span :class="account.is_active ? 'badge bg-success' : 'badge bg-secondary'">
                                                {{ account.is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button @click="editAccount(account)" class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button @click="deleteAccount(account)" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-5 text-muted">
                            <i class="bi bi-list-ol" style="font-size: 3rem;"></i>
                            <p class="mt-3">Aucun compte trouvé</p>
                            <button @click="showCreateModal" class="btn btn-primary">
                                Créer le premier compte
                            </button>
                        </div>

                        <!-- Pagination -->
                        <nav v-if="accounts.data && accounts.data.length > 0" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: !accounts.prev_page_url }">
                                    <Link class="page-link" :href="accounts.prev_page_url || '#'" preserve-scroll>Précédent</Link>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">Page {{ accounts.current_page }} / {{ accounts.last_page }}</span>
                                </li>
                                <li class="page-item" :class="{ disabled: !accounts.next_page_url }">
                                    <Link class="page-link" :href="accounts.next_page_url || '#'" preserve-scroll>Suivant</Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal fade" id="accountModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingAccount ? 'Modifier le compte' : 'Nouveau compte' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form @submit.prevent="saveAccount">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <input v-model="accountForm.code" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input v-model="accountForm.name" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select v-model="accountForm.type" class="form-select" required>
                                    <option value="asset">Actif</option>
                                    <option value="liability">Passif</option>
                                    <option value="equity">Capitaux propres</option>
                                    <option value="revenue">Produits</option>
                                    <option value="expense">Charges</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea v-model="accountForm.description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-check">
                                <input v-model="accountForm.is_active" type="checkbox" class="form-check-input" id="is_active">
                                <label class="form-check-label" for="is_active">Compte actif</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                {{ editingAccount ? 'Enregistrer' : 'Créer' }}
                            </button>
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
    accounts: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || ''
});

const accountForm = reactive({
    code: '',
    name: '',
    type: 'expense',
    description: '',
    is_active: true
});

const editingAccount = ref(null);
let accountModal = null;

const applyFilters = () => {
    router.get(route('accounting.accounts'), filters, {
        preserveState: true,
        preserveScroll: true
    });
};

const getTypeLabel = (type) => {
    const labels = {
        asset: 'Actif',
        liability: 'Passif',
        equity: 'Capitaux propres',
        revenue: 'Produits',
        expense: 'Charges'
    };
    return labels[type] || type;
};

const getTypeClass = (type) => {
    const classes = {
        asset: 'badge bg-success',
        liability: 'badge bg-danger',
        equity: 'badge bg-primary',
        revenue: 'badge bg-info',
        expense: 'badge bg-warning'
    };
    return classes[type] || 'badge bg-secondary';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount || 0);
};

const showCreateModal = () => {
    editingAccount.value = null;
    Object.assign(accountForm, {
        code: '',
        name: '',
        type: 'expense',
        description: '',
        is_active: true
    });
    if (!accountModal) {
        accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
    }
    accountModal.show();
};

const editAccount = (account) => {
    editingAccount.value = account;
    Object.assign(accountForm, {
        code: account.code,
        name: account.name,
        type: account.type,
        description: account.description || '',
        is_active: account.is_active
    });
    if (!accountModal) {
        accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
    }
    accountModal.show();
};

const saveAccount = () => {
    const url = editingAccount.value
        ? route('accounting.accounts.update', editingAccount.value.id)
        : route('accounting.accounts.store');

    const method = editingAccount.value ? 'patch' : 'post';

    router[method](url, accountForm, {
        preserveScroll: true,
        onSuccess: () => {
            if (accountModal) accountModal.hide();
        }
    });
};

const deleteAccount = (account) => {
    if (confirm(`Supprimer le compte ${account.code} - ${account.name} ?`)) {
        router.delete(route('accounting.accounts.destroy', account.id), {
            preserveScroll: true
        });
    }
};
</script>
