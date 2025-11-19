<template>
    <Teleport to="body">
        <Transition name="shortcuts-modal">
            <div v-if="show" class="shortcuts-overlay" @click="close">
                <div class="shortcuts-modal" @click.stop>
                    <!-- Header -->
                    <div class="shortcuts-header">
                        <div class="header-content">
                            <i class="bi bi-keyboard"></i>
                            <div>
                                <h2>Raccourcis Clavier</h2>
                                <p>Gagnez du temps avec ces raccourcis</p>
                            </div>
                        </div>
                        <button @click="close" class="close-btn">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <!-- Shortcuts List -->
                    <div class="shortcuts-content">
                        <!-- Navigation -->
                        <div class="shortcuts-section">
                            <h3 class="section-title">
                                <i class="bi bi-compass"></i>
                                Navigation
                            </h3>
                            <div class="shortcuts-list">
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd>
                                    </div>
                                    <div class="shortcut-desc">Tableau de bord</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>C</kbd>
                                    </div>
                                    <div class="shortcut-desc">Liste des clients</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>F</kbd>
                                    </div>
                                    <div class="shortcut-desc">Liste des factures</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>P</kbd>
                                    </div>
                                    <div class="shortcut-desc">Liste des produits</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>R</kbd>
                                    </div>
                                    <div class="shortcut-desc">Rapports</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Alt</kbd> + <kbd>Backspace</kbd>
                                    </div>
                                    <div class="shortcut-desc">Page précédente</div>
                                </div>
                            </div>
                        </div>

                        <!-- Création rapide -->
                        <div class="shortcuts-section">
                            <h3 class="section-title">
                                <i class="bi bi-plus-circle"></i>
                                Création rapide
                            </h3>
                            <div class="shortcuts-list">
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>N</kbd>
                                    </div>
                                    <div class="shortcut-desc">Nouvelle facture</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>C</kbd>
                                    </div>
                                    <div class="shortcut-desc">Nouveau client</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="shortcuts-section">
                            <h3 class="section-title">
                                <i class="bi bi-lightning"></i>
                                Actions
                            </h3>
                            <div class="shortcuts-list">
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>K</kbd>
                                    </div>
                                    <div class="shortcut-desc">Recherche globale</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>Ctrl</kbd> + <kbd>R</kbd>
                                    </div>
                                    <div class="shortcut-desc">Rafraîchir</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>?</kbd>
                                    </div>
                                    <div class="shortcut-desc">Afficher cette aide</div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation dans les listes -->
                        <div class="shortcuts-section">
                            <h3 class="section-title">
                                <i class="bi bi-list-ul"></i>
                                Navigation dans les listes
                            </h3>
                            <div class="shortcuts-list">
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>J</kbd>
                                    </div>
                                    <div class="shortcut-desc">Élément suivant</div>
                                </div>
                                <div class="shortcut-item">
                                    <div class="shortcut-keys">
                                        <kbd>K</kbd>
                                    </div>
                                    <div class="shortcut-desc">Élément précédent</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="shortcuts-footer">
                        <div class="footer-tip">
                            <i class="bi bi-info-circle"></i>
                            <span>Appuyez sur <kbd>Esc</kbd> pour fermer cette fenêtre</span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

// Fermer avec Escape
const handleEscape = (event) => {
    if (event.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
});
</script>

<style scoped>
.shortcuts-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
}

.shortcuts-modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.shortcuts-header {
    padding: 28px 32px;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-shrink: 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-content > i {
    font-size: 32px;
    color: #3b82f6;
}

.header-content h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.header-content p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #6b7280;
}

.close-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    border: none;
    background: #f3f4f6;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #6b7280;
    transition: all 0.2s;
    flex-shrink: 0;
}

.close-btn:hover {
    background: #e5e7eb;
}

.shortcuts-content {
    flex: 1;
    overflow-y: auto;
    padding: 32px;
}

.shortcuts-section {
    margin-bottom: 36px;
}

.shortcuts-section:last-child {
    margin-bottom: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #f3f4f6;
}

.section-title i {
    color: #3b82f6;
    font-size: 20px;
}

.shortcuts-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

.shortcut-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 18px;
    background: #f9fafb;
    border-radius: 12px;
    transition: all 0.2s;
}

.shortcut-item:hover {
    background: #f3f4f6;
    transform: translateX(4px);
}

.shortcut-keys {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.shortcut-desc {
    flex: 1;
    font-size: 14px;
    color: #4b5563;
    text-align: right;
}

kbd {
    padding: 6px 10px;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #d1d5db;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    display: inline-block;
    min-width: 28px;
    text-align: center;
}

.shortcuts-footer {
    padding: 20px 32px;
    border-top: 2px solid #f3f4f6;
    background: #f9fafb;
    flex-shrink: 0;
}

.footer-tip {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #6b7280;
}

.footer-tip i {
    font-size: 18px;
    color: #3b82f6;
}

/* Transitions */
.shortcuts-modal-enter-active,
.shortcuts-modal-leave-active {
    transition: opacity 0.2s;
}

.shortcuts-modal-enter-active .shortcuts-modal,
.shortcuts-modal-leave-active .shortcuts-modal {
    transition: transform 0.2s, opacity 0.2s;
}

.shortcuts-modal-enter-from,
.shortcuts-modal-leave-to {
    opacity: 0;
}

.shortcuts-modal-enter-from .shortcuts-modal,
.shortcuts-modal-leave-to .shortcuts-modal {
    transform: scale(0.95);
    opacity: 0;
}

/* Scrollbar */
.shortcuts-content::-webkit-scrollbar {
    width: 10px;
}

.shortcuts-content::-webkit-scrollbar-track {
    background: #f3f4f6;
}

.shortcuts-content::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 5px;
}

.shortcuts-content::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
    .shortcuts-modal {
        max-height: 95vh;
    }

    .shortcuts-header {
        padding: 20px;
    }

    .shortcuts-content {
        padding: 20px;
    }

    .shortcuts-list {
        grid-template-columns: 1fr;
    }

    .shortcut-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .shortcut-desc {
        text-align: left;
    }
}

/* Dark Mode */
:global(.dark) .shortcuts-modal {
    background: #1f2937;
}

:global(.dark) .shortcuts-header {
    border-bottom-color: #374151;
}

:global(.dark) .header-content h2 {
    color: #f3f4f6;
}

:global(.dark) .header-content p {
    color: #9ca3af;
}

:global(.dark) .close-btn {
    background: #374151;
    color: #d1d5db;
}

:global(.dark) .close-btn:hover {
    background: #4b5563;
}

:global(.dark) .section-title {
    color: #f3f4f6;
    border-bottom-color: #374151;
}

:global(.dark) .shortcut-item {
    background: #374151;
}

:global(.dark) .shortcut-item:hover {
    background: #4b5563;
}

:global(.dark) .shortcut-desc {
    color: #d1d5db;
}

:global(.dark) kbd {
    background: #1f2937;
    border-color: #4b5563;
    color: #f3f4f6;
}

:global(.dark) .shortcuts-footer {
    background: #111827;
    border-top-color: #374151;
}

:global(.dark) .footer-tip {
    color: #9ca3af;
}

:global(.dark) .shortcuts-content::-webkit-scrollbar-track {
    background: #374151;
}

:global(.dark) .shortcuts-content::-webkit-scrollbar-thumb {
    background: #4b5563;
}

:global(.dark) .shortcuts-content::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>
