<template>
    <div class="min-vh-100 d-flex flex-column">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <Link class="navbar-brand" :href="route('dashboard')">
                    <i class="bi bi-calculator me-2"></i>ComptaPro
                </Link>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <Link class="nav-link" :class="{ active: route().current('dashboard') }" :href="route('dashboard')">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :class="{ active: route().current('customers.*') }" :href="route('customers.index')">
                                <i class="bi bi-people me-1"></i>Clients
                            </Link>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" :class="{ active: route().current('invoices.*') || route().current('products.*') }" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-receipt me-1"></i>Ventes
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link class="dropdown-item" :href="route('invoices.index')">
                                        <i class="bi bi-file-text me-2"></i>Factures & Devis
                                    </Link>
                                </li>
                                <li>
                                    <Link class="dropdown-item" :href="route('products.index')">
                                        <i class="bi bi-box me-2"></i>Produits & Services
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" :class="{ active: route().current('suppliers.*') || route().current('purchases.*') }" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-cart me-1"></i>Achats
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link class="dropdown-item" :href="route('suppliers.index')">
                                        <i class="bi bi-building me-2"></i>Fournisseurs
                                    </Link>
                                </li>
                                <li>
                                    <Link class="dropdown-item" :href="route('purchases.index')">
                                        <i class="bi bi-file-earmark-text me-2"></i>Factures d'achat
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" :class="{ active: route().current('accounting.*') }" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-journal-text me-1"></i>Comptabilité
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <Link class="dropdown-item" :href="route('accounting.accounts')">
                                        <i class="bi bi-list-ol me-2"></i>Plan comptable
                                    </Link>
                                </li>
                                <li>
                                    <Link class="dropdown-item" :href="route('accounting.journals')">
                                        <i class="bi bi-book me-2"></i>Journaux
                                    </Link>
                                </li>
                                <li>
                                    <Link class="dropdown-item" :href="route('accounting.entries')">
                                        <i class="bi bi-journal-plus me-2"></i>Écritures
                                    </Link>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <Link class="dropdown-item" :href="route('accounting.general-ledger')">
                                        <i class="bi bi-table me-2"></i>Grand livre
                                    </Link>
                                </li>
                                <li>
                                    <Link class="dropdown-item" :href="route('accounting.trial-balance')">
                                        <i class="bi bi-calculator me-2"></i>Balance
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :class="{ active: route().current('reports.*') }" :href="route('reports.index')">
                                <i class="bi bi-graph-up me-1"></i>Rapports
                            </Link>
                        </li>
                        <li class="nav-item">
                            <Link class="nav-link" :class="{ active: route().current('companies.*') }" :href="route('companies.index')">
                                <i class="bi bi-building me-1"></i>Sociétés
                            </Link>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>{{ $page.props.auth.user.name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <Link class="dropdown-item" :href="route('profile.edit')">
                                        <i class="bi bi-gear me-2"></i>Profil
                                    </Link>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <Link class="dropdown-item" :href="route('logout')" method="post" as="button">
                                        <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="bg-white border-bottom py-3">
            <div class="container-fluid">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-fill bg-light">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-dark text-white-50 py-3 mt-auto">
            <div class="container-fluid text-center">
                <small>&copy; 2025 ComptaPro SaaS - Solution comptable multi-pays</small>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
.nav-link.active {
    font-weight: 600;
}
</style>
