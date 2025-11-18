<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Nouvelle facture d'achat</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <form @submit.prevent="submit">
                    <div class="row">
                        <!-- Main Form -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Type <span class="text-danger">*</span></label>
                                            <select v-model="form.type" class="form-select" required>
                                                <option value="purchase">Facture</option>
                                                <option value="credit_note">Avoir</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                                            <select v-model="form.supplier_id" class="form-select" required>
                                                <option value="">Sélectionner un fournisseur</option>
                                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                                    {{ supplier.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Statut <span class="text-danger">*</span></label>
                                            <select v-model="form.status" class="form-select" required>
                                                <option value="draft">Brouillon</option>
                                                <option value="received">Reçue</option>
                                                <option value="approved">Approuvée</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">N° facture fournisseur</label>
                                            <input v-model="form.supplier_invoice_number" type="text" class="form-control" placeholder="Numéro du fournisseur">
                                        </div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date facture <span class="text-danger">*</span></label>
                                            <input v-model="form.invoice_date" type="date" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date d'échéance</label>
                                            <input v-model="form.due_date" type="date" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea v-model="form.notes" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchase Lines -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Lignes de facture</h5>
                                    <button type="button" @click="addLine" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div v-if="form.lines.length === 0" class="text-center text-muted py-4">
                                        <p>Aucune ligne ajoutée</p>
                                        <button type="button" @click="addLine" class="btn btn-primary">
                                            Ajouter la première ligne
                                        </button>
                                    </div>

                                    <div v-for="(line, index) in form.lines" :key="index" class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">Ligne {{ index + 1 }}</h6>
                                            <button type="button" @click="removeLine(index)" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small">Produit</label>
                                                <select v-model="line.product_id" @change="selectProduct(index)" class="form-select form-select-sm">
                                                    <option value="">Saisie libre</option>
                                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                                        {{ product.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small">Compte comptable</label>
                                                <select v-model="line.account_id" class="form-select form-select-sm">
                                                    <option value="">Sélectionner un compte</option>
                                                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                                                        {{ account.code }} - {{ account.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small">Description <span class="text-danger">*</span></label>
                                                <input v-model="line.description" type="text" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Quantité <span class="text-danger">*</span></label>
                                                <input v-model.number="line.quantity" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Unité</label>
                                                <input v-model="line.unit" type="text" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Prix unitaire HT <span class="text-danger">*</span></label>
                                                <input v-model.number="line.unit_price" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">TVA (%) <span class="text-danger">*</span></label>
                                                <input v-model.number="line.vat_rate" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-12 text-end">
                                                <strong>Total TTC: {{ formatAmount(line.total) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Sidebar -->
                        <div class="col-lg-4">
                            <div class="card sticky-top" style="top: 1rem;">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Récapitulatif</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total HT</td>
                                            <td class="text-end fw-bold">{{ formatAmount(totals.subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td>TVA</td>
                                            <td class="text-end fw-bold">{{ formatAmount(totals.tax) }}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Total TTC</strong></td>
                                            <td class="text-end"><strong>{{ formatAmount(totals.total) }}</strong></td>
                                        </tr>
                                    </table>
                                    <hr>
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="bi bi-check-lg me-2"></i>Créer la facture
                                    </button>
                                    <Link :href="route('purchases.index')" class="btn btn-secondary w-100">
                                        Annuler
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    suppliers: Array,
    products: Array,
    accounts: Array
});

const form = reactive({
    supplier_id: '',
    supplier_invoice_number: '',
    type: 'purchase',
    status: 'draft',
    invoice_date: new Date().toISOString().split('T')[0],
    due_date: '',
    notes: '',
    lines: []
});

const addLine = () => {
    form.lines.push({
        product_id: '',
        account_id: '',
        description: '',
        quantity: 1,
        unit: 'unité',
        unit_price: 0,
        vat_rate: 20,
        subtotal: 0,
        tax_amount: 0,
        total: 0
    });
};

const removeLine = (index) => {
    form.lines.splice(index, 1);
};

const selectProduct = (index) => {
    const line = form.lines[index];
    if (line.product_id) {
        const product = props.products.find(p => p.id === line.product_id);
        if (product) {
            line.description = product.description || product.name;
            line.unit_price = product.unit_price || 0;
            line.vat_rate = product.vat_rate || 20;
            calculateLine(index);
        }
    }
};

const calculateLine = (index) => {
    const line = form.lines[index];
    const subtotal = line.quantity * line.unit_price;
    const tax = subtotal * (line.vat_rate / 100);
    line.subtotal = subtotal;
    line.tax_amount = tax;
    line.total = subtotal + tax;
};

const totals = computed(() => {
    const subtotal = form.lines.reduce((sum, line) => sum + (line.quantity * line.unit_price), 0);
    const tax = form.lines.reduce((sum, line) => sum + (line.quantity * line.unit_price * line.vat_rate / 100), 0);
    return { subtotal, tax, total: subtotal + tax };
});

const formatAmount = (value) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(value || 0);
};

const submit = () => {
    router.post(route('purchases.store'), form);
};

// Add first line by default
addLine();
</script>
