<template>
    <div class="loading-container" :class="sizeClass">
        <div v-if="type === 'spinner'" class="spinner" :class="variantClass">
            <div class="spinner-inner"></div>
        </div>

        <div v-else-if="type === 'dots'" class="dots" :class="variantClass">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>

        <div v-else-if="type === 'pulse'" class="pulse" :class="variantClass">
            <div class="pulse-ring"></div>
            <div class="pulse-ring"></div>
            <div class="pulse-ring"></div>
        </div>

        <div v-else-if="type === 'bars'" class="bars" :class="variantClass">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>

        <div v-if="text" class="loading-text">{{ text }}</div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'spinner',
        validator: (value) => ['spinner', 'dots', 'pulse', 'bars'].includes(value)
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'white'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
    },
    text: {
        type: String,
        default: ''
    }
});

const variantClass = computed(() => `loading-${props.variant}`);
const sizeClass = computed(() => `loading-${props.size}`);
</script>

<style scoped>
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

/* Sizes */
.loading-sm .spinner { width: 24px; height: 24px; }
.loading-md .spinner { width: 40px; height: 40px; }
.loading-lg .spinner { width: 56px; height: 56px; }
.loading-xl .spinner { width: 72px; height: 72px; }

.loading-sm .dots .dot { width: 6px; height: 6px; }
.loading-md .dots .dot { width: 10px; height: 10px; }
.loading-lg .dots .dot { width: 14px; height: 14px; }
.loading-xl .dots .dot { width: 18px; height: 18px; }

/* Spinner */
.spinner {
    position: relative;
}

.spinner-inner {
    width: 100%;
    height: 100%;
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loading-success .spinner-inner {
    border-top-color: #10b981;
}

.loading-warning .spinner-inner {
    border-top-color: #f59e0b;
}

.loading-danger .spinner-inner {
    border-top-color: #ef4444;
}

.loading-white .spinner-inner {
    border-color: rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Dots */
.dots {
    display: flex;
    gap: 8px;
}

.dot {
    border-radius: 50%;
    background: #3b82f6;
    animation: dotPulse 1.4s infinite ease-in-out;
}

.loading-success .dot {
    background: #10b981;
}

.loading-warning .dot {
    background: #f59e0b;
}

.loading-danger .dot {
    background: #ef4444;
}

.loading-white .dot {
    background: #fff;
}

.dot:nth-child(1) {
    animation-delay: -0.32s;
}

.dot:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes dotPulse {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Pulse */
.pulse {
    position: relative;
    width: 40px;
    height: 40px;
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid #3b82f6;
    border-radius: 50%;
    opacity: 0;
    animation: pulseRing 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}

.loading-success .pulse-ring {
    border-color: #10b981;
}

.loading-warning .pulse-ring {
    border-color: #f59e0b;
}

.loading-danger .pulse-ring {
    border-color: #ef4444;
}

.loading-white .pulse-ring {
    border-color: #fff;
}

.pulse-ring:nth-child(2) {
    animation-delay: 0.5s;
}

.pulse-ring:nth-child(3) {
    animation-delay: 1s;
}

@keyframes pulseRing {
    0% {
        transform: scale(0.33);
        opacity: 1;
    }
    100% {
        transform: scale(1);
        opacity: 0;
    }
}

/* Bars */
.bars {
    display: flex;
    gap: 4px;
    align-items: center;
}

.bar {
    width: 4px;
    height: 20px;
    background: #3b82f6;
    border-radius: 2px;
    animation: barBounce 1.2s infinite ease-in-out;
}

.loading-success .bar {
    background: #10b981;
}

.loading-warning .bar {
    background: #f59e0b;
}

.loading-danger .bar {
    background: #ef4444;
}

.loading-white .bar {
    background: #fff;
}

.bar:nth-child(1) {
    animation-delay: -0.45s;
}

.bar:nth-child(2) {
    animation-delay: -0.3s;
}

.bar:nth-child(3) {
    animation-delay: -0.15s;
}

@keyframes barBounce {
    0%, 40%, 100% {
        transform: scaleY(0.4);
    }
    20% {
        transform: scaleY(1);
    }
}

/* Loading Text */
.loading-text {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.loading-white .loading-text {
    color: #fff;
}
</style>
