import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

/**
 * Composable pour la gestion des filtres avancés
 * Supporte: recherche, tri, pagination, filtres multiples, sauvegarde état
 */
export function useAdvancedFilters(routeName, options = {}) {
    const {
        defaultFilters = {},
        debounceMs = 300,
        preserveScroll = true,
        preserveState = true,
        saveToLocalStorage = true,
        storageKey = `filters_${routeName}`
    } = options;

    // Charger les filtres depuis localStorage si disponibles
    const loadSavedFilters = () => {
        if (saveToLocalStorage) {
            try {
                const saved = localStorage.getItem(storageKey);
                return saved ? JSON.parse(saved) : defaultFilters;
            } catch {
                return defaultFilters;
            }
        }
        return defaultFilters;
    };

    const filters = ref(loadSavedFilters());
    const isFiltering = ref(false);

    // Déterminer s'il y a des filtres actifs
    const hasActiveFilters = computed(() => {
        return Object.values(filters.value).some(value => {
            if (Array.isArray(value)) return value.length > 0;
            if (typeof value === 'string') return value.trim() !== '';
            if (typeof value === 'boolean') return value === true;
            return value !== null && value !== undefined && value !== '';
        });
    });

    // Compter les filtres actifs
    const activeFiltersCount = computed(() => {
        return Object.entries(filters.value).filter(([key, value]) => {
            if (Array.isArray(value)) return value.length > 0;
            if (typeof value === 'string') return value.trim() !== '';
            if (typeof value === 'boolean') return value === true;
            return value !== null && value !== undefined && value !== '';
        }).length;
    });

    // Appliquer les filtres avec debounce
    const applyFilters = debounce(() => {
        isFiltering.value = true;

        // Sauvegarder dans localStorage
        if (saveToLocalStorage) {
            localStorage.setItem(storageKey, JSON.stringify(filters.value));
        }

        // Nettoyer les filtres vides
        const cleanFilters = Object.entries(filters.value).reduce((acc, [key, value]) => {
            if (Array.isArray(value) && value.length > 0) {
                acc[key] = value;
            } else if (typeof value === 'string' && value.trim() !== '') {
                acc[key] = value;
            } else if (typeof value === 'boolean' && value === true) {
                acc[key] = value;
            } else if (value !== null && value !== undefined && value !== '') {
                acc[key] = value;
            }
            return acc;
        }, {});

        router.get(route(routeName), cleanFilters, {
            preserveScroll,
            preserveState,
            onFinish: () => {
                isFiltering.value = false;
            }
        });
    }, debounceMs);

    // Réinitialiser tous les filtres
    const clearFilters = () => {
        filters.value = { ...defaultFilters };
        if (saveToLocalStorage) {
            localStorage.removeItem(storageKey);
        }
        applyFilters();
    };

    // Réinitialiser un filtre spécifique
    const clearFilter = (filterName) => {
        if (Array.isArray(filters.value[filterName])) {
            filters.value[filterName] = [];
        } else if (typeof filters.value[filterName] === 'boolean') {
            filters.value[filterName] = false;
        } else {
            filters.value[filterName] = '';
        }
        applyFilters();
    };

    // Définir un filtre spécifique
    const setFilter = (filterName, value) => {
        filters.value[filterName] = value;
        applyFilters();
    };

    // Définir plusieurs filtres en une fois
    const setFilters = (newFilters) => {
        filters.value = { ...filters.value, ...newFilters };
        applyFilters();
    };

    // Toggle un filtre booléen
    const toggleFilter = (filterName) => {
        filters.value[filterName] = !filters.value[filterName];
        applyFilters();
    };

    // Ajouter/retirer une valeur d'un filtre tableau
    const toggleArrayFilter = (filterName, value) => {
        if (!Array.isArray(filters.value[filterName])) {
            filters.value[filterName] = [];
        }

        const index = filters.value[filterName].indexOf(value);
        if (index > -1) {
            filters.value[filterName].splice(index, 1);
        } else {
            filters.value[filterName].push(value);
        }
        applyFilters();
    };

    // Obtenir les filtres actifs formatés pour affichage
    const getActiveFiltersDisplay = () => {
        return Object.entries(filters.value)
            .filter(([key, value]) => {
                if (Array.isArray(value)) return value.length > 0;
                if (typeof value === 'string') return value.trim() !== '';
                if (typeof value === 'boolean') return value === true;
                return value !== null && value !== undefined && value !== '';
            })
            .map(([key, value]) => ({
                key,
                value,
                label: formatFilterLabel(key, value)
            }));
    };

    // Formater le label d'un filtre
    const formatFilterLabel = (key, value) => {
        const labels = {
            search: 'Recherche',
            status: 'Statut',
            type: 'Type',
            date_from: 'Du',
            date_to: 'Au',
            min_amount: 'Montant min',
            max_amount: 'Montant max',
            sort: 'Tri',
            order: 'Ordre'
        };

        const label = labels[key] || key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());

        if (Array.isArray(value)) {
            return `${label}: ${value.join(', ')}`;
        } else if (typeof value === 'boolean') {
            return label;
        } else {
            return `${label}: ${value}`;
        }
    };

    // Exporter les filtres actifs
    const exportFilters = () => {
        return JSON.stringify(filters.value);
    };

    // Importer des filtres
    const importFilters = (filtersJson) => {
        try {
            const imported = JSON.parse(filtersJson);
            filters.value = { ...defaultFilters, ...imported };
            applyFilters();
            return true;
        } catch {
            return false;
        }
    };

    return {
        filters,
        isFiltering,
        hasActiveFilters,
        activeFiltersCount,
        applyFilters,
        clearFilters,
        clearFilter,
        setFilter,
        setFilters,
        toggleFilter,
        toggleArrayFilter,
        getActiveFiltersDisplay,
        exportFilters,
        importFilters
    };
}
