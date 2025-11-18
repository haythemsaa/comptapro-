<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Nouveau produit</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                                            <input v-model="form.sku" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input v-model="form.name" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Catégorie</label>
                                            <input v-model="form.category" type="text" class="form-control" placeholder="ex: Service, Produit...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Unité <span class="text-danger">*</span></label>
                                            <select v-model="form.unit" class="form-select" required>
                                                <option value="unité">Unité</option>
                                                <option value="heure">Heure</option>
                                                <option value="jour">Jour</option>
                                                <option value="kg">Kilogramme</option>
                                                <option value="m">Mètre</option>
                                                <option value="m²">Mètre carré</option>
                                                <option value="litre">Litre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Prix unitaire HT <span class="text-danger">*</span></label>
                                            <input v-model.number="form.unit_price" type="number" step="0.01" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Prix de revient</label>
                                            <input v-model.number="form.cost_price" type="number" step="0.01" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Taux TVA (%) <span class="text-danger">*</span></label>
                                            <input v-model.number="form.vat_rate" type="number" step="0.01" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Quantité en stock</label>
                                            <input v-model.number="form.stock_quantity" type="number" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Stock minimum</label>
                                            <input v-model.number="form.min_stock_level" type="number" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input v-model="form.is_active" type="checkbox" class="form-check-input" id="is_active">
                                                <label class="form-check-label" for="is_active">
                                                    Produit actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Créer le produit
                                        </button>
                                        <Link :href="route('products.index')" class="btn btn-secondary">
                                            Annuler
                                        </Link>
                                    </div>
                                </form>
                            </div>
                        </div>
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

const form = reactive({
    sku: '',
    name: '',
    description: '',
    category: '',
    unit_price: 0,
    cost_price: 0,
    vat_rate: 20,
    unit: 'unité',
    stock_quantity: null,
    min_stock_level: null,
    is_active: true
});

const submit = () => {
    router.post(route('products.store'), form);
};
</script>
