<template>
    <Transition name="quick-actions">
        <div v-if="isOpen" class="quick-actions-fab" @click="toggle">
            <div class="fab-button">
                <Transition name="icon-rotate" mode="out-in">
                    <i v-if="isExpanded" key="close" class="bi bi-x"></i>
                    <i v-else key="open" class="bi bi-lightning-fill"></i>
                </Transition>
            </div>

            <Transition name="actions-expand">
                <div v-if="isExpanded" class="actions-menu" @click.stop>
                    <div class="actions-grid">
                        <!-- Nouvelle facture -->
                        <button
                            @click="navigateTo('invoices.create')"
                            class="action-item action-invoice"
                        >
                            <div class="action-icon">
                                <i class="bi bi-file-earmark-plus"></i>
                            </div>
                            <span class="action-label">Nouvelle Facture</span>
                        </button>

                        <!-- Nouveau client -->
                        <button
                            @click="navigateTo('customers.create')"
                            class="action-item action-customer"
                        >
                            <div class="action-icon">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <span class="action-label">Nouveau Client</span>
                        </button>

                        <!-- Nouveau produit -->
                        <button
                            @click="navigateTo('products.create')"
                            class="action-item action-product"
                        >
                            <div class="action-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <span class="action-label">Nouveau Produit</span>
                        </button>

                        <!-- Rapports -->
                        <button
                            @click="navigateTo('reports.index')"
                            class="action-item action-report"
                        >
                            <div class="action-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="action-label">Rapports</span>
                        </button>

                        <!-- Export -->
                        <button
                            @click="handleExport"
                            class="action-item action-export"
                        >
                            <div class="action-icon">
                                <i class="bi bi-download"></i>
                            </div>
                            <span class="action-label">Exporter</span>
                        </button>

                        <!-- Recherche -->
                        <button
                            @click="openGlobalSearch"
                            class="action-item action-search"
                        >
                            <div class="action-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <span class="action-label">Rechercher</span>
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['open-search', 'export']);

const isExpanded = ref(false);

const toggle = () => {
    isExpanded.value = !isExpanded.value;
};

const navigateTo = (routeName) => {
    router.visit(route(routeName));
    isExpanded.value = false;
};

const openGlobalSearch = () => {
    emit('open-search');
    isExpanded.value = false;
};

const handleExport = () => {
    emit('export');
    isExpanded.value = false;
};
</script>

<style scoped>
.quick-actions-fab {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 1000;
}

.fab-button {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
    transition: all 0.3s;
}

.fab-button:hover {
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 12px 32px rgba(59, 130, 246, 0.6);
}

.fab-button:active {
    transform: scale(0.95);
}

.actions-menu {
    position: absolute;
    bottom: 80px;
    right: 0;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    padding: 20px;
    min-width: 280px;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 16px 12px;
    border: none;
    border-radius: 12px;
    background: #f9fafb;
    cursor: pointer;
    transition: all 0.2s;
}

.action-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

.action-invoice .action-icon {
    background: linear-gradient(135deg, #10b981, #059669);
}

.action-customer .action-icon {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.action-product .action-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.action-report .action-icon {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.action-export .action-icon {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
}

.action-search .action-icon {
    background: linear-gradient(135deg, #ec4899, #db2777);
}

.action-label {
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    text-align: center;
}

/* Transitions */
.quick-actions-enter-active,
.quick-actions-leave-active {
    transition: all 0.3s;
}

.quick-actions-enter-from,
.quick-actions-leave-to {
    opacity: 0;
    transform: scale(0);
}

.icon-rotate-enter-active,
.icon-rotate-leave-active {
    transition: all 0.3s;
}

.icon-rotate-enter-from {
    opacity: 0;
    transform: rotate(-180deg) scale(0.5);
}

.icon-rotate-leave-to {
    opacity: 0;
    transform: rotate(180deg) scale(0.5);
}

.actions-expand-enter-active,
.actions-expand-leave-active {
    transition: all 0.3s;
    transform-origin: bottom right;
}

.actions-expand-enter-from,
.actions-expand-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

/* Responsive */
@media (max-width: 768px) {
    .quick-actions-fab {
        bottom: 20px;
        right: 20px;
    }

    .fab-button {
        width: 56px;
        height: 56px;
        font-size: 24px;
    }

    .actions-menu {
        min-width: 260px;
    }

    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .action-item {
        padding: 14px 10px;
    }

    .action-icon {
        width: 42px;
        height: 42px;
        font-size: 20px;
    }

    .action-label {
        font-size: 11px;
    }
}

/* Dark Mode */
:global(.dark) .actions-menu {
    background: #1f2937;
}

:global(.dark) .action-item {
    background: #374151;
}

:global(.dark) .action-item:hover {
    background: #4b5563;
}

:global(.dark) .action-label {
    color: #f3f4f6;
}
</style>
