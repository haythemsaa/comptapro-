<template>
    <div class="layout-wrapper">
        <!-- Global Search Modal -->
        <GlobalSearch v-model="showGlobalSearch" />

        <!-- Keyboard Shortcuts Help -->
        <KeyboardShortcutsHelp :show="showShortcutsHelp" @close="showShortcutsHelp = false" />

        <!-- Quick Actions FAB -->
        <QuickActions
            :is-open="true"
            @open-search="showGlobalSearch = true"
            @export="handleQuickExport"
        />

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
            <div class="sidebar-header">
                <Link :href="route('dashboard')" class="sidebar-brand">
                    <i class="bi bi-calculator-fill brand-icon"></i>
                    <span class="brand-text" v-show="!sidebarCollapsed">ComptaPro</span>
                </Link>
                <button class="sidebar-toggle" @click="toggleSidebar" title="Réduire/Agrandir">
                    <i class="bi" :class="sidebarCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <nav class="sidebar-nav">
                    <!-- Dashboard -->
                    <Link :href="route('dashboard')" class="nav-item" :class="{ active: route().current('dashboard') }">
                        <i class="bi bi-speedometer2"></i>
                        <span v-show="!sidebarCollapsed">Dashboard</span>
                    </Link>

                    <!-- Clients -->
                    <Link :href="route('customers.index')" class="nav-item" :class="{ active: route().current('customers.*') }">
                        <i class="bi bi-people-fill"></i>
                        <span v-show="!sidebarCollapsed">Clients</span>
                    </Link>

                    <!-- Ventes -->
                    <div class="nav-group">
                        <div class="nav-group-header" @click="toggleGroup('sales')" :class="{ active: salesOpen }">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span v-show="!sidebarCollapsed">Ventes</span>
                            <i v-show="!sidebarCollapsed" class="bi bi-chevron-down group-arrow" :class="{ rotated: salesOpen }"></i>
                        </div>
                        <div class="nav-group-content" v-show="salesOpen && !sidebarCollapsed">
                            <Link :href="route('invoices.index')" class="nav-subitem" :class="{ active: route().current('invoices.*') }">
                                <i class="bi bi-file-text"></i>
                                <span>Factures & Devis</span>
                            </Link>
                            <Link :href="route('products.index')" class="nav-subitem" :class="{ active: route().current('products.*') }">
                                <i class="bi bi-box-seam"></i>
                                <span>Produits & Services</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Achats -->
                    <div class="nav-group">
                        <div class="nav-group-header" @click="toggleGroup('purchases')" :class="{ active: purchasesOpen }">
                            <i class="bi bi-cart-check-fill"></i>
                            <span v-show="!sidebarCollapsed">Achats</span>
                            <i v-show="!sidebarCollapsed" class="bi bi-chevron-down group-arrow" :class="{ rotated: purchasesOpen }"></i>
                        </div>
                        <div class="nav-group-content" v-show="purchasesOpen && !sidebarCollapsed">
                            <Link :href="route('suppliers.index')" class="nav-subitem" :class="{ active: route().current('suppliers.*') }">
                                <i class="bi bi-building"></i>
                                <span>Fournisseurs</span>
                            </Link>
                            <Link :href="route('purchases.index')" class="nav-subitem" :class="{ active: route().current('purchases.*') }">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Factures d'achat</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Comptabilité -->
                    <div class="nav-group">
                        <div class="nav-group-header" @click="toggleGroup('accounting')" :class="{ active: accountingOpen }">
                            <i class="bi bi-journal-text"></i>
                            <span v-show="!sidebarCollapsed">Comptabilité</span>
                            <i v-show="!sidebarCollapsed" class="bi bi-chevron-down group-arrow" :class="{ rotated: accountingOpen }"></i>
                        </div>
                        <div class="nav-group-content" v-show="accountingOpen && !sidebarCollapsed">
                            <Link :href="route('accounting.accounts')" class="nav-subitem">
                                <i class="bi bi-list-ol"></i>
                                <span>Plan comptable</span>
                            </Link>
                            <Link :href="route('accounting.journals')" class="nav-subitem">
                                <i class="bi bi-book"></i>
                                <span>Journaux</span>
                            </Link>
                            <Link :href="route('accounting.entries')" class="nav-subitem">
                                <i class="bi bi-journal-plus"></i>
                                <span>Écritures</span>
                            </Link>
                            <div class="nav-divider"></div>
                            <Link :href="route('accounting.general-ledger')" class="nav-subitem">
                                <i class="bi bi-table"></i>
                                <span>Grand livre</span>
                            </Link>
                            <Link :href="route('accounting.trial-balance')" class="nav-subitem">
                                <i class="bi bi-calculator"></i>
                                <span>Balance</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Rapports -->
                    <Link :href="route('reports.index')" class="nav-item" :class="{ active: route().current('reports.*') }">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span v-show="!sidebarCollapsed">Rapports</span>
                    </Link>

                    <!-- Sociétés -->
                    <Link :href="route('companies.index')" class="nav-item" :class="{ active: route().current('companies.*') }">
                        <i class="bi bi-buildings"></i>
                        <span v-show="!sidebarCollapsed">Sociétés</span>
                    </Link>
                </nav>
            </div>

            <div class="sidebar-footer" v-show="!sidebarCollapsed">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ $page.props.auth.user.name }}</div>
                        <div class="user-email">{{ $page.props.auth.user.email }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" @click="toggleSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Rechercher..." v-model="searchQuery" @input="handleSearch">
                    </div>
                </div>
                <div class="header-right">
                    <!-- Global Search Button -->
                    <button class="header-icon-btn" @click="showGlobalSearch = true" title="Recherche globale (Ctrl+K)">
                        <i class="bi bi-search"></i>
                    </button>

                    <!-- Keyboard Shortcuts -->
                    <button class="header-icon-btn" @click="showShortcutsHelp = true" title="Raccourcis clavier (?)">
                        <i class="bi bi-keyboard"></i>
                    </button>

                    <!-- Theme Toggle -->
                    <DarkModeToggle />

                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="header-icon-btn position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-bell-fill"></i>
                            <span class="notification-badge" v-if="notifications > 0">{{ notifications }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <div class="dropdown-header">
                                <h6>Notifications</h6>
                                <span class="badge bg-primary rounded-pill">{{ notifications }}</span>
                            </div>
                            <div class="notification-list">
                                <a href="#" class="notification-item">
                                    <i class="bi bi-exclamation-circle text-warning"></i>
                                    <div>
                                        <strong>5 factures</strong> en retard
                                        <small>Il y a 2 heures</small>
                                    </div>
                                </a>
                                <a href="#" class="notification-item">
                                    <i class="bi bi-check-circle text-success"></i>
                                    <div>
                                        <strong>Paiement reçu</strong>
                                        <small>Hier</small>
                                    </div>
                                </a>
                                <a href="#" class="notification-item">
                                    <i class="bi bi-info-circle text-info"></i>
                                    <div>
                                        <strong>Nouveau client</strong> ajouté
                                        <small>Il y a 3 jours</small>
                                    </div>
                                </a>
                            </div>
                            <div class="dropdown-footer">
                                <a href="#">Voir toutes les notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="user-menu-btn" data-bs-toggle="dropdown">
                            <div class="user-avatar-small">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span class="user-name-header">{{ $page.props.auth.user.name }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end user-dropdown">
                            <li class="dropdown-header">
                                <div class="text-muted small">Connecté en tant que</div>
                                <div class="fw-bold">{{ $page.props.auth.user.email }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <Link :href="route('profile.edit')" class="dropdown-item">
                                    <i class="bi bi-person me-2"></i>Mon profil
                                </Link>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-gear me-2"></i>Paramètres
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <Link :href="route('logout')" method="post" as="button" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Header -->
            <div v-if="$slots.header" class="page-header">
                <div class="container-fluid">
                    <slot name="header" />
                </div>
            </div>

            <!-- Main Content -->
            <main class="main-content">
                <div class="content-wrapper">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer class="main-footer">
                <div class="container-fluid">
                    <div class="footer-content">
                        <div class="footer-left">
                            <span>&copy; 2025 ComptaPro SaaS</span>
                            <span class="separator">•</span>
                            <a href="#">Documentation</a>
                            <span class="separator">•</span>
                            <a href="#">Support</a>
                        </div>
                        <div class="footer-right">
                            <span class="text-muted">Version 1.0.0</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Overlay for mobile -->
        <div class="sidebar-overlay" :class="{ active: sidebarCollapsed && isMobile }" @click="toggleSidebar"></div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import KeyboardShortcutsHelp from '@/Components/KeyboardShortcutsHelp.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import QuickActions from '@/Components/QuickActions.vue';
import { useComptaProShortcuts } from '@/Composables/useKeyboardShortcuts';
import { useDataExport } from '@/Composables/useDataExport';

// Initialize keyboard shortcuts
const { showHelp: showShortcutsHelp } = useComptaProShortcuts();

// Initialize data export
const { downloadCSV } = useDataExport();

// State
const sidebarCollapsed = ref(localStorage.getItem('sidebar-collapsed') === 'true');
const darkMode = ref(localStorage.getItem('dark-mode') === 'true');
const salesOpen = ref(true);
const purchasesOpen = ref(true);
const accountingOpen = ref(true);
const searchQuery = ref('');
const notifications = ref(3);
const isMobile = ref(false);
const showGlobalSearch = ref(false);

// Toggle sidebar
const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
};

// Toggle group
const toggleGroup = (group) => {
    if (group === 'sales') salesOpen.value = !salesOpen.value;
    if (group === 'purchases') purchasesOpen.value = !purchasesOpen.value;
    if (group === 'accounting') accountingOpen.value = !accountingOpen.value;
};

// Toggle theme
const toggleTheme = () => {
    darkMode.value = !darkMode.value;
    localStorage.setItem('dark-mode', darkMode.value);
    document.documentElement.setAttribute('data-theme', darkMode.value ? 'dark' : 'light');
};

// Handle search
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        showGlobalSearch.value = true;
    }
};

// Handle quick export
const handleQuickExport = () => {
    window.$toast?.info('Fonction d\'export disponible sur chaque page', 'Export');
};

// Check mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 992;
};

