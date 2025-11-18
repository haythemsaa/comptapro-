<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Modifier facture d'achat - {{ invoice.invoice_number }}</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div v-if="invoice.status !== 'draft'" class="alert alert-warning">
                    Attention : Seules les factures en brouillon peuvent être modifiées.
                </div>

                <form @submit.prevent="submit" v-if="invoice.status === 'draft'">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informations générales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Type</label>
                                            <select v-model="form.type" class="form-select" required>
                                                <option value="purchase">Facture</option>
                                                <option value="credit_note">Avoir</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Fournisseur</label>
                                            <select v-model="form.supplier_id" class="form-select" required>
                                                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                                    {{ supplier.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Statut</label>
                                            <select v-model="form.status" class="form-select" required>
                                                <option value="draft">Brouillon</option>
                                                <option value="received">Reçue</option>
                                                <option value="approved">Approuvée</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">N° facture fournisseur</label>
                                            <input v-model="form.supplier_invoice_number" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date facture</label>
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

                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <h5 class="card-title mb-0">Lignes de facture</h5>
                                    <button type="button" @click="addLine" class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>Ajouter
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div v-for="(line, index) in form.lines" :key="index" class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6>Ligne {{ index + 1 }}</h6>
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
                                                <label class="form-label small">Compte</label>
                                                <select v-model="line.account_id" class="form-select form-select-sm">
                                                    <option value="">Compte...</option>
                                                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                                                        {{ account.code }} - {{ account.name }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small">Description</label>
                                                <input v-model="line.description" type="text" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Quantité</label>
                                                <input v-model.number="line.quantity" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Unité</label>
                                                <input v-model="line.unit" type="text" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Prix HT</label>
                                                <input v-model.number="line.unit_price" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">TVA %</label>
                                                <input v-model.number="line.vat_rate" @input="calculateLine(index)" type="number" step="0.01" class="form-control form-control-sm" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card sticky-top" style="top: 1rem;">
                                <div class="card-body">
                                    <h5>Récapitulatif</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Total HT</td>
                                            <td class="text-end">{{ formatAmount(totals.subtotal) }}</td>
                                        </tr>
                                        <tr>
                                            <td>TVA</td>
                                            <td class="text-end">{{ formatAmount(totals.tax) }}</td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Total TTC</strong></td>
                                            <td class="text-end"><strong>{{ formatAmount(totals.total) }}</strong></td>
                                        </tr>
                                    </table>
                                    <hr>
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="bi bi-check-lg me-2"></i>Mettre à jour
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
    invoice: Object,
    suppliers: Array,
    products: Array,
    accounts: Array
});

const form = reactive({
    supplier_id: props.invoice.supplier_id,
    supplier_invoice_number: props.invoice.supplier_invoice_number,
    type: props.invoice.type,
    status: props.invoice.status,
    invoice_date: props.invoice.invoice_date,
    due_date: props.invoice.due_date,
    notes: props.invoice.notes,
    lines: props.invoice.lines.map(line => ({
        product_id: line.product_id,
        account_id: line.account_id,
        description: line.description,
        quantity: line.quantity,
        unit: line.unit,
        unit_price: line.unit_price,
        vat_rate: line.vat_rate,
        subtotal: line.subtotal,
        tax_amount: line.tax_amount,
        total: line.total
    }))
});

const addLine = () => {
    form.lines.push({
        product_id: '', account_id: '', description: '', quantity: 1, unit: 'unité',
        unit_price: 0, vat_rate: 20, subtotal: 0, tax_amount: 0, total: 0
    });
};

const removeLine = (index) => form.lines.splice(index, 1);

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
    line.subtotal = line.quantity * line.unit_price;
    line.tax_amount = line.subtotal * (line.vat_rate / 100);
    line.total = line.subtotal + line.tax_amount;
};

const totals = computed(() => {
    const subtotal = form.lines.reduce((sum, line) => sum + (line.quantity * line.unit_price), 0);
    const tax = form.lines.reduce((sum, line) => sum + (line.quantity * line.unit_price * line.vat_rate / 100), 0);
    return { subtotal, tax, total: subtotal + tax };
});

const formatAmount = (value) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value || 0);

const submit = () => router.patch(route('purchases.update', props.invoice.id), form);
</script>
