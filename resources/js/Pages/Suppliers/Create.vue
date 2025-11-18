<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Nouveau fournisseur</h2>
        </template>

        <div class="py-4">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <h5 class="mb-3">Informations générales</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input v-model="form.name" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                            <select v-model="form.category" class="form-select" required>
                                                <option value="goods">Biens</option>
                                                <option value="services">Services</option>
                                                <option value="both">Les deux</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input v-model="form.email" type="email" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Téléphone</label>
                                            <input v-model="form.phone" type="tel" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Numéro de TVA</label>
                                            <input v-model="form.vat_number" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Personne de contact</label>
                                            <input v-model="form.contact_person" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Adresse</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label">Adresse</label>
                                            <textarea v-model="form.address" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Code postal</label>
                                            <input v-model="form.postal_code" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ville</label>
                                            <input v-model="form.city" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Pays (code ISO)</label>
                                            <input v-model="form.country_code" type="text" class="form-control" maxlength="2" placeholder="FR, BE, CH...">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Informations bancaires</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-12">
                                            <label class="form-label">Numéro de compte</label>
                                            <input v-model="form.bank_account" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">IBAN</label>
                                            <input v-model="form.iban" type="text" class="form-control" maxlength="34">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">BIC/SWIFT</label>
                                            <input v-model="form.bic" type="text" class="form-control" maxlength="11">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Conditions de paiement</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Délai de paiement <span class="text-danger">*</span></label>
                                            <select v-model="form.payment_term" class="form-select" required>
                                                <option value="immediate">Immédiat</option>
                                                <option value="15_days">15 jours</option>
                                                <option value="30_days">30 jours</option>
                                                <option value="45_days">45 jours</option>
                                                <option value="60_days">60 jours</option>
                                                <option value="90_days">90 jours</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mt-4">
                                                <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive">
                                                <label class="form-check-label" for="isActive">
                                                    Fournisseur actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Notes</h5>
                                    <div class="mb-4">
                                        <textarea v-model="form.notes" class="form-control" rows="3" placeholder="Notes internes..."></textarea>
                                    </div>

                                    <hr>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Créer le fournisseur
                                        </button>
                                        <Link :href="route('suppliers.index')" class="btn btn-secondary">
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
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country_code: '',
    vat_number: '',
    payment_term: '30_days',
    bank_account: '',
    iban: '',
    bic: '',
    contact_person: '',
    category: 'both',
    is_active: true,
    notes: ''
});

const submit = () => {
    router.post(route('suppliers.store'), form);
};
</script>
