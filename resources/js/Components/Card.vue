<template>
    <div class="modern-card" :class="[variantClass, { 'card-hover': hover, 'card-clickable': clickable }]" @click="handleClick">
        <div class="card-header" v-if="$slots.header || title">
            <slot name="header">
                <h6 class="card-title">{{ title }}</h6>
            </slot>
            <div class="card-actions" v-if="$slots.actions">
                <slot name="actions"></slot>
            </div>
        </div>

        <div class="card-body" :class="{ 'card-body-padded': padded }">
            <slot></slot>
        </div>

        <div class="card-footer" v-if="$slots.footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'gradient', 'bordered', 'elevated'].includes(value)
    },
    hover: {
        type: Boolean,
        default: true
    },
    clickable: {
        type: Boolean,
        default: false
    },
    padded: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['click']);

const variantClass = computed(() => `card-${props.variant}`);

const handleClick = (event) => {
    if (props.clickable) {
        emit('click', event);
    }
};
</script>

<style scoped>
.modern-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-default {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #f3f4f6;
}

.card-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.card-bordered {
    border: 2px solid #e5e7eb;
}

.card-elevated {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.card-hover:hover {
    transform: translateY(-4px);
}

.card-default.card-hover:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.card-gradient.card-hover:hover {
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
}

.card-bordered.card-hover:hover {
    border-color: #3b82f6;
}

.card-clickable {
    cursor: pointer;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.card-gradient .card-header {
    border-bottom-color: rgba(255, 255, 255, 0.2);
}

.card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}

.card-gradient .card-title {
    color: #fff;
}

.card-actions {
    display: flex;
    gap: 8px;
}

.card-body {
    overflow: hidden;
}

.card-body-padded {
    padding: 24px;
}

.card-footer {
    padding: 16px 24px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    background: #f9fafb;
}

.card-gradient .card-footer {
    background: rgba(255, 255, 255, 0.1);
    border-top-color: rgba(255, 255, 255, 0.2);
}
</style>
