<template>
    <AuthenticatedLayout>
        <div class="customer-create-page">
            <!-- Header -->
            <div class="page-header animate-fadeInDown">
                <div class="header-content">
                    <div class="header-left">
                        <Link :href="route('customers.index')" class="back-btn">
                            <i class="bi bi-arrow-left"></i>
                        </Link>
                        <div>
                            <h1 class="page-title">
                                <i class="bi bi-person-plus me-3"></i>
                                Nouveau Client
                            </h1>
                            <p class="page-subtitle">Créez un nouveau client</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card animate-fadeInUp">
                <form @submit.prevent="submit">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="bi bi-info-circle me-2"></i>
                                Informations de base
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group col-span-2">
                                <label for="name" class="form-label required">Nom du client</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': form.errors.name }"
                                    placeholder="Ex: Entreprise ABC"
                                    required
                                />
                                <div v-if="form.errors.name" class="error-message">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-with-icon">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="form-input with-icon"
                                        :class="{ 'error': form.errors.email }"
                                        placeholder="contact@exemple.fr"
                                    />
                                </div>
                                <div v-if="form.errors.email" class="error-message">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Téléphone</label>
                                <div class="input-with-icon">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="form-input with-icon"
                                        :class="{ 'error': form.errors.phone }"
                                        placeholder="+33 1 23 45 67 89"
                                    />
                                </div>
                                <div v-if="form.errors.phone" class="error-message">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="vat_number" class="form-label">Numéro TVA</label>
                                <input
                                    id="vat_number"
                                    v-model="form.vat_number"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': form.errors.vat_number }"
                                    placeholder="FR12345678901"
                                />
                                <div v-if="form.errors.vat_number" class="error-message">
                                    {{ form.errors.vat_number }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="bi bi-geo-alt me-2"></i>
                                Adresse
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group col-span-2">
                                <label for="address" class="form-label">Adresse complète</label>
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    class="form-input"
                                    :class="{ 'error': form.errors.address }"
                                    rows="3"
                                    placeholder="123 Rue de la République"
                                ></textarea>
                                <div v-if="form.errors.address" class="error-message">
                                    {{ form.errors.address }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input
                                    id="postal_code"
                                    v-model="form.postal_code"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': form.errors.postal_code }"
                                    placeholder="75001"
                                />
                                <div v-if="form.errors.postal_code" class="error-message">
                                    {{ form.errors.postal_code }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="city" class="form-label">Ville</label>
                                <input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    class="form-input"
                                    :class="{ 'error': form.errors.city }"
                                    placeholder="Paris"
                                />
                                <div v-if="form.errors.city" class="error-message">
                                    {{ form.errors.city }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="country_code" class="form-label">Code pays</label>
                                <select
                                    id="country_code"
                                    v-model="form.country_code"
                                    class="form-input"
                                    :class="{ 'error': form.errors.country_code }"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option value="FR">France</option>
                                    <option value="BE">Belgique</option>
                                    <option value="CH">Suisse</option>
                                    <option value="LU">Luxembourg</option>
                                    <option value="DE">Allemagne</option>
                                    <option value="ES">Espagne</option>
                                    <option value="IT">Italie</option>
                                    <option value="GB">Royaume-Uni</option>
                                </select>
                                <div v-if="form.errors.country_code" class="error-message">
                                    {{ form.errors.country_code }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Settings -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="bi bi-credit-card me-2"></i>
                                Paramètres de paiement
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="payment_term" class="form-label required">Délai de paiement</label>
                                <select
                                    id="payment_term"
                                    v-model="form.payment_term"
                                    class="form-input"
                                    :class="{ 'error': form.errors.payment_term }"
                                    required
                                >
                                    <option value="">Sélectionner...</option>
                                    <option value="immediate">Comptant</option>
                                    <option value="15_days">15 jours</option>
                                    <option value="30_days">30 jours</option>
                                    <option value="45_days">45 jours</option>
                                    <option value="60_days">60 jours</option>
                                </select>
                                <div v-if="form.errors.payment_term" class="error-message">
                                    {{ form.errors.payment_term }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="credit_limit" class="form-label">Limite de crédit</label>
                                <div class="input-with-icon">
                                    <i class="bi bi-currency-euro input-icon"></i>
                                    <input
                                        id="credit_limit"
                                        v-model="form.credit_limit"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-input with-icon"
                                        :class="{ 'error': form.errors.credit_limit }"
                                        placeholder="0.00"
                                    />
                                </div>
                                <div v-if="form.errors.credit_limit" class="error-message">
                                    {{ form.errors.credit_limit }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="bi bi-journal-text me-2"></i>
                                Notes
                            </h3>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Notes internes</label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                class="form-input"
                                :class="{ 'error': form.errors.notes }"
                                rows="4"
                                placeholder="Ajoutez des notes internes..."
                            ></textarea>
                            <div v-if="form.errors.notes" class="error-message">
                                {{ form.errors.notes }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <Link :href="route('customers.index')" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle me-2"></i>
                            Annuler
                        </Link>
                        <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ form.processing ? 'Création...' : 'Créer le client' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country_code: '',
    vat_number: '',
    payment_term: '30_days',
    credit_limit: '',
    notes: '',
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => {
            window.$toast?.success('Client créé avec succès', 'Succès');
        },
        onError: () => {
            window.$toast?.error('Erreur lors de la création du client', 'Erreur');
        }
    });
};
</script>

<style scoped>
.customer-create-page {
    padding: 32px;
    max-width: 1000px;
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
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.back-btn {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #fff;
    border: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 20px;
    transition: all 0.3s;
    text-decoration: none;
}

.back-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #1f2937;
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
    margin: 4px 0 0 0;
    font-size: 16px;
}

/* Form Card */
.form-card {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.form-section {
    margin-bottom: 40px;
}

.form-section:last-of-type {
    margin-bottom: 32px;
}

.section-header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
}

.section-title i {
    color: #3b82f6;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.col-span-2 {
    grid-column: span 2;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
}

.form-label.required::after {
    content: '*';
    color: #ef4444;
    margin-left: 4px;
}

.form-input {
    height: 48px;
    padding: 0 16px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s;
    background: #fff;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.form-input.error {
    border-color: #ef4444;
}

.form-input.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

textarea.form-input {
    height: auto;
    padding: 12px 16px;
    resize: vertical;
    font-family: inherit;
}

select.form-input {
    cursor: pointer;
}

.input-with-icon {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
}

.form-input.with-icon {
    padding-left: 44px;
}

.error-message {
    margin-top: 6px;
    font-size: 13px;
    color: #ef4444;
}

/* Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 24px;
    border-top: 2px solid #f3f4f6;
}

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

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
    .customer-create-page {
        padding: 16px;
    }

    .form-card {
        padding: 24px 16px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .col-span-2 {
        grid-column: span 1;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn-lg {
        width: 100%;
        justify-content: center;
    }
}
</style>
