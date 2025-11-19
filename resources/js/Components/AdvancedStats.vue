<template>
    <div class="advanced-stats">
        <div class="stats-grid">
            <div
                v-for="stat in stats"
                :key="stat.id"
                :class="['stat-card', `stat-${stat.variant}`]"
                :style="{ animationDelay: `${stat.index * 100}ms` }"
            >
                <div class="stat-icon-wrapper">
                    <div class="stat-icon">
                        <i :class="`bi ${stat.icon}`"></i>
                    </div>
                    <div v-if="stat.trend" :class="['stat-trend', `trend-${stat.trend > 0 ? 'up' : 'down'}`]">
                        <i :class="`bi bi-arrow-${stat.trend > 0 ? 'up' : 'down'}`"></i>
                        <span>{{ Math.abs(stat.trend) }}%</span>
                    </div>
                </div>

                <div class="stat-content">
                    <div class="stat-label">{{ stat.label }}</div>
                    <div class="stat-value">
                        <AnimatedNumber :value="stat.value" :format="stat.format" />
                    </div>
                    <div v-if="stat.subtitle" class="stat-subtitle">
                        {{ stat.subtitle }}
                    </div>
                </div>

                <div v-if="stat.chart" class="stat-mini-chart">
                    <svg viewBox="0 0 100 30" class="sparkline">
                        <polyline
                            :points="generateSparkline(stat.chartData)"
                            fill="none"
                            :stroke="getChartColor(stat.variant)"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    stats: {
        type: Array,
        required: true
    }
});

// Composant AnimatedNumber inline
const AnimatedNumber = {
    props: {
        value: {
            type: Number,
            required: true
        },
        format: {
            type: String,
            default: 'number'
        }
    },
    setup(props) {
        const displayValue = ref(0);
        const duration = 1000;

        onMounted(() => {
            const startTime = Date.now();
            const startValue = 0;
            const endValue = props.value;

            const animate = () => {
                const now = Date.now();
                const progress = Math.min((now - startTime) / duration, 1);

                // Easing function (easeOutQuad)
                const easeProgress = 1 - Math.pow(1 - progress, 3);

                displayValue.value = startValue + (endValue - startValue) * easeProgress;

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    displayValue.value = endValue;
                }
            };

            animate();
        });

        const formattedValue = computed(() => {
            const val = displayValue.value;

            switch (props.format) {
                case 'currency':
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'EUR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(val);

                case 'percent':
                    return `${val.toFixed(1)}%`;

                case 'compact':
                    if (val >= 1000000) {
                        return `${(val / 1000000).toFixed(1)}M`;
                    } else if (val >= 1000) {
                        return `${(val / 1000).toFixed(1)}K`;
                    }
                    return val.toFixed(0);

                default:
                    return val.toFixed(0);
            }
        });

        return { formattedValue };
    },
    template: '<span>{{ formattedValue }}</span>'
};

const generateSparkline = (data) => {
    if (!data || data.length === 0) return '';

    const width = 100;
    const height = 30;
    const padding = 2;

    const max = Math.max(...data);
    const min = Math.min(...data);
    const range = max - min || 1;

    return data
        .map((value, index) => {
            const x = (index / (data.length - 1)) * width;
            const y = height - padding - ((value - min) / range) * (height - padding * 2);
            return `${x},${y}`;
        })
        .join(' ');
};

const getChartColor = (variant) => {
    const colors = {
        primary: '#3b82f6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#8b5cf6'
    };
    return colors[variant] || colors.primary;
};
</script>

<style scoped>
.advanced-stats {
    width: 100%;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    animation: slideInUp 0.5s ease-out forwards;
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

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--stat-color-1), var(--stat-color-2));
}

.stat-card.stat-primary {
    --stat-color-1: #3b82f6;
    --stat-color-2: #1d4ed8;
}

.stat-card.stat-success {
    --stat-color-1: #10b981;
    --stat-color-2: #059669;
}

.stat-card.stat-warning {
    --stat-color-1: #f59e0b;
    --stat-color-2: #d97706;
}

.stat-card.stat-danger {
    --stat-color-1: #ef4444;
    --stat-color-2: #dc2626;
}

.stat-card.stat-info {
    --stat-color-1: #8b5cf6;
    --stat-color-2: #6d28d9;
}

.stat-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.stat-trend i {
    font-size: 14px;
}

.trend-up {
    background: #d1fae5;
    color: #065f46;
}

.trend-down {
    background: #fee2e2;
    color: #991b1b;
}

.stat-content {
    margin-bottom: 16px;
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 500;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
}

.stat-subtitle {
    font-size: 13px;
    color: #9ca3af;
    margin-top: 6px;
}

.stat-mini-chart {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.sparkline {
    width: 100%;
    height: 30px;
}

/* Dark Mode */
:global(.dark) .stat-card {
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

:global(.dark) .stat-mini-chart {
    border-top-color: #374151;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        font-size: 22px;
    }

    .stat-value {
        font-size: 28px;
    }
}
</style>
