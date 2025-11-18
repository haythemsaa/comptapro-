<template>
    <teleport to="body">
        <transition name="onboarding-fade">
            <div v-if="isVisible" class="onboarding-overlay">
                <div class="onboarding-container">
                    <!-- Progress Bar -->
                    <div class="onboarding-progress">
                        <div
                            v-for="(step, index) in steps"
                            :key="index"
                            class="progress-step"
                            :class="{
                                'active': index === currentStep,
                                'completed': index < currentStep
                            }"
                        >
                            <div class="progress-circle">
                                <i v-if="index < currentStep" class="bi bi-check"></i>
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <div class="progress-label">{{ step.title }}</div>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div class="onboarding-content">
                        <transition name="slide" mode="out-in">
                            <div :key="currentStep" class="step-content">
                                <div class="step-icon">
                                    <i class="bi" :class="steps[currentStep].icon"></i>
                                </div>
                                <h2 class="step-title">{{ steps[currentStep].title }}</h2>
                                <p class="step-description">{{ steps[currentStep].description }}</p>

                                <!-- Interactive Demo -->
                                <div class="step-demo">
                                    <component :is="steps[currentStep].component" />
                                </div>

                                <!-- Features List -->
                                <ul class="features-list">
                                    <li
                                        v-for="(feature, index) in steps[currentStep].features"
                                        :key="index"
                                        class="feature-item animate-fadeInUp"
                                        :style="{ animationDelay: `${index * 0.1}s` }"
                                    >
                                        <i class="bi bi-check-circle-fill feature-icon"></i>
                                        <span>{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                        </transition>
                    </div>

                    <!-- Navigation -->
                    <div class="onboarding-navigation">
                        <button
                            @click="skip"
                            class="btn-skip"
                        >
                            Passer le tutoriel
                        </button>
                        <div class="nav-buttons">
                            <button
                                v-if="currentStep > 0"
                                @click="previousStep"
                                class="btn-nav btn-prev"
                            >
                                <i class="bi bi-arrow-left me-2"></i>
                                Précédent
                            </button>
                            <button
                                v-if="currentStep < steps.length - 1"
                                @click="nextStep"
                                class="btn-nav btn-next"
                            >
                                Suivant
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                            <button
                                v-else
                                @click="finish"
                                class="btn-nav btn-finish"
                            >
                                <i class="bi bi-check-circle me-2"></i>
                                Commencer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script setup>
import { ref, onMounted, defineAsyncComponent } from 'vue';

const emit = defineEmits(['close', 'complete']);

const isVisible = ref(false);
const currentStep = ref(0);

// Demo Components
const DashboardDemo = defineAsyncComponent(() => ({
    template: `
        <div class="demo-box">
            <div class="demo-dashboard">
                <div class="demo-card">
                    <div class="demo-icon">📊</div>
                    <div class="demo-label">Statistiques</div>
                </div>
                <div class="demo-card">
                    <div class="demo-icon">💰</div>
                    <div class="demo-label">Revenus</div>
                </div>
                <div class="demo-card">
                    <div class="demo-icon">📈</div>
                    <div class="demo-label">Graphiques</div>
                </div>
            </div>
        </div>
    `
}));

const InvoiceDemo = defineAsyncComponent(() => ({
    template: `
        <div class="demo-box">
            <div class="demo-invoice">
                <div class="demo-invoice-header">
                    <div class="demo-logo">🧾</div>
                    <div class="demo-number">INV-00001</div>
                </div>
                <div class="demo-invoice-lines">
                    <div class="demo-line"></div>
                    <div class="demo-line"></div>
                    <div class="demo-line"></div>
                </div>
                <div class="demo-invoice-total">Total: 1,234.56 €</div>
            </div>
        </div>
    `
}));

const CustomerDemo = defineAsyncComponent(() => ({
    template: `
        <div class="demo-box">
            <div class="demo-customers">
                <div class="demo-customer-card">
                    <div class="demo-avatar">AB</div>
                    <div class="demo-info">
                        <div class="demo-name">Client ABC</div>
                        <div class="demo-email">contact@abc.fr</div>
                    </div>
                </div>
                <div class="demo-customer-card">
                    <div class="demo-avatar">XY</div>
                    <div class="demo-info">
                        <div class="demo-name">XYZ SARL</div>
                        <div class="demo-email">info@xyz.fr</div>
                    </div>
                </div>
            </div>
        </div>
    `
}));

const ReportDemo = defineAsyncComponent(() => ({
    template: `
        <div class="demo-box">
            <div class="demo-reports">
                <div class="demo-chart">
                    <div class="demo-bar" style="height: 60%"></div>
                    <div class="demo-bar" style="height: 80%"></div>
                    <div class="demo-bar" style="height: 45%"></div>
                    <div class="demo-bar" style="height: 95%"></div>
                </div>
                <div class="demo-legend">Rapports financiers</div>
            </div>
        </div>
    `
}));

const SettingsDemo = defineAsyncComponent(() => ({
    template: `
        <div class="demo-box">
            <div class="demo-settings">
                <div class="demo-setting-item">
                    <div class="demo-setting-icon">🎨</div>
                    <div class="demo-setting-label">Apparence</div>
                </div>
                <div class="demo-setting-item">
                    <div class="demo-setting-icon">🔔</div>
                    <div class="demo-setting-label">Notifications</div>
                </div>
                <div class="demo-setting-item">
                    <div class="demo-setting-icon">🌍</div>
                    <div class="demo-setting-label">Langue</div>
                </div>
            </div>
        </div>
    `
}));

const steps = [
    {
        title: 'Bienvenue sur ComptaPro',
        icon: 'bi-house-heart-fill',
        description: 'Découvrez votre tableau de bord intuitif qui centralise toutes vos données comptables en un coup d\'œil.',
        component: DashboardDemo,
        features: [
            'Vue d\'ensemble de votre activité en temps réel',
            'Statistiques clés et indicateurs de performance',
            'Graphiques interactifs et visuels',
            'Accès rapide aux fonctionnalités principales'
        ]
    },
    {
        title: 'Créez vos factures',
        icon: 'bi-file-earmark-text-fill',
        description: 'Créez et gérez vos devis, factures et avoirs en quelques clics avec notre éditeur moderne.',
        component: InvoiceDemo,
        features: [
            'Création rapide de devis et factures',
            'Modèles personnalisables et professionnels',
            'Génération automatique de PDF',
            'Suivi des paiements et relances automatiques',
            'Liens de paiement en ligne sécurisés'
        ]
    },
    {
        title: 'Gérez vos clients',
        icon: 'bi-people-fill',
        description: 'Centralisez toutes les informations de vos clients et suivez l\'historique de vos relations commerciales.',
        component: CustomerDemo,
        features: [
            'Fiche client complète avec coordonnées',
            'Historique des factures par client',
            'Suivi du chiffre d\'affaires par client',
            'Notes et informations personnalisées',
            'Export et import de données'
        ]
    },
    {
        title: 'Analysez vos performances',
        icon: 'bi-graph-up-arrow',
        description: 'Générez des rapports détaillés pour piloter votre activité et prendre les bonnes décisions.',
        component: ReportDemo,
        features: [
            'Compte de résultat (profit & loss)',
            'Rapport de TVA automatisé',
            'Bilan comptable',
            'Comparaison année vs année',
            'Export PDF et Excel'
        ]
    },
    {
        title: 'Personnalisez votre expérience',
        icon: 'bi-gear-fill',
        description: 'Configurez l\'application selon vos préférences pour une expérience optimale.',
        component: SettingsDemo,
        features: [
            'Thème clair ou sombre',
            'Préférences de notifications',
            'Langue et format de date',
            'Personnalisation de l\'interface',
            'Gestion des paramètres de l\'entreprise'
        ]
    }
];

const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
};

const previousStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const skip = () => {
    isVisible.value = false;
    localStorage.setItem('onboarding_completed', 'skipped');
    emit('close');
};

const finish = () => {
    isVisible.value = false;
    localStorage.setItem('onboarding_completed', 'true');
    emit('complete');
    window.$toast?.success('Bienvenue sur ComptaPro ! 🎉', 'Tutoriel terminé');
};

onMounted(() => {
    // Check if onboarding has been completed
    const completed = localStorage.getItem('onboarding_completed');
    if (!completed) {
        // Show onboarding after a short delay
        setTimeout(() => {
            isVisible.value = true;
        }, 500);
    }
});

// Expose method to manually show onboarding
const show = () => {
    currentStep.value = 0;
    isVisible.value = true;
};

defineExpose({ show });
</script>

<style scoped>
.onboarding-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(8px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.onboarding-container {
    background: #fff;
    border-radius: 24px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
}

/* Progress Bar */
.onboarding-progress {
    display: flex;
    justify-content: space-between;
    padding: 32px 40px 24px;
    border-bottom: 2px solid #f3f4f6;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px 24px 0 0;
}

.progress-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.progress-step::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 0;
}

.progress-step:first-child::before {
    display: none;
}

.progress-step.completed::before {
    background: rgba(255, 255, 255, 0.8);
}

