<template>
    <Head title="Paramètres" />

    <AuthenticatedLayout>
        <template #header>
            <div class="settings-header">
                <div>
                    <h2 class="page-title">Paramètres</h2>
                    <p class="page-subtitle">Personnalisez votre expérience ComptaPro</p>
                </div>
            </div>
        </template>

        <div class="settings-container">
            <!-- Tabs Navigation -->
            <div class="settings-tabs">
                <button v-for="tab in tabs" :key="tab.id"
                        class="tab-item"
                        :class="{ active: activeTab === tab.id }"
                        @click="activeTab = tab.id">
                    <i class="bi" :class="tab.icon"></i>
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="settings-content">
                <!-- Profile Tab -->
                <div v-if="activeTab === 'profile'" class="tab-panel animate-fadeIn">
                    <Card title="Informations personnelles">
                        <form @submit.prevent="saveProfile">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nom complet</label>
                                    <input type="text" v-model="profileForm.name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" v-model="profileForm.email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="tel" v-model="profileForm.phone" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Poste</label>
                                    <input type="text" v-model="profileForm.position" class="form-control">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </Card>

                    <Card title="Changer le mot de passe" class="mt-4">
                        <form @submit.prevent="changePassword">
                            <div class="form-group">
                                <label>Mot de passe actuel</label>
                                <input type="password" v-model="passwordForm.current" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Nouveau mot de passe</label>
                                <input type="password" v-model="passwordForm.new" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Confirmer le mot de passe</label>
                                <input type="password" v-model="passwordForm.confirm" class="form-control" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    Changer le mot de passe
                                </button>
                            </div>
                        </form>
                    </Card>
                </div>

                <!-- Appearance Tab -->
                <div v-if="activeTab === 'appearance'" class="tab-panel animate-fadeIn">
                    <Card title="Thème">
                        <div class="theme-selector">
                            <div class="theme-option" 
                                 :class="{ active: theme === 'light' }"
                                 @click="setTheme('light')">
                                <div class="theme-preview light">
                                    <i class="bi bi-sun-fill"></i>
                                </div>
                                <span>Clair</span>
                            </div>
                            <div class="theme-option"
                                 :class="{ active: theme === 'dark' }"
                                 @click="setTheme('dark')">
                                <div class="theme-preview dark">
                                    <i class="bi bi-moon-fill"></i>
                                </div>
                                <span>Sombre</span>
                            </div>
                            <div class="theme-option"
                                 :class="{ active: theme === 'auto' }"
                                 @click="setTheme('auto')">
                                <div class="theme-preview auto">
                                    <i class="bi bi-circle-half"></i>
                                </div>
                                <span>Auto</span>
                            </div>
                        </div>
                    </Card>

                    <Card title="Personnalisation" class="mt-4">
                        <div class="customization-options">
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Sidebar compacte</h6>
                                    <p>Réduire la sidebar par défaut</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="compactSidebar">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Animations réduites</h6>
                                    <p>Désactiver les animations pour de meilleures performances</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="reducedMotion">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Notifications sonores</h6>
                                    <p>Émettre un son pour les notifications importantes</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="soundNotifications">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Notifications Tab -->
                <div v-if="activeTab === 'notifications'" class="tab-panel animate-fadeIn">
                    <Card title="Préférences de notifications">
                        <div class="notification-options">
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Factures en retard</h6>
                                    <p>Recevoir une notification pour les factures impayées</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="notifSettings.overdueInvoices">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Nouveaux paiements</h6>
                                    <p>Notification lors de la réception d'un paiement</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="notifSettings.newPayments">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Nouveaux clients</h6>
                                    <p>Notification lors de l'ajout d'un nouveau client</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="notifSettings.newCustomers">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="option-item">
                                <div class="option-info">
                                    <h6>Résumé hebdomadaire</h6>
                                    <p>Recevoir un résumé par email chaque lundi</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" v-model="notifSettings.weeklySummary">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Language Tab -->
                <div v-if="activeTab === 'language'" class="tab-panel animate-fadeIn">
                    <Card title="Langue et région">
                        <div class="form-group">
                            <label>Langue de l'interface</label>
                            <select v-model="language" class="form-select">
                                <option value="fr">Français</option>
                                <option value="en">English</option>
                                <option value="de">Deutsch</option>
                                <option value="es">Español</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Format de date</label>
                            <select v-model="dateFormat" class="form-select">
                                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fuseau horaire</label>
                            <select v-model="timezone" class="form-select">
                                <option value="Europe/Paris">Europe/Paris (CET)</option>
                                <option value="Europe/Brussels">Europe/Brussels (CET)</option>
                                <option value="Europe/Zurich">Europe/Zurich (CET)</option>
                                <option value="America/Montreal">America/Montreal (EST)</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button @click="saveLanguageSettings" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                Enregistrer
                            </button>
                        </div>
                    </Card>
                </div>

                <!-- About Tab -->
                <div v-if="activeTab === 'about'" class="tab-panel animate-fadeIn">
                    <Card variant="gradient">
                        <div class="about-content">
                            <div class="about-logo">
                                <i class="bi bi-calculator-fill"></i>
                            </div>
                            <h3>ComptaPro SaaS</h3>
                            <p class="version">Version 1.0.0</p>
                            <p class="description">
                                Solution de comptabilité moderne et élégante pour les entreprises multi-pays.
                            </p>
                            <div class="about-links">
                                <a href="#" class="about-link">
                                    <i class="bi bi-book"></i>
                                    Documentation
                                </a>
                                <a href="#" class="about-link">
                                    <i class="bi bi-chat-dots"></i>
                                    Support
                                </a>
                                <a href="#" class="about-link">
                                    <i class="bi bi-github"></i>
                                    GitHub
                                </a>
                            </div>
                        </div>
                    </Card>

                    <Card title="Informations système" class="mt-4">
                        <div class="system-info">
                            <div class="info-row">
                                <span class="info-label">Laravel</span>
                                <span class="info-value">11.x</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Vue.js</span>
                                <span class="info-value">3.x</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Bootstrap</span>
                                <span class="info-value">5.x</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">PHP</span>
                                <span class="info-value">8.2+</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const activeTab = ref('profile');

