<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="h4 mb-0">Nouvelle société</h2>
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
                                            <label class="form-label">Pays <span class="text-danger">*</span></label>
                                            <select v-model="form.country_id" class="form-select" required>
                                                <option value="">Sélectionner un pays</option>
                                                <option v-for="country in countries" :key="country.id" :value="country.id">
                                                    {{ country.name }} ({{ country.currency }})
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Plan d'abonnement <span class="text-danger">*</span></label>
                                            <select v-model="form.subscription_plan" class="form-select" required>
                                                <option value="starter">Starter</option>
                                                <option value="professional">Professional</option>
                                                <option value="enterprise">Enterprise</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nom commercial <span class="text-danger">*</span></label>
                                            <input v-model="form.name" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Raison sociale <span class="text-danger">*</span></label>
                                            <input v-model="form.legal_name" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Numéro de TVA</label>
                                            <input v-model="form.vat_number" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Numéro d'entreprise</label>
                                            <input v-model="form.registration_number" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Contact</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input v-model="form.email" type="email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Téléphone</label>
                                            <input v-model="form.phone" type="tel" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Site web</label>
                                            <input v-model="form.website" type="url" class="form-control">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Adresse</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <label class="form-label">Adresse ligne 1 <span class="text-danger">*</span></label>
                                            <input v-model="form.address_line1" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Adresse ligne 2</label>
                                            <input v-model="form.address_line2" type="text" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Code postal <span class="text-danger">*</span></label>
                                            <input v-model="form.postal_code" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                                            <input v-model="form.city" type="text" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Région/État</label>
                                            <input v-model="form.state" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <h5 class="mb-3">Paramètres comptables</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Début d'exercice fiscal <span class="text-danger">*</span></label>
                                            <select v-model.number="form.fiscal_year_start" class="form-select" required>
                                                <option v-for="month in 12" :key="month" :value="month">
                                                    {{ getMonthName(month) }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Créer la société
                                        </button>
                                        <Link :href="route('companies.index')" class="btn btn-secondary">
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

defineProps({
    countries: Array
});

const form = reactive({
    country_id: '',
    name: '',
    legal_name: '',
    vat_number: '',
    registration_number: '',
    email: '',
    phone: '',
    website: '',
    address_line1: '',
    address_line2: '',
    postal_code: '',
    city: '',
    state: '',
    fiscal_year_start: 1,
    subscription_plan: 'professional'
});

const getMonthName = (month) => {
    const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    return months[month - 1];
};

const submit = () => {
    router.post(route('companies.store'), form);
};
</script>
