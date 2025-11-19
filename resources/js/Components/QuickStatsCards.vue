<template>
    <div class="quick-stats-grid">
        <div
            v-for="(stat, index) in stats"
            :key="stat.id"
            :class="['quick-stat-card', `stat-${stat.variant}`]"
            :style="{ animationDelay: `${index * 50}ms` }"
        >
            <div class="stat-header">
                <div :class="['stat-icon-container', `bg-${stat.variant}`]">
                    <i :class="`bi ${stat.icon}`"></i>
                </div>
                <div v-if="stat.trend" :class="['stat-trend-badge', `trend-${stat.trend > 0 ? 'up' : 'down'}`]">
                    <i :class="`bi bi-arrow-${stat.trend > 0 ? 'up' : 'down'}-short`"></i>
                    <span>{{ Math.abs(stat.trend) }}%</span>
                </div>
            </div>

            <div class="stat-body">
                <div class="stat-label">{{ stat.label }}</div>
                <div class="stat-value">
                    {{ formatValue(stat.value, stat.format) }}
                </div>
                <div v-if="stat.subtitle" class="stat-subtitle">
                    {{ stat.subtitle }}
                </div>
            </div>

            <div v-if="stat.action" class="stat-footer">
                <a :href="stat.action.url" class="stat-action-link">
                    {{ stat.action.label }}
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Mini Progress Bar -->
            <div v-if="stat.progress !== undefined" class="stat-progress">
                <div
                    class="stat-progress-bar"
                    :style="{ width: `${stat.progress}%` }"
                ></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Array,
        required: true,
        validator: (stats) => {
            return stats.every(stat =>
                stat.hasOwnProperty('id') &&
                stat.hasOwnProperty('label') &&
                stat.hasOwnProperty('value')
            );
        }
    }
});

const formatValue = (value, format) => {
    if (format === 'currency') {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    if (format === 'percent') {
        return `${value}%`;
    }

    if (format === 'number') {
        return new Intl.NumberFormat('fr-FR').format(value);
    }

    if (format === 'compact') {
        if (value >= 1000000) {
            return `${(value / 1000000).toFixed(1)}M`;
        }
        if (value >= 1000) {
            return `${(value / 1000).toFixed(1)}K`;
        }
        return value;
    }

    return value;
};
</script>

<style scoped>
.quick-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.quick-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.4s ease-out forwards;
    opacity: 0;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quick-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--stat-color-1), var(--stat-color-2));
}

.quick-stat-card.stat-primary {
    --stat-color-1: #3b82f6;
    --stat-color-2: #1d4ed8;
}

.quick-stat-card.stat-success {
    --stat-color-1: #10b981;
    --stat-color-2: #059669;
}

.quick-stat-card.stat-warning {
    --stat-color-1: #f59e0b;
    --stat-color-2: #d97706;
}

.quick-stat-card.stat-danger {
    --stat-color-1: #ef4444;
    --stat-color-2: #dc2626;
}

.quick-stat-card.stat-info {
    --stat-color-1: #8b5cf6;
    --stat-color-2: #6d28d9;
}

.quick-stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.stat-icon-container {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
}

.bg-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.bg-success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.bg-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.bg-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.bg-info {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.stat-trend-badge {
    display: flex;
    align-items: center;
    gap: 3px;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
}

.trend-up {
    background: #d1fae5;
    color: #065f46;
}

.trend-down {
    background: #fee2e2;
    color: #991b1b;
}

.stat-body {
    margin-bottom: 14px;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 30px;
    font-weight: 800;
    color: #1f2937;
    line-height: 1.2;
    margin-bottom: 6px;
}

.stat-subtitle {
    font-size: 13px;
    color: #9ca3af;
    margin-top: 4px;
}

.stat-footer {
    padding-top: 12px;
    border-top: 1px solid #f3f4f6;
}

.stat-action-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #3b82f6;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}

.stat-action-link:hover {
    color: #1d4ed8;
    transform: translateX(4px);
}

.stat-action-link i {
    font-size: 16px;
}

.stat-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #f3f4f6;
}

.stat-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--stat-color-1), var(--stat-color-2));
    transition: width 1s ease-out;
}

/* Dark Mode */
:global(.dark) .quick-stat-card {
    background: #1f2937;
}

:global(.dark) .stat-label {
    color: #9ca3af;
}

:global(.dark) .stat-value {
    color: #f3f4f6;
}

:global(.dark) .stat-subtitle {
    color: #6b7280;
}

:global(.dark) .stat-footer {
    border-top-color: #374151;
}

:global(.dark) .stat-progress {
    background: #374151;
}

/* Responsive */
@media (max-width: 768px) {
    .quick-stats-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .quick-stat-card {
        padding: 16px;
    }

    .stat-icon-container {
        width: 44px;
        height: 44px;
        font-size: 20px;
    }

    .stat-value {
        font-size: 26px;
    }
}
</style>
