<template>
    <transition name="alert">
        <div v-if="show" class="alert-container" :class="[variantClass, sizeClass]">
            <div class="alert-icon" v-if="showIcon">
                <i class="bi" :class="iconClass"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title" v-if="title">{{ title }}</div>
                <div class="alert-message">
                    <slot>{{ message }}</slot>
                </div>
            </div>
            <button v-if="closable" type="button" class="alert-close" @click="close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </transition>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: true
    },
    variant: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'info', 'warning', 'danger'].includes(value)
    },
    title: {
        type: String,
        default: ''
    },
    message: {
        type: String,
        default: ''
    },
    closable: {
        type: Boolean,
        default: true
    },
    showIcon: {
        type: Boolean,
        default: true
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    autoClose: {
        type: Number,
        default: 0 // 0 = no auto close
    }
});

const emit = defineEmits(['close', 'update:show']);

const variantClass = computed(() => `alert-${props.variant}`);
const sizeClass = computed(() => `alert-${props.size}`);

const iconClass = computed(() => {
    const icons = {
        success: 'bi-check-circle-fill',
        info: 'bi-info-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        danger: 'bi-x-circle-fill'
    };
    return icons[props.variant];
});

const close = () => {
    emit('close');
    emit('update:show', false);
};

watch(() => props.show, (newValue) => {
    if (newValue && props.autoClose > 0) {
        setTimeout(() => {
            close();
        }, props.autoClose);
    }
});

onMounted(() => {
    if (props.show && props.autoClose > 0) {
        setTimeout(() => {
            close();
        }, props.autoClose);
    }
});
</script>

<style scoped>
.alert-container {
    display: flex;
    align-items: start;
    gap: 12px;
    padding: 16px;
    border-radius: 12px;
    border: 1px solid;
    transition: all 0.3s ease;
}

/* Sizes */
.alert-sm {
    padding: 12px;
    font-size: 14px;
}

.alert-md {
    padding: 16px;
    font-size: 15px;
}

.alert-lg {
    padding: 20px;
    font-size: 16px;
}

/* Variants */
.alert-success {
    background: #d1fae5;
    border-color: #6ee7b7;
    color: #065f46;
}

.alert-info {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1e40af;
}

.alert-warning {
    background: #fef3c7;
    border-color: #fcd34d;
    color: #92400e;
}

.alert-danger {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #991b1b;
}

.alert-icon {
    flex-shrink: 0;
    font-size: 20px;
}

.alert-success .alert-icon {
    color: #10b981;
}

.alert-info .alert-icon {
    color: #3b82f6;
}

.alert-warning .alert-icon {
    color: #f59e0b;
}

.alert-danger .alert-icon {
    color: #ef4444;
}

.alert-content {
    flex: 1;
    min-width: 0;
}

.alert-title {
    font-weight: 700;
    margin-bottom: 4px;
    font-size: 16px;
}

.alert-message {
    line-height: 1.5;
}

.alert-close {
    flex-shrink: 0;
    background: transparent;
    border: none;
    padding: 4px;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.3s;
    color: inherit;
    opacity: 0.7;
}

.alert-close:hover {
    opacity: 1;
    background: rgba(0, 0, 0, 0.1);
}

/* Transitions */
.alert-enter-active,
.alert-leave-active {
    transition: all 0.3s ease;
}

.alert-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.alert-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