.progress-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    border: 2px solid rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    font-size: 16px;
    position: relative;
    z-index: 1;
    transition: all 0.3s;
}

.progress-step.active .progress-circle {
    background: #fff;
    color: #667eea;
    border-color: #fff;
    box-shadow: 0 4px 12px rgba(255, 255, 255, 0.4);
}

.progress-step.completed .progress-circle {
    background: rgba(16, 185, 129, 0.9);
    border-color: rgba(16, 185, 129, 1);
}

.progress-label {
    margin-top: 12px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    text-align: center;
    font-weight: 600;
}

.progress-step.active .progress-label {
    color: #fff;
    font-weight: 700;
}

/* Content */
.onboarding-content {
    padding: 48px 40px;
    flex: 1;
    overflow-y: auto;
}

.step-content {
    text-align: center;
}

.step-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 24px;
    border-radius: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: #fff;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.step-title {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px 0;
}

.step-description {
    font-size: 18px;
    color: #6b7280;
    margin: 0 0 32px 0;
    line-height: 1.6;
}

/* Demo Box */
.demo-box {
    margin: 32px 0;
    padding: 32px;
    background: #f9fafb;
    border-radius: 16px;
    border: 2px solid #e5e7eb;
}

/* Dashboard Demo */
.demo-dashboard {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.demo-card {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    text-align: center;
}

.demo-icon {
    font-size: 40px;
    margin-bottom: 12px;
}

.demo-label {
    font-size: 14px;
    font-weight: 600;
    color: #4b5563;
}

/* Invoice Demo */
.demo-invoice {
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.demo-invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e5e7eb;
}

.demo-logo {
    font-size: 32px;
}

.demo-number {
    font-weight: 700;
    color: #3b82f6;
    font-family: 'Courier New', monospace;
}

.demo-invoice-lines {
    margin-bottom: 20px;
}

.demo-line {
    height: 12px;
    background: #e5e7eb;
    border-radius: 6px;
    margin-bottom: 12px;
}

.demo-invoice-total {
    text-align: right;
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

/* Customer Demo */
.demo-customers {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.demo-customer-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 16px;
}

.demo-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.demo-info {
    flex: 1;
    text-align: left;
}

.demo-name {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.demo-email {
    font-size: 14px;
    color: #6b7280;
}

/* Reports Demo */
.demo-reports {
    text-align: center;
}

.demo-chart {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 16px;
    height: 120px;
    margin-bottom: 16px;
}

.demo-bar {
    width: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px 8px 0 0;
    transition: all 0.3s;
}

.demo-legend {
    font-weight: 600;
    color: #4b5563;
}

/* Settings Demo */
.demo-settings {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.demo-setting-item {
    background: #fff;
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 16px;
}

.demo-setting-icon {
    font-size: 28px;
}

.demo-setting-label {
    font-weight: 600;
    color: #1f2937;
}

/* Features List */
.features-list {
    list-style: none;
    padding: 0;
    margin: 32px 0 0 0;
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    font-size: 16px;
    color: #4b5563;
}

.feature-icon {
    color: #10b981;
    font-size: 20px;
    flex-shrink: 0;
}

/* Navigation */
.onboarding-navigation {
    padding: 24px 40px;
    border-top: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-skip {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s;
}

.btn-skip:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.nav-buttons {
    display: flex;
    gap: 12px;
}

.btn-nav {
    padding: 12px 24px;
    border-radius: 12px;
    border: none;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
}

.btn-prev {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-prev:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.btn-next,
.btn-finish {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-next:hover,
.btn-finish:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
}

/* Animations */
.onboarding-fade-enter-active,
.onboarding-fade-leave-active {
    transition: opacity 0.3s;
}

.onboarding-fade-enter-from,
.onboarding-fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

/* Responsive */
@media (max-width: 768px) {
    .onboarding-progress {
        padding: 24px 16px 16px;
    }

    .progress-label {
        display: none;
    }

    .onboarding-content {
        padding: 32px 24px;
    }

    .step-title {
        font-size: 24px;
    }

    .step-description {
        font-size: 16px;
    }

    .demo-dashboard {
        grid-template-columns: 1fr;
    }

    .onboarding-navigation {
        padding: 16px 24px;
        flex-direction: column;
        gap: 12px;
    }

    .btn-skip {
        order: 2;
    }

    .nav-buttons {
        order: 1;
        width: 100%;
        justify-content: space-between;
    }
}
</style>
