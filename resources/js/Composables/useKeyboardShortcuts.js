import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable pour la gestion des raccourcis clavier globaux
 * Supporte: combinaisons modifiers (Ctrl, Alt, Shift), séquences
 */
export function useKeyboardShortcuts() {
    const shortcuts = ref([]);
    const isEnabled = ref(true);
    const showHelp = ref(false);

    // Normaliser la touche
    const normalizeKey = (key) => {
        return key.toLowerCase().trim();
    };

    // Vérifier si un élément de formulaire est focus
    const isFormElementFocused = () => {
        const activeElement = document.activeElement;
        const tagName = activeElement?.tagName.toLowerCase();
        const isContentEditable = activeElement?.contentEditable === 'true';

        return (
            tagName === 'input' ||
            tagName === 'textarea' ||
            tagName === 'select' ||
            isContentEditable
        );
    };

    // Enregistrer un raccourci
    const registerShortcut = (config) => {
        const {
            key,
            ctrl = false,
            alt = false,
            shift = false,
            meta = false,
            callback,
            description = '',
            ignoreFormFields = true,
            global = true
        } = config;

        const shortcut = {
            key: normalizeKey(key),
            ctrl,
            alt,
            shift,
            meta,
            callback,
            description,
            ignoreFormFields,
            global,
            id: `${ctrl ? 'ctrl+' : ''}${alt ? 'alt+' : ''}${shift ? 'shift+' : ''}${meta ? 'meta+' : ''}${normalizeKey(key)}`
        };

        shortcuts.value.push(shortcut);
        return shortcut.id;
    };

    // Désenregistrer un raccourci
    const unregisterShortcut = (id) => {
        const index = shortcuts.value.findIndex(s => s.id === id);
        if (index > -1) {
            shortcuts.value.splice(index, 1);
        }
    };

    // Gérer l'événement clavier
    const handleKeyDown = (event) => {
        if (!isEnabled.value) return;

        const key = normalizeKey(event.key);

        // Trouver le raccourci correspondant
        const matchingShortcut = shortcuts.value.find(shortcut => {
            return (
                shortcut.key === key &&
                shortcut.ctrl === (event.ctrlKey || event.metaKey) &&
                shortcut.alt === event.altKey &&
                shortcut.shift === event.shiftKey &&
                shortcut.meta === event.metaKey
            );
        });

        if (matchingShortcut) {
            // Ignorer si un champ de formulaire est focus (sauf si global)
            if (matchingShortcut.ignoreFormFields && isFormElementFocused()) {
                return;
            }

            // Prévenir le comportement par défaut
            event.preventDefault();
            event.stopPropagation();

            // Exécuter le callback
            matchingShortcut.callback(event);
        }
    };

    // Activer/désactiver les raccourcis
    const enable = () => {
        isEnabled.value = true;
    };

    const disable = () => {
        isEnabled.value = false;
    };

    const toggle = () => {
        isEnabled.value = !isEnabled.value;
    };

    // Obtenir tous les raccourcis
    const getAllShortcuts = () => {
        return shortcuts.value.map(s => ({
            id: s.id,
            keys: formatShortcutKeys(s),
            description: s.description
        }));
    };

    // Formater les touches pour affichage
    const formatShortcutKeys = (shortcut) => {
        const keys = [];

        if (shortcut.ctrl) keys.push('Ctrl');
        if (shortcut.alt) keys.push('Alt');
        if (shortcut.shift) keys.push('Shift');
        if (shortcut.meta) keys.push('⌘');

        keys.push(shortcut.key.toUpperCase());

        return keys.join(' + ');
    };

    // Afficher l'aide des raccourcis
    const toggleHelp = () => {
        showHelp.value = !showHelp.value;
    };

    // Setup des listeners
    onMounted(() => {
        document.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown);
    });

    return {
        shortcuts,
        isEnabled,
        showHelp,
        registerShortcut,
        unregisterShortcut,
        enable,
        disable,
        toggle,
        getAllShortcuts,
        formatShortcutKeys,
        toggleHelp
    };
}

/**
 * Raccourcis globaux prédéfinis pour ComptaPro
 */
export function useComptaProShortcuts() {
    const {
        registerShortcut,
        showHelp,
        toggleHelp,
        getAllShortcuts
    } = useKeyboardShortcuts();

    // Navigation
    registerShortcut({
        key: 'd',
        ctrl: true,
        shift: true,
        callback: () => router.visit(route('dashboard')),
        description: 'Aller au tableau de bord'
    });

    registerShortcut({
        key: 'c',
        ctrl: true,
        shift: true,
        callback: () => router.visit(route('customers.index')),
        description: 'Aller aux clients'
    });

    registerShortcut({
        key: 'f',
        ctrl: true,
        shift: true,
        callback: () => router.visit(route('invoices.index')),
        description: 'Aller aux factures'
    });

    registerShortcut({
        key: 'p',
        ctrl: true,
        shift: true,
        callback: () => router.visit(route('products.index')),
        description: 'Aller aux produits'
    });

    registerShortcut({
        key: 'r',
        ctrl: true,
        shift: true,
        callback: () => router.visit(route('reports.index')),
        description: 'Aller aux rapports'
    });

    // Création rapide
    registerShortcut({
        key: 'n',
        ctrl: true,
        alt: true,
        callback: () => router.visit(route('invoices.create')),
        description: 'Nouvelle facture'
    });

    registerShortcut({
        key: 'c',
        ctrl: true,
        alt: true,
        callback: () => router.visit(route('customers.create')),
        description: 'Nouveau client'
    });

    // Recherche globale
    registerShortcut({
        key: 'k',
        ctrl: true,
        callback: () => {
            const searchInput = document.querySelector('input[type="search"], input[placeholder*="Recherch"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        },
        description: 'Recherche globale',
        ignoreFormFields: false
    });

    // Aide
    registerShortcut({
        key: '?',
        shift: true,
        callback: toggleHelp,
        description: 'Afficher l\'aide des raccourcis'
    });

    // Rafraîchir
    registerShortcut({
        key: 'r',
        ctrl: true,
        callback: (e) => {
            e.preventDefault();
            router.reload();
        },
        description: 'Rafraîchir la page'
    });

    // Retour
    registerShortcut({
        key: 'backspace',
        alt: true,
        callback: () => window.history.back(),
        description: 'Retour à la page précédente'
    });

    // Navigation dans les listes (j/k comme Vim)
    registerShortcut({
        key: 'j',
        callback: () => {
            const currentFocus = document.activeElement;
            const focusableElements = Array.from(document.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])'));
            const currentIndex = focusableElements.indexOf(currentFocus);

            if (currentIndex < focusableElements.length - 1) {
                focusableElements[currentIndex + 1]?.focus();
            }
        },
        description: 'Élément suivant',
        ignoreFormFields: true
    });

    registerShortcut({
        key: 'k',
        callback: () => {
            const currentFocus = document.activeElement;
            const focusableElements = Array.from(document.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])'));
            const currentIndex = focusableElements.indexOf(currentFocus);

            if (currentIndex > 0) {
                focusableElements[currentIndex - 1]?.focus();
            }
        },
        description: 'Élément précédent',
        ignoreFormFields: true
    });

    return {
        showHelp,
        toggleHelp,
        getAllShortcuts
    };
}
