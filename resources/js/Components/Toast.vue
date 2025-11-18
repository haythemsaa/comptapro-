<template>
    <teleport to="body">
        <div class="toast-container">
            <transition-group name="toast-list">
                <div v-for="toast in toasts" :key="toast.id" 
                     class="toast-item" 
                     :class="`toast-${toast.variant}`">
                    <div class="toast-icon">
                        <i class="bi" :class="getIcon(toast.variant)"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-title" v-if="toast.title">{{ toast.title }}</div>
                        <div class="toast-message">{{ toast.message }}</div>
                    </div>
                    <button class="toast-close" @click="removeToast(toast.id)">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="toast-progress" :style="{ animationDuration: toast.duration + 'ms' }"></div>
                </div>
            </transition-group>
        </div>
    </teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const toasts = ref([]);
let nextId = 1;

const getIcon = (variant) => {
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    return icons[variant] || icons.info;
};

const addToast = (message, variant = 'info', title = '', duration = 5000) => {
    const id = nextId++;
    const toast = {
        id,
        message,
        variant,
        title,
        duration
    };
    
    toasts.value.push(toast);
    
    if (duration > 0) {
        setTimeout(() => {
            removeToast(id);
        }, duration);
    }
    
    return id;
};

const removeToast = (id) => {
    const index = toasts.value.findIndex(t => t.id === id);
    if (index !== -1) {
        toasts.value.splice(index, 1);
    }
};

const clearAll = () => {
    toasts.value = [];
};

// Expose methods to window for global access
onMounted(() => {
    window.$toast = {
        success: (message, title = 'Succès !', duration = 5000) => addToast(message, 'success', title, duration),
        error: (message, title = 'Erreur !', duration = 5000) => addToast(message, 'error', title, duration),
        warning: (message, title = 'Attention !', duration = 5000) => addToast(message, 'warning', title, duration),
        info: (message, title = 'Information', duration = 5000) => addToast(message, 'info', title, duration),
        clear: clearAll
    };
});

defineExpose({
    addToast,
    removeToast,
    clearAll
});
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 80px;
    right: 24px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    pointer-events: none;
}

.toast-item {
    display: flex;
    align-items: start;
    gap: 12px;
    padding: 16px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    pointer-events: all;
    position: relative;
    overflow: hidden;
    min-width: 320px;
}

.toast-success {
    border-left: 4px solid #10b981;
}

.toast-error {
    border-left: 4px solid #ef4444;
}

.toast-warning {
    border-left: 4px solid #f59e0b;
}

.toast-info {
    border-left: 4px solid #3b82f6;
}

.toast-icon {
    flex-shrink: 0;
    font-size: 24px;
}

.toast-success .toast-icon {
    color: #10b981;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-warning .toast-icon {
    color: #f59e0b;
}

.toast-info .toast-icon {
    color: #3b82f6;
}

.toast-content {
    flex: 1;
    min-width: 0;
}

.toast-title {
    font-weight: 700;
    font-size: 15px;
    color: #1f2937;
    margin-bottom: 4px;
}

.toast-message {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
}

.toast-close {
    flex-shrink: 0;
    background: transparent;
    border: none;
    padding: 4px;
    cursor: pointer;
    border-radius: 4px;
    color: #9ca3af;
    transition: all 0.3s;
}

.toast-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: currentColor;
    width: 100%;
    animation: progress linear forwards;
}

.toast-success .toast-progress {
    color: #10b981;
}

.toast-error .toast-progress {
    color: #ef4444;
}

.toast-warning .toast-progress {
    color: #f59e0b;
}

.toast-info .toast-progress {
    color: #3b82f6;
}

@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}

/* Transition */
.toast-list-enter-active {
    animation: slideInRight 0.3s ease;
}

.toast-list-leave-active {
    animation: slideOutRight 0.3s ease;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Mobile */
@media (max-width: 575px) {
    .toast-container {
        right: 12px;
        left: 12px;
        max-width: none;
    }
    
    .toast-item {
        min-width: auto;
    }
}
</style>
