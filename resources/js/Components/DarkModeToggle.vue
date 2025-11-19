<template>
    <div class="dark-mode-toggle">
        <button
            @click="toggleMode"
            class="toggle-button"
            :title="modeTooltip"
        >
            <Transition name="icon-fade" mode="out-in">
                <i v-if="mode === 'light'" key="light" class="bi bi-sun-fill"></i>
                <i v-else-if="mode === 'dark'" key="dark" class="bi bi-moon-stars-fill"></i>
                <i v-else key="auto" class="bi bi-circle-half"></i>
            </Transition>
        </button>

        <!-- Dropdown Menu (optionnel) -->
        <Transition name="dropdown">
            <div v-if="showDropdown" class="mode-dropdown">
                <button
                    @click="setModeAndClose('light')"
                    class="mode-option"
                    :class="{ active: mode === 'light' }"
                >
                    <i class="bi bi-sun"></i>
                    <span>Clair</span>
                    <i v-if="mode === 'light'" class="bi bi-check2 check-icon"></i>
                </button>

                <button
                    @click="setModeAndClose('dark')"
                    class="mode-option"
                    :class="{ active: mode === 'dark' }"
                >
                    <i class="bi bi-moon-stars"></i>
                    <span>Sombre</span>
                    <i v-if="mode === 'dark'" class="bi bi-check2 check-icon"></i>
                </button>

                <button
                    @click="setModeAndClose('auto')"
                    class="mode-option"
                    :class="{ active: mode === 'auto' }"
                >
                    <i class="bi bi-circle-half"></i>
                    <span>Automatique</span>
                    <i v-if="mode === 'auto'" class="bi bi-check2 check-icon"></i>
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useDarkMode } from '@/Composables/useDarkMode';

const props = defineProps({
    showDropdown: {
        type: Boolean,
        default: false
    }
});

const { mode, enableDark, enableLight, enableAuto, toggle } = useDarkMode();

const modeTooltip = computed(() => {
    const tooltips = {
        light: 'Mode clair actif - Cliquer pour passer en mode sombre',
        dark: 'Mode sombre actif - Cliquer pour passer en mode automatique',
        auto: 'Mode automatique actif - Cliquer pour passer en mode clair'
    };
    return tooltips[mode.value] || 'Changer le thème';
});

const toggleMode = () => {
    // Cycle: light → dark → auto → light
    if (mode.value === 'light') {
        enableDark();
    } else if (mode.value === 'dark') {
        enableAuto();
    } else {
        enableLight();
    }
};

const setModeAndClose = (newMode) => {
    if (newMode === 'light') {
        enableLight();
    } else if (newMode === 'dark') {
        enableDark();
    } else {
        enableAuto();
    }
};
</script>

<style scoped>
.dark-mode-toggle {
    position: relative;
}

.toggle-button {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #1f2937;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.toggle-button:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: scale(1.05);
}

.toggle-button:active {
    transform: scale(0.95);
}

.toggle-button i {
    transition: all 0.3s;
}

/* Mode Dropdown */
.mode-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    padding: 8px;
    min-width: 180px;
    z-index: 1000;
}

.mode-option {
    width: 100%;
    padding: 10px 14px;
    border: none;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #4b5563;
    border-radius: 8px;
    transition: all 0.2s;
    text-align: left;
}

.mode-option:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.mode-option.active {
    background: #eff6ff;
    color: #1e40af;
}

.mode-option i:first-child {
    font-size: 18px;
    width: 20px;
    text-align: center;
}

.mode-option span {
    flex: 1;
}

.check-icon {
    color: #10b981;
    font-size: 16px;
    font-weight: bold;
}

/* Transitions */
.icon-fade-enter-active,
.icon-fade-leave-active {
    transition: all 0.2s;
}

.icon-fade-enter-from {
    opacity: 0;
    transform: rotate(-180deg) scale(0.5);
}

.icon-fade-leave-to {
    opacity: 0;
    transform: rotate(180deg) scale(0.5);
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Dark Mode Styles */
:global(.dark) .toggle-button {
    background: #374151;
    border-color: #4b5563;
    color: #f3f4f6;
}

:global(.dark) .toggle-button:hover {
    background: #4b5563;
    border-color: #6b7280;
}

:global(.dark) .mode-dropdown {
    background: #1f2937;
    border-color: #374151;
}

:global(.dark) .mode-option {
    color: #d1d5db;
}

:global(.dark) .mode-option:hover {
    background: #374151;
    color: #f3f4f6;
}

:global(.dark) .mode-option.active {
    background: #1e3a8a;
    color: #93c5fd;
}
</style>