const tabs = [
    { id: 'profile', label: 'Profil', icon: 'bi-person-fill' },
    { id: 'appearance', label: 'Apparence', icon: 'bi-palette-fill' },
    { id: 'notifications', label: 'Notifications', icon: 'bi-bell-fill' },
    { id: 'language', label: 'Langue', icon: 'bi-globe' },
    { id: 'about', label: 'À propos', icon: 'bi-info-circle-fill' }
];

const profileForm = reactive({
    name: 'Admin User',
    email: 'admin@comptapro.com',
    phone: '+33 6 12 34 56 78',
    position: 'Comptable'
});

const passwordForm = reactive({
    current: '',
    new: '',
    confirm: ''
});

const theme = ref('light');
const compactSidebar = ref(false);
const reducedMotion = ref(false);
const soundNotifications = ref(true);

const notifSettings = reactive({
    overdueInvoices: true,
    newPayments: true,
    newCustomers: false,
    weeklySummary: true
});

const language = ref('fr');
const dateFormat = ref('DD/MM/YYYY');
const timezone = ref('Europe/Paris');

const setTheme = (newTheme) => {
    theme.value = newTheme;
    localStorage.setItem('theme', newTheme);
    
    if (newTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
    
    window.$toast?.success('Thème modifié avec succès', 'Apparence');
};

const saveProfile = () => {
    window.$toast?.success('Profil mis à jour avec succès', 'Profil');
};

const changePassword = () => {
    if (passwordForm.new !== passwordForm.confirm) {
        window.$toast?.error('Les mots de passe ne correspondent pas', 'Erreur');
        return;
    }
    window.$toast?.success('Mot de passe modifié avec succès', 'Sécurité');
    passwordForm.current = '';
    passwordForm.new = '';
    passwordForm.confirm = '';
};

const saveLanguageSettings = () => {
    window.$toast?.success('Paramètres de langue enregistrés', 'Langue');
};
</script>

<style scoped>
.settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.page-title {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 4px 0 0;
    font-size: 14px;
}

.settings-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 24px;
    max-width: 1400px;
}

.settings-tabs {
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    height: fit-content;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.tab-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-align: left;
}

.tab-item:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.tab-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.tab-item i {
    font-size: 18px;
}

.settings-content {
    min-height: 600px;
}

.tab-panel {
    animation: fadeIn 0.3s ease;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

.form-control,
.form-select {
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-control:focus,
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
}

/* Theme Selector */
.theme-selector {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.theme-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 20px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.theme-option:hover {
    border-color: #3b82f6;
    transform: translateY(-4px);
}

.theme-option.active {
    border-color: #3b82f6;
    background: #eff6ff;
}

.theme-preview {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.theme-preview.light {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #fff;
}

.theme-preview.dark {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    color: #fff;
}

.theme-preview.auto {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
}

/* Customization Options */
.customization-options,
.notification-options {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.option-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.option-info h6 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
}

.option-info p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    width: 50px;
    height: 28px;
}

.toggle-switch input {
    display: none;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #d1d5db;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.3s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 22px;
    height: 22px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(22px);
}

/* About */
.about-content {
    text-align: center;
    padding: 40px 20px;
    color: #fff;
}

.about-logo {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    margin: 0 auto 20px;
}

.about-content h3 {
    font-size: 28px;
    margin-bottom: 8px;
}

.version {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 16px;
}

.description {
    opacity: 0.95;
    margin-bottom: 32px;
}

.about-links {
    display: flex;
    gap: 16px;
    justify-content: center;
}

.about-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s;
}

.about-link:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* System Info */
.system-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
}

.info-label {
    font-weight: 600;
    color: #374151;
}

.info-value {
    color: #6b7280;
    font-family: 'Courier New', monospace;
}

/* Responsive */
@media (max-width: 991px) {
    .settings-container {
        grid-template-columns: 1fr;
    }
    
    .settings-tabs {
        flex-direction: row;
        overflow-x: auto;
    }
    
    .tab-item span {
        display: none;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .theme-selector {
        grid-template-columns: 1fr;
    }
}
</style>