// Initialize
onMounted(() => {
    if (darkMode.value) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
/* Layout Structure */
.layout-wrapper {
    display: flex;
    min-height: 100vh;
    background: var(--bg-main, #f8f9fa);
    position: relative;
}

/* Sidebar Styles */
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    color: #cbd5e1;
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1040;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

.sidebar-collapsed {
    width: 70px;
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    text-decoration: none;
    font-size: 20px;
    font-weight: 700;
    transition: all 0.3s;
}

.sidebar-brand:hover {
    color: #60a5fa;
}

.brand-icon {
    font-size: 28px;
    color: #60a5fa;
}

.brand-text {
    white-space: nowrap;
}

.sidebar-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #cbd5e1;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

/* Sidebar Content */
.sidebar-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px 0;
}

.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

/* Navigation */
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 0 12px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #cbd5e1;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
    font-weight: 500;
    position: relative;
}

.nav-item i {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    transform: translateX(4px);
}

.nav-item.active {
    background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Navigation Groups */
.nav-group {
    margin: 4px 0;
}

.nav-group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #cbd5e1;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    position: relative;
}

.nav-group-header i:first-child {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.nav-group-header:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.nav-group-header.active {
    color: #60a5fa;
}

.group-arrow {
    margin-left: auto;
    font-size: 14px;
    transition: transform 0.3s;
}

.group-arrow.rotated {
    transform: rotate(180deg);
}

.nav-group-content {
    padding: 4px 0 4px 24px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.nav-subitem {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 14px;
    margin: 2px 0;
}

.nav-subitem i {
    font-size: 16px;
    width: 20px;
}

.nav-subitem:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    transform: translateX(4px);
}

.nav-subitem.active {
    background: rgba(96, 165, 250, 0.2);
    color: #60a5fa;
}

.nav-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 8px 0;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
}

.user-avatar {
    font-size: 32px;
    color: #60a5fa;
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: #fff;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-email {
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Main Wrapper */
.main-wrapper {
    flex: 1;
    margin-left: 260px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.sidebar-collapsed ~ .main-wrapper {
    margin-left: 70px;
}

/* Top Header */
.top-header {
    height: 70px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    position: sticky;
    top: 0;
    z-index: 1030;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: #374151;
    cursor: pointer;
    padding: 8px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f3f4f6;
    padding: 10px 16px;
    border-radius: 10px;
    width: 350px;
    transition: all 0.3s;
}

.search-box:focus-within {
    background: #e5e7eb;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-box i {
    color: #9ca3af;
    font-size: 18px;
}

.search-box input {
    border: none;
    background: none;
    outline: none;
    flex: 1;
    color: #374151;
    font-size: 14px;
}

.search-box input::placeholder {
    color: #9ca3af;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icon-btn {
    background: #f3f4f6;
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s;
}

.header-icon-btn:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

.user-menu-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f3f4f6;
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.user-menu-btn:hover {
    background: #e5e7eb;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.user-name-header {
    font-weight: 600;
    color: #374151;
    font-size: 14px;
}

/* Dropdowns */
.notification-dropdown {
    width: 360px;
    padding: 0;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
}

.dropdown-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dropdown-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: start;
    gap: 12px;
    padding: 14px 20px;
    text-decoration: none;
    color: #374151;
    transition: all 0.3s;
    border-bottom: 1px solid #f3f4f6;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item i {
    font-size: 24px;
    margin-top: 4px;
}

.notification-item strong {
    display: block;
    color: #1f2937;
    font-weight: 600;
    margin-bottom: 2px;
}

.notification-item small {
    display: block;
    color: #9ca3af;
    font-size: 12px;
}

.dropdown-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.dropdown-footer a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.user-dropdown {
    width: 240px;
    padding: 8px 0;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
}

.user-dropdown .dropdown-header {
    padding: 12px 16px;
}

.user-dropdown .dropdown-item {
    padding: 10px 16px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.user-dropdown .dropdown-item:hover {
    background: #f3f4f6;
}

/* Page Header */
.page-header {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 24px 0;
}

/* Main Content */
.main-content {
    flex: 1;
    padding: 24px;
}

.content-wrapper {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Footer */
.main-footer {
    background: #fff;
    border-top: 1px solid #e5e7eb;
    padding: 16px 0;
    margin-top: auto;
}

.footer-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    color: #6b7280;
}

.footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-left a {
    color: #3b82f6;
    text-decoration: none;
}

.footer-left a:hover {
    text-decoration: underline;
}

.separator {
    color: #d1d5db;
}

/* Sidebar Overlay */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1039;
}

.sidebar-overlay.active {
    display: block;
}

/* Dark Mode */
[data-theme="dark"] {
    --bg-main: #111827;
}

[data-theme="dark"] .top-header,
[data-theme="dark"] .page-header,
[data-theme="dark"] .main-footer {
    background: #1f2937;
    border-color: #374151;
    color: #e5e7eb;
}

[data-theme="dark"] .search-box {
    background: #374151;
}

[data-theme="dark"] .search-box input {
    color: #e5e7eb;
}

[data-theme="dark"] .header-icon-btn,
[data-theme="dark"] .user-menu-btn {
    background: #374151;
    color: #e5e7eb;
}

[data-theme="dark"] .header-icon-btn:hover,
[data-theme="dark"] .user-menu-btn:hover {
    background: #4b5563;
}

/* Responsive */
@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar-collapsed {
        transform: translateX(0);
    }

    .main-wrapper {
        margin-left: 0 !important;
    }

    .mobile-menu-btn {
        display: block;
    }

    .search-box {
        width: 200px;
    }

    .user-name-header {
        display: none;
    }
}

@media (max-width: 575px) {
    .search-box {
        display: none;
    }

    .footer-content {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
