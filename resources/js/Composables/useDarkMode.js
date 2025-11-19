import { ref, watch, onMounted } from 'vue';

/**
 * Composable pour la gestion du mode sombre
 * Supporte: auto (système), dark, light
 * Persiste dans localStorage
 */
export function useDarkMode() {
    const STORAGE_KEY = 'comptapro_dark_mode';
    const isDark = ref(false);
    const mode = ref('auto'); // 'auto', 'dark', 'light'

    // Détecter la préférence système
    const getSystemPreference = () => {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    };

    // Charger la préférence sauvegardée
    const loadPreference = () => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const { mode: savedMode } = JSON.parse(saved);
                mode.value = savedMode || 'auto';
            }
        } catch (error) {
            console.error('Error loading dark mode preference:', error);
        }
    };

    // Sauvegarder la préférence
    const savePreference = () => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                mode: mode.value,
                timestamp: Date.now()
            }));
        } catch (error) {
            console.error('Error saving dark mode preference:', error);
        }
    };

    // Appliquer le thème
    const applyTheme = () => {
        let shouldBeDark = false;

        if (mode.value === 'dark') {
            shouldBeDark = true;
        } else if (mode.value === 'light') {
            shouldBeDark = false;
        } else {
            // Mode auto: utiliser la préférence système
            shouldBeDark = getSystemPreference();
        }

        isDark.value = shouldBeDark;

        // Appliquer la classe sur le HTML
        if (shouldBeDark) {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }

        // Mettre à jour la couleur de la barre d'adresse sur mobile
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', shouldBeDark ? '#1f2937' : '#ffffff');
        }

        savePreference();
    };

    // Activer le mode sombre
    const enableDark = () => {
        mode.value = 'dark';
        applyTheme();
        window.$toast?.success('Mode sombre activé', 'Thème');
    };

    // Activer le mode clair
    const enableLight = () => {
        mode.value = 'light';
        applyTheme();
        window.$toast?.success('Mode clair activé', 'Thème');
    };

    // Activer le mode auto (système)
    const enableAuto = () => {
        mode.value = 'auto';
        applyTheme();
        window.$toast?.success('Mode automatique activé', 'Thème');
    };

    // Toggle entre dark et light
    const toggle = () => {
        if (mode.value === 'dark') {
            enableLight();
        } else {
            enableDark();
        }
    };

    // Définir le mode
    const setMode = (newMode) => {
        if (['auto', 'dark', 'light'].includes(newMode)) {
            mode.value = newMode;
            applyTheme();
        }
    };

    // Écouter les changements de préférence système
    const setupSystemListener = () => {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            // Listener moderne
            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', (e) => {
                    if (mode.value === 'auto') {
                        applyTheme();
                    }
                });
            } else if (mediaQuery.addListener) {
                // Fallback pour navigateurs anciens
                mediaQuery.addListener((e) => {
                    if (mode.value === 'auto') {
                        applyTheme();
                    }
                });
            }
        }
    };

    // Initialisation
    onMounted(() => {
        loadPreference();
        applyTheme();
        setupSystemListener();
    });

    // Watcher pour les changements de mode
    watch(mode, () => {
        applyTheme();
    });

    return {
        isDark,
        mode,
        enableDark,
        enableLight,
        enableAuto,
        toggle,
        setMode,
        applyTheme
    };
}
